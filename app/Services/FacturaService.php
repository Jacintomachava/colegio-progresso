<?php

namespace App\Services\Progresso;

use App\Models\ProgressoCobranca;
use App\Models\ProgressoFactura;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FacturaService
{
    /**
     * Deve ser chamado dentro de uma transacção DB.
     */
    public function emitirMatricula(
        ProgressoCobranca $cobranca,
        int $diasVencimento = 10
    ): ProgressoFactura {

        if (!DB::transactionLevel()) {
            throw new RuntimeException(
                'A emissão deve ocorrer dentro de uma transacção.'
            );
        }

        if (!in_array($cobranca->tipo, ['MATRICULA', 'RENOVACAO'])) {
            throw new RuntimeException(
                'A cobrança não corresponde a uma matrícula.'
            );
        }

        $cobranca = ProgressoCobranca::query()
            ->whereKey($cobranca->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($cobranca->facturaItem()->exists()) {
            throw new RuntimeException(
                'Esta cobrança já possui uma factura.'
            );
        }

        $ano = now()->year;

        // Garante a existência do contador.
        DB::table('progresso_documento_sequencias')
            ->insertOrIgnore([
                'escola_id' => $cobranca->escola_id,
                'ano' => $ano,
                'tipo' => 'FT',
                'ultimo' => 0,
            ]);

        $sequencia = DB::table('progresso_documento_sequencias')
            ->where('escola_id', $cobranca->escola_id)
            ->where('ano', $ano)
            ->where('tipo', 'FT')
            ->lockForUpdate()
            ->first();

        $proximo = $sequencia->ultimo + 1;

        DB::table('progresso_documento_sequencias')
            ->where('escola_id', $cobranca->escola_id)
            ->where('ano', $ano)
            ->where('tipo', 'FT')
            ->update(['ultimo' => $proximo]);

        $numero = sprintf(
            'FT-%06d-%d',
            $proximo,
            $ano
        );

        $factura = ProgressoFactura::create([
            'escola_id' => $cobranca->escola_id,
            'aluno_id' => $cobranca->aluno_id,
            'matricula_id' => $cobranca->matricula_id,
            'numero' => $numero,
            'data_emissao' => today(),
            'data_vencimento' => today()->addDays($diasVencimento),
            'subtotal' => $cobranca->valor_original,
            'desconto' => $cobranca->valor_desconto,
            'iva' => 0,
            'total' => $cobranca->valor_total,
            'estado' => 'PENDENTE',
        ]);

        $factura->itens()->create([
            'cobranca_id' => $cobranca->id,
            'descricao' => $cobranca->descricao,
            'quantidade' => 1,
            'preco_unitario' => $cobranca->valor_original,
            'valor_total' => $cobranca->valor_total,
        ]);

        return $factura;
    }
}