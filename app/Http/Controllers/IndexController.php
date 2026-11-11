<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Professor;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    //Base de Dados para DeepSeek
    public function index()
    {
        // Contar alunos ativos que não estão suspensos e não desistiram
        $total_alunos_ativos = Aluno::where('suspenso', 0)->where('desistiu', 0)->count();

        // Contar professores ativos
        $total_professores_ativos = Professor::where('estado', 1)->count();

        // Retorna a view com os dados carregados
        return view('leading.home.index', [
            'total_alunos_ativos' => $total_alunos_ativos,
            'total_professores_ativos' => $total_professores_ativos,
        ]);
    }

    public function login()
    {
        return view('leading.politicas.index', [
        ]);
    }

    public function politica()
    {
        return view('login', [
        ]);
    }
}
