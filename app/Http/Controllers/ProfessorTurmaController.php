<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnoAcademico;
use App\Models\DisciplinaCurriculo;
use App\Models\Turma;
use App\Models\Disciplina;
use App\Models\ProfessorTurma;
use App\Models\Professor;
use App\Models\ProfessorTurmaDisciplina;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProfessorTurmaController extends Controller
{
    public function index()
    {
        $anoAcademico = AnoAcademico::where('estado', true)->first();

        $professorTurmas = ProfessorTurma::orderBy('id', 'desc')->where('ano_academico_id',$anoAcademico->id)->get();
        /*$turmas = Turma::with(['classe', 'escola'])
            ->where('ano_academico_id', $anoAcademico->id)
            ->select('turmas.*')
            ->join(
                DB::raw('(SELECT MIN(id) as id FROM turmas GROUP BY classe_id, escola_id) t2'),
                'turmas.id', '=', 't2.id'
            )
            ->orderBy('escola_id') // primeiro por escola
            ->orderBy('classe_id') // depois por classe
            ->get(); */

        $subQuery = DB::table('turmas')
            ->selectRaw('MIN(id) as id')
            ->where('ano_academico_id', $anoAcademico->id)
            ->groupBy('classe_id', 'escola_id');

        $turmas = Turma::with(['classe', 'escola'])
            ->joinSub($subQuery, 't2', function ($join) {
                $join->on('turmas.id', '=', 't2.id');
            })
            ->orderBy('escola_id')
            ->orderBy('classe_id')
            ->get();


        //$professores = Professor::where('escola_id',Auth::user()->escola_id)->where('estado',1)->get();
        $professores = Professor::where('estado',1)->get();


         // Retorna a view com os dados carregados
         return view('turma.professor.index', [
            'professorTurmas' => $professorTurmas,
            'turmas' => $turmas,
            'professores' => $professores,
        ]);

    }

    public function show($turmaID)
    {
        $turmaProfessor = ProfessorTurma::where('id', $turmaID)->first();
        $turmaProfessorDisciplinas = ProfessorTurmaDisciplina::where('classe_id', $turmaProfessor->turma->classe->id)->where('professor_id',$turmaProfessor->professor_id)->get();

        // Obtém IDs das disciplinas já atribuídas
        $disciplinasAtribuidasIds = $turmaProfessorDisciplinas->pluck('disciplina_id');

        // Disciplinas do currículo que ainda não foram atribuídas
        $disciplinasNaoAtribuidasCurriculo = DisciplinaCurriculo::where('classe_id', $turmaProfessor->turma->classe->id)
            ->whereNotIn('disciplina_id', $disciplinasAtribuidasIds)
            ->with('disciplina')
            ->get()
            ->map(function ($curriculo) {
                return $curriculo->disciplina; // Retorna apenas o objeto da disciplina
            });

        // Disciplinas co-curriculares que ainda não foram atribuídas
        $disciplinasCoCurriculares = Disciplina::where('co_curricular', 1)
            ->whereNotIn('id', $disciplinasAtribuidasIds)
            ->get();

        // Mescla as disciplinas do currículo com as co-curriculares
        $disciplinasNaoAtribuidas = $disciplinasNaoAtribuidasCurriculo->merge($disciplinasCoCurriculares);

        // Retorna a view com os dados carregados
        return view('turma.professor.disciplina.index', [
            'turmaProfessor' => $turmaProfessor,
            'turmaProfessorDisciplinas' => $turmaProfessorDisciplinas,
            'disciplinasNaoAtribuidas' => $disciplinasNaoAtribuidas,
        ]);
    }
    
    public function storeDisciplina(Request $request){

        try {

            DB::beginTransaction();

            $disciplinas = $request->input('disciplinas', []);
            $professor = $request->input('professor');
            $turma = $request->input('turma');
            $professorTurma1 = $request->input('professorTurma');

            $professorTurma = ProfessorTurma::where('id', $professorTurma1)->first();

            if (!empty($disciplinas)) {

                foreach ($disciplinas as $disciplinaId) {
                    ProfessorTurmaDisciplina::create([
                        'disciplina_id' => $disciplinaId,
                        'professor_id' => $professor,
                        'turma_id' => $turma,
                        'ano_academico_id' => $professorTurma->ano_academico_id,
                        'classe_id' => $professorTurma->turma->classe_id,
                        'professor_turma_id' => $professorTurma->id,
                    ]);
                }
    
                DB::commit();
        
                return response()->json(['status' => 1, 'message' => 'Disciplina Atribuido ao professor com Sucesso!']);    

            }
    
            
        } catch (\Exception $e) {

            DB::rollBack();
            //$errorMessage = DatabaseErrorHandler::handle($e);
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function store(Request $request){
        
        try {

         DB::beginTransaction();

            $anoAcademico = AnoAcademico::where('estado', true)->first();
            $turma = Turma::where('id', $request->input('turma'))->first();
            $todasDisciplina = $request->input('todasdisciplina'); 
            $professor = $request->input('professor'); 

            //Faturacao da Encomenda
            $professorTurma = new ProfessorTurma();
            $professorTurma->turma_id = $turma->id;
            $professorTurma->ano_academico_id = $anoAcademico->id;
            $professorTurma->professor_id = $professor;
            $professorTurma->todas_disciplina = $todasDisciplina; 
            $professorTurma->director_turma = $request->input('director'); 
            $professorTurma->user_id = Auth::user()->id;

            if($professorTurma->save()){

                if($todasDisciplina == 1){

                    $disciplinas = $request->input('disciplinas', []);
    
                    foreach ($disciplinas as $disciplinaId) {
                        ProfessorTurmaDisciplina::create([
                            'disciplina_id' => $disciplinaId,
                            'professor_id' => $professor,
                            'turma_id' => $turma->id,
                            'ano_academico_id' => $anoAcademico->id,
                            'classe_id' => $turma->classe_id,
                            'professor_turma_id' => $professorTurma->id,
                        ]);
                    }
    
                    DB::commit();
    
                    return response()->json(['status' => 1, 'message' => 'Turma Atribuido ao professor com Sucesso!']);
    
                }else if($todasDisciplina == 0){
    
                    $disciplinas = DisciplinaCurriculo::where('classe_id', $turma->classe_id)
                                        ->where('estado',1)  
                                        ->get();
    
                    foreach ($disciplinas as $disciplinaId) {
                        ProfessorTurmaDisciplina::create([
                            'disciplina_id' => $disciplinaId->disciplina_id, // Acessando o ID correto
                            'professor_id' => $professor,
                            'turma_id' => $turma->id,
                            'ano_academico_id' => $anoAcademico->id,
                            'classe_id' => $turma->classe_id,
                            'professor_turma_id' => $professorTurma->id,
                        ]);
                    }
    
                    DB::commit();
    
                    return response()->json(['status' => 1, 'message' => 'Turma Atribuido ao professor com Sucesso!']);
                }  

            }  

        } catch (\Exception $e) {

            DB::rollBack();
            //$errorMessage = DatabaseErrorHandler::handle($e);
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function retirarDisciplinaProfessor($turmaProfessorDisciplinaID)
    {

        try {

            // Ativar o ano acadêmico selecionado
            $turmaProfessorDisciplina = ProfessorTurmaDisciplina::findOrFail($turmaProfessorDisciplinaID);
            $turmaProfessorDisciplina->delete();
            

            return response()->json([
                'status' => 'success',
                'message' => 'Disciplina Retirado com sucesso!'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro ao retirar a Disciplina.'.$e->getMessage()
            ]);
        }
    }

    public function retirarTurmaProfessor($turmaProfessorID)
    {

        try {

            DB::beginTransaction();

           // Buscar o registro do ProfessorTurma
            $turmaProfessor = ProfessorTurma::findOrFail($turmaProfessorID);

            // Deletar todas as disciplinas associadas ao ProfessorTurma
            ProfessorTurmaDisciplina::where('professor_id', $turmaProfessor->professor_id)->delete();

            // Deletar o registro do ProfessorTurma
            if($turmaProfessor->delete()){
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Disciplina Retirado com sucesso!'
                ]);
            }


        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro ao retirar a Disciplina.'.$e->getMessage()
            ]);
        }
    }
}
