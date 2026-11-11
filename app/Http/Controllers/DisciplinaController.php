<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnoAcademico;
use App\Models\Taxa;
use App\Models\Disciplina;
use App\Models\Classe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DisciplinaController extends Controller
{
    public function index()
    {
        $disciplinas = Disciplina::orderBy('nome', 'desc')->get();
         // Retorna a view com os dados carregados
         return view('parametrizacao.disciplina.index', [
            'disciplinas' => $disciplinas,
        ]);

    }

    public function create()
    {
        // Retorna a view com os dados carregados
        return view('parametrizacao.disciplina.create', [

        ]);

    }

    public function store(Request $request){
        
        try {

         DB::beginTransaction();
            //Faturacao da Encomenda
            $propina = 0;
            if($request->input('tipo')==1){
                $propina = $request->input('propina');
            }

            $disciplina = new Disciplina();
            $disciplina->nome = $request->input('nome');
            $disciplina->descricao = $request->input('descricao');
            $disciplina->co_curricular = $request->input('tipo'); 
            $disciplina->propina = $propina; 

            if($disciplina->save()){

                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Disciplina Registado com Sucesso!']);
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
