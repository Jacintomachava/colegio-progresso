<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnoAcademico;
use App\Models\Taxa;
use App\Models\Turma;
use App\Models\Disciplina;
use App\Models\Classe;
use App\Models\Ciclo;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TaxaController extends Controller
{
    
    public function index()
    {
        $taxas = Taxa::orderBy('ciclo_id', 'asc')->get();
        $disciplinas = Disciplina::where('co_curricular',1)->orderBy('id', 'desc')->get();
         // Retorna a view com os dados carregados
         return view('parametrizacao.taxa.index', [
            'taxas' => $taxas,
            'disciplinas' => $disciplinas, 
        ]);

    }

    public function create()
    {
        $categorias = Categoria::where('receita', 1)->get();
        $ciclos = Ciclo::all();
        // Retorna a view com os dados carregados
        return view('parametrizacao.taxa.create', [
            'categorias' => $categorias,
            'ciclos' => $ciclos,
        ]);

    }

    public function store(Request $request){
        
        try {

         DB::beginTransaction();

            //Faturacao da Encomenda
            $taxa = new Taxa();
            $taxa->ciclo_id = $request->input('ciclo');
            $taxa->matricula_normal = $request->input('matricula_normal');
            $taxa->matricula_gold = $request->input('matricula_gold');
            $taxa->propina_meio_dia_normal = $request->input('propina_normal_meio_dia');
            $taxa->propina_todo_dia_normal = $request->input('propina_normal_todo_dia'); 
            $taxa->propina_meio_dia_gold = $request->input('propina_gold_meio_dia');
            $taxa->propina_todo_dia_gold = $request->input('propina_gold_todo_dia'); 
            $taxa->user_id  = Auth::user()->id; 

            if($taxa->save()){

                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Taxas Registados com Sucesso!']);

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
}
