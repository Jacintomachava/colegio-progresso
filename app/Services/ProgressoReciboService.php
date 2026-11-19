<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

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

        // Evitar emissão duplicada.
        $existente = DB::table('progresso_recibos')
            ->where('pagamento_id', $pagamentoId)
            ->first();

        if ($existente) {
            return $existente;
        }

        $ano = Carbon::parse(
            $pagamento->confirmado_em
        )->year;

        $escolaId = (int) $pagamento->escola_id;

        /*
        |--------------------------------------------------------------------------
        | NUMERAÇÃO DO RECIBO
        |--------------------------------------------------------------------------
        | Mantemos a sequência RC já existente.
        | Apenas alteramos a apresentação para RT.
        |
        | Exemplo:
        | RC-000007-2026 -> próximo RT260008
        |--------------------------------------------------------------------------
        */

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

        // Novo formato: RT260008
        $numero = sprintf(
            'RC%02d%04d',
            $ano % 100,
            $proximo
        );

        // Verificação adicional contra duplicações.
        $numeroExistente = DB::table('progresso_recibos')
            ->where('escola_id', $escolaId)
            ->where('numero', $numero)
            ->exists();

        if ($numeroExistente) {
            throw new \RuntimeException(
                "O número de recibo {$numero} já existe."
            );
        }

        DB::table('progresso_documento_sequencias')
            ->where('escola_id', $escolaId)
            ->where('ano', $ano)
            ->where('tipo', 'RC')
            ->update([
                'ultimo' => $proximo,
            ]);

        /*
        |--------------------------------------------------------------------------
        | VALOR DO RECIBO
        |--------------------------------------------------------------------------
        */

        $valor = round(
            (float) $pagamento->valor_recebido
            - (float) $pagamento->valor_troco
            - (float) $pagamento->valor_credito_gerado
            + (float) $pagamento->valor_credito_utilizado,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | REGISTAR RECIBO
        |--------------------------------------------------------------------------
        */

        $reciboId = DB::table('progresso_recibos')
            ->insertGetId([
                'escola_id' => $escolaId,
                'pagamento_id' => $pagamentoId,
                'numero' => $numero,
                'data_emissao' => Carbon::parse(
                    $pagamento->confirmado_em
                )->toDateString(),
                'valor' => number_format(
                    $valor,
                    2,
                    '.',
                    ''
                ),
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
