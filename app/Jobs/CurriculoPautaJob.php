<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AnoAcademico;
use App\Models\Matricula;
use App\Models\DisciplinaCurriculo;
use App\Models\Pauta;
use App\Models\CicloAno;

class CurriculoPautaJob implements ShouldQueue
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
        $cicloAtivo = CicloAno::where('tipo', 'GERAR PAUTA')->first();
        $anoAtivo = AnoAcademico::where('id', $cicloAtivo->ano_academico_id)->first();

        $alunosMatriculados = Matricula::where('ano_academico_id', $anoAtivo->id)->where('desistiu',0)->get();

        foreach ($alunosMatriculados as $alunoMatriculado){
            $this->gerarPauta($alunoMatriculado->aluno_id,$anoAtivo->id,$alunoMatriculado->classe_id,$alunoMatriculado->turma_id,$alunoMatriculado->escola_id);
        }
    }

    /* Esse metodo gera a Pauta anual de cada estudante
     Onde recebe o id de estudante, o Ano Academico, e a TaxaID
     Para preencher mensalidades que devem ser pagos
    */
    public function gerarPauta($alunoID, $anoAcademicoID, $classeID, $turmaID, $escolaID)
    {
        $curriculos = DisciplinaCurriculo::where('classe_id', $classeID)
                            ->where('estado', 1)
                            ->get();

        foreach ($curriculos as $curriculo) {

            $verificarSeJaTem = Pauta::where('aluno_id',$alunoID)->where('ano_academico_id',$anoAcademicoID)->where('escola_id',$escolaID)->where('disciplina_id',$curriculo->disciplina_id)->where('classe_id',$classeID)->first();

            if($verificarSeJaTem===null){

                $pauta = new Pauta();
                $pauta->ano_academico_id = $anoAcademicoID;
                $pauta->disciplina_id = $curriculo->disciplina_id;
                $pauta->classe_id = $classeID;
                $pauta->aluno_id = $alunoID;
                $pauta->turma_id = $turmaID;
                $pauta->escola_id = $escolaID;
                $pauta->save();

                \Log::info('Aluno: '.$pauta->aluno->pessoa->nome
                            .' Adicionado a Disciplina: '.$curriculo->disciplina->nome
                            .' na '.$pauta->classe->nome);


            }

        }
    }
}
