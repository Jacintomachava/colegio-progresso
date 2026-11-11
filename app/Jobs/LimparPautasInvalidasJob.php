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
use Illuminate\Support\Facades\Log;

class LimparPautasInvalidasJob implements ShouldQueue
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
        $anoAtivo = AnoAcademico::where('estado', 1)->first();

        if (!$anoAtivo) {
            Log::error("⚠ Nenhum ano académico ativo encontrado.");
            return;
        }

        $alunos = Matricula::where('ano_academico_id', $anoAtivo->id)
            ->where('desistiu', 0)
            ->get();

        foreach ($alunos as $aluno) {

            $alunoID = $aluno->aluno_id;
            $classeID = $aluno->classe_id;
            $escolaID = $aluno->escola_id;
            $turmaID = $aluno->turma_id;

            // Disciplinas corretas do currículo
            $disciplinasCurriculo = DisciplinaCurriculo::where('classe_id', $classeID)
                ->where('estado', 1)
                ->pluck('disciplina_id')
                ->toArray();

            // Disciplinas atualmente na pauta do aluno
            $disciplinasNaPauta = Pauta::where('aluno_id', $alunoID)
                ->where('ano_academico_id', $anoAtivo->id)
                ->where('escola_id', $escolaID)
                ->pluck('disciplina_id')
                ->toArray();

            // Disciplinas inválidas = tudo que NÃO está no currículo
            $disciplinasInvalidas = array_diff($disciplinasNaPauta, $disciplinasCurriculo);

            // Disciplinas que existem no currículo mas ainda não existem na pauta
            $disciplinasEmFalta = array_diff($disciplinasCurriculo, $disciplinasNaPauta);

            if (count($disciplinasInvalidas) > 0) {

                Pauta::where('aluno_id', $alunoID)
                    ->where('ano_academico_id', $anoAtivo->id)
                    ->where('escola_id', $escolaID)
                    ->whereIn('disciplina_id', $disciplinasInvalidas)
                    ->delete();

                foreach ($disciplinasInvalidas as $disciplinaID) {
                    Log::warning("🚮 Pauta removida: aluno {$aluno->aluno->pessoa->nome}, disciplina ID {$disciplinaID}");
                }
            }

            if (count($disciplinasEmFalta) > 0) {

                // Inserir disciplinas que faltam
                foreach ($disciplinasEmFalta as $disciplinaID) {

                    Pauta::firstOrCreate([
                        'aluno_id'         => $alunoID,
                        'classe_id'        => $classeID,
                        'disciplina_id'    => $disciplinaID,
                        'escola_id'        => $escolaID,
                        'ano_academico_id' => $anoAtivo->id,
                        'turma_id'         => $turmaID,
                    ]);

                    Log::info("➕ Disciplina adicionada: {$aluno->aluno->pessoa->nome}");
                }
            }


        }

        Log::info("✔ Limpeza de pautas inválidas concluída com sucesso.");
    }
}
