<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProgressoCobrancasMatriculaService
{
    /**
     * Executar dentro da transacção da matrícula.
     * A cobrança MATRÍCULA é criada pelo Controller.
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

        /*
        |--------------------------------------------------------------------------
        | 1. IDENTIFICAR TIPO DE INSCRIÇÃO
        |--------------------------------------------------------------------------
        | Classe normal = INTERNO
        | Curso = EXTERNO
        |
        | Utilizar o campo booleano real da tabela classes.
        |--------------------------------------------------------------------------
        */

        $classeNormal = (bool) $classe->classe;

        $mesInicio = (int) ($valores->mes_inicio ?? 0);
        $mesFim = (int) ($valores->mes_fim ?? 0);

        /*
        |--------------------------------------------------------------------------
        | 2. PROPINA CURRICULAR - APENAS INTERNOS
        |--------------------------------------------------------------------------
        */

        if ($classeNormal) {

            $this->validarMeses($mesInicio, $mesFim);

            $valorPropina = round(
                (float) ($valores->valor_propina ?? 0),
                2
            );

            if ($valorPropina < 0) {
                throw new RuntimeException(
                    'O valor da propina não pode ser negativo.'
                );
            }

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
                    descricao: 'Propina - '
                        . $classe->nome
                        . ' - '
                        . $this->nomeMes($mes)
                        . '/'
                        . $ano,
                    ano: $ano,
                    mes: $mes,
                    valorOriginal: $valorPropina,
                    desconto: $desconto,
                    vencimento: $this->vencimento($ano, $mes)
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. ACTIVIDADES - INTERNOS E EXTERNOS
        |--------------------------------------------------------------------------
        |
        | As actividades são cobradas independentemente
        | do tipo de aluno.
        |
        | EXTERNO:
        | - Não paga matrícula
        | - Não paga propina curricular
        | - Não paga inscrição no curso
        | - Paga apenas as actividades seleccionadas
        |--------------------------------------------------------------------------
        */

        foreach ($atividades as $atividade) {

            $periodicidade = strtoupper(
                trim((string) ($atividade->periodicidade ?? 'MENSAL'))
            );

            $tipo = strtoupper(
                trim((string) $atividade->tipo)
            );

            /*
            |--------------------------------------------------------------------------
            | 4. VALIDAR TIPO DE ACTIVIDADE
            |--------------------------------------------------------------------------
            */

            if (!in_array(
                $tipo,
                ['SERVICO', 'AULA_EXTRA', 'CURSO_FERIAS'],
                true
            )) {
                throw new RuntimeException(
                    'Tipo de actividade inválido: ' . $tipo
                );
            }

            $valor = round(
                (float) ($atividade->valor_mensal ?? 0),
                2
            );

            if ($valor < 0) {
                throw new RuntimeException(
                    'O valor da actividade '
                    . $atividade->nome
                    . ' não pode ser negativo.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 5. ACTIVIDADE DE PAGAMENTO ÚNICO
            |--------------------------------------------------------------------------
            */

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
                    vencimento: now()->toDateString()
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | 6. ACTIVIDADE MENSAL
            |--------------------------------------------------------------------------
            */

            if ($periodicidade !== 'MENSAL') {

                throw new RuntimeException(
                    'Periodicidade inválida para a actividade '
                    . $atividade->nome
                );
            }

            $this->validarMeses($mesInicio, $mesFim);

            for ($mes = $mesInicio; $mes <= $mesFim; $mes++) {

                $this->criarCobranca(
                    escolaId: $escolaId,
                    alunoId: $alunoId,
                    matriculaId: $matriculaId,
                    tipo: $tipo,
                    origemId: (int) $atividade->id,
                    descricao: $atividade->nome
                        . ' - '
                        . $this->nomeMes($mes)
                        . '/'
                        . $ano,
                    ano: $ano,
                    mes: $mes,
                    valorOriginal: $valor,
                    desconto: 0,
                    vencimento: $this->vencimento($ano, $mes)
                );
            }
        }
    }


    private function validarMeses(
        int $mesInicio,
        int $mesFim
    ): void {

        if (
            $mesInicio < 1 ||
            $mesFim > 12 ||
            $mesInicio > $mesFim
        ) {
            throw new RuntimeException(
                'Intervalo de meses inválido na configuração.'
            );
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

        $total = round(
            $valorOriginal - $desconto,
            2
        );

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

    private function vencimento(
        int $ano,
        int $mes
    ): string {

        return Carbon::create(
            $ano,
            $mes,
            1
        )->toDateString();
    }
}
