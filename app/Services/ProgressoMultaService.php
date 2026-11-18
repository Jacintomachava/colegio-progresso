<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProgressoMultaService
{
    /**
     * Calcula a multa acumulada até à data indicada.
     *
     * Regra:
     * - Dias 6 a 15 do mês do vencimento: 25 MT/dia
     * - Dia 16 até ao fim desse mês: 40 MT/dia
     * - Dia 1 do mês seguinte em diante: 60 MT/dia
     *
     * A multa só começa depois da data de vencimento.
     */
    public function calcular(
        int $escolaId,
        string $dataVencimento,
        ?string $dataCalculo = null
    ): float {
        $vencimento = Carbon::parse($dataVencimento)->startOfDay();

        $data = $dataCalculo
            ? Carbon::parse($dataCalculo)->startOfDay()
            : Carbon::today();

        if ($data->lte($vencimento)) {
            return 0.00;
        }

        $regras = DB::table('progresso_regras_multas')
            ->where('escola_id', $escolaId)
            ->where('activo', 1)
            ->orderBy('mes_atraso')
            ->orderBy('dia_inicio')
            ->get();

        if ($regras->isEmpty()) {
            throw new InvalidArgumentException(
                'Não existem regras de multas configuradas para esta escola.'
            );
        }

        $inicioMesVencimento = $vencimento->copy()->startOfMonth();
        $inicioMesSeguinte = $vencimento->copy()->addMonthNoOverflow()->startOfMonth();

        $totalCentavos = 0;

        // Iteração por dia para respeitar cada escalão.
        // O primeiro dia possível é o dia seguinte ao vencimento.
        $dia = $vencimento->copy()->addDay();

        while ($dia->lte($data)) {

            if ($dia->lt($inicioMesSeguinte)) {

                // Mês de vencimento: escalões 6-15 e 16-fim.
                $regra = $regras->first(function ($item) use ($dia) {
                    return (int) $item->mes_atraso === 1
                        && $dia->day >= (int) $item->dia_inicio
                        && $dia->day <= (int) $item->dia_fim;
                });

            } else {

                // Mês seguinte e posteriores: 60 MT/dia,
                // segundo a regra operacional confirmada.
                $regra = $regras->first(function ($item) use ($dia) {
                    return (int) $item->mes_atraso === 2
                        && $dia->day >= (int) $item->dia_inicio
                        && $dia->day <= (int) $item->dia_fim;
                });
            }

            if (!$regra) {
                throw new InvalidArgumentException(
                    'Regra de multa não encontrada para ' .
                    $dia->format('Y-m-d')
                );
            }

            // Converter para centavos para evitar erros de float
            // na acumulação de valores monetários.
            $totalCentavos += (int) round(
                (float) $regra->valor_diario * 100
            );

            $dia->addDay();
        }

        return $totalCentavos / 100;
    }

    /**
     * Simula a multa de uma cobrança.
     * Não altera a base de dados.
     */
    public function simularCobranca(
        int $cobrancaId,
        ?string $dataCalculo = null
    ): array {
        $cobranca = DB::table('progresso_cobrancas')
            ->where('id', $cobrancaId)
            ->first();

        if (!$cobranca) {
            throw new InvalidArgumentException(
                'Cobrança não encontrada.'
            );
        }

        if (!$cobranca->data_vencimento) {
            throw new InvalidArgumentException(
                'Cobrança sem data de vencimento.'
            );
        }

        // Uma cobrança já liquidada não deve continuar
        // a gerar multas novas.
        $multa = in_array($cobranca->estado, ['PAGO', 'CANCELADO'])
            ? 0.00
            : $this->calcular(
                (int) $cobranca->escola_id,
                $cobranca->data_vencimento,
                $dataCalculo
            );

        return [
            'cobranca_id' => $cobranca->id,
            'tipo' => $cobranca->tipo,
            'descricao' => $cobranca->descricao,
            'data_vencimento' => $cobranca->data_vencimento,
            'valor_original' => (float) $cobranca->valor_total,
            'multa_simulada' => $multa,
            'total_simulado' => round(
                (float) $cobranca->valor_total + $multa,
                2
            ),
        ];
    }
}
