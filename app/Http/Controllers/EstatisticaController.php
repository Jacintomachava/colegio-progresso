<?php

namespace App\Http\Controllers;

use App\Models\AnoAcademico;
use App\Models\Escola;
use App\Models\Funcionario;
use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Models\Meses;
use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EstatisticaController extends Controller
{
    public function index()
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $data = Carbon::now();
        $mesAtual = now()->month;

        // Numero Total
        $totalMatriculados = Matricula::where('desistiu', 0)->where('ano_academico_id', $anoAcademicoAtivo->id)->where('pagou', 1)->count();
        $totalValorMatricula = Matricula::where('desistiu', 0)->where('ano_academico_id', $anoAcademicoAtivo->id)->where('pagou', 1)->sum('valor_total');
        $totalFuncionario = Funcionario::where('codigo', '<>', null)->where('ex_funcionario',0)->count();
        $totalSalarioFuncionario = Funcionario::where('salario', '>', 0)->where('ex_funcionario',0)->sum('salario');
        $totalPropinaActual = Mensalidade::where('mes_id', '<=', $mesAtual)
            ->where('pagou', 1)
            ->where('disistiu',0)
            ->where('ano_academico_id',$anoAcademicoAtivo->id)
            ->sum(DB::raw('(valor + valor_co_curricular - desconto + (multa / 100 * (valor + valor_co_curricular)))'));
        $totalPropinaAdiantamento = Mensalidade::where('mes_id', '>', $mesAtual)
            ->where('pagou', 1)
            ->where('disistiu',0)
            ->where('ano_academico_id',$anoAcademicoAtivo->id)
            ->sum(DB::raw('(valor + valor_co_curricular - desconto + (multa / 100 * (valor + valor_co_curricular)))'));

        // Total Paga no mes Actual
        $totalPropinasPendenteAteMesActual = Mensalidade::where('mes_id', '<', $mesAtual)
            ->where('pagou', 0)
            ->where('disistiu', 0)
            ->where('ano_academico_id', $anoAcademicoAtivo->id)
            ->sum(DB::raw('(valor + valor_co_curricular - desconto + (multa / 100 * (valor + valor_co_curricular)))'));
        $matriculaPendentes = Matricula::where('desistiu', 0)->where('ano_academico_id', $anoAcademicoAtivo->id)->where('pagou', 0)->sum('valor_total');

        // Total Pagamentos neste mes
        $pagamentosMesActual = Pagamento::where('pagou', 1)->whereMonth('updated_at', $mesAtual)->whereYear('updated_at', Carbon::now()->year)->sum('valor');

        $dados = Mensalidade::select(
            'mes_id',
            DB::raw('SUM((valor + valor_co_curricular - desconto + (multa / 100 * (valor + valor_co_curricular)))) as total')
        )
        ->where('pagou', 1)
        ->where('ano_academico_id', $anoAcademicoAtivo->id)
        ->groupBy('mes_id')
        ->get()
        ->map(function ($item) {
            $mesNome = DB::table('meses')->where('id', $item->mes_id)->value('nome');

            return [
                'name' => $mesNome,
                'value' => round($item->total, 2),
            ];
        });

        // Mensalidades
        $meses = Meses::orderBy('id')->get();

        $labelsMeses = [];
        $mensalidadesPagas = [];
        $mensalidadesPendentes = [];

        foreach ($meses as $mes) {
            $labelsMeses[] = $mes->nome;

            $quantidadePagas = Mensalidade::where('mes_id', $mes->id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->where('pagou', true)
                ->where('disistiu', 0)
                ->count();

            $quantidadePendentes = Mensalidade::where('mes_id', $mes->id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->where('pagou', false)
                ->where('disistiu', 0)
                ->count();

            $mensalidadesPagas[] = $quantidadePagas;
            $mensalidadesPendentes[] = $quantidadePendentes;
        }

        $escola = 0;

        // Retorna a view com os dados carregados
        return view('estatistica.index', [
            // Total nas cardes
            'totalMatriculados' => $totalMatriculados,
            'totalValorMatricula' => $totalValorMatricula,
            'totalFuncionario' => $totalFuncionario,
            'totalSalarioFuncionario' => $totalSalarioFuncionario,
            'totalPropinaActual' => $totalPropinaActual,
            'totalPropinaAdiantamento' => $totalPropinaAdiantamento,
            // Mes Actual
            'mes' => $this->mes($mesAtual),
            // Pagamentos Pendente Ate dias Actuais
            'matriculaPendentes' => $matriculaPendentes,
            'totalPropinasPendenteAteMesActual' => $totalPropinasPendenteAteMesActual,
            'pagamentosMesActual' => $pagamentosMesActual,
            // Escola
            'escola' => $escola,
            // Graficos Pizza de Mensalidade
            'dadosGrafico' => $dados,
            // Grafico Barra de mensalidade
            'labelsMeses' => $labelsMeses,
            'mensalidadesPagas' => $mensalidadesPagas,
            'mensalidadesPendentes' => $mensalidadesPendentes,
        ]);
    }

    public function show($escola1)
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $data = Carbon::now();
        $mesAtual = now()->month;

        $escola = $escola1;

        // Numero Total
        $totalMatriculados = Matricula::where('desistiu', 0)->where('escola_id', $escola)->where('ano_academico_id', $anoAcademicoAtivo->id)->where('pagou', 1)->count();
        $totalValorMatricula = Matricula::where('desistiu', 0)->where('escola_id', $escola)->where('ano_academico_id', $anoAcademicoAtivo->id)->where('pagou', 1)->sum('valor_total');
        $totalFuncionario = Funcionario::where('codigo', '<>', null)->where('escola_id', $escola)->count();
        $totalSalarioFuncionario = Funcionario::where('salario', '>', 0)->where('escola_id', $escola)->sum('salario');
        $totalPropinaActual = Mensalidade::where('mes_id', '<=', $mesAtual)
            ->where('pagou', 1)
            ->where('escola_id', $escola)
            ->where('ano_academico_id', $anoAcademicoAtivo->id)
            ->sum(DB::raw('(valor + valor_co_curricular - desconto + (multa / 100 * (valor + valor_co_curricular)))'));
        $totalPropinaAdiantamento = Mensalidade::where('mes_id', '>', $mesAtual)
            ->where('pagou', 1)
            ->where('escola_id', $escola)
            ->where('ano_academico_id', $anoAcademicoAtivo->id)
            ->sum(DB::raw('(valor + valor_co_curricular - desconto + (multa / 100 * (valor + valor_co_curricular)))'));

        // Total Paga no mes Actual
        $totalPropinasPendenteAteMesActual = Mensalidade::where('mes_id', '<=', $mesAtual)
            ->where('pagou', 0)
            ->where('escola_id', $escola)
            ->where('ano_academico_id', $anoAcademicoAtivo->id)
            ->sum(DB::raw('(valor + valor_co_curricular - desconto + (multa / 100 * (valor + valor_co_curricular)))'));
        $matriculaPendentes = Matricula::where('desistiu', 0)->where('pagou', 0)->where('escola_id', $escola)->where('ano_academico_id', $anoAcademicoAtivo->id)->sum('valor_total');

        // Total Pagamentos neste mes
        $pagamentosMesActual = Pagamento::where('pagou', 1)->whereMonth('updated_at', $mesAtual)->where('escola_id', $escola)->sum('valor');

        $dados = Mensalidade::select(
            'mes_id',
            DB::raw('SUM((valor + valor_co_curricular - desconto + (multa / 100 * (valor + valor_co_curricular)))) as total')
        )
        ->where('pagou', 1)
        ->where('escola_id', $escola)
        ->where('ano_academico_id', $anoAcademicoAtivo->id)
        ->groupBy('mes_id')
        ->get()
        ->map(function ($item) {
            $mesNome = DB::table('meses')->where('id', $item->mes_id)->value('nome');

            return [
                'name' => $mesNome,
                'value' => round($item->total, 2),
            ];
        });

        // Mensalidades
        $meses = Meses::orderBy('id')->get();

        $labelsMeses = [];
        $mensalidadesPagas = [];
        $mensalidadesPendentes = [];

        foreach ($meses as $mes) {
            $labelsMeses[] = $mes->nome;

            $quantidadePagas = Mensalidade::where('mes_id', $mes->id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->where('escola_id', $escola)
                ->where('pagou', true)
                ->count();

            $quantidadePendentes = Mensalidade::where('mes_id', $mes->id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->where('escola_id', $escola)
                ->where('pagou', false)
                ->count();

            $mensalidadesPagas[] = $quantidadePagas;
            $mensalidadesPendentes[] = $quantidadePendentes;
        }

        // Retorna a view com os dados carregados
        return view('estatistica.index', [
            // Total nas cardes
            'totalMatriculados' => $totalMatriculados,
            'totalValorMatricula' => $totalValorMatricula,
            'totalFuncionario' => $totalFuncionario,
            'totalSalarioFuncionario' => $totalSalarioFuncionario,
            'totalPropinaActual' => $totalPropinaActual,
            'totalPropinaAdiantamento' => $totalPropinaAdiantamento,
            // Mes Actual
            'mes' => $this->mes($mesAtual),
            // Pagamentos Pendente Ate dias Actuais
            'matriculaPendentes' => $matriculaPendentes,
            'totalPropinasPendenteAteMesActual' => $totalPropinasPendenteAteMesActual,
            'pagamentosMesActual' => $pagamentosMesActual,
            // Escola
            'escola' => $escola,
            // Graficos Pizza de Mensalidade
            'dadosGrafico' => $dados,
            // Grafico Barra de mensalidade
            'labelsMeses' => $labelsMeses,
            'mensalidadesPagas' => $mensalidadesPagas,
            'mensalidadesPendentes' => $mensalidadesPendentes,
        ]);
    }

    public static function mes($numero)
    {
        if ($numero == 1) {
            return 'Janeiro';
        } elseif ($numero == 2) {
            return 'Fevereiro';
        } elseif ($numero == 3) {
            return 'Março';
        } elseif ($numero == 4) {
            return 'Abril';
        } elseif ($numero == 5) {
            return 'Maio';
        } elseif ($numero == 6) {
            return 'Junho';
        } elseif ($numero == 7) {
            return 'Julho';
        } elseif ($numero == 8) {
            return 'Agosto';
        } elseif ($numero == 9) {
            return 'Setembro';
        } elseif ($numero == 10) {
            return 'Outubro';
        } elseif ($numero == 11) {
            return 'Novembro';
        }

        return 'Desconhecido';
    }
}
