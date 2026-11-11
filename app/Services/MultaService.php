<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\AlunoRelacaoPessoa;
use App\Models\AnoAcademico;
use App\Models\Mensagem;
use App\Models\Mensalidade;
use App\Models\Pagamento;
use Carbon\Carbon;

class MultaService
{
    /* Esse metodo Verifica o estado Actual de
    Mensalidades nesse estudante para saber se
    Tem multa ou nao.
    */
    public static function getMulta($aluno)
    {
        $currentDate = Carbon::now(); // Data atual ou $data = '2025-04-14';
        $year_last_two_digits = substr($currentDate->year, -2); // Últimos dois dígitos do ano
        $suspenso = false; // Flag para suspensão

        $mesAtual = Carbon::now()->month;

        $multas = Mensalidade::where('data_fim', '<', now()->toDateString())
                ->where('aluno_id', $aluno)
                ->where('pagou', 0)
                ->where('mes_id', '>=', $mesAtual - 1) // de agora pra frente
                ->get();

        if ($multas->count() == 0) {
            $alunoData = Aluno::find($aluno);
            if ($alunoData) {
                $alunoData->suspenso = false;
                $alunoData->save();
            }

            return;
        }

        foreach ($multas as $fine) {
            // Dias de atraso em relação à data de vencimento da mensalidade
            $diffDays = Carbon::parse($fine->data_fim)->diffInDays(now()->toDateString(), false);
            \Log::info('Numeros de dias diferentes '.$diffDays);

            // Multa de 25% (atraso de 10 a 20 dias)
            if ($diffDays > 0 && $fine->multa == 0) {
                self::processarMulta($fine, 25, 10, $aluno, $year_last_two_digits);
                \Log::info('Mula de 25% gerada para '.$aluno);
            }

            // Multa de 75% (atraso de 21 a 30 dias)
            elseif ($diffDays > 0 && $fine->multa == 25) {
                self::processarMulta($fine, 75, 10, $aluno, $year_last_two_digits);
                \Log::info('Mula de 75% gerada para '.$aluno);
            }

            // Multa de 100% (atraso superior a 30 dias)
            elseif ($diffDays > 0 && $fine->multa == 75) {
                self::processarMulta($fine, 100, Carbon::now()->endOfYear()->toDateString(), $aluno, $year_last_two_digits);
                \Log::info('Mula de 100% gerada para '.$aluno);
                $suspenso = true; // Marca o aluno como suspenso
            }

            $fine->save(); // Salva as alterações na mensalidade
        }

        // Se o aluno foi suspenso, atualiza o status
        if ($suspenso) {
            $alunoData = Aluno::find($aluno);
            if ($alunoData) {
                $alunoData->suspenso = true;
                $alunoData->save();
            }
        }
    }

    public static function processarMulta($fine, $multa, $prazo, $aluno, $year_last_two_digits)
    {
        // Calcula o valor total com a multa
        $valorBase = $fine->valor + $fine->valor_co_curricular - $fine->desconto;
        $valorMulta = ($multa / 100) * $valorBase;
        $valorTotal = $valorBase + $valorMulta;

        $nomeAluno = Aluno::find($aluno);
        $dataPrazo = '';

        // Cancela pagamento anterior (se houver)
        $novoPagamento = Pagamento::find($fine->pagamento_id);
        $novoPagamento->valor = $valorTotal;
        // Define o prazo de pagamento
        if (is_numeric($prazo)) {
            $novoPagamento->prazo_pagamento = Carbon::parse($fine->data_fim)->addDays($prazo);
            $dataPrazo = Carbon::parse($fine->data_fim)->addDays($prazo);
        } else {
            $novoPagamento->prazo_pagamento = $prazo; // Caso o prazo seja uma data fixa (último dia do ano)
            $dataPrazo = $prazo;
        }

        $dataFormatada = Carbon::parse($dataPrazo)->format('d-m-Y');
        $mesMaiusculo = strtoupper($fine->mes->nome);
        $valorFormatada = number_format($valorTotal, 2, ',', '.');

        if ($nomeAluno->desistiu == false) {
            $sms = 'Prezado encarregado do(a) '.$nomeAluno->pessoa->nome.' informamos que foi aplicada a multa de '.$multa.'% a mensalidade de '.$mesMaiusculo.' ENTIDADE: 90028, REFERENCIA: '.$novoPagamento->referencia.', VALOR: '.$valorFormatada.' MZN. Banco: Millennium BIM, Novo prazo '.$dataFormatada.'. NAO USAR ATM para pagar. Cumpra os prazos e evite agravamento da multa.';
            self::alertaMultaSMS($aluno, $sms);
        }

        if ($novoPagamento->save()) {
            // Atualiza a mensalidade com os novos detalhes
            $fine->multa = $multa;
            $fine->data_fim = $novoPagamento->prazo_pagamento;
        }
    }

