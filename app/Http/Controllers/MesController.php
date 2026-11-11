<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnoAcademico;
use App\Models\Meses;
use App\Models\Disciplina;
use App\Models\Classe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MesController extends Controller
{
    public function index()
    {
        $meses = Meses::orderBy('id', 'asc')->get();
         // Retorna a view com os dados carregados
         return view('parametrizacao.mes.index', [
            'meses' => $meses,
        ]);

    }
}
