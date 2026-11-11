<?php

namespace App\Http\Controllers;

use Karson\MpesaPhpSdk\Mpesa;
use App\Models\Aluno;
use App\Models\AnoAcademico;
use App\Models\Escola;
use App\Models\Mensalidade;
use App\Models\Pagamento;
use App\Models\AlunoRelacaoPessoa;
use App\Models\Mensagem;
use App\Models\Matricula;
use App\Models\Taxa;
use App\Models\Classe;
use App\Models\Pauta;
use App\Models\DisciplinaCurriculo;
use App\Models\MensagemSessao;
use App\Models\Meses;
use App\Models\Mpesa1;
use App\Services\EntidadeReferenciaService;
use App\Services\MultaService;
use App\Services\ReceitaDespesaService;
use App\Services\SMSService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropinaController extends Controller
{
    public function index()
    {
        $alunos = Aluno::where('escola_id', Auth::user()->escola_id)->where('desistiu',0)->orderBy('id', 'desc')->get();
        $mensalidades = Mensalidade::where('escola_id', Auth::user()->escola_id)->orderBy('id', 'desc')->get();
        $meses = Meses::all();

        // Retorna a view com os dados carregados
        return view('secretaria.propina.index', [
            'alunos' => $alunos,
            'meses' => $meses,
        ]);
    }

    public function show($alunoCode)
    {
        $aluno = Aluno::where('codigo', $alunoCode)->first();

        //MultaService::getMulta($aluno->id);

        $mensalidades = Mensalidade::where('aluno_id', $aluno->id)->where('pagou', 0)->get();
        $mensalidadesPago = Mensalidade::where('aluno_id', $aluno->id)->where('pagou', 1)->get();

        // Retorna a view com os dados carregados
        return view('secretaria.propina.show', [
            'mensalidades' => $mensalidades,
            'aluno' => $aluno,
            'mensalidadesPago' => $mensalidadesPago,
        ]);
    }

    public function showAluno($alunoCode)
    {
        $aluno = Aluno::where('codigo', $alunoCode)->first();

        //MultaService::getMulta($aluno->id);

        $mensalidades = Mensalidade::where('aluno_id', $aluno->id)->where('pagou', 0)->get();
        $mensalidadesPago = Mensalidade::where('aluno_id', $aluno->id)->where('pagou', 1)->get();

        // Retorna a view com os dados carregados
        return view('secretaria.propina.showAluno', [
            'mensalidades' => $mensalidades,
            'aluno' => $aluno,
            'mensalidadesPago' => $mensalidadesPago,
        ]);
    }

    public function referenciaPropina($codigo)
    {

        try {

         DB::beginTransaction();

            $aluno = Aluno::where('codigo', $codigo)->first();
            $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
            $matricula = Matricula::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAcademicoAtivo->id)->first();
            $classe = Classe::where('id', $matricula->classe_id)->first();
            // Pagar na taxa selecionado pelo utser
            $taxa = Taxa::where('ciclo_id', $classe->ciclo_id)->first();

            //No inicios os alunos nao tem aulas CoCurricular
            $valorMensalidadeCoCurricular = 0; 
            $valorTotalMensalidade = 0;
            $valorMensalidadeCurricular = 0;
            $valorCoCurricular = 0;

            if ($matricula->plano == 1) {

                if ($matricula->periodo->tipo == 'meio_dia') {
                    $valorMensalidadeCurricular = $taxa->propina_meio_dia_normal;
                    $valorTotalMensalidade = $taxa->propina_meio_dia_normal;
                } elseif ($matricula->periodo->tipo == 'todo_dia') {
                    $valorMensalidadeCurricular = $taxa->propina_todo_dia_normal;
                    $valorTotalMensalidade = $taxa->propina_todo_dia_normal;
                }

            } elseif ($matricula->plano == 2) {

                if ($matricula->periodo->tipo == 'meio_dia') {
                    $valorMensalidadeCurricular = $taxa->propina_meio_dia_gold;
                    $valorTotalMensalidade = $taxa->propina_meio_dia_gold;
                } elseif ($matricula->periodo->tipo == 'todo_dia') {
                    $valorMensalidadeCurricular = $taxa->propina_todo_dia_gold;
                    $valorTotalMensalidade = $taxa->propina_todo_dia_gold;
                }
            }

            $this->gerarMensalidades($aluno->id, $anoAcademicoAtivo->id, $valorTotalMensalidade, $valorCoCurricular);
            $this->gerarPauta($aluno->id, $anoAcademicoAtivo->id, $matricula->classe_id, $matricula->turma_id);

            DB::commit();  

            return response()->json(['status' => 1, 'message' => 'Referencias Geradas com sucesso!']);

            } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function referenciaSMS($codigo,$mesID)
    {

        try {

            DB::beginTransaction();

                $aluno = Aluno::where('codigo', $codigo)->first();
                $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
                $telefoneEncarregado = AlunoRelacaoPessoa::where('aluno_id', $aluno->id)
                    ->where('relacao_id', 1)
                    ->with('pessoa') // já carrega a relação
                    ->first();

                $propinaActual = Mensalidade::where('id', $mesID)->where('aluno_id', $aluno->id)->first();

                $telefone = substr($telefoneEncarregado->pessoa->telefone, -9);

                $dataFormatada = Carbon::parse($propinaActual->data_fim)->format('d-m-Y');
                $mesMaiusculo = strtoupper($propinaActual->mes->nome);
                $valorFormatada = number_format($propinaActual->pagamento->valor, 2, ',', '.');

                $sms = 'Prezado encarregado do(a) '.$aluno->pessoa->nome.' pague a mensalidade de '.$mesMaiusculo.' prazo '.$dataFormatada.' ENTIDADE: 90028, REFERENCIA: '.$propinaActual->pagamento->referencia.', VALOR: '.$valorFormatada.' MZN. Banco: Millennium BIM. Evita multas';

                $qtd = SMSService::quantidadeSMS($sms);

                $mensagem = new Mensagem();
                $mensagem->telefone = $telefone;
                $mensagem->descricao = $sms;
                $mensagem->qtd = $qtd;
                $mensagem->canal = 'SMS';
                $mensagem->tipo = 'Pendente';
                $mensagem->custo_real = $qtd *1.35;
                $mensagem->credito = $qtd *1.85;

                if($mensagem->save())
                {
                    // Atualizando a sessão do usuário
                    MensagemSessao::updateOrCreate(
                        ['telefone' => substr($telefone, -9)], // Critério de busca
                        [
                            'ultima_conversa' => $sms,
                            'conversas' => DB::raw('conversas + 1'), // Incrementar conversas
                            'created_at' => now() // Atualizar data de criação quando necessário
                        ]
                    );

                    DB::commit();  

                    return response()->json(['status' => 1, 'message' => 'Pagamento efectuado com sucesso!']);
                }

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function cancelarPagamentoPropina($referencia)
    {
        try {
            DB::beginTransaction();

            $pagamento = Pagamento::where('referencia', $referencia)->first();
            $pagamento->pagou = false;
            $pagamento->descricao = '';
            $pagamento->motivo = '';
            $pagamento->tipo = 2;

            if ($pagamento->save()) {
                $propina = Mensalidade::where('pagamento_id', $pagamento->id)->first();
                $propina->pagou = false;

                if ($propina->save()) {
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Pagamento cancelada com sucesso.',
                    ]);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function corrigirPropinaStore(Request $request)
    {
        try {
            DB::beginTransaction();

            $current_year = Carbon::now()->year;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);

            $valor = floatval($request->input('valor') ?? 0);
            $valorCoCurricular = floatval($request->input('valor_co_curricular') ?? 0);
            $desconto = floatval($request->input('desconto') ?? 0);
            $multa = floatval($request->input('multa') ?? 0);

            $subtotal = $valor + $valorCoCurricular;
            $valorMulta = ($multa / 100) * $subtotal;
            $valorTotal = $subtotal - $desconto + $valorMulta;

            $propina = Mensalidade::where('id', $request->input('propinaID'))->first();

            if ($request->input('tipo_pagamento') == 0) {
                if ($request->input('valor_total') != 0) {
                    return response()->json(['status' => 0, 'message' => 'O Valor a Pagar deve ser igual a Zero(0)!']);
                }
                if ($request->input('motivo') == null) {
                    return response()->json(['status' => 0, 'message' => 'O Motivo da Isenção é Obrigatorio']);
                }
            }

            $pagamento = Pagamento::where('id', $propina->pagamento_id)->first();
            $pagamento->desconto = $desconto;
            $pagamento->metodo_pagamento_id = 3;
            $pagamento->user_id = Auth::user()->id;
            $pagamento->escola_id = Auth::user()->escola_id;
            $pagamento->categoria_id = 2;
            $pagamento->valor = $valorTotal;
            $pagamento->descricao = 'Confirmado Pagamento de Propina do Aluno '.$propina->aluno->codigo;
            $pagamento->motivo = $request->input('motivo');
            $pagamento->prazo_pagamento = $request->input('data_pagamento');
            $pagamento->tipo = $request->input('tipo_pagamento');  // Tipo Pagamento 0 Normal  Com Desconto 0 Isencao

            if ($pagamento->save()) {
                $propina->data_fim = $request->input('data_pagamento');
                $propina->desconto = $desconto;
                $propina->multa = $multa;
                $propina->valor = $valor;
                $propina->valor_co_curricular = $valorCoCurricular;

                if ($propina->save()) {
                    DB::commit();

                    return response()->json(['status' => 1, 'message' => 'Pagamento efectuado com sucesso!']);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function pagarPropinaStore(Request $request)
    {
        try {
            DB::beginTransaction();

            $current_year = Carbon::now()->year;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);

            $valor = floatval($request->input('valor') ?? 0);
            $valorCoCurricular = floatval($request->input('valor_co_curricular') ?? 0);
            $desconto = floatval($request->input('desconto') ?? 0);
            $multa = floatval($request->input('multa') ?? 0);

            $subtotal = $valor + $valorCoCurricular;
            $valorMulta = ($multa / 100) * $subtotal;
            $valorTotal = $subtotal - $desconto + $valorMulta;

            $propina = Mensalidade::where('id', $request->input('propinaID'))->first();

            /*if($request->input('tipo_pagamento')==1){

                if($request->input('desconto')==0){
                    return response()->json(['status' => 0, 'message' => 'O desconto é Obrigatorio']);
                }
                if($request->input('motivo')==null){
                    return response()->json(['status' => 0, 'message' => 'O Motivo de desconto é Obrigatorio']);
                }
            }*/

            if ($request->input('tipo_pagamento') != 0) {
                if ($request->input('referencia') == null) {
                    return response()->json(['status' => 0, 'message' => 'O Codigo de Talao é Obrigatorio']);
                }
            }

            if ($request->input('tipo_pagamento') == 0) {
                if ($request->input('valor_total') != 0) {
                    return response()->json(['status' => 0, 'message' => 'O Valor a Pagar deve ser igual a Zero(0)!']);
                }
                if ($request->input('motivo') == null) {
                    return response()->json(['status' => 0, 'message' => 'O Motivo da Isenção é Obrigatorio']);
                }
            }

            $pagamento = Pagamento::where('id', $propina->pagamento_id)->first();
            $pagamento->desconto = $desconto;
            $pagamento->entidade = Auth::user()->codigo;
            $pagamento->metodo_pagamento_id = 1;
            $pagamento->user_id = Auth::user()->id;
            $pagamento->pagou = true;
            $pagamento->escola_id = Auth::user()->escola_id;
            $pagamento->categoria_id = 2;
            $pagamento->valor = $valorTotal;
            $pagamento->descricao = $request->input('referencia');
            $pagamento->motivo = $request->input('motivo');
            $pagamento->data_pagamento = $request->input('data_pagamento');
            $pagamento->tipo = $request->input('tipo_pagamento');  // Tipo Pagamento 0 Normal  Com Desconto 0 Isencao

            if ($pagamento->save()) {
                $propina->pagou = true;
                $propina->desconto = $desconto;
                $propina->multa = $multa;

                if ($propina->save()) {
                    DB::commit();

                    return response()->json(['status' => 1, 'message' => 'Pagamento efectuado com sucesso!']);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function pagarPropinaMpesaStore(Request $request)
    {
        try {
            DB::beginTransaction();

            $current_year = Carbon::now()->year;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);

            $propina = Mensalidade::where('id', $request->input('propinaID'))->first();

            $telefone = $request->input('telefone');
            $valor = $request->input('valor_total');
            $codigo1 = Str::random(8);

            // Expressão regular: começa com 84 ou 85 e tem 9 dígitos no total
            if (!preg_match('/^8[45]\d{7}$/', $telefone)) {

                return response()->json(['status' => 0, 'message' => 'Número inválido. Deve começar com 84 ou 85']);

            }

            $referencia = str_replace('.', '', $propina->pagamento->recibo);         

            // Remove pontos e troca vírgula por ponto
            $valor = str_replace('.', '', $valor); // remove separadores de milhar
            $valor = str_replace(',', '.', $valor); // troca vírgula decimal por ponto

            $mpesa = new \Karson\MpesaPhpSdk\Mpesa();

            // Credenciais
            $mpesa->setApiKey('eotig3de5fbkdw0w0j68y12uhn77wy6b');
            $mpesa->setPublicKey('MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAmptSWqV7cGUUJJhUBxsMLonux24u+FoTlrb+4Kgc6092JIszmI1QUoMohaDDXSVueXx6IXwYGsjjWY32HGXj1iQhkALXfObJ4DqXn5h6E8y5/xQYNAyd5bpN5Z8r892B6toGzZQVB7qtebH4apDjmvTi5FGZVjVYxalyyQkj4uQbbRQjgCkubSi45Xl4CGtLqZztsKssWz3mcKncgTnq3DHGYYEYiKq0xIj100LGbnvNz20Sgqmw/cH+Bua4GJsWYLEqf/h/yiMgiBbxFxsnwZl0im5vXDlwKPw+QnO2fscDhxZFAwV06bgG0oEoWm9FnjMsfvwm0rUNYFlZ+TOtCEhmhtFp+Tsx9jPCuOd5h2emGdSKD8A6jtwhNa7oQ8RtLEEqwAn44orENa1ibOkxMiiiFpmmJkwgZPOG/zMCjXIrrhDWTDUOZaPx/lEQoInJoE2i43VN/HTGCCw8dKQAwg0jsEXau5ixD0GUothqvuX3B9taoeoFAIvUPEq35YulprMM7ThdKodSHvhnwKG82dCsodRwY428kg2xM/UjiTENog4B6zzZfPhMxFlOSFX4MnrqkAS+8Jamhy1GgoHkEMrsT5+/ofjCx0HjKbT5NuA2V/lmzgJLl3jIERadLzuTYnKGWxVJcGLkWXlEPYLbiaKzbJb2sYxt+Kt5OxQqC1MCAwEAAQ==');
            $mpesa->setServiceProviderCode('171717');
            $mpesa->setEnv('test'); // 'live' production environment

            $invoice_id = str_replace('-', '', $referencia); // Eg: Invoice number
            $phone_number = '258' . preg_replace('/^(\+?258)?/', '', $telefone); // Prefixed with country code (258)
            $amount = floatval(str_replace('.', '', $valor) ?? 0);
            $reference_id = $invoice_id.''.$codigo1; // Should be unique for each transaction

            $result = $mpesa->c2b($invoice_id, $phone_number, $amount, $reference_id);


            $transacao = new Mpesa1();
            $transacao->numero_factura = $propina->pagamento->recibo;
            $transacao->descricao = $referencia;
            $transacao->telefone = $telefone;
            $transacao->valor = $valor;
            $transacao->referencia = $propina->pagamento->referencia;
            $transacao->categoria_id = 2;
            $transacao->response_desc = $result->response->output_ResponseDesc;
            $transacao->response_code = $result->response->output_ResponseCode;
            $transacao->transaction_id = $result->response->output_TransactionID;
            $transacao->conversation_id = $result->response->output_ConversationID;
            $transacao->third_party_reference = $result->response->output_ThirdPartyReference;
            $transacao->save();


            if ($result->response->output_ResponseCode == 'INS-0') {

                    $pagamento = Pagamento::where('id', $propina->pagamento_id)->first();
                    $pagamento->entidade = Auth::user()->codigo;
                    $pagamento->metodo_pagamento_id = 2;
                    $pagamento->user_id = Auth::user()->id;
                    $pagamento->pagou = true;
                    $pagamento->escola_id = Auth::user()->escola_id;
                    $pagamento->categoria_id = 2;
                    $pagamento->valor = $valor;
                    $pagamento->descricao = $result->response->output_TransactionID;

                    if ($pagamento->save()) {
                        $propina->pagou = true;

                        if ($propina->save()) {
                            DB::commit();

                            return response()->json(['status' => 1, 'message' => 'Pagamento efectuado com sucesso!']);
                        }
                    }

                } else {

                    if ($result->response->output_ResponseCode == 'INS-1') {
                        return response()->json(['status' => 0, 'message' => 'Erro Interno ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-6') {
                        return response()->json(['status' => 0, 'message' => 'A transacao Falhou ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-5') {
                        return response()->json(['status' => 0, 'message' => 'Transacao Cancelada por Cliente ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-9') {
                        return response()->json(['status' => 0, 'message' => 'Tempo de Espera Esgotado ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-15') {
                        return response()->json(['status' => 0, 'message' => 'Valor Invalido ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-25') {
                        return response()->json(['status' => 0, 'message' => 'Credencial de segurança inválida']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-25') {
                        return response()->json(['status' => 0, 'message' => 'Credencial de segurança inválida']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-2006') {
                        return response()->json(['status' => 0,  'message' => 'Saldo Insuficiente ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-2001') {
                        return response()->json(['status' => 0,  'message' => 'Senha Errada... ']);
                    }

                    return response()->json(['status' => 0, 'message' => 'Ocorreu um erro: '.$result->response->output_ResponseDesc]);
           }


        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function pagarPropina($propinaID)
    {
        //$anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        // Buscar Alunos Que nao pagaram Matricula
        $propina = Mensalidade::where('id', $propinaID) // Certifique-se de carregar a relação 'pessoa' também
                ->where('escola_id', Auth::user()->escola_id)
                //->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->first();

        // Retorna a view com os dados carregados
        return view('secretaria.propina.pagamento.create', [
            'propina' => $propina,
            //'anoAcademicoAtivo' => $anoAcademicoAtivo,
        ]);
    }

    public function pagarPropinaMpesa($propinaID)
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        // Buscar Alunos Que nao pagaram Matricula
        $propina = Mensalidade::where('id', $propinaID) // Certifique-se de carregar a relação 'pessoa' também
                ->where('escola_id', Auth::user()->escola_id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->first();

        // Retorna a view com os dados carregados
        return view('secretaria.propina.pagamento.createMpesa', [
            'propina' => $propina,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
        ]);
    }

    public function corrigirPropina($propinaID)
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        // Buscar Alunos Que nao pagaram Matricula
        $propina = Mensalidade::where('id', $propinaID) // Certifique-se de carregar a relação 'pessoa' também
                ->where('escola_id', Auth::user()->escola_id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->first();

        // Retorna a view com os dados carregados
        return view('secretaria.propina.pagamento.edit', [
            'propina' => $propina,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        $current_year = Carbon::now()->year;
        $currentDate = Carbon::now();
        $year_last_two_digits = substr($current_year, -2);
        $current_month = Carbon::now()->format('m');
        $mensalidades = $request->input('meses');

        $escola = Escola::where('id', Auth::user()->escola_id)->first();
        $aluno = Aluno::where('id', $request->input('aluno'))->first();
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $todosPagamento = Pagamento::all();
        $totalMensalidades = Mensalidade::where('ano_academico_id', $anoAcademicoAtivo->id)->where('pagou', 1)->get();

        $fatura = str_pad(count($todosPagamento) + 1, 7, '0', STR_PAD_LEFT).''.$year_last_two_digits;
        $codigoMatricula = str_pad(count($totalMensalidades) + 1, 8, '0', STR_PAD_LEFT).'.'.$year_last_two_digits;
        $recibo = str_pad(count($todosPagamento) + 1, 7, '0', STR_PAD_LEFT).''.$year_last_two_digits;
        $valorTotal = substr($request['value'], 0, -3);
        $referencia = EntidadeReferenciaService::GerarReferencia($fatura, $valorTotal);

        try {
            $pagamento = new Pagamento();
            $pagamento->recibo = $recibo;
            $pagamento->factura = $fatura;
            $pagamento->desconto = $request->input('discount');
            $pagamento->entidade = 90018;
            $pagamento->referencia = $referencia;
            $pagamento->metodo_pagamento_id = 3;
            $pagamento->aluno_id = $aluno->id;
            $pagamento->user_id = Auth::user()->id;
            $pagamento->escola_id = Auth::user()->escola_id;
            $pagamento->taxa_id = 13;
            $pagamento->categoria_id = 2;
            $pagamento->valor = $valorTotal;

            // Verifica se a data atual é antes ou depois do dia 5
            if ($currentDate->day <= 5) {
                // Define o prazo como o dia 5 do mês atual
                $pagamento->prazo_pagamento = $currentDate->startOfMonth()->addDays(4); // Dia 5 do mês atual
            } else {
                // Define o prazo como o dia 5 do próximo mês
                $pagamento->prazo_pagamento = $currentDate->addMonth()->startOfMonth()->addDays(4); // Dia 5 do próximo mês
            }

            if ($pagamento->save()) {
                if (!is_null($mensalidades) && is_array($mensalidades)) {
                    foreach ($mensalidades as $index => $mes) {
                        // Tirar o ID
                        $partes = explode('-', $mes);
                        $bribeId = $partes[0];
                        // FIm
                        $mensalidades1 = Mensalidade::find($bribeId);
                        $mensalidades1->pagou = false;
                        $mensalidades1->pagamento_id = $pagamento->id;
                        $mensalidades1->save();
                    }
                }

                // categoria 1 = Matricula 2 = Propinas
                $descricao1 = 'Factura de Propina ';
                ReceitaDespesaService::receita($valorTotal, 2, $descricao1, $pagamento->id, false, $referencia);

                DB::commit();

                return response()->json(['message' => 'Factura Gerada com Sucesso', 'factura' => $referencia, 'status' => 201], 201);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 400,
                'message' => 'Ocorreu um erro:'.PHP_EOL.$e->getMessage(),
            ]);
        }
    }

    public function relatorioPropinas(Request $request)
    {
        $current_year = Carbon::now()->year;
        $today = Carbon::now();

        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $escola = Escola::where('id', Auth::user()->escola_id)->first();
        $mes = $request->input('mes');

        $alunos = Aluno::where('desistiu', 0)->where('escola_id', Auth::user()->escola_id)->get();

        $mesSelecionado = Meses::where('id',$mes)->first();

        /*$mensalidades = Mensalidade::with(['mes', 'aluno.pessoa','pagamento'])
                        ->where('escola_id', Auth::user()->escola_id)
                        ->where('ano_academico_id', $anoAcademicoAtivo->id)
                        ->where('mes_id', '<',3)
                        ->orderBy('mes_id')
                        ->orderBy('aluno_id')
                        ->get()
                        ->groupBy(function ($item) {
                            return $item->mes->nome;
                        });*/


        /*$mensalidadesPorMes = Mensalidade::with(['mes', 'aluno.pessoa', 'pagamento'])
            ->where('escola_id', Auth::user()->escola_id)
            ->where('ano_academico_id', $anoAcademicoAtivo->id)
            ->where('disistiu', 0)
            ->where('mes_id', $mes)
            ->orderBy('mes_id')
            ->orderBy('aluno_id')
            ->get(); */

        $mensalidadesPorMes = Mensalidade::with([
            'mes',
            'aluno.pessoa',
            'pagamento',
            //'aluno.matriculas.classe' // carrega as classes
        ])
        ->where('escola_id', Auth::user()->escola_id)
        ->where('ano_academico_id', $anoAcademicoAtivo->id)
        ->where('disistiu', 0)
        ->where('mes_id', $mes)
        ->orderBy('aluno_id')
        ->get()
        ->groupBy(function($mensalidade) use ($anoAcademicoAtivo) {
            // pega a classe da matrícula do ano ativo
            $matricula = $mensalidade->aluno->matriculas->where('ano_academico_id', $anoAcademicoAtivo->id)->first();
            return $matricula ? $matricula->classe->nome : 'Sem Classe';
        }); 


        $pdf = \PDF::loadView('secretaria.propina.pdf.propinas', [
            'escola' => $escola,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'mesSelecionado' => $mesSelecionado,
            'today' => $today,
            'mensalidadesPorMes' => $mensalidadesPorMes,
        ])->setPaper('a4', 'Portrait');

        $fikeName = 'Propinas-'.$current_year;

        return $pdf->stream($fikeName.'.pdf');
    }

    public function facturaPDF($alunoCodigo)
    {
        $current_year = Carbon::now()->year;
        $today = Carbon::now();

        $aluno = Aluno::where('codigo', $alunoCodigo)->first();
        MultaService::getMulta($aluno->id);

        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $mensalidades = Mensalidade::with('mes', 'pagamento')
                ->where('aluno_id', $aluno->id)
                ->where('pagou', 0)
                ->get();

        $pdf = \PDF::loadView('secretaria.propina.pdf.factura', ['anoAcademicoAtivo' => $anoAcademicoAtivo, 'today' => $today, 'mensalidades' => $mensalidades, 'aluno' => $aluno]);
        $fikeName = 'Fatura-'.$alunoCodigo;

        return $pdf->stream($fikeName.'.pdf');
    }

    public function reciboPropinaPDF($referencia)
    {
        $current_year = Carbon::now()->year;
        $today = Carbon::now();

        $pagamento = Pagamento::where('referencia', $referencia)->first();
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();

        $aluno = Aluno::where('id', $pagamento->aluno_id)->first();
        $mensalidade = Mensalidade::where('pagamento_id', $pagamento->id)->first();

        $pdf = \PDF::loadView('secretaria.propina.pdf.recibo1', ['pagamento' => $pagamento, 'anoAcademicoAtivo' => $anoAcademicoAtivo, 'today' => $today, 'mensalidade' => $mensalidade, 'aluno' => $aluno]);
        $fikeName = 'Recibo-'.$referencia;

        return $pdf->stream($fikeName.'.pdf');
    }

    public function reciboPDF($alunoCodigo)
    {
        $current_year = Carbon::now()->year;
        $today = Carbon::now();

        $aluno = Aluno::where('codigo', $alunoCodigo)->first();
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $mensalidades = Mensalidade::with('mes', 'pagamento')
                ->where('aluno_id', $aluno->id)
                ->where('pagou', 1)
                ->get();

        $pdf = \PDF::loadView('secretaria.propina.pdf.recibo', ['anoAcademicoAtivo' => $anoAcademicoAtivo, 'today' => $today, 'mensalidades' => $mensalidades, 'aluno' => $aluno]);
        $fikeName = 'Recibo-'.$alunoCodigo;

        return $pdf->stream($fikeName.'.pdf');
    }

     /* Esse metodo gera a mensalidade anual de cada estudante
     Onde recebe o id de estudante, o Ano Academico, e a TaxaID
     Para preencher mensalidades que devem ser pagos
    */
    public function gerarMensalidades($alunoID, $anoAcademicoID, $valor, $valorCoCurricular)
    {
        $months = Meses::all();
        $anoAcademicoAtivo = AnoAcademico::where('id', $anoAcademicoID)->first();

        $current_year = $anoAcademicoAtivo->ano;
        $current_month = Carbon::now()->format('m');
        $year_last_two_digits = substr($current_year, -2);

        $valorTotal = $valor + $valorCoCurricular;

        foreach ($months as $month) {
            $todosPagamento = Pagamento::all();
            $fatura = str_pad(count($todosPagamento) + 1, 5, '0', STR_PAD_LEFT).''.$current_month.''.$year_last_two_digits;
            $recibo = str_pad(count($todosPagamento) + 1, 5, '0', STR_PAD_LEFT).''.$year_last_two_digits;
            $referencia = EntidadeReferenciaService::GerarReferencia($fatura, $valorTotal);

            $pagamento = new Pagamento();
            $pagamento->recibo = $recibo;
            $pagamento->factura = $fatura;
            $pagamento->desconto = 0;
            $pagamento->entidade = 90028;
            $pagamento->entidade = 90028;
            $pagamento->referencia = $referencia;
            $pagamento->metodo_pagamento_id = 3;
            $pagamento->aluno_id = $alunoID;
            $pagamento->user_id = Auth::user()->id;
            $pagamento->escola_id = Auth::user()->escola_id;
            $pagamento->categoria_id = 2;
            $pagamento->valor = $valorTotal;
            $pagamento->prazo_pagamento = Carbon::createFromFormat('Y-m-d', $month->data_inicio)->setYear($anoAcademicoAtivo->ano);
            $pagamento->descricao = 'Confirmado Pagamento da propina de '.$month->nome.' '.$anoAcademicoAtivo->ano;

            if ($pagamento->save()) {
                // categoria 1 = Matricula 2 = Propinas
                $descricao1 = 'Factura da Propina '.$month->nome;
                $descricao1 = 'Factura da Propina '.$month->nome;
                ReceitaDespesaService::receita($valorTotal, 2, $descricao1, $pagamento->id, false, $referencia);

                $mensalidade = new Mensalidade();
                $mensalidade->valor = $valor;
                $mensalidade->valor_co_curricular = $valorCoCurricular;
                $mensalidade->mes_id = $month->id;
                $mensalidade->ano_academico_id = $anoAcademicoID;
                $mensalidade->data_inicio = Carbon::createFromFormat('Y-m-d', $month->data_inicio)->setYear($anoAcademicoAtivo->ano);
                $mensalidade->data_fim = Carbon::createFromFormat('Y-m-d', $month->data_fim)->setYear($anoAcademicoAtivo->ano);
                $mensalidade->aluno_id = $alunoID;
                $mensalidade->escola_id = Auth::user()->escola_id;
                $mensalidade->pagamento_id = $pagamento->id;
                $mensalidade->save();
            }
        }
    }

    /* Esse metodo gera a Pauta anual de cada estudante
     Onde recebe o id de estudante, o Ano Academico, e a TaxaID
     Para preencher mensalidades que devem ser pagos
    */
    public function gerarPauta($alunoID, $anoAcademicoID, $classeID, $turmaID)
    {    
        $curriculos = DisciplinaCurriculo::where('classe_id', $classeID)
                            ->where('estado', 1)
                            ->get();
         $anoAcademicoAtivo = AnoAcademico::where('id', $anoAcademicoID)->first();

        foreach ($curriculos as $curriculo) {

           $verificarPauta = Pauta::where('ano_academico_id', $anoAcademicoAtivo->id)->where('disciplina_id',$curriculo->disciplina_id)->where('classe_id',$classeID)->first();

           if($verificarPauta==null)
            {
                $pauta = new Pauta();
                $pauta->ano_academico_id = $anoAcademicoID;
                $pauta->disciplina_id = $curriculo->disciplina_id;
                $pauta->classe_id = $classeID;
                $pauta->aluno_id = $alunoID;
                $pauta->turma_id = $turmaID;
                $pauta->escola_id = Auth::user()->escola_id;
                $pauta->save();
            }
            
        }
    }
}
