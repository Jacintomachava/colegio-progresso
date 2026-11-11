<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Aluno;
use App\Models\AnoAcademico;
use App\Models\Mensagem;
use App\Models\BIM;
use App\Models\Mensalidade;
use App\Models\Pagamento;
use App\Models\Matricula;
use App\Models\AlunoRelacaoPessoa;
use App\Services\ReceitaDespesaService;
use App\Services\SMSService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApiEntidadeReferenciaController extends Controller
{

    public function validarDocumento(Request $request)
    {
        // Captura os dados da requisição
        $Apikey = $request->input('Apikey');
        $codigoEntidade = $request->input('codigoEntidade');
        $referenciaDoc = $request->input('referenciaDoc');
        $montanteTran = $request->input('montanteTran'); // Converte para float
        $ValidAPIKey = '1|k6gl3BSinezwb5C3nngYuNQm1XtaoWw40e1LyXyy';

        $currentDateFormatted = Carbon::now()->format('Y/m/d');
        $currentDate = Carbon::now();

        \Log::info("📩 Pegando todo request na consulta...".$referenciaDoc.' entidade: '.$codigoEntidade.' Valor: '.$montanteTran);

        // Verifica API Key (recomenda-se guardar no .env)
        //if (strtolower($Apikey) === strtolower($ValidAPIKey)) {
            // Valida os parâmetros de entrada
            if (
                is_numeric($codigoEntidade) &&
                is_numeric($referenciaDoc) &&
                is_numeric($montanteTran) &&
                strlen($codigoEntidade) == 5 &&
                strlen($referenciaDoc) == 11
            ) {
                // Consulta o documento na base de dados
                $validarDocumento = Pagamento::where('referencia', $referenciaDoc)
                    ->where('entidade', $codigoEntidade)
                    //->where('cancelado', 0)
                    //->where('prazo_pagamento', '>=', $currentDate)
                    ->first();

                //\Log::info("📩 Parametros validados...");

                // Verifica se o documento foi encontrado
                if (!$validarDocumento) {

                    \Log::info("📩 Consulta Guia nao encontrado...");

                    return response()->json([
                        'msgid' => 'E004',
                        'msgtype' => 'E',
                        'msg' => 'A Guia não foi encontrada',
                        'estado' => 'P002',
                        'datadoc' => $currentDateFormatted,
                        'valorTotal' => $montanteTran
                    ]);
                }

                // Compara o valor do documento com o montante informado
                if (round($validarDocumento->valor, 2) != round($montanteTran / 100, 2)) {

                    \Log::info("📩 Montante indicado inválido...");
                    return response()->json([
                        'msgid' => 'E007',
                        'msgtype' => 'E',
                        'msg' => 'Montante indicado inválido',
                        'estado' => 'P002',
                        'datadoc' => $currentDateFormatted,
                        'valorTotal' => $montanteTran
                    ]);
                }

                // Verifica se o documento ainda não foi pago
                if ($validarDocumento->pagou == 0) {
                    $valorTotalInteiro = round($validarDocumento->valor * 100);
                    $data_formatada = Carbon::now()->format('Y/m/d'); // Carbon::parse($validarDocumento->created_at)->format('Y/m/d');

                    \Log::info("📩 Validação efectuada com sucesso...");
                    return response()->json([
                        'msgid' => 'S001',
                        'msgtype' => 'S',
                        'msg' => 'Validação efectuada com sucesso',
                        'datadoc' => $data_formatada,
                        'valorTotal' => $valorTotalInteiro,
                        'estado' => 'P002'
                    ]);
                }

                // Documento já foi pago
                if ($validarDocumento->pagou == 1) {
                    \Log::info("📩 Documento já foi pago...");
                    return response()->json([
                        'msgid' => 'E008',
                        'msgtype' => 'E',
                        'msg' => 'A Referência já foi paga, há tentativa de duplicação.',
                        'estado' => 'P002',
                        'datadoc' => $currentDateFormatted,
                        'valorTotal' => $montanteTran
                    ]);
                }

                // Fallback interno de erro
                \Log::info("📩 Fallback interno de erro...");
                return response()->json([
                    'msgid' => 'E002',
                    'msgtype' => 'E',
                    'msg' => 'Erro interno de processamento',
                    'estado' => 'P002',
                    'datadoc' => $currentDateFormatted,
                    'valorTotal' => $montanteTran
                ]);
            } else {
                // Parâmetros inválidos
                \Log::info("📩 Parâmetros inválidos...");
                return response()->json([
                    'msgid' => 'E005',
                    'msgtype' => 'E',
                    'msg' => 'Parâmetro de entrada inválido',
                    'estado' => 'P002',
                    'datadoc' => $currentDateFormatted,
                    'valorTotal' => $montanteTran
                ]);
            }
        /*} else {
            // API Key inválida
             \Log::info("📩 API Key invalida nas consultas...");
            return response()->json([
                'msgid' => 'E003',
                'msgtype' => 'E',
                'msg' => 'API Key inválida',
                'datadoc' => $currentDateFormatted,
                'valorTotal' => $montanteTran
            ]);
        }*/

        // Fallback geral de erro (caso nenhum if seja satisfeito)
         \Log::info("📩 Fallback geral de erro (caso nenhum if seja satisfeito)...");
        return response()->json([
            'msgid' => 'E006',
            'msgtype' => 'E',
            'msg' => 'Ocorreu um erro desconhecido',
            'datadoc' => $currentDateFormatted,
            'valorTotal' => $montanteTran
        ]);
    }

    public function registoPagamento(Request $request)
    {
        $Apikey = $request->input('Apikey');
        $codigoEntidade = $request->input('codigoEntidade');
        $referenciaDoc = $request->input('referenciaDoc');
        $datatransaccao = $request->input('datatransaccao');
        $valor = $request->input('valor'); 
        $refbancariatran = $request->input('refbancariatran');

        $ValidAPIKey = '1|k6gl3BSinezwb5C3nngYuNQm1XtaoWw40e1LyXyy';
    
        $currentDateFormatted = Carbon::now()->format('Y/m/d');

        \Log::info("📩 Pegando todo request no Registo Pagamento: ".$referenciaDoc.' entidade: '.$codigoEntidade.' Valor: '.$valor);

        DB::beginTransaction();
    
        //if (strtolower($Apikey) === strtolower($ValidAPIKey)) {
            if (
                is_numeric($codigoEntidade) &&
                is_numeric($referenciaDoc) &&
                strlen($codigoEntidade) == 5 &&
                strlen($referenciaDoc) == 11 &&
                is_numeric($valor)
            ) {
                
    
                try {
                    // Verifica se o documento existe
                    $registoDocumento = Pagamento::where('referencia', $referenciaDoc)
                        ->where('entidade', $codigoEntidade)
                        ->first();
    
                    if (!$registoDocumento) {

                        \Log::info("📩 Pagamento Guia nao encontrado no registo de pagamento: ");

                        return response()->json([
                            'msgid' => 'E015',
                            'msgtype' => 'E',
                            'msg' => 'A Guia não foi encontrada ',
                            'referenciarecibo' => null,
                            'data' => $currentDateFormatted
                        ]);
                    }
    
                    if (round($registoDocumento->valor, 2) != round($valor / 100, 2)) {

                        \Log::info("📩 Montante indicado inválido no pagamento...");

                         DB::rollBack();

                        return response()->json([
                            'msgid' => 'E007',
                            'msgtype' => 'E',
                            'msg' => 'Montante indicado inválido',
                            'referenciarecibo' => null,
                            'data' => $currentDateFormatted
                        ]);
                    }
    
                    if ($registoDocumento->pagou == 0) {
                        // Criação do registo BIM
                        $bim = new BIM();
                        $bim->entity = $codigoEntidade;
                        $bim->reference = $referenciaDoc;
                        $bim->transaction_data = $datatransaccao;
                        $bim->value = round($valor / 100, 2);
                        $bim->transaction_reference = $refbancariatran;
    
                        // Marcar documento como pago
                        $registoDocumento->pagou = true;
                        $registoDocumento->data_pagamento = Carbon::now();

                        //Mandar para JSON
                        $data_formatada = Carbon::now()->format('Y/m/d');
                        $recibo = $registoDocumento->recibo;

    
                        if ($bim->save() && $registoDocumento->save()) {
                            
                            DB::commit();
    
                            \Log::info("📩 Pedido processado com sucesso...");
    
                            return response()->json([
                                'msgid' => 'S011',
                                'msgtype' => 'S',
                                'msg' => 'Pedido processado com sucesso',
                                'referenciarecibo' => $recibo,
                                'data' => $data_formatada
                            ]);
                        }
    
                        // Se por algum motivo não salvou corretamente
                        DB::rollBack();
    
                        \Log::info("📩 Erro interno de processamento...");
                        return response()->json([
                            'msgid' => 'E013',
                            'msgtype' => 'E',
                            'msg' => 'Erro interno de processamento',
                            'referenciarecibo' => null,
                            'data' => $currentDateFormatted
                        ]);
                    }
    
                    if ($registoDocumento->pagou == 1) {

                        \Log::info("📩 A Referência já foi paga, há tentativa de duplicação...");

                         DB::rollBack();

                        return response()->json([
                            'msgid' => 'E008',
                            'msgtype' => 'E',
                            'msg' => 'A Referência já foi paga, há tentativa de duplicação',
                            'referenciarecibo' => null,
                            'data' => $currentDateFormatted
                        ]);
                    }
    
                    // Fallback inesperado
                    DB::rollBack();

                    \Log::info("📩 Erro interno de processamento...");

                    return response()->json([
                        'msgid' => 'E013',
                        'msgtype' => 'E',
                        'msg' => 'Erro interno de processamento',
                        'referenciarecibo' => null,
                        'data' => $currentDateFormatted
                    ]);
                } catch (\Exception $e) {

                    DB::rollBack();

                    \Log::info("📩 Ocorreu um erro desconhecido:");

                    return response()->json([
                        'msgid' => 'E017',
                        'msgtype' => 'E',
                        'msg' => 'Ocorreu um erro desconhecido: ' . $e->getMessage(),
                        'referenciarecibo' => null,
                        'data' => $currentDateFormatted
                    ]);
                }
            } else {

                \Log::info("📩 Parâmetro de entrada inválido:");

                 DB::rollBack();

                return response()->json([
                    'msgid' => 'E016',
                    'msgtype' => 'E',
                    'msg' => 'Parâmetro de entrada inválido',
                    'referenciarecibo' => null,
                    'data' => $currentDateFormatted
                ]);
            }
        /*} else {

            \Log::info("📩 API Key inválida: nos Pagamentos ");

            return response()->json([
                'msgid' => 'E014',
                'msgtype' => 'E',
                'msg' => 'API Key inválida',
                'referenciarecibo' => null,
                'data' => $currentDateFormatted
            ]);
        }*/
    }


    /*
    public function pagamentoMensalidade($pagamentoID)
    {
        Mensalidade::where('pagamento_id', $pagamentoID)
             ->update(['pagou' => 1]);
    }

    public function pagamentoMatricula($pagamentoID){

        Matricula::where('pagamento_id', $pagamentoID)
            ->update(['pagou' => 1]);
    } */



    
}
