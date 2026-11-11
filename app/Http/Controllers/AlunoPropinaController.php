<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Disciplina;
use App\Models\Aluno;
use App\Models\Mensalidade;
use App\Services\ReceitaDespesaService;
use App\Services\EntidadeReferenciaService;
use App\Services\MultaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AlunoPropinaController extends Controller
{
    public function show()
    {
        $aluno = Aluno::where('pessoa_id',Auth::user()->pessoa_id)->first();
        
        MultaService::getMulta($aluno->id);

        $mensalidades = Mensalidade::where('aluno_id', $aluno->id)->where('pagou',0)->get();
        $mensalidadesPago = Mensalidade::where('aluno_id', $aluno->id)->where('pagou',1)->get();

        // Retorna a view com os dados carregados
        return view('secretaria.propina.show', [
            'mensalidades' => $mensalidades,
            'aluno' => $aluno,
            'mensalidadesPago' => $mensalidadesPago,
        ]);

    }

    

}
