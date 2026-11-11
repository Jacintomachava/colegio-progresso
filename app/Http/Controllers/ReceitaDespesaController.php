<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fornecedor;
use App\Models\Categoria;
use App\Models\ReceitaDespesa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReceitaDespesaController extends Controller
{
    public function index()
    {

        $fornecedores = Fornecedor::all();
        $categorias = Categoria::all();
        $receitasDespesas = ReceitaDespesa::orderBy('id', 'desc')->get();;

        return view('receitaDespesa.index', [
            'receitasDespesas' => $receitasDespesas,
            'fornecedores' => $fornecedores,
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {

        DB::beginTransaction();

        try {

            $receitasDespesa = new ReceitaDespesa();
            $receitasDespesa->valor = $request['valor'];
            $receitasDespesa->tipo = $request['tipo'];
            $receitasDespesa->descricao = $request['descricao'];
            $receitasDespesa->automatico = false;
            $receitasDespesa->fornecedor_id = $request['fornrcedor'];
            $receitasDespesa->categoria_id = $request['categoria'];
            $receitasDespesa->pagou= $request['pagamento'];


            if ($receitasDespesa->save()) {

                DB::commit();

                return response()->json(['message' => $request['tipo'].' Registado com sucesso', 'status' => 200], 200);

            }


        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 400,
                'message' => 'Ocorreu um erro:' . PHP_EOL . $e->getMessage(),
            ]);
        }

    }
}
