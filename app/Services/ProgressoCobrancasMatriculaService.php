<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProgressoCobrancasMatriculaService
{
    /**
     * Executar dentro da transacção da matrícula.
     *
     * A cobrança da matrícula já foi criada pelo Controller.
     */
    public function gerar(
        int $escolaId,
        int $alunoId,
        int $matriculaId,
        int $ano,
        object $classe,
        object $valores,
        $atividades,
        float $bolsaPercentual = 0
    ): void {

        $mesInicio = (int) $valores->mes_inicio;
        $mesFim = (int) $valores->mes_fim;

        if (
            $mesInicio < 1 ||
            $mesFim > 12 ||
            $mesInicio > $mesFim
        ) {
            throw new RuntimeException(
                'Intervalo de meses inválido na configuração da classe.'
            );
        }

        /*
         * PROPINA CURRICULAR
         */
        $valorPropina = round((float) $valores->valor_propina, 2);

        $desconto = round(
            $valorPropina * $bolsaPercentual / 100,
            2
        );

        for ($mes = $mesInicio; $mes <= $mesFim; $mes++) {

            $this->criarCobranca(
                escolaId: $escolaId,
                alunoId: $alunoId,
                matriculaId: $matriculaId,
                tipo: 'PROPINA',
                origemId: $matriculaId,
                descricao: 'Propina - ' . $classe->nome
                    . ' - ' . $this->nomeMes($mes)
                    . '/' . $ano,
                ano: $ano,
                mes: $mes,
                valorOriginal: $valorPropina,
                desconto: $desconto,
                vencimento: $this->vencimento($ano, $mes)
            );
        }

        /*
         * SERVIÇOS E AULAS EXTRAS
         */
        foreach ($atividades as $atividade) {

            $periodicidade = $atividade->periodicidade ?? 'MENSAL';

            $tipo = $atividade->tipo;

            if (!in_array($tipo, ['SERVICO', 'AULA_EXTRA'], true)) {
                throw new RuntimeException(
                    'Tipo de actividade inválido: ' . $tipo
                );
            }

            $valor = round((float) $atividade->valor_mensal, 2);

            if ($periodicidade === 'UNICA') {

                $this->criarCobranca(
                    escolaId: $escolaId,
                    alunoId: $alunoId,
                    matriculaId: $matriculaId,
                    tipo: $tipo,
                    origemId: (int) $atividade->id,
                    descricao: $atividade->nome . ' - ' . $ano,
                    ano: $ano,
                    mes: null,
                    valorOriginal: $valor,
                    desconto: 0,
                    vencimento: Carbon::create($ano, $mesInicio, 1)
                        ->toDateString()
                );

                continue;
            }

            if ($periodicidade !== 'MENSAL') {
                throw new RuntimeException(
                    'Periodicidade inválida para ' . $atividade->nome
                );
            }

            for ($mes = $mesInicio; $mes <= $mesFim; $mes++) {

                $this->criarCobranca(
                    escolaId: $escolaId,
                    alunoId: $alunoId,
                    matriculaId: $matriculaId,
                    tipo: $tipo,
                    origemId: (int) $atividade->id,
                    descricao: $atividade->nome . ' - '
                        . $this->nomeMes($mes) . '/' . $ano,
                    ano: $ano,
                    mes: $mes,
                    valorOriginal: $valor,
                    desconto: 0,
                    vencimento: $this->vencimento($ano, $mes)
                );
            }
        }
    }

    private function criarCobranca(
        int $escolaId,
        int $alunoId,
        int $matriculaId,
        string $tipo,
        int $origemId,
        string $descricao,
        int $ano,
        ?int $mes,
        float $valorOriginal,
        float $desconto,
        string $vencimento
    ): void {

        // Evita duplicar a mesma cobrança desta matrícula.
        $existente = DB::table('progresso_cobrancas')
            ->where('escola_id', $escolaId)
            ->where('aluno_id', $alunoId)
            ->where('matricula_id', $matriculaId)
            ->where('tipo', $tipo)
            ->where('origem_id', $origemId)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->exists();

        if ($existente) {
            return;
        }

        $total = round($valorOriginal - $desconto, 2);

        DB::table('progresso_cobrancas')->insert([
            'escola_id' => $escolaId,
            'aluno_id' => $alunoId,
            'matricula_id' => $matriculaId,
            'tipo' => $tipo,
            'origem_id' => $origemId,
            'descricao' => $descricao,
            'ano' => $ano,
            'mes' => $mes,
            'valor_original' => $valorOriginal,
            'valor_desconto' => $desconto,
            'valor_iva' => 0,
            'taxa_iva' => 0,
            'valor_total' => $total,
            'data_vencimento' => $vencimento,
            'estado' => 'PENDENTE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function nomeMes(int $mes): string
    {
        return [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ][$mes];
    }

    private function vencimento(int $ano, int $mes): string
    {
        // Provisório: será substituído pela regra de
        // vencimento específica do mês e da escola.
        return Carbon::create($ano, $mes, 1)->toDateString();
    }
}
