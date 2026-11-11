<?php

namespace App\Http\Controllers;

use App\Models\AnoAcademico;
use App\Models\Classe;
use App\Models\Matricula;
use App\Models\HistoricoRegistoNota;
use App\Models\Pauta;
use App\Models\Disciplina;
use App\Models\Professor;
use App\Models\ProfessorTurma;
use App\Models\ProfessorTurmaDisciplina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotasController extends Controller
{
    public function show($classe_ID,$ano)
    {
        $anoAtivo = AnoAcademico::where('ano', $ano)->first();
        //$anoAtivo = AnoAcademico::where('ano', 2025)->first();
        $professor = Professor::where('pessoa_id', Auth::user()->pessoa_id)->first();

        $classeID = $classe_ID;
        $disciplina = 1;

        $minhaClasse = Classe::where('id', $classe_ID)->first();

        // $turmaProfessor = ProfessorTurma::where('turma_id', $turmaID)->first();
        $turmaProfessorDisciplinas = ProfessorTurmaDisciplina::where('classe_id', $classeID)->where('professor_id', $professor->id)->get()->unique('disciplina_id');
        // $pautas = Pauta::where('turma_id', $turmaID)->get();
        $alunosMatriculados = Matricula::where('classe_id', $classeID)->where('ano_academico_id', $anoAtivo->id) ->where('pauta',1)->where('escola_id', Auth::user()->escola_id)->get();

        // Obtenha as disciplinas do professor para a turma
        $turmaProfessorDisciplinasA = ProfessorTurmaDisciplina::where('classe_id', $classeID)
                    ->where('professor_id', $professor->id)
                    ->pluck('disciplina_id'); // Obter apenas os IDs das disciplinas
                    
        // Obtenha as pautas associadas às disciplinas
        $pautas = Pauta::where('classe_id', $classeID)
                    //->whereIn('disciplina_id', $turmaProfessorDisciplinasA) // Filtra pelas disciplinas
                    ->where('disciplina_id',$disciplina)
                    ->where('escola_id', Auth::user()->escola_id)
                    ->where('ano_academico_id', $anoAtivo->id)
                    ->where('pauta',1)
                    ->orderBy('numero', 'asc') 
                    ->get();



        // Manter Dados
        $teste = null;
        

        // Retorna a view com os dados carregados
        return view('turma.professor.notas.show', [
            // 'turmaProfessor' => $turmaProfessor,
            'turmaProfessorDisciplinas' => $turmaProfessorDisciplinas,
            'pautas' => $pautas,
            'anoAtivo' => $anoAtivo,
            'ano' => $ano,
            'alunosMatriculados' => $alunosMatriculados,
            'teste' => $teste,
            'disciplina' => $disciplina,
            'minhaClasse' => $minhaClasse,
        ]);
    }

    ///notas/{classe}/{discplina}/{trimestre}
    public function notas($classe,$disciplina,$trimestre,$ano)
    {

        $classe_ID = $classe;
        $disciplina_ID = $disciplina;
        $trimestre = $trimestre;

        $disciplina_id = $disciplina_ID;

        $anoAtivo = AnoAcademico::where('ano', $ano)->first();
        $professor = Professor::where('pessoa_id', Auth::user()->pessoa_id)->first();
        $classe = Classe::findOrFail($classe_ID);

        $disciplinas = ProfessorTurmaDisciplina::where('classe_id', $classe_ID)
            ->where('professor_id', $professor->id)
            //->where('escola_id', Auth::user()->escola_id)
            ->get()
            ->unique('disciplina_id');


        $nomeDisciplina = Disciplina::findOrFail($disciplina_id);


        $classes = Classe::whereIn('id', function ($query) use ($professor) {
                    $query->select('classe_id')
                        ->from('professor_turma_disciplina')
                        ->where('professor_id', $professor->id)
                        //->where('escola_id', Auth::user()->escola_id)
                        ->distinct(); // <-- evita repetição
                    })->get();

        $alunosMatriculados = Matricula::where('classe_id', $classe_ID)
            ->where('escola_id', Auth::user()->escola_id)
            ->where('ano_academico_id', $anoAtivo->id)
            ->where('pauta',1)
            ->with(['aluno.pessoa'])
            ->get()
            ->sortBy('aluno.pessoa.nome');

        /*$alunosMatriculados = Matricula::where('classe_id', $classe_ID)
                    ->where('escola_id', Auth::user()->escola_id)
                    ->where('ano_academico_id', $anoAtivo->id)
                    ->whereHas('aluno.pautas', function ($query) use ($disciplina_ID, $anoAtivo, $classe_ID) {
                        $query->where('disciplina_id', $disciplina_ID)
                            ->where('ano_academico_id', $anoAtivo->id)
                            ->where('classe_id', $classe_ID);
                    })
                    ->with(['aluno.pessoa'])
                    ->get()
                    ->sortBy('aluno.pessoa.nome'); */

        $pautas = Pauta::where('classe_id', $classe_ID)
            ->where('escola_id', Auth::user()->escola_id)
            ->where('ano_academico_id', $anoAtivo->id)
            ->where('disciplina_id', $disciplina_ID)
             ->where('pauta',1)
            ->get()
            ->unique('aluno_id'); // <-- impede repetição


        return view('notas.index', compact(
            'ano','anoAtivo', 'classe', 'disciplinas', 'alunosMatriculados', 'pautas', 'trimestre','disciplina_id','classes','nomeDisciplina'
        ));

    }

    ///notas/{classe}/{discplina}/{trimestre}
    public function notas1($classe_ID,$disciplina_ID,$trimestre,$ano)
    {


        $anoAtivo = AnoAcademico::where('ano', $ano)->first();
        $professor = Professor::where('pessoa_id', Auth::user()->pessoa_id)->first();
        $classe = Classe::findOrFail($classe_ID);
        $disciplina_id = $disciplina_ID;

        $disciplinas = ProfessorTurmaDisciplina::where('classe_id', $classe_ID)
            ->where('professor_id', $professor->id)
            //->where('escola_id', Auth::user()->escola_id)
            ->get()
            ->unique('disciplina_id');

        $nomeDisciplina = Disciplina::findOrFail($disciplina_id);


        $classes = Classe::whereIn('id', function ($query) use ($professor) {
                    $query->select('classe_id')
                        ->from('professor_turma_disciplina')
                        ->where('professor_id', $professor->id)
                        //->where('escola_id', Auth::user()->escola_id)
                        ->distinct(); // <-- evita repetição
                    })->get();

        $alunosMatriculados = Matricula::where('classe_id', $classe_ID)
            ->where('escola_id', Auth::user()->escola_id)
            ->where('ano_academico_id', $anoAtivo->id)
             ->where('pauta',1)
            ->with(['aluno.pessoa'])
            ->get()
            ->sortBy('aluno.pessoa.nome');

        $pautas = Pauta::where('classe_id', $classe_ID)
            ->where('escola_id', Auth::user()->escola_id)
            ->where('ano_academico_id', $anoAtivo->id)
            ->where('disciplina_id', $disciplina_id)
             ->where('pauta',1)
            ->get()
            ->unique('aluno_id'); // <-- impede repetição



        return view('notas.index', compact(
            'ano','anoAtivo', 'classe', 'disciplinas', 'alunosMatriculados', 'pautas', 'trimestre','disciplina_id','classes','nomeDisciplina'
        ));

    }

    public function update(Request $request)
    {
        $classe_id = $request->classe;
        $disciplina_id = $request->disciplina;
        $trimestre = $request->trimestre;
        $escola_id = Auth::user()->escola_id;
        $ano = $request->ano;

        $anoAtivo = AnoAcademico::where('ano', $ano)->first();

        if (!$classe_id || !$disciplina_id || !$trimestre) {
            return response()->json([
                'status' => 0,
                'message' => 'Dados insuficientes para atualizar as notas.'
            ], 400);
        }

        foreach ($request->notas as $aluno_id => $campos) {
            // busca a pauta existente
            $pauta = Pauta::where('aluno_id', $aluno_id)
                ->where('classe_id', $classe_id)
                ->where('disciplina_id', $disciplina_id)
                ->where('escola_id', $escola_id)
                ->where('ano_academico_id', $anoAtivo->id)
                ->first();

            // se não existir, apenas pula
            if (!$pauta) {
                continue;
            }

            if (isset($campos['numero'])) {

                Pauta::where('aluno_id', $aluno_id)
                    ->where('classe_id', $classe_id)
                    ->where('escola_id', $escola_id)
                    ->where('ano_academico_id', $anoAtivo->id)
                    ->update([
                        'numero' => ($campos['numero'] !== '' && $campos['numero'] !== null)
                            ? $campos['numero']
                            : 0
                    ]);
            }

            // atualiza os campos, substituindo nulos/vazios por 0
            foreach ($campos as $campo => $valor) {
                $pauta->$campo = ($valor !== null && $valor !== '') ? $valor : 0;
            }

            // atualiza apenas os campos que têm valor
            /*foreach ($campos as $campo => $valor) {
                if ($valor !== null && $valor !== '') {
                    $pauta->$campo = $valor;
                }
            }*/

            $pauta->save();
        }

        return response()->json([
            'status' => 1,
            'message' => 'Notas atualizadas com sucesso.'
        ]);
    }


    public function buscar(Request $request)
    {
        $anoAtivo = AnoAcademico::where('estado', 1)->first();
        $professor = Professor::where('pessoa_id', Auth::user()->pessoa_id)->first();

        $minhaClasse = Classe::where('id', $request->input('classe'))->first();

        // $turmaProfessor = ProfessorTurma::where('turma_id', $request->input('turma'))->first();
        $alunosMatriculados = Matricula::where('classe_id', $request->input('classe'))->where('pauta',1)->where('ano_academico_id', $anoAtivo->id)->get();
        $turmaProfessorDisciplinas = ProfessorTurmaDisciplina::where('classe_id', $request->input('classe'))->where('professor_id', $professor->id)->get()->unique('disciplina_id');
        $pautas = Pauta::where('classe_id', $request->input('classe'))
                        ->where('disciplina_id', $request->input('disciplina'))
                        ->where('pauta',1)
                        ->where('ano_academico_id', $anoAtivo->id)
                        ->get();

        // Manter Dados
        $teste = null;
        $disciplina = $request->input('disciplina');

        // Ordenar em ordem Alfabetica
        $pautas = $pautas->unique('aluno_id')->sortBy(function ($pauta) {
            return $pauta->aluno->pessoa->nome ?? '';
        });

        // Retorna a view com os dados carregados
        return view('turma.professor.notas.show', [
            // 'turmaProfessor' => $turmaProfessor,
            'turmaProfessorDisciplinas' => $turmaProfessorDisciplinas,
            'pautas' => $pautas,
            'anoAtivo' => $anoAtivo,
            'ano' => $anoAtivo->ano,
            'alunosMatriculados' => $alunosMatriculados,
            'teste' => $teste,
            'disciplina' => $disciplina,
            'minhaClasse' => $minhaClasse,
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            if ($request->input('aluno') == null) {
                return response()->json(['status' => 0, 'message' => 'Seleciona o Aluno']);
            }

            // Criar Campo
            $nome = $request->input('teste');
            $trimestre = $request->input('trimestre');
            $teste = $nome.''.$trimestre;

            // Dados Actualizacao
            $disciplina = $request->input('disciplina');
            $classe = $request->input('classe');
            $aluno = $request->input('aluno');
            $ano = $request->input('ano');

            $professor = Professor::where('pessoa_id', Auth::user()->pessoa_id)->first();

            $pauta = Pauta::where('ano_academico_id', $ano)
                            ->where('classe_id', $classe)
                            ->where('aluno_id', $aluno)
                            ->where('disciplina_id', $disciplina)
                            ->first();

            if ($pauta == null) {
                return response()->json(['status' => 0, 'message' => 'Aluno Não Inscrito Nessa Disciplina']);
            }

            if ($pauta->activo == 1) {
                $pauta->$teste = $request->input('nota');

                $historicoRegistoNota = new HistoricoRegistoNota();
                $historicoRegistoNota->professor_id = $professor->id;
                $historicoRegistoNota->ano_academico_id = $ano;
                $historicoRegistoNota->aluno_id = $aluno;
                $historicoRegistoNota->classe_id = $classe;
                $historicoRegistoNota->disciplina_id = $disciplina;
                $historicoRegistoNota->trimestre = $trimestre;
                $historicoRegistoNota->teste = $teste;
                $historicoRegistoNota->nota = $request->input('nota');


                if ($pauta->save() && $historicoRegistoNota->save()) {
                    DB::commit();

                    return response()->json(['status' => 1, 'message' => 'Nota Lancada com sucesso!']);
                }
            } else {
                return response()->json(['status' => 0, 'message' => 'Essa Disciplina foi Cancelada']);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // $errorMessage = DatabaseErrorHandler::handle($e);
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
