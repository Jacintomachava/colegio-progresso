<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnoAcademico;
use App\Models\Taxa;
use App\Models\Disciplina;
use App\Models\Classe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClasseController extends Controller
{
    public function index()
    {
        //carrega as classes
        $classes = Classe::orderBy('nome', 'desc')->get();
         // Retorna a view com os dados carregados
         return view('parametrizacao.classe.index', [
            'classes' => $classes,
        ]);

    }
}
