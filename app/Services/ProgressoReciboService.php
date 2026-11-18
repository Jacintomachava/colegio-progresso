<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgressoReciboService
{
    /**
     * Deve ser chamado dentro da transacção
     * que confirma o pagamento.
     */
    public function emitir(int $pagamentoId): object
    {
        if (DB::transactionLevel() === 0) {
            throw new \RuntimeException(
                'A emissão do recibo exige uma transacção activa.'
            );
        }

        $pagamento = DB::table('progresso_pagamentos')
            ->where('id', $pagamentoId)
            ->lockForUpdate()
            ->first();

        if (!$pagamento) {
            throw ValidationException::withMessages([
                'pagamento' => 'Pagamento não encontrado.',
            ]);
        }

        if ($pagamento->estado !== 'CONFIRMADO') {
            throw ValidationException::withMessages([
                'pagamento' => 'O pagamento ainda não foi confirmado.',
            ]);
        }

        $existente = DB::table('progresso_recibos')
            ->where('pagamento_id', $pagamentoId)
            ->first();

        if ($existente) {
            return $existente;
        }

        $ano = \Carbon\Carbon::parse(
            $pagamento->confirmado_em
        )->year;

        $escolaId = (int) $pagamento->escola_id;

        // Garante que a sequência RC existe.
        // A chave primária composta impede duplicações.
        DB::table('progresso_documento_sequencias')
            ->insertOrIgnore([
                'escola_id' => $escolaId,
                'ano' => $ano,
                'tipo' => 'RC',
                'ultimo' => 0,
            ]);

        $sequencia = DB::table('progresso_documento_sequencias')
            ->where('escola_id', $escolaId)
            ->where('ano', $ano)
            ->where('tipo', 'RC')
            ->lockForUpdate()
            ->first();

        if (!$sequencia) {
            throw new \RuntimeException(
                'Não foi possível obter a sequência do recibo.'
            );
        }

        $proximo = (int) $sequencia->ultimo + 1;

        $numero = sprintf(
            'RC-%06d-%d',
            $proximo,
            $ano
        );

        DB::table('progresso_documento_sequencias')
            ->where('escola_id', $escolaId)
            ->where('ano', $ano)
            ->where('tipo', 'RC')
            ->update([
                'ultimo' => $proximo,
            ]);

        // Valor efectivamente liquidado:
        // meios recebidos - troco + crédito utilizado.
        // O crédito gerado permanece no saldo do aluno.
        $valor = round(
            (float) $pagamento->valor_recebido
            - (float) $pagamento->valor_troco
            - (float) $pagamento->valor_credito_gerado
            + (float) $pagamento->valor_credito_utilizado,
            2
        );

        $reciboId = DB::table('progresso_recibos')
            ->insertGetId([
                'escola_id' => $escolaId,
                'pagamento_id' => $pagamentoId,
                'numero' => $numero,
                'data_emissao' => \Carbon\Carbon::parse(
                    $pagamento->confirmado_em
                )->toDateString(),
                'valor' => number_format($valor, 2, '.', ''),
                'estado' => 'EMITIDO',
                'user_id' => $pagamento->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return DB::table('progresso_recibos')
            ->where('id', $reciboId)
            ->first();
    }
}
