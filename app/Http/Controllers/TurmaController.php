<?php

namespace App\Http\Controllers;

use App\Models\AnoAcademico;
use App\Models\Classe;
use App\Models\CicloAno;
use App\Models\DisciplinaCurriculo;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Taxa;
use App\Models\Turma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TurmaController extends Controller
{
    public function index()
    {
        $anoAcademico = AnoAcademico::where('estado', true)->first();
        // $turmas = Turma::where('escola_id', Auth::user()->escola_id)->where('classe_id', '>', 3)->where('ano_academico_id', $anoAcademico->id)->orderBy('classe_id', 'asc')->get()->groupBy('classe_id', 'ano_academico_id');
        $turmas = Turma::select('classe_id', 'ano_academico_id','escola_id')
            ->where('escola_id', Auth::user()->escola_id)
            ->where('classe_id', '>', 3)
            ->where('ano_academico_id', $anoAcademico->id)
            ->groupBy('classe_id', 'ano_academico_id','escola_id')
            ->with(['classe', 'anoAcademico','escola'])
            ->get();


        // Retorna a view com os dados carregados
        return view('parametrizacao.turma.index', [
            'turmas' => $turmas,
        ]);
    }

    public function create()
    {

        $cicloActivo = CicloAno::where('tipo','MATRICULA')->first();
        $anoAcademico = AnoAcademico::where('id', $cicloActivo->ano_academico_id)->first();

        $classes = Classe::orderBy('id', 'desc')->get();
        $taxas = Taxa::orderBy('id', 'desc')->get();
        $periodos = Periodo::orderBy('id', 'desc')->get();
        $escolas = Escola::all();

        // Retorna a view com os dados carregados
        return view('parametrizacao.turma.create', [
            'anoAcademico' => $anoAcademico,
            'classes' => $classes,
            'taxas' => $taxas,
            'periodos' => $periodos,
            'escolas' => $escolas,
        ]);
    }

    public function edit($turmaID)
    {
        $anoAcademico = AnoAcademico::where('estado', true)->first();
        $classes = Classe::orderBy('id', 'desc')->get();
        $periodos = Periodo::orderBy('id', 'desc')->get();
        $escolas = Escola::all();
        $turma = Turma::where('id', $turmaID)->first();

        // Retorna a view com os dados carregados
        return view('parametrizacao.turma.edit', [
            'anoAcademico' => $anoAcademico,
            'classes' => $classes,
            'turma' => $turma,
            'periodos' => $periodos,
            'escolas' => $escolas,
        ]);
    }

    public function alunosTurma($turma)
    {
        $anoAcademico = AnoAcademico::where('estado', true)->first();
        $matriculaAlunoTurmas = Matricula::where('turma_id', $turma)->where('escola_id', Auth::user()->escola_id)->get();
        $turmas = Turma::where('id', $turma)->first();
        $alunosSemTurmas = Matricula::where('turma_id', null)->where('escola_id', Auth::user()->escola_id)->where('classe_id', $turmas->classe_id)->get();

        // Retorna a view com os dados carregados
        return view('parametrizacao.turma.alunosturmas', [
            'anoAcademico' => $anoAcademico,
            'matriculaAlunoTurmas' => $matriculaAlunoTurmas,
            'turmas' => $turmas,
            'alunosSemTurmas' => $alunosSemTurmas,
        ]);
    }

    public function atribuirTurma()
    {
        $anoAcademico = AnoAcademico::where('estado', true)->first();
        $turmas = Turma::where('ano_academico_id', $anoAcademico->id)->where('escola_id', Auth::user()->escola_id)->get();

        // Retorna a view com os dados carregados
        return view('parametrizacao.turma.atribuirTurma', [
            'anoAcademico' => $anoAcademico,
            'turmas' => $turmas,
        ]);
    }

    public function atribuirTurmaAluno(Request $request)
    {
        try {
            DB::beginTransaction();

            $alunos = $request->input('alunos', []);
            $turma = $request->input('turma');
            $ano = $request->input('ano');

            if (!empty($alunos)) {
                $quantidade = count($alunos);

                if (PautaFinalService::decrimentarVagaTurma($turma, $quantidade)) {
                    foreach ($alunos as $alunoID) {
                        $alunoTurma = Matricula::where('ano_academico_id', $ano)->where('aluno_id', $alunoID)->first();
                        $alunoTurma->turma_id = $turma;
                        $alunoTurma->save();
                    }

                    DB::commit();

                    return response()->json(['status' => 1, 'message' => 'Aluno(s) Atribuidos Turma com sucesso!']);
                }

                return response()->json(['status' => 0, 'message' => 'Sem Vaga Nessa Turma']);
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

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $cicloActivo = CicloAno::where('tipo','MATRICULA')->first();
            $anoAcademicoAtivo = AnoAcademico::where('id', $cicloActivo->ano_academico_id)->first();

            // Faturacao da Encomenda
            $turma = new Turma();
            $turma->nome = $request->input('nome');
            $turma->vaga = $request->input('vaga');
            $turma->ano_academico_id = $anoAcademicoAtivo->id; //$request->input('ano');
            $turma->classe_id = $request->input('classe');
            $turma->periodo_id = $request->input('periodo');
            $turma->user_id = Auth::user()->id;
            $turma->escola_id = $request->input('escola');

            if ($turma->save()) {
                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Ano Lectivo Registado com Sucesso!']);
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

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            // Faturacao da Encomenda
            $turma = Turma::where('id', $request->input('turma'))->first();
            $turma->nome = $request->input('nome');
            $turma->vaga = $request->input('vaga');
            $turma->ano_academico_id = $request->input('ano');
            $turma->classe_id = $request->input('classe');
            $turma->periodo_id = $request->input('periodo');
            $turma->user_id = Auth::user()->id;
            $turma->escola_id = $request->input('escola');

            if ($turma->save()) {
                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Turma Actualizado com Sucesso!']);
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

    public function apiTurma($classeID, $escolaId)
    {
        $anoAcademicoAtivo =  AnoAcademico::where('estado', true)->first();

        $turmas = Turma::with('periodo', 'classe')
                         ->where('classe_id', $classeID)
                         ->where('escola_id', $escolaId)   // Pegar turmas dessa escola
                         ->where('vaga', '<>', 0)
                         ->where('ano_academico_id', $anoAcademicoAtivo->id)
                         ->get();

        return response()->json($turmas);
    }

    public function apiDisciplina($turmaID)
    {
        $turma = Turma::where('id', $turmaID)->first();

        if (!$turma) {
            return response()->json(['error' => 'Turma não encontrada'], 404);
        }

        // Disciplinas do currículo
        $disciplinasCurriculo = DisciplinaCurriculo::with('disciplina')
            ->where('classe_id', $turma->classe_id)
            ->where('estado', 1)
            ->get();

        // Disciplinas co-curriculares (independente da classe)
        $disciplinasCoCurriculares = Disciplina::where('co_curricular', 1)->get();

        // Unindo os resultados
        $disciplinas = $disciplinasCurriculo->pluck('disciplina')->merge($disciplinasCoCurriculares);

        return response()->json($disciplinas);
    }

    public function apiAlunoSemTurma($turmaID)
    {
    }
}
