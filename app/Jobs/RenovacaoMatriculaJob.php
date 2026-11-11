<?php

namespace App\Jobs;

use App\Models\Aluno;
use App\Models\CicloAno;
use App\Services\MatriculaService;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RenovacaoMatriculaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Buscar configuração atual da RENOVAÇÃO
        |--------------------------------------------------------------------------
        |
        | A tabela ciclo_ano é quem determina:
        |
        | - ano académico da renovação
        | - valor NORMAL
        | - valor GOLD
        |
        */
        $ciclo = CicloAno::where('tipo', 'RENOVACAO')->where('estado', 1)->first();

        /*
        |--------------------------------------------------------------------------
        | 2. Verificar se existe renovação ativa
        |--------------------------------------------------------------------------
        */
        if (!$ciclo) {

            Log::info(
                'RenovacaoMatriculaJob: Renovação não está ativa.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Dados configurados no ciclo
        |--------------------------------------------------------------------------
        */
        $anoAcademicoID = $ciclo->ano_academico_id;
        $valorNormal = $ciclo->valor_renovacao_normal;
        $valorGold = $ciclo->valor_renovacao_gold;

        /*
        |--------------------------------------------------------------------------
        | 4. Validar valores
        |--------------------------------------------------------------------------
        */
        if ($valorNormal <= 0 || $valorGold <= 0) {

            Log::warning(
                'RenovacaoMatriculaJob: Valores de renovação inválidos. '
                .'Normal: '.$valorNormal
                .' | Gold: '.$valorGold
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Buscar somente alunos que precisam renovar
        |--------------------------------------------------------------------------
        |
        | Regras:
        |
        | desistiu = 0
        |
        | E NÃO existe matrícula para o aluno no ano académico
        | configurado em ciclo_ano.
        |
        */
        Aluno::where('desistiu', 0)

            ->whereDoesntHave(
                'matriculas',
                function ($query) use ($anoAcademicoID) {

                    $query->where(
                        'ano_academico_id',
                        $anoAcademicoID
                    );
                }
            )

            ->chunkById(
                100,
                function ($alunos) use (
                    $anoAcademicoID,
                    $valorNormal,
                    $valorGold
                ) {

                    foreach ($alunos as $aluno) {

                        /*
                        |--------------------------------------------------------------------------
                        | 6. Renovar aluno
                        |--------------------------------------------------------------------------
                        |
                        | O Service é responsável por descobrir:
                        |
                        | plano = 1 → NORMAL
                        | plano = 2 → GOLD
                        |
                        | e aplicar o valor correspondente.
                        |
                        */

                        MatriculaService::renovacao(
                            $aluno->id,
                            $anoAcademicoID,
                            $valorNormal,
                            $valorGold
                        );
                    }
                }
            );

        /*
        |--------------------------------------------------------------------------
        | 7. Finalização
        |--------------------------------------------------------------------------
        */
        Log::info(
            'RenovacaoMatriculaJob concluído. '
            .'Ano académico ID: '.$anoAcademicoID
            .' | Normal: '.$valorNormal.' MZN'
            .' | Gold: '.$valorGold.' MZN'
        );
    }
}