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

class GerarMensalidadesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Ciclo configurado para MENSALIDADE
        |--------------------------------------------------------------------------
        */
        $ciclo = CicloAno::where('tipo','MENSALIDADE')->where('estado', 1)->first();

        if (!$ciclo) {

            Log::info(
                'GerarMensalidadesJob: '
                .'Mensalidades não estão ativas.'
            );

            return;
        }

        $anoAcademicoID = $ciclo->ano_academico_id;

        /*
        |--------------------------------------------------------------------------
        | 2. Matrículas do ano configurado
        |--------------------------------------------------------------------------
        |
        | Aqui já não buscamos todos os alunos.
        |
        | Só quem realmente está matriculado no ano configurado
        | deve possuir mensalidades.
        |--------------------------------------------------------------------------
        */
        Matricula::where('ano_academico_id', $anoAcademicoID)->where('desistiu', 0)
            ->with(['periodo'])
            ->chunkById(
                100,
                function ($matriculas) {

                    foreach ($matriculas as $matricula) {

                        MensalidadeService::gerar($matricula);
                    }
                }
            );

        Log::info(
            'GerarMensalidadesJob concluído. '
            .'Ano académico ID: '
            .$anoAcademicoID
        );
    }
}