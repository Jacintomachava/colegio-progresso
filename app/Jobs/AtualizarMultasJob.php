<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Aluno;
use Carbon\Carbon;
use App\Models\Mensalidade;
use App\Models\Matricula;
use App\Services\MultaService;
use App\Services\MatriculaService;

class AtualizarMultasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $alunos = Aluno::where('desistiu',0)->get();

        \Log::info("Verificar Multa dos alunos ");

        
            foreach ($alunos as $aluno) {
                MultaService::getMulta($aluno->id);

            } 
                
        /*
            foreach ($alunos as $aluno) {
                
                $matricularAluno = Matricula::where('ano_academico_id', 3)->where('aluno_id', $aluno->id)->first();

                if($matricularAluno==null){
                    
                    MatriculaService::renovacao($aluno->id,3);
                    \Log::info("Verificar Renovacao");
                }
            } 

        */

        

    }

}
