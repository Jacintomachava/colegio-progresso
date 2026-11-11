<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Funcionario;
use App\Models\Categoria;
use App\Models\Mensalidade;
use App\Models\ReceitaDespesa;
use App\Models\Pagamento;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PagamentoController extends Controller
{

    public function index()
    {
        $pagamentos = Pagamento::where('escola_id',Auth::user()->escola_id)->orderBy('updated_at', 'desc')->get();
        // Retorna a view com os dados carregados
        return view('pagamento.index', [
            'pagamentos' => $pagamentos,
        ]);

    }

    public function indexPagamento()
    {
        $pagamentos = Pagamento::where('escola_id',Auth::user()->escola_id)->where('pagou',1)->orderBy('updated_at', 'desc')->get();
        // Retorna a view com os dados carregados
        return view('pagamento.show', [
            'pagamentos' => $pagamentos,
        ]);
    }

    public function show()
    {
        $pagamentos = Pagamento::orderBy('id', 'desc')->get();

        $totalReceita = ReceitaDespesa::where('escola_id',Auth::user()->escola_id)->where('tipo', 'Receita')->where('pagou', 1)->sum('valor');
        $totalDespesa = ReceitaDespesa::where('escola_id',Auth::user()->escola_id)->where('tipo', 'Despesa')->where('pagou', 1)->sum('valor');
        $totalReceitaPendente = ReceitaDespesa::where('escola_id',Auth::user()->escola_id)->where('tipo', 'Receita')->where('pagou', 0)->sum('valor');
        $totalDespesaPendente = ReceitaDespesa::where('escola_id',Auth::user()->escola_id)->where('tipo', 'Despesa')->where('pagou', 0)->sum('valor');

        $totalDespesa = $totalDespesa + Funcionario::where('escola_id',Auth::user()->escola_id)->sum('salario');

        // Total de despesas por categoria
        $despesas = ReceitaDespesa::where('tipo', 'Despesa')
                ->where('pagou', true)
                ->where('escola_id',Auth::user()->escola_id)
                ->selectRaw('categoria_id, SUM(valor) as total')
                ->groupBy('categoria_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => Categoria::find($item->categoria_id)->nome,
                        'value' => $item->total,
                    ];
                });

        // Adiciona os salários como uma nova categoria
        $despesas->push([
            'name' => 'Salários',
            'value' => $totalDespesa,
        ]);

        // Total de receitas por categoria
        $receitas = ReceitaDespesa::where('tipo', 'Receita')
                ->where('pagou', true)
                ->where('escola_id',Auth::user()->escola_id)
                ->selectRaw('categoria_id, SUM(valor) as total')
                ->groupBy('categoria_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => Categoria::find($item->categoria_id)->nome,
                        'value' => $item->total,
                    ];
                });

            $data = DB::table('pagamentos')
                ->selectRaw("
                    MONTH(created_at) as mes, 
                    SUM(CASE WHEN cancelado = 1 THEN valor ELSE 0 END) as cancelado,
                    SUM(CASE WHEN pagou = 1 AND cancelado = 0 THEN valor ELSE 0 END) as pago,
                    SUM(CASE WHEN pagou = 0 AND cancelado = 0 THEN valor ELSE 0 END) as nao_pago
                ")
                ->where('escola_id',Auth::user()->escola_id)
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy('mes')
                ->get();
        
            // Formata os dados para o gráfico
            $chartData = [
                'meses' => $data->pluck('mes')->map(fn($m) => DateTime::createFromFormat('!m', $m)->format('F'))->toArray(),
                'cancelado' => $data->pluck('cancelado')->toArray(),
                'pago' => $data->pluck('pago')->toArray(),
                'nao_pago' => $data->pluck('nao_pago')->toArray(),
            ];

        // Retorna a view com os dados carregados
        return view('pagamento.estatistica.index', [
            'totalReceita' => $totalReceita,
            'totalDespesa' => $totalDespesa,
            'totalReceitaPendente' => $totalReceitaPendente,
            'totalDespesaPendente' => $totalDespesaPendente,
            'receitas' =>$receitas,
            'despesas' =>$despesas,
            'chartData' => $chartData,
        ]);
    }

    
    public function update($codigo)
    {
        try {
            DB::beginTransaction();
    
            $pagamento = Pagamento::where('referencia', $codigo)->first();
            if (!$pagamento) {
                return response()->json(['status' => 0, 'message' => 'Pagamento não encontrado.']);
            }
    
            $pagamento->cancelado = 1;
    
            $receitaDespesa = ReceitaDespesa::where('pagamento_id', $pagamento->id)->first();
            if ($receitaDespesa) {
                $receitaDespesa->delete();
            }
    
            Mensalidade::where('pagamento_id', $pagamento->id)
                ->update(['pagou' => 0, 'pagamento_id' => null]);
    
            if ($pagamento->save()) {
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Factura Cancelada com sucesso!']);
            }
    
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Falha ao salvar o pagamento.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
            ]);
        }
    }
    
}
