<?php

namespace App\Services;

use App\Models\AnoAcademico;
use App\Models\Mensalidade;
use App\Models\Meses;
use App\Models\Pagamento;
use App\Models\Matricula;
use App\Models\Taxa;
use App\Models\Aluno;
use App\Models\Classe;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class MensalidadeService
{
    public static function gerar(Matricula $matricula): void {

        /*
        |--------------------------------------------------------------------------
        | 1. Ano académico
        |--------------------------------------------------------------------------
        */
        $anoAcademico = AnoAcademico::find(3);

        if (!$anoAcademico) {
            throw new \Exception(
                'Ano académico não encontrado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Classe
        |--------------------------------------------------------------------------
        */
        $classe = Classe::find($matricula->classe_id);

        if (!$classe) {
            throw new \Exception(
                'Classe não encontrada para o aluno '
                .$matricula->aluno_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Taxa
        |--------------------------------------------------------------------------
        |
        | Mantemos a mesma regra que já tens na matrícula.
        |--------------------------------------------------------------------------
        */
        if ($classe->id == 9) {

            $taxa = Taxa::where('ciclo_id', 5)->first();

        } else {

            $taxa = Taxa::where('ciclo_id', $classe->ciclo_id)->first();
        }

        if (!$taxa) {
            throw new \Exception(
                'Taxa não encontrada para a classe '
                .$classe->id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Determinar valor curricular
        |--------------------------------------------------------------------------
        |
        | plano 1 = NORMAL
        | plano 2 = GOLD
        |--------------------------------------------------------------------------
        */
        $plano = (int) $matricula->plano;
        $periodo = $matricula->periodo;

        if (!$periodo) {
            throw new \Exception(
                'Período não encontrado para o aluno '
                .$matricula->aluno_id
            );
        }

        $valorMensalidade = 0;

        /*
         * NORMAL
         */
        if ($plano === 1) {

            if ($periodo->tipo === 'meio_dia') {

                $valorMensalidade = $taxa->propina_meio_dia_normal;

            } elseif ($periodo->tipo === 'todo_dia') {

                $valorMensalidade = $taxa->propina_todo_dia_normal;
            }
        /*
         * GOLD
         */
        } elseif ($plano === 2) {

            if ($periodo->tipo === 'meio_dia') {

                $valorMensalidade = $taxa->propina_meio_dia_gold;

            } elseif ($periodo->tipo === 'todo_dia') {

                $valorMensalidade = $taxa->propina_todo_dia_gold;
            }

        } else {

            throw new \Exception(
                'Plano inválido para o aluno '
                .$matricula->aluno_id
            );
        }

        if ($valorMensalidade <= 0) {

            throw new \Exception(
                'Valor da mensalidade inválido para o aluno '
                .$matricula->aluno_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Co-curricular
        |--------------------------------------------------------------------------
        |
        | Inicialmente deixamos zero.
        |
        | Depois podemos incorporar as disciplinas cocurriculares
        | do aluno exatamente como já tens no processo de matrícula.
        |--------------------------------------------------------------------------
        */
        $valorCoCurricular = 0;

        /*
        |--------------------------------------------------------------------------
        | 6. Gerar mensalidades
        |--------------------------------------------------------------------------
        */

        self::gerarMensalidades($matricula, $anoAcademico, $valorMensalidade, $valorCoCurricular);
    }

    private static function gerarMensalidades(Matricula $matricula, AnoAcademico $anoAcademico, $valor, $valorCoCurricular): void {

        /*
        |--------------------------------------------------------------------------
        | NÃO GERAR DEZEMBRO
        |--------------------------------------------------------------------------
        |
        | mes_id = 11 → Dezembro
        |--------------------------------------------------------------------------
        */
        $months = Meses::where('id', '!=', 11)->orderBy('id')->get();

        $valorTotal = $valor + $valorCoCurricular;

        foreach ($months as $month) {

            /*
            |--------------------------------------------------------------------------
            | 1. PROTEÇÃO CONTRA DUPLICAÇÃO
            |--------------------------------------------------------------------------
            |
            | aluno + ano + mês
            |
            */
            $jaExiste = Mensalidade::where('aluno_id', $matricula->aluno_id)->where('ano_academico_id', $anoAcademico->id)->where('mes_id', $month->id)->exists();

            if ($jaExiste) {
                continue;
            }

            DB::beginTransaction();

            try {

                /*
                |--------------------------------------------------------------------------
                | Datas
                |--------------------------------------------------------------------------
                */
                $dataInicio = Carbon::parse($month->data_inicio)->setYear($anoAcademico->ano);
                $dataFim = Carbon::parse($month->data_fim)->setYear($anoAcademico->ano);

                /*
                |--------------------------------------------------------------------------
                | Factura / recibo
                |--------------------------------------------------------------------------
                */
                $currentMonth = Carbon::now()->format('m');
                $yearLastTwoDigits = substr($anoAcademico->ano, -2);
                $numeroPagamento = Pagamento::count() + 1;
                $fatura = str_pad($numeroPagamento, 5, '0', STR_PAD_LEFT).$currentMonth.$yearLastTwoDigits;
                $recibo = str_pad($numeroPagamento, 5, '0', STR_PAD_LEFT).$yearLastTwoDigits;

                /*
                |--------------------------------------------------------------------------
                | Referência
                |--------------------------------------------------------------------------
                */
                $referencia = EntidadeReferenciaService::GerarReferencia($fatura, $valorTotal);

                /*
                |--------------------------------------------------------------------------
                | Pagamento
                |--------------------------------------------------------------------------
                */
                $pagamento = new Pagamento();

                $pagamento->recibo = $recibo;
                $pagamento->factura = $fatura;
                $pagamento->desconto = 0;
                $pagamento->entidade = 90028;
                $pagamento->referencia = $referencia;
                $pagamento->metodo_pagamento_id = 3;
                $pagamento->aluno_id = $matricula->aluno_id;
                /*
                 * Job não possui Auth::user().
                 *
                 * Utilizamos o utilizador associado
                 * à matrícula.
                 */
                $pagamento->user_id = $matricula->user_id;
                $pagamento->escola_id = $matricula->escola_id;
                // 2 = Propina
                $pagamento->categoria_id = 2;
                $pagamento->valor = $valorTotal;
                $pagamento->prazo_pagamento = $dataInicio;
                $pagamento->descricao = 'Pagamento da propina de '.$month->nome.' '.$anoAcademico->ano;

                $pagamento->save();

                /*
                |--------------------------------------------------------------------------
                | Receita
                |--------------------------------------------------------------------------
                */
                ReceitaDespesaService::receita($valorTotal, 2, 'Factura da Propina '.$month->nome, $pagamento->id, false, $referencia);

                /*
                |--------------------------------------------------------------------------
                | Mensalidade
                |--------------------------------------------------------------------------
                */
                $mensalidade = new Mensalidade();

                $mensalidade->valor = $valor;
                $mensalidade->valor_co_curricular = $valorCoCurricular;
                $mensalidade->mes_id = $month->id;
                $mensalidade->ano_academico_id = $anoAcademico->id;
                $mensalidade->data_inicio = $dataInicio;
                $mensalidade->data_fim = $dataFim;
                $mensalidade->aluno_id = $matricula->aluno_id;
                $mensalidade->escola_id = $matricula->escola_id;
                $mensalidade->pagamento_id = $pagamento->id;

                $mensalidade->save();

                DB::commit();

                Log::info(
                    'Mensalidade criada. '
                    .'Aluno: '.$matricula->aluno_id
                    .' | Mês: '.$month->nome
                    .' | Ano: '.$anoAcademico->ano
                    .' | Valor: '.$valorTotal
                );


            } catch (\Throwable $e) {

                DB::rollBack();

                Log::error(
                    'Erro ao gerar mensalidade. '
                    .'Aluno: '.$matricula->aluno_id
                    .' | Mês: '.$month->nome
                    .' | Ano: '.$anoAcademico->ano
                    .' | Erro: '.$e->getMessage()
                );
            }
        }
    }


    
    public static function gerarMensalidadeDezembro(
        $alunoID,
        $anoAcademicoID,
        bool $executarLimpeza = false
    ): bool {

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. Verificar aluno
            |--------------------------------------------------------------------------
            */
            $aluno = Aluno::find($alunoID);

            if (!$aluno || $aluno->desistiu == 1) {
                DB::rollBack();
                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Verificar ano académico
            |--------------------------------------------------------------------------
            */
            $anoAcademico = AnoAcademico::find($anoAcademicoID);

            if (!$anoAcademico) {
                throw new \Exception('Ano académico não encontrado.');
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Identificar os meses
            |--------------------------------------------------------------------------
            | Conforme a tabela meses:
            | Fevereiro = ID 1
            | Dezembro = ID 12
            |--------------------------------------------------------------------------
            */
            $mesDezembro = Meses::find(12);

            if (!$mesDezembro) {
                throw new \Exception('Mês de Dezembro não encontrado.');
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Buscar Fevereiro do mesmo aluno e ano académico
            |--------------------------------------------------------------------------
            */
            $mensalidadesFevereiro = Mensalidade::where('aluno_id', $alunoID)
                ->where('ano_academico_id', $anoAcademicoID)
                ->where('mes_id', 1)
                ->get();

            if ($mensalidadesFevereiro->isEmpty()) {
                DB::rollBack();

                Log::warning(
                    "Dezembro ignorado: aluno {$alunoID} não possui Fevereiro."
                );

                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Verificar se Fevereiro foi pago
            |--------------------------------------------------------------------------
            */
            $idsPagamentosFevereiro = $mensalidadesFevereiro
                ->pluck('pagamento_id')
                ->filter()
                ->unique();

            $fevereiroPago = Pagamento::whereIn(
                    'id',
                    $idsPagamentosFevereiro
                )
                ->where('pagou', 1)
                ->exists();

            /*
            |--------------------------------------------------------------------------
            | 6. Buscar Dezembro do mesmo ano académico
            |--------------------------------------------------------------------------
            */
            $mensalidadesDezembro = Mensalidade::where('aluno_id', $alunoID)
                ->where('ano_academico_id', $anoAcademicoID)
                ->where('mes_id', $mesDezembro->id)
                ->lockForUpdate()
                ->get();

            /*
            |--------------------------------------------------------------------------
            | 7. Se Fevereiro foi pago, tratar Dezembro indevido
            |--------------------------------------------------------------------------
            */
            if ($fevereiroPago) {

                foreach ($mensalidadesDezembro as $dezembro) {

                    $pagamentoDezembro = Pagamento::find(
                        $dezembro->pagamento_id
                    );

                    /*
                    | Nunca apagar um pagamento confirmado.
                    */
                    if (
                        $pagamentoDezembro &&
                        (int) $pagamentoDezembro->pagou === 1
                    ) {
                        Log::warning(
                            "Dezembro preservado por estar pago. "
                            ."Aluno: {$alunoID} "
                            ."| Mensalidade: {$dezembro->id}"
                        );

                        continue;
                    }

                    /*
                    | Por segurança, simular antes de apagar.
                    */
                    if (!$executarLimpeza) {

                        Log::warning(
                            "SIMULAÇÃO: Dezembro indevido encontrado. "
                            ."Aluno: {$alunoID} "
                            ."| Ano académico: {$anoAcademicoID} "
                            ."| Mensalidade: {$dezembro->id} "
                            ."| Pagamento: ".($dezembro->pagamento_id ?? 'N/A')
                        );

                        continue;
                    }

                    /*
                    | ATENÇÃO:
                    | A eliminação da mensalidade não anula
                    | automaticamente a factura nem a receita.
                    | Executar apenas depois de confirmar
                    | como essas dependências serão tratadas.
                    */
                    $dezembro->delete();

                    Log::info(
                        "Mensalidade de Dezembro eliminada. "
                        ."Aluno: {$alunoID} "
                        ."| Ano académico: {$anoAcademicoID} "
                        ."| Mensalidade: {$dezembro->id}"
                    );
                }

                DB::commit();

                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | 8. Se Dezembro já existe, não duplicar
            |--------------------------------------------------------------------------
            */
            if ($mensalidadesDezembro->isNotEmpty()) {

                DB::commit();

                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | 9. Obter Fevereiro como referência
            |--------------------------------------------------------------------------
            */
            $fevereiro = $mensalidadesFevereiro->first();

            $pagamentoFevereiro = Pagamento::find(
                $fevereiro->pagamento_id
            );

            if (!$pagamentoFevereiro) {
                throw new \Exception(
                    "Pagamento de Fevereiro não encontrado. Aluno: {$alunoID}"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 10. Valores da mensalidade
            |--------------------------------------------------------------------------
            */
            $valor = $fevereiro->valor;
            $valorCoCurricular = $fevereiro->valor_co_curricular ?? 0;
            $valorTotal = $valor + $valorCoCurricular;

            /*
            |--------------------------------------------------------------------------
            | 11. Datas de Dezembro
            |--------------------------------------------------------------------------
            */
            $dataInicio = Carbon::parse(
                $mesDezembro->data_inicio
            )->setYear($anoAcademico->ano);

            $dataFim = Carbon::parse(
                $mesDezembro->data_fim
            )->setYear($anoAcademico->ano);

            /*
            |--------------------------------------------------------------------------
            | 12. Número da factura e recibo
            |--------------------------------------------------------------------------
            | Mantida a tua lógica actual.
            | Recomenda-se substituir count() + 1
            | por uma sequência segura contra concorrência.
            |--------------------------------------------------------------------------
            */
            $currentMonth = Carbon::now()->format('m');

            $yearLastTwoDigits = substr(
                (string) $anoAcademico->ano,
                -2
            );

            $numeroPagamento = Pagamento::count() + 1;

            $fatura = str_pad(
                $numeroPagamento,
                5,
                '0',
                STR_PAD_LEFT
            ).$currentMonth.$yearLastTwoDigits;

            $recibo = str_pad(
                $numeroPagamento,
                5,
                '0',
                STR_PAD_LEFT
            ).$yearLastTwoDigits;

            /*
            |--------------------------------------------------------------------------
            | 13. Gerar referência
            |--------------------------------------------------------------------------
            */
            $referencia = EntidadeReferenciaService::GerarReferencia(
                $fatura,
                $valorTotal
            );

            /*
            |--------------------------------------------------------------------------
            | 14. Criar pagamento
            |--------------------------------------------------------------------------
            */
            $pagamento = new Pagamento();

            $pagamento->recibo = $recibo;
            $pagamento->factura = $fatura;
            $pagamento->desconto = 0;
            $pagamento->entidade = 90028;
            $pagamento->referencia = $referencia;
            $pagamento->metodo_pagamento_id = 3;
            $pagamento->aluno_id = $alunoID;
            $pagamento->user_id = $pagamentoFevereiro->user_id;
            $pagamento->escola_id = $pagamentoFevereiro->escola_id;
            $pagamento->categoria_id = 2;
            $pagamento->valor = $valorTotal;
            $pagamento->prazo_pagamento = $dataInicio;
            $pagamento->descricao =
                'Pagamento da propina de Dezembro '.$anoAcademico->ano;

            $pagamento->save();

            /*
            |--------------------------------------------------------------------------
            | 15. Registar receita
            |--------------------------------------------------------------------------
            */
            ReceitaDespesaService::receita(
                $valorTotal,
                2,
                'Factura da Propina Dezembro',
                $pagamento->id,
                false,
                $referencia
            );

            /*
            |--------------------------------------------------------------------------
            | 16. Criar mensalidade de Dezembro
            |--------------------------------------------------------------------------
            */
            $mensalidade = new Mensalidade();

            $mensalidade->valor = $valor;
            $mensalidade->valor_co_curricular = $valorCoCurricular;
            $mensalidade->mes_id = $mesDezembro->id;
            $mensalidade->ano_academico_id = $anoAcademicoID;
            $mensalidade->data_inicio = $dataInicio;
            $mensalidade->data_fim = $dataFim;
            $mensalidade->aluno_id = $alunoID;
            $mensalidade->escola_id = $pagamentoFevereiro->escola_id;
            $mensalidade->pagamento_id = $pagamento->id;

            $mensalidade->save();

            DB::commit();

            Log::info(
                "Mensalidade de Dezembro criada. "
                ."Aluno: {$alunoID} "
                ."| Ano: {$anoAcademico->ano} "
                ."| Valor: {$valorTotal}"
            );

            return true;

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                "Erro ao processar Dezembro. "
                ."Aluno: {$alunoID} "
                ."| Ano académico: {$anoAcademicoID} "
                ."| Erro: {$e->getMessage()}"
            );

            return false;
        }
    }


}