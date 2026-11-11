<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pessoa;
use App\Models\Professor;
use App\Models\Provincia;
use App\Models\Funcionario;
use App\Models\AnoAcademico;
use App\Models\User;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Escola;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class FuncionarioController extends Controller
{
    public function index()
    {
        $funcionarios = Funcionario::orderBy('id', 'desc')->where('ex_funcionario',0)->get();
        // Retorna a view com os dados carregados
        return view('funcionario.index', [
            'funcionarios' => $funcionarios,
        ]);
    }

    public function create()
    {
        $provincias = Provincia::all();
        $roles = Role::where('automatico',0)->get();
        $escolas = Escola::all();
        // Retorna a view com os dados carregados
        return view('funcionario.create', [
            'provincias'=>$provincias,
            'roles' => $roles,
            'escolas' => $escolas,
        ]);

    }

    public function edit($codigoFuncionario)
    {
        $provincias = Provincia::all();
        $roles = Role::where('automatico',0)->get();
        $escolas = Escola::all();
        $funcionario = Funcionario::where('codigo', $codigoFuncionario)->first();
        $user = User::where('codigo',$codigoFuncionario)->first();
        $userRoles = $user->roles->pluck('id')->toArray();

        // Retorna a view com os dados carregados
        return view('funcionario.edit', [
            'provincias'=>$provincias,
            'roles' => $roles,
            'escolas' => $escolas,
            'funcionario' => $funcionario,
            'user' => $user,
            'userRoles' => $userRoles,
        ]);

    }

    public function show($codigo)
    {
        $funcionario = Funcionario::where('codigo', $codigo)->first();
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();

        // Retorna a view com os dados carregados
        return view('funcionario.show', [
            'funcionario' => $funcionario,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
        ]);

    }


    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'escola' => ['required', 'integer', 'exists:escolas,id'],
            'imagem' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max('2mb'),
            ],
            'alunos' => ['nullable', 'array'],
            'alunos.*' => ['integer'],
        ]);

        $caminhoImagem = null;

        try {
            $escola = Escola::findOrFail($dados['escola']);

            // Guarda o ficheiro fora da pasta public.
            // O nome é gerado pelo servidor.
            if ($request->hasFile('imagem')) {
                $caminhoImagem = $request
                    ->file('imagem')
                    ->store('avatares', 'local');
            }

            DB::beginTransaction();

            $ano = Carbon::now()->format('y');

            $totalFuncionario = Funcionario::count();

            $codigoFuncionario = $escola->codigo
                . '.F'
                . str_pad($totalFuncionario + 6, 3, '0', STR_PAD_LEFT)
                . '.'
                . $ano;

            $pessoaFuncionario = new Pessoa();

            $pessoaFuncionario->nome = $request->input('nome');
            $pessoaFuncionario->data_nascimento = $request->input('data_nascimento');
            $pessoaFuncionario->genero = $request->input('genero');
            $pessoaFuncionario->tipo_documento = $request->input('tipo_documento');
            $pessoaFuncionario->numero_documento = $request->input('numero_documento');
            $pessoaFuncionario->endereco = $request->input('endereco');
            $pessoaFuncionario->naturalidade = $request->input('naturalidade');
            $pessoaFuncionario->email = $request->input('email');
            $pessoaFuncionario->telefone = $request->input('telefone');
            $pessoaFuncionario->nuit = $request->input('nuit');
            $pessoaFuncionario->nivel_academico = $request->input('nivel_academico');

            $pessoaFuncionario->save();

            $funcionario = new Funcionario();

            $funcionario->codigo = $codigoFuncionario;
            $funcionario->pessoa_id = $pessoaFuncionario->id;
            $funcionario->cargo = $request->input('cargo');
            $funcionario->salario = $request->input('salario');
            $funcionario->area = $request->input('area');
            $funcionario->data_inicio_funcoes = $request->input('data_inicio_funcoes');
            $funcionario->escola_id = $escola->id;
            $funcionario->provincia_id = $request->input('provincia');

            $userFuncionario = new User();

            $userFuncionario->nome = $request->input('nome');
            $userFuncionario->codigo = $codigoFuncionario;
            $userFuncionario->telefone = $request->input('telefone');
            $userFuncionario->email = $request->input('email');

            // Preferível criar uma senha aleatória e
            // obrigar o utilizador a defini-la.
            $userFuncionario->password = Hash::make(
                $request->input('telefone')
            );

            $userFuncionario->pessoa_id = $pessoaFuncionario->id;
            $userFuncionario->escola_id = $escola->id;
            $userFuncionario->funcionario = 1;

            if ($caminhoImagem) {
                $userFuncionario->avatar = $caminhoImagem;
                $funcionario->avatar = $caminhoImagem;
            }

            $funcionario->save();
            $userFuncionario->save();

            $userFuncionario->assignRole('Funcionario');

            foreach ($request->input('alunos', []) as $alunoID) {
                $roleUser = new RoleUser();

                $roleUser->role_id = $alunoID;
                $roleUser->model_type = User::class;
                $roleUser->model_id = $userFuncionario->id;

                $roleUser->save();
            }

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Funcionário cadastrado com sucesso!'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            if ($caminhoImagem) {
                Storage::disk('local')->delete($caminhoImagem);
            }

            report($e);

            return response()->json([
                'status' => 0,
                'message' => 'Não foi possível cadastrar o funcionário.'
            ], 500);
        }
    }

    public function update(Request $request){
        
        try {

         DB::beginTransaction();

            $current_year = Carbon::now()->year;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);
            $totalFuncionario = Funcionario::all();
            $escola = Escola::where('id',$request->input('escola'))->first();
            $funcionario1 = Funcionario::where('codigo',$request->input('codigo'))->first();

            //Registo de Aluno
            $pessoaFuncionario = Pessoa::where('id',$funcionario1->pessoa_id)->first();
            $pessoaFuncionario->nome = $request->input('nome');
            $pessoaFuncionario->data_nascimento = $request->input('data_nascimento');
            $pessoaFuncionario->genero = $request->input('genero');
            $pessoaFuncionario->tipo_documento = $request->input('tipo_documento');
            $pessoaFuncionario->numero_documento = $request->input('numero_documento');
            $pessoaFuncionario->endereco = $request->input('endereco');
            $pessoaFuncionario->naturalidade = $request->input('naturalidade');
            $pessoaFuncionario->email = $request->input('email');
            $pessoaFuncionario->telefone = $request->input('telefone');
            $pessoaFuncionario->nuit = $request->input('nuit');
            $pessoaFuncionario->nivel_academico = $request->input('nivel_academico');


            if($pessoaFuncionario->save()){

                //Registar Encarregado
                $funcionario = Funcionario::where('codigo',$request->input('codigo'))->first();
                $funcionario->cargo = $request->input('cargo');
                $funcionario->salario = $request->input('salario');
                $funcionario->area = $request->input('area');
                $funcionario->data_inicio_funcoes = $request->input('data_inicio_funcoes');
                $funcionario->provincia_id = $request->input('provincia');

                if($request->input('escola')==0){
                    $funcionario->escola_id = 2;
                }else{
                    $funcionario->escola_id = $request->input('escola');
                }

                 //Registar Aluno Como Utilizador
                 $userFuncionario = User::where('codigo',$request->input('codigo'))->first();;
                 $userFuncionario->nome = $request->input('nome');
                 $userFuncionario->telefone = $request->input('telefone'); //Forma de ter codigo estudante
                 $userFuncionario->email = $request->input('email');  //Forma de ter codigo estudante
                 $userFuncionario->funcionario = 1;

                 if($request->input('escola')==0){
                    $userFuncionario->todas_escolas = 1;
                    $userFuncionario->escola_id = 2;
                 }else{
                    $userFuncionario->escola_id = $escola->id;
                 }

                 if ($request->hasFile('imagem')) {
                    $file = $request->file('imagem');
                    // Obter a extensão original do arquivo
                    $extension = $file->getClientOriginalExtension();
                    // Gerar um nome único para o arquivo mantendo a extensão original
                    $fileName = $request->input('codigo'). '.' . $extension;
                    // Caminho onde a imagem será armazenada
                    $path = public_path('avatar');
                    // Salvar a imagem no diretório especificado
                    $file->move($path, $fileName);
                    // Atualizar o campo avatar no banco de dados
                    $userFuncionario->avatar = $fileName;
                    
                    $funcionario->avatar = $fileName;
                }
                

                if($funcionario->save() && $userFuncionario->save()){

                    $userFuncionario->assignRole('Funcionario');

                    $alunos1 = $request->input('alunos', []);

                    RoleUser::where('model_id', $userFuncionario->id)->delete();

                    foreach ($alunos1 as $alunoID) {
                        $roleUser = new RoleUser();
                        $roleUser->role_id = $alunoID;
                        $roleUser->model_type = 'App\Models\User';
                        $roleUser->model_id = $userFuncionario->id;
                        $roleUser->save();
                    }

                    DB::commit();

                    return response()->json(['status' => 1, 'message' => 'Funcionario Actualizado com Sucesso!']);

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

    public function retirarFuncionario($funcionarioID)
    {
        try {
            // Ativar o ano acadêmico selecionado
            $funcionario = Funcionario::where('id', $funcionarioID)->first();
            $anoAcademicoAtivo = AnoAcademico::where('estado', 1)->first();
            $user = User::where('codigo', $funcionario->codigo)->first();

            $estado = $funcionario->ex_funcionario;

            if ($estado == 1) {
                $funcionario->ex_funcionario = false;
                $user->estado = true;

                if($funcionario->cargo =='Professor'){

                    $professor = Professor::where('codigo', $funcionario->codigo)->first();
                    $professor->estado = true;
                    $professor->save();

                }

            }

            if ($estado == 0) {
                $funcionario->ex_funcionario = true;
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
                'message' => 'Funcionario Retirado com sucesso!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro ao retirar a Aluno.'.$e->getMessage(),
            ]);
        }
    }

    public function atualizarAvatar(Request $request)
    {
        $request->validate([
            'codigo' => 'required',
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $funcionario = Funcionario::where('codigo', $request->codigo)
            ->firstOrFail();

        DB::beginTransaction();

        try {

            $file = $request->file('avatar');

            $nome = Str::uuid() . '.' . $file->getClientOriginalExtension();

            $destino = public_path('avatar');

            if (!File::exists($destino)) {
                File::makeDirectory($destino, 0755, true);
            }

            /*
            * Foto antiga
            */
            $fotoAntiga = $funcionario->avatar;

            /*
            * Guardar nova imagem
            */
            $file->move($destino, $nome);

            /*
            * Atualizar funcionário
            */
            $funcionario->avatar = $nome;
            $funcionario->save();

            /*
            * Atualizar utilizador
            *
            * Se Funcionario tiver relação:
            *
            * public function user()
            * {
            *     return $this->belongsTo(User::class);
            * }
            */
            if ($funcionario->user) {

                $funcionario->user->avatar = $nome;
                $funcionario->user->save();
            }

            DB::commit();

            /*
            * Só eliminar a antiga depois da BD ter sido atualizada.
            */
            if (
                $fotoAntiga &&
                $fotoAntiga !== 'default.png' &&
                File::exists(public_path('avatar/' . $fotoAntiga))
            ) {
                File::delete(public_path('avatar/' . $fotoAntiga));
            }

            return response()->json([
                'status' => 1,
                'message' => 'Foto atualizada com sucesso.',
                'avatar' => $nome,
                'avatar_url' => url('/avatar/' . $nome),
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => 'Não foi possível atualizar a foto.',
            ], 500);
        }
    }
}
