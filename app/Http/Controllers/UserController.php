<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\AlunoRelacaoPessoa;
use App\Models\Escola;
use App\Models\Mensagem;
use App\Models\MensagemSessao;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\SaldoSMS;
use App\Models\User;
use App\Services\SMSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('funcionario', 1)->orderBy('id', 'desc')->get();

        // Retorna a view com os dados carregados
        return view('user.index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        $roles = Role::where('automatico', 0)->get();
        $escolas = Escola::all();

        // Retorna a view com os dados carregados
        return view('user.create', [
            'roles' => $roles,
            'escolas' => $escolas,
        ]);
    }

    public function edit($codigo)
    {
        $roles = Role::where('automatico', 0)->get();
        $user = User::where('codigo', $codigo)->first();
        $userRoles = $user->roles->pluck('id')->toArray();
        $escolas = Escola::all();

        // Retorna a view com os dados carregados
        return view('user.create', [
            'roles' => $roles,
            'escolas' => $escolas,
            'user' => $user,
            'userRoles' => $userRoles,
        ]);
    }

    public function reporSenha($user_id)
    {
        try {
            DB::beginTransaction();

            // Actualizar User
            $user = User::where('id', $user_id)->first();
            $novaSenha = '123';
            if ($user->funcionario == 1) {
                $novaSenha = $user->telefone;
            } else {
                $novaSenha = $user->codigo;
            }
            $user->password = bcrypt($novaSenha);

            if ($user->save()) {
                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Senha Alterada com Sucesso!']);
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

            // Actualizar User
            $user = User::where('codigo', $request->input('utilizador'))->first();
            $user->estado = $request->input('estado');
            $user->escola_id = $request->input('escola_id');

            if ($request->input('escola') == 0) {
                $user->todas_escolas = 1;
                $user->escola_id = 2;
            } else {
                $user->escola_id = $request->input('escola_id');
            }

            $alunos1 = $request->input('roles', []);
            RoleUser::where('model_id', $user->id)->delete();

            if ($user->save()) {
                foreach ($alunos1 as $alunoID) {
                    $role = Role::where('name', $alunoID)->first();

                    $roleUser = new RoleUser();
                    $roleUser->role_id = $role->id;
                    $roleUser->model_type = 'App\Models\User';
                    $roleUser->model_id = $user->id;
                    $roleUser->save();
                }

                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Utilizador Actualizado com Sucesso!']);
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

    public function senhaUpdate(Request $request)
    {
        try {
            DB::beginTransaction();

            // Actualizar User
            $user = User::where('codigo', $request->input('user'))->first();
            $user->password = bcrypt($request->input('novaSenha'));

            if ($user->save()) {
                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Senha Alterada com Sucesso!']);
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

    public function recuperarSenhaTelefone(Request $request)
    {
        try {
            DB::beginTransaction();

            // Actualizar User
            $user = User::where('telefone', $request->input('telefone'))->orWhere('codigo', $request->input('telefone'))->first();
            $numero = rand(1000, 9999);
            $telefone = $request->input('telefone');
            $tipo = 'Enviada';

            $saldoCredito = SaldoSMS::where('codigo', 'saldo')->first();

            if (!$saldoCredito || $saldoCredito->saldo <= 0) {
                return response()->json(['status' => 0, 'message' => 'Sem SMS Por favor ligue para administracao ']);
            }

            if ($user && $user->funcionario == 1) {
                $user->password = bcrypt($numero);

                $mensagem1 = 'A nova senha para '.$user->nome.' é '.$numero;
                $qtd = SMSService::quantidadeSMS($mensagem1);

                $mensagem = new Mensagem();
                $mensagem->telefone = substr($telefone, -9);
                $mensagem->descricao = $mensagem1;
                $mensagem->tipo = $tipo;
                $mensagem->qtd = $qtd;
                $mensagem->canal = 'SMS';
                $credito = $qtd * 2;
                $saldoCredito->saldo = $saldoCredito->saldo - $credito;
                $mensagem->custo_real = 1.2;
                $mensagem->credito = $credito;

                if ($mensagem->save() && $saldoCredito->save() && $user->save()) {
                    // Atualizando a sessão do usuário
                    MensagemSessao::updateOrCreate(
                        ['telefone' => substr($telefone, -9)], // Critério de busca
                        [
                            'ultima_conversa' => $mensagem1,
                            'conversas' => DB::raw('conversas + 1'), // Incrementar conversas
                            'created_at' => now(), // Atualizar data de criação quando necessário
                        ]
                    );

                    SMSService::sendSMS($telefone, $mensagem1, 'LHAYSSO');

                    DB::commit();  // 🔥 Faltava isso para confirmar a transação

                    return response()->json(['status' => 1, 'message' => 'Senha Alterada com Sucesso! Foi enviado nova senha para '.$request->input('telefone')]);
                }
            } else {
                $user1 = User::where('codigo', $request->input('telefone'))->first();

                if ($user1) {
                    $aluno = Aluno::where('pessoa_id', $user1->pessoa_id)->first();
                    $pessoa = AlunoRelacaoPessoa::where('aluno_id', $aluno->id)->where('relacao_id', 1)->first();
                    $user1->password = bcrypt($numero);
                    $telefone = $pessoa->telefone;

                    $mensagem1 = 'A nova senha para o aluno '.$aluno->pessoa->nome.' é '.$numero;
                    $qtd = SMSService::quantidadeSMS($mensagem1);

                    $mensagem = new Mensagem();
                    $mensagem->telefone = substr($telefone, -9);
                    $mensagem->descricao = $mensagem1;
                    $mensagem->tipo = $tipo;
                    $mensagem->qtd = $qtd;
                    $mensagem->canal = 'SMS';
                    $credito = $qtd * 2;
                    $saldoCredito->saldo = $saldoCredito->saldo - $credito;
                    $mensagem->custo_real = 1.2;
                    $mensagem->credito = $credito;

                    if ($mensagem->save() && $saldoCredito->save() && $user1->save()) {
                        // Atualizando a sessão do usuário
                        MensagemSessao::updateOrCreate(
                            ['telefone' => substr($telefone, -9)], // Critério de busca
                            [
                                'ultima_conversa' => $mensagem1,
                                'conversas' => DB::raw('conversas + 1'), // Incrementar conversas
                                'created_at' => now(), // Atualizar data de criação quando necessário
                            ]
                        );

                        SMSService::sendSMS($telefone, $mensagem1, 'LHAYSSO');

                        DB::commit();  // 🔥 Faltava isso para confirmar a transação

                        return response()->json(['status' => 1, 'message' => 'Senha Alterada com Sucesso! Nova senha enviando para '.$pessoa->telefone]);
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // $errorMessage = DatabaseErrorHandler::handle($e);
            return response()->json([
                'status' => 0,
                'message' => 'Utilizador não encontrado para user ',
            ]);
        }
    }

    public function escolaUpdate(Request $request)
    {
        try {
            DB::beginTransaction();

            // Actualizar User
            $user = User::where('codigo', $request->input('user'))->first();
            $user->escola_id = $request->input('escola');

            $escola = Escola::where('id', $request->input('escola'))->first();

            if ($user->save()) {
                // Cria a sessão com informações do usuário
                session([
                    'escola' => $escola->nome,
                    'logotipo' => $escola->logotipo,
                    'slogan' => $escola->slogan,
                ]);

                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Escola mudada com Sucesso!']);
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

    public function mudarEscola()
    {
        $escolas = Escola::all();

        // Retorna a view com os dados carregados
        return view('user.mudar_escola.create', [
            'escolas' => $escolas,
        ]);
    }

    public function recuperarSenhaIndex()
    {
        // Retorna a view com os dados carregados
        return view('recuperarSenha', [
        ]);
    }

    public function minhaConta()
    {
        // Retorna a view com os dados carregados
        return view('user.senha.create', [
        ]);
    }

    public function senhaIndex()
    {
        // Retorna a view com os dados carregados
        return view('user.senha.create', [
        ]);
    }
}
