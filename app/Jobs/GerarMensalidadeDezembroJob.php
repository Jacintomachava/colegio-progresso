<?php

namespace App\Jobs;

use App\Models\CicloAno;
use App\Models\Matricula;
use App\Services\MensalidadeService;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GerarMensalidadeDezembroJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Ano configurado para mensalidades
        |--------------------------------------------------------------------------
        */
        $ciclo = CicloAno::where('tipo','MENSALIDADE')->where('estado', 1)->first();

        if (!$ciclo) {

            Log::info(
                'GerarMensalidadeDezembroJob: '
                .'Mensalidades não estão ativas.'
            );

            return;
        }

        $anoAcademicoID = $ciclo->ano_academico_id;

        /*
        |--------------------------------------------------------------------------
        | 2. Alunos matriculados no ano e ativos
        |--------------------------------------------------------------------------
        */
        Matricula::where(
                'ano_academico_id',
                $anoAcademicoID
            )
            ->whereHas(
                'aluno',
                function ($query) {

                    $query->where(
                        'desistiu',
                        0
                    );
                }
            )

            ->chunkById(
                100,
                function ($matriculas) use (
                    $anoAcademicoID
                ) {

                    foreach ($matriculas as $matricula) {

                        MensalidadeService::gerarMensalidadeDezembro(
                                        $matricula->aluno_id,
                                        $anoAcademicoID,
                                        true
                                    );
                        
                    }
                }
            );


        Log::info(
            'GerarMensalidadeDezembroJob concluído. '
            .'Ano académico ID: '
            .$anoAcademicoID
        );
    }
}