<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\ReceitaDespesa;
use App\Models\AnoAcademico;
use App\Models\Pagamento;
use App\Models\Mensalidade;
use App\Models\Meses;
use App\Models\Aluno;
use App\Models\User;
use App\Services\EntidadeReferenciaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class ReceitaDespesaService
{
    /**$categoria,$pagamentoID,$valor,$descricao)
     * Retorna todas as categorias de receitas e despesas.
     */
    public static function receita($valor,$categoria,$descricao, $pagamentoID,$pagou,$code)
    {
        $pagamento = Pagamento::where('id', $pagamentoID)->first();

        $receitaDespesa = new ReceitaDespesa();
        $receitaDespesa->valor = $valor;
        $receitaDespesa->tipo = 'Receita';
        $receitaDespesa->pagou = $pagou;
        $receitaDespesa->codigo = $code;
        $receitaDespesa->descricao = $descricao;
        $receitaDespesa->categoria_id = $categoria;
        $receitaDespesa->pagamento_id = $pagamentoID;
        $receitaDespesa->escola_id = $pagamento->escola_id;
        $receitaDespesa->save();
    }

    public static function receitaPaga($pagamentoID, $descricao)
    {
        $receitaDespesa = ReceitaDespesa::where('pagamento_id',$pagamentoID)->first();
        $receitaDespesa->pagou = true;
        $receitaDespesa->descricao = $descricao;
        $receitaDespesa->save();
    }

    public static function cancelarReceita($pagamentoID)
    {
        $receitaDespesa = ReceitaDespesa::where('pagamento_id',$pagamentoID)->first();
        $receitaDespesa->cancelado = true;
        $receitaDespesa->save();
    }

    public static function actualizarMensalidades($alunoID, $anoAcademicoID, $valor,$valorCoCurricular,$mesId)
    {
        $months = Meses::where('id','>=',$mesId)->get();
        $anoAcademicoAtivo = AnoAcademico::where('id', $anoAcademicoID)->first();
        $aluno = Aluno::where('id', $alunoID)->first();
        $user = User::where('pessoa_id', $aluno->pessoa_id)->first();

        $current_year = $anoAcademicoAtivo->ano;
        $current_month = Carbon::now()->format('m');
        $year_last_two_digits = substr($current_year, -2);

        $valorTotal = $valor+$valorCoCurricular;


        foreach ($months as $month) {

            $todosPagamento = Pagamento::all();
            $fatura = str_pad(count($todosPagamento)+1, 5, '0', STR_PAD_LEFT).''.$current_month.''.$year_last_two_digits;
            $recibo = str_pad(count($todosPagamento)+1, 5, '0', STR_PAD_LEFT).''.$year_last_two_digits;
            $referencia = EntidadeReferenciaService::GerarReferencia($fatura, $valorTotal);

            $pagamento = new Pagamento();
            $pagamento->recibo = $recibo;
            $pagamento->factura = $fatura;
            $pagamento->desconto = 0;
            $pagamento->entidade = 90018;
            $pagamento->referencia = $referencia;
            $pagamento->metodo_pagamento_id = 3;
            $pagamento->aluno_id = $alunoID;
            $pagamento->user_id = $user->id; //
            $pagamento->escola_id = $aluno->escola_id;
            $pagamento->categoria_id = 2;
            $pagamento->valor = $valorTotal;
            $pagamento->prazo_pagamento = Carbon::createFromFormat('Y-m-d', $month->data_inicio)->setYear($anoAcademicoAtivo->ano);

            if($pagamento->save()){

                //categoria 1 = Matricula 2 = Propinas
                $descricao1 = "Factura da Propina ".$month->nome;
                ReceitaDespesaService::receita($valorTotal,2,$descricao1, $pagamento->id, false,$referencia);

                $mensalidade = Mensalidade::where('mes_id',$month->id)->where('aluno_id',$alunoID)->where('ano_academico_id',$anoAcademicoID)->first();
                
                //Cancelar Pagamento
                $cancelarPagamento = Pagamento::where('id',$mensalidade->pagamento_id)->first();
                $cancelarPagamento->cancelado = true;
                $cancelarPagamento->save();

                $mensalidade->valor = $valor;
                $mensalidade->valor_co_curricular = $valorCoCurricular;
                $mensalidade->mes_id = $month->id;
                $mensalidade->ano_academico_id = $anoAcademicoID;
                $mensalidade->data_inicio = Carbon::createFromFormat('Y-m-d', $month->data_inicio)->setYear($anoAcademicoAtivo->ano);
                $mensalidade->data_fim = Carbon::createFromFormat('Y-m-d', $month->data_fim)->setYear($anoAcademicoAtivo->ano);
                $mensalidade->aluno_id = $alunoID;
                $mensalidade->escola_id = $aluno->escola_id;
                $mensalidade->pagamento_id = $pagamento->id;
                $mensalidade->save();

            }

        }
    }

}