    public static function alertaMultaSMS($aluno, $sms)
    { 
        $qtd = SMSService::quantidadeSMS($sms);
        $telefoneEncarregado = AlunoRelacaoPessoa::where('aluno_id', $aluno)->where('relacao_id', 1)->first();

        if ($telefoneEncarregado) {

            // padroniza telefone
            $telefone = substr($telefoneEncarregado->pessoa->telefone, -9);

            Mensagem::firstOrCreate(
                [
                    'descricao' => $sms,
                    'telefone'  => $telefone,
                    'canal'     => 'SMS',
                ],
                [
                    'tipo'       => 'Pendente',
                    'qtd'        => $qtd,
                    'custo_real' => 1.35 * $qtd,
                    'credito'    => 1.85 * $qtd,
                ]
            );

            \Log::info('Aluno notificado: '.$aluno);

            /*$existeMensagem = Mensagem::where('descricao', $sms)
                ->where('telefone', $telefone)
                ->where('canal', 'SMS')
                ->first();

            if (! $existeMensagem) {
                $mensagem = new Mensagem();
                $mensagem->telefone = substr($telefoneEncarregado->pessoa->telefone, -9);
                $mensagem->descricao = $sms;
                $mensagem->tipo = 'Pendente';
                $mensagem->qtd = $qtd;
                $mensagem->canal = 'SMS';
                $mensagem->custo_real = 1.2 * $qtd;
                $mensagem->credito = 1.8* $qtd;
                $mensagem->save();
                \Log::info('Aluno notificado: '.$aluno);
            }*/
        }
    }

    public static function referenciaMesSMS($alunoId, $sms)
    {
        $qtd = SMSService::quantidadeSMS($sms);

        $telefoneEncarregado = AlunoRelacaoPessoa::where('aluno_id', $alunoId)
            ->where('relacao_id', 1)
            ->with('pessoa') // já carrega a relação
            ->first();

        if ($telefoneEncarregado) {
            // padroniza telefone
            $telefone = substr($telefoneEncarregado->pessoa->telefone, -9);

            // verifica se já existe mensagem idêntica para esse telefone no mesmo dia
            $existeMensagem = Mensagem::where('descricao', $sms)
                ->where('telefone', $telefone)
                ->where('canal', 'SMS')
                ->first();

            if (! $existeMensagem) {
                $mensagem = new Mensagem();
                $mensagem->telefone   = $telefone;
                $mensagem->descricao  = $sms;
                $mensagem->tipo       = 'Pendente';
                $mensagem->qtd        = $qtd;
                $mensagem->canal      = 'SMS';
                $mensagem->custo_real = 1.35 * $qtd;
                $mensagem->credito    = 1.85 * $qtd;
                $mensagem->save();

                \Log::info("Aluno {$alunoId} notificado.");
            }
        }
    }


    public static function alunoTemDivida($alunoID)
    {
        $currentDate = Carbon::now(); // Data atual

        // Verifica se existe alguma mensalidade vencida, não paga e com multa
        return Mensalidade::where('data_fim', '<', $currentDate)
            ->where('aluno_id', $alunoID)
            ->where('multa', '>', 0)
            ->where('pagou', 0)
            ->exists(); // Retorna true se encontrar, false se não
    }

    // Email Anexo Por Email
    public static function facturaAnexoPropinaPDF($alunoCodigo)
    {
        $current_year = Carbon::now()->year;
        $today = Carbon::now();

        $aluno = Aluno::where('codigo', $alunoCodigo)->first();
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $mensalidades = Mensalidade::with('mes', 'pagamento')
                ->where('aluno_id', $aluno->id)
                ->where('pagou', 0)
                ->get();

        $pdf = \PDF::loadView('secretaria.propina.pdf.factura', ['anoAcademicoAtivo' => $anoAcademicoAtivo, 'today' => $today, 'mensalidades' => $mensalidades, 'aluno' => $aluno]);

        // Caminho temporário para salvar o PDF
        $path = public_path('relatorio');
        $fileName = $alunoCodigo.'.pdf';
        $fullPath = $path.'/'.$fileName;
        // Salvar o PDF no servidor
        $pdf->save($fullPath);

        return $fileName;
    }
}
