<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnoAcademico;
use App\Models\Taxa;
use App\Models\Disciplina;
use App\Models\MetodoPagamento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MetodoPagamentoController extends Controller
{
    
    public function index()
    {
        $metodoPagamentos = MetodoPagamento::orderBy('id', 'asc')->get();
         // Retorna a view com os dados carregados
         return view('parametrizacao.metodo.index', [
            'metodoPagamentos' => $metodoPagamentos,
        ]);

    }
}
