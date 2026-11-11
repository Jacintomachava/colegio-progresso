<?php

namespace App\Http\Controllers;

use App\Models\AnoAcademico;
use App\Models\Escola;
use App\Models\Funcionario;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Professor;
use App\Models\ProfessorTurma;
use App\Models\Curriculo;
use App\Models\ProfessorTurmaDisciplina;
use App\Models\Provincia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfessorController extends Controller
{
    public function index()
    {
        $professores = Professor::where('escola_id', Auth::user()->escola_id)->where('estado',1)->orderBy('id', 'desc')->get();

        // Retorna a view com os dados carregados
        return view('professor.index', [
            'professores' => $professores,
        ]);
    }

    public function create()
    {
        $provincias = Provincia::all();
        $escolas = Escola::all();

        // Retorna a view com os dados carregados
        return view('professor.create', [
            'provincias' => $provincias,
            'escolas' => $escolas,
        ]);
    }

    public function edit($professorCodigo)
    {
        $provincias = Provincia::all();
        $escolas = Escola::all();
        $professor = Professor::where('codigo', $professorCodigo)->first();
        $funcionario = Funcionario::where('codigo', $professorCodigo)->first();

        // Retorna a view com os dados carregados
        return view('professor.edit', [
            'provincias' => $provincias,
            'escolas' => $escolas,
            'professor' => $professor,
            'funcionario' => $funcionario,
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $current_year = Carbon::now()->year;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);
            $totalProfessor = Professor::all();
            $escola = Escola::where('id', $request->input('escola'))->first();
            $codigoProfessor = $escola->codigo.'.P'.str_pad(count($totalProfessor) + 1, 3, '0', STR_PAD_LEFT).'.'.$year_last_two_digits;

            // Registo de Aluno
            $pessoaProfessor = new Pessoa();
            $pessoaProfessor->nome = $request->input('nome');
            $pessoaProfessor->data_nascimento = $request->input('data_nascimento');
            // $pessoaAluno->avatar = $request->input('classe');  //Outra forna de enviar Imagem
            $pessoaProfessor->genero = $request->input('genero');
            $pessoaProfessor->tipo_documento = $request->input('tipo_documento');
            $pessoaProfessor->numero_documento = $request->input('numero_documento');
            $pessoaProfessor->endereco = $request->input('endereco');
            $pessoaProfessor->naturalidade = $request->input('naturalidade');
            $pessoaProfessor->email = $request->input('email');
            $pessoaProfessor->telefone = $request->input('telefone');
            $pessoaProfessor->nuit = $request->input('nuit');
            $pessoaProfessor->nivel_academico = $request->input('nivel_academico');

            if ($pessoaProfessor->save()) {
                // Registar Encarregado
                $professor = new Professor();
                $professor->codigo = $codigoProfessor;
                $professor->pessoa_id = $pessoaProfessor->id;
                $professor->escola_id = Auth::user()->escola_id;

                // Registar Aluno Como Utilizador
                $userProefessor = new User();
                $userProefessor->nome = $request->input('nome');
                $userProefessor->codigo = $codigoProfessor;  // Forma de ter codigo estudante
                $userProefessor->password = bcrypt($request->input('telefone'));
                $userProefessor->pessoa_id = $pessoaProfessor->id;
                $userProefessor->escola_id = Auth::user()->escola_id;
                $userProefessor->funcionario = true;
                $userProefessor->email = $request->input('email');
                $userProefessor->telefone = $request->input('telefone');

                // Registar Encarregado
                $funcionario = new Funcionario();
                $funcionario->codigo = $codigoProfessor;
                $funcionario->pessoa_id = Auth::user()->escola_id;
                $funcionario->cargo = 'Professor';
                $funcionario->salario = $request->input('salario');
                $funcionario->area = $request->input('area');
                $funcionario->data_inicio_funcoes = $request->input('data_inicio_funcoes');
                $funcionario->escola_id = $request->input('escola');
                $funcionario->provincia_id = $request->input('provincia');

                if ($request->hasFile('imagem')) {
                    $file = $request->file('imagem');
                    // Obter a extensão original do arquivo
                    $extension = $file->getClientOriginalExtension();
                    // Gerar um nome único para o arquivo mantendo a extensão original
                    $fileName = $codigoProfessor.'.'.$extension;
                    // Caminho onde a imagem será armazenada
                    $path = public_path('avatar');
                    // Salvar a imagem no diretório especificado
                    $file->move($path, $fileName);
                    // Atualizar o campo avatar no banco de dados
                    $userProefessor->avatar = $file;
                    $funcionario->avatar = $fileName;
                }

                if ($professor->save() && $userProefessor->save() && $funcionario->save()) {
                    $userProefessor->assignRole('Professor');

                    DB::commit();

                    return response()->json(['status' => 1, 'message' => 'Professor Cadastrado com Sucesso!']);
                }
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

            $current_year = Carbon::now()->year;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);
            $totalProfessor = Professor::all();
            $escola = Escola::where('id', $request->input('escola'))->first();
            $codigoProfessor = $escola->codigo.'.P'.str_pad(count($totalProfessor) + 1, 4, '0', STR_PAD_LEFT).'.'.$year_last_two_digits;

            if ($request->input('codigo') == null) {
                return response()->json(['status' => 0, 'message' => 'O codigo do Professor é obrigatorio!']);
            }

            // Actualizar Professor
            $professor = Professor::where('codigo', $request->input('codigo'))->first();

            // Actualizar Pessoa
            $pessoaProfessor = Pessoa::where('id', $professor->pessoa_id)->first();
            $pessoaProfessor->nome = $request->input('nome');
            $pessoaProfessor->data_nascimento = $request->input('data_nascimento');
            // $pessoaAluno->avatar = $request->input('classe');  //Outra forna de enviar Imagem
            $pessoaProfessor->genero = $request->input('genero');
            $pessoaProfessor->tipo_documento = $request->input('tipo_documento');
            $pessoaProfessor->numero_documento = $request->input('numero_documento');
            $pessoaProfessor->endereco = $request->input('endereco');
            $pessoaProfessor->naturalidade = $request->input('naturalidade');
            $pessoaProfessor->email = $request->input('email');
            $pessoaProfessor->telefone = $request->input('telefone');
            $pessoaProfessor->nuit = $request->input('nuit');
            $pessoaProfessor->nivel_academico = $request->input('nivel_academico');

            if ($pessoaProfessor->save()) {
                // Registar Encarregado
                $funcionario = Funcionario::where('codigo', $request->input('codigo'))->first();
                $funcionario->codigo = $request->input('codigo');
                $funcionario->pessoa_id = $pessoaProfessor->id;
                $funcionario->cargo = 'Professor';
                $funcionario->salario = $request->input('salario');
                $funcionario->area = $request->input('area');
                $funcionario->data_inicio_funcoes = $request->input('data_inicio_funcoes');
                $funcionario->escola_id = $request->input('escola');
                $funcionario->provincia_id = $request->input('provincia');

                if ($request->hasFile('imagem')) {
                    $file = $request->file('imagem');
                    // Obter a extensão original do arquivo
                    $extension = $file->getClientOriginalExtension();
                    // Gerar um nome único para o arquivo mantendo a extensão original
                    $fileName = $codigoProfessor.'.'.$extension;
                    // Caminho onde a imagem será armazenada
                    $path = public_path('avatar');
                    // Salvar a imagem no diretório especificado
                    $file->move($path, $fileName);
                    // Atualizar o campo avatar no banco de dados
                    $userProefessor->avatar = $file;
                    $funcionario->avatar = $fileName;
                }

                if ($professor->save() && $funcionario->save()) {
                    DB::commit();

                    return response()->json(['status' => 1, 'message' => 'Professor Actualizado com Sucesso!']);
                }
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

    public function home()
    {
        $professor = Professor::where('pessoa_id', Auth::user()->pessoa_id)->first();
        $anoAtivo = AnoAcademico::where('estado', 1)->first();
        $current_year = Carbon::now()->year;

        $turmaProfessor = ProfessorTurma::where('professor_id', $professor->id)->where('ano_academico_id', $anoAtivo->id)->get();
        $pautaFinalizada = ProfessorTurma::where('professor_id', $professor->id)->where('ano_academico_id', $anoAtivo->id)->where('pauta_finalizada', 1)->get();
        $turmaProfessorDisciplinas = ProfessorTurmaDisciplina::where('professor_id', $professor->id)->where('ano_academico_id', $anoAtivo->id)->get();

        // Obter as turmas do professor no ano acadêmico ativo
        $turmaProfessor1 = ProfessorTurma::where('professor_id', $professor->id)
                ->where('ano_academico_id', $anoAtivo->id)
                ->pluck('turma_id'); // Obtem apenas os IDs das turmas
        // Obter os alunos matriculados nessas turmas
        $alunosMatriculados = Matricula::whereIn('turma_id', $turmaProfessor1)->get();
        $totalAlunosMatriculado = Matricula::where('ano_academico_id', $anoAtivo->id)->where('escola_id',Auth::user()->escola_id)->where('desistiu',0)->count();
        $totalCurriculos = Curriculo::count();
        $totalAnoAcademico = AnoAcademico::where('ano','>=',2025)->where('ano','<', $current_year)->count();


        // Retorna a view com os dados carregados
        return view('home.professor.index', [
            'turmaProfessor' => $turmaProfessor,
            'turmaProfessorDisciplinas' => $turmaProfessorDisciplinas,
            'pautaFinalizada' => $pautaFinalizada,
            'alunosMatriculados' => $alunosMatriculados,
            'totalAlunosMatriculado' => $totalAlunosMatriculado,
            'anoAtivo' => $anoAtivo,
            'totalCurriculos' => $totalCurriculos,
            'current_year' => $current_year,
            'totalAnoAcademico' => $totalAnoAcademico,
        ]);
    }

    public function retirarProfessor($professorID)
    {
        try {
            // Ativar o ano acadêmico selecionado
            $funcionario = Professor::where('id', $professorID)->first();
            $anoAcademicoAtivo = AnoAcademico::where('estado', 1)->first();
            $user = User::where('codigo', $funcionario->codigo)->first();

            $estado = $funcionario->estado;

            if ($estado == 1) {
                $funcionario->estado = false;
                $user->estado = true;

                if($funcionario->cargo =='Professor'){

                    $professor = Professor::where('codigo', $funcionario->codigo)->first();
                    $professor->estado = true;
                    $professor->save();

                }

            }

            if ($estado == 0) {
                $funcionario->estado = true;
                $user->estado = false;

                if($funcionario->cargo =='Professor'){

                    $professor = Professor::where('codigo', $funcionario->codigo)->first();
                    $professor->estado = false;
                    $professor->save();

                }

            }

            $funcionario->save();
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Professor Retirado com sucesso!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro ao retirar a Pro.'.$e->getMessage(),
            ]);
        }
    }

    public function arquivo()
    {
        $current_year = Carbon::now()->year;
        $ciclosarquivados = AnoAcademico::where('ano','>=',2025)->where('ano','<', $current_year)->get();

         // Retorna a view com os dados carregados
        return view('home.professor.arquivo', [
            'ciclosarquivados' => $ciclosarquivados,
        ]);
    }
}
