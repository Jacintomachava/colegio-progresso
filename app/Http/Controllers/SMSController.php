<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProfessorTurma;
use App\Models\SMSDestinatario;

class SMSController extends Controller
{
    //
    public function index()
    {
        $notificacoesPendentes = SMSDestinatario::where('estado','Pendente')->orderBy('id', 'desc')->get();
        $notificacoesEnviadas = SMSDestinatario::where('estado','<>','Pendente')->orderBy('id', 'desc')->get();

         // Retorna a view com os dados carregados
         return view('notificacoes.sms.index', [
            'notificacoesPendentes' => $notificacoesPendentes,
            'notificacoesEnviadas' => $notificacoesEnviadas,
        ]);

    }
}
