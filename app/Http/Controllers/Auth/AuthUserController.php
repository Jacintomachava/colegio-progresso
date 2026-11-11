<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Escola;
use App\Models\HistoricoLogin;
use App\Models\ReclamacaoElogioPedido;

class AuthUserController extends Controller
{
    //
    public function login(Request $request)
    {

        // Tenta autenticar o usuário com email, senha e estado ativo
        if (Auth::attempt(['email' => $request['user'], 'password' => $request['senha'], 'estado' => true])) {

            $user = Auth::user(); // Obtém o usuário autenticado
            $escola = Escola::where('id',$user->escola_id)->first();
            $codigo = substr($user->codigo, 3, 1);
            
            // Cria a sessão com informações do usuário
            session([
                'escola' => $escola->nome,
                'logotipo' => $escola->logotipo,
                'slogan' => $escola->slogan, 
            ]);

            $historicoLogin = new HistoricoLogin();
            $historicoLogin->user_id = $user->id;
            $historicoLogin->nome = $user->nome;
            $historicoLogin->codigo = $user->codigo;
            $historicoLogin->save();


            $reclamacoes = ReclamacaoElogioPedido::where(function ($query) {
                                $query->where('tipo', 'Reclamação')
                                    ->orWhere('tipo', 'Pedido');
                            })
                            ->where('estado', 'Pendente')
                            ->latest()
                            ->take(10)
                            ->get();
            $total = ReclamacaoElogioPedido::where(function ($query) {
                                $query->where('tipo', 'Reclamação')
                                    ->orWhere('tipo', 'Pedido');
                            })
                            ->where('estado', 'Pendente')
                            ->count();

            session([
                'reclamacoes' => $reclamacoes,
                'totalReclamacao' => $total
            ]);

            return response()->json(['status' => 1, 'codigo'=>$codigo, 'message' => 'Autenticado com Sucesso']);
        }
        elseif(Auth::attempt(['telefone' => $request['user'], 'password' => $request['senha'], 'estado' => true])){

            $user = Auth::user(); // Obtém o usuário autenticado
            $escola = Escola::where('id',$user->escola_id)->first();
            $codigo = substr($user->codigo, 3, 1);
            
            // Cria a sessão com informações do usuário
            session([
                'escola' => $escola->nome,
                'logotipo' => $escola->logotipo,
                'slogan' => $escola->slogan, 
            ]);

            $historicoLogin = new HistoricoLogin();
            $historicoLogin->user_id = $user->id;
            $historicoLogin->nome = $user->nome;
            $historicoLogin->codigo = $user->codigo;
            $historicoLogin->save();


            $reclamacoes = ReclamacaoElogioPedido::where(function ($query) {
                                $query->where('tipo', 'Reclamação')
                                    ->orWhere('tipo', 'Pedido');
                            })
                            ->where('estado', 'Pendente')
                            ->latest()
                            ->take(10)
                            ->get();
            $total = ReclamacaoElogioPedido::where(function ($query) {
                                $query->where('tipo', 'Reclamação')
                                    ->orWhere('tipo', 'Pedido');
                            })
                            ->where('estado', 'Pendente')
                            ->count();

            session([
                'reclamacoesLayout' => $reclamacoes,
                'totalReclamacao' => $total
            ]);

            return response()->json(['status' => 1, 'codigo'=>$codigo, 'message' => 'Autenticado com Sucesso']);

        }elseif(Auth::attempt(['codigo' => $request['user'], 'password' => $request['senha'], 'estado' => true])){

            $user = Auth::user(); // Obtém o usuário autenticado
            $escola = Escola::where('id',$user->escola_id)->first();
            $codigo = substr($user->codigo, 3, 1);
            
            // Cria a sessão com informações do usuário
            session([
                'escola' => $escola->nome,
                'logotipo' => $escola->logotipo,
                'slogan' => $escola->slogan, 
            ]);

            $historicoLogin = new HistoricoLogin();
            $historicoLogin->user_id = $user->id;
            $historicoLogin->nome = $user->nome;
            $historicoLogin->codigo = $user->codigo;
            $historicoLogin->save();


            $reclamacoes = ReclamacaoElogioPedido::where(function ($query) {
                                $query->where('tipo', 'Reclamação')
                                    ->orWhere('tipo', 'Pedido');
                            })
                            ->where('estado', 'Pedente')
                            ->latest()
                            ->take(10)
                            ->get();
            $total = ReclamacaoElogioPedido::where(function ($query) {
                                $query->where('tipo', 'Reclamação')
                                    ->orWhere('tipo', 'Pedido');
                            })
                            ->where('estado', 'Pendente')
                            ->count();

            session([
                'reclamacoesLayout' => $reclamacoes,
                'totalReclamacao' => $total
            ]);

            return response()->json(['status' => 1, 'codigo'=>$codigo, 'message' => 'Autenticado com Sucesso']);

        }

        // Se o usuário não existir
        return response()->json(['status' => 0,  'message' => 'Utilizador ou Senha Errada']);
    }
}
