<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProgressoCobrancaMultaService
{
    public function __construct(
        private ProgressoMultaService $multaService
    ) {}

    public function simular(int $cobrancaId, ?string $data = null): array
    {
        $cobranca = DB::table('progresso_cobrancas')
            ->where('id', $cobrancaId)
            ->first();

        if (!$cobranca) {
            throw new InvalidArgumentException(
                'Cobrança não encontrada.'
            );
        }

        if ($cobranca->tipo !== 'PROPINA') {
            return [
                'cobranca_id' => $cobranca->id,
                'multa' => 0.00,
            ];
        }

        if (in_array($cobranca->estado, ['PAGO', 'CANCELADO'])) {
            return [
                'cobranca_id' => $cobranca->id,
                'multa' => 0.00,
            ];
        }

        if (!$cobranca->data_vencimento) {
            throw new InvalidArgumentException(
                'A propina não possui data de vencimento.'
            );
        }

        $multa = $this->multaService->calcular(
            (int) $cobranca->escola_id,
            $cobranca->data_vencimento,
            $data
        );

        return [
            'cobranca_id' => $cobranca->id,
            'multa' => $multa,
        ];
    }
}