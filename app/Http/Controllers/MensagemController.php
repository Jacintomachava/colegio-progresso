<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnoAcademico;
use App\Models\Taxa;
use App\Models\Disciplina;
use App\Models\Periodo;
use App\Models\Classe;
use App\Models\Escola;
use App\Models\Aluno;
use App\Models\MetodoPagamento;
use App\Models\NotificarSaldoSMS;
use App\Models\Mensagem;
use App\Models\MensagemSessao;
use App\Models\PlanoEducativoIndividualizado;
use App\Models\Turma;
use App\Models\CoCurricular;
use App\Models\AlunoRelacaoPessoa;
use App\Models\Pagamento;
use App\Models\User;
use App\Models\Medico;
use App\Models\Relacao;
use App\Models\Saude;
use App\Models\RecolherCrianca;
use App\Models\ContactoUrgencia;
use App\Models\Irmao;
use App\Models\Matricula;
use App\Models\SaldoSMS;
use App\Models\Mensalidade;
use App\Models\DisciplinaCurriculo;
use App\Models\Pauta;
use App\Models\Pessoa;
use App\Models\Professor;
use App\Models\Encarregado;
use App\Models\MensagemPeriodica;
use App\Models\EscolaAnterior;
use App\Models\CompraCredito;
use App\Services\ReceitaDespesaService;
use App\Services\EntidadeReferenciaService;
use App\Services\MultaService;
use App\Services\EmailService;
use App\Services\SMSService;
use App\Services\PautaFinalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class MensagemController extends Controller
{
    //
    public function index()
    {
        $mensagens = Mensagem::orderBy('updated_at', 'desc')->get();
        $contatos = MensagemSessao::orderBy('updated_at', 'desc')->get();
        $pacotes = CompraCredito::orderBy('updated_at', 'desc')->get();

        //Mensagem
        $saldo = SaldoSMS::where('codigo','saldo')->first();
        $creditoSMSPendente = Mensagem::where('tipo','Pendente')->sum('credito');
        $mensagensPeriodicas = MensagemPeriodica::all();

        // 🟢 Gráfico 1: Consumo de créditos por mês e canal (WhatsApp e SMS)
        $consumoPorCanal = DB::table('mensagem')
                    ->selectRaw("DATE_FORMAT(created_at, '%M') as mes, canal, SUM(credito) as total_credito")
                    ->groupBy('mes', 'canal')
                    ->orderBy('mes')
                    ->get();

            // Obter os meses únicos
        $meses = $consumoPorCanal->pluck('mes')->unique()->values();

        // 🟢 Gráfico 2: Consumo de crédito e lucro por mês
        $consumoLucroPorMes = Mensagem::selectRaw("DATE_FORMAT(created_at, '%M') as mes, SUM(credito) as total_credito, SUM(credito - custo_real) as lucro")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Retorna a view com os dados carregados
        return view('mensagem.index', [
            'mensagens' => $mensagens,
            'contatos' => $contatos,
            'pacotes' => $pacotes,
            //Graficos
            'consumoPorCanal' => $consumoPorCanal,
            'consumoLucroPorMes' => $consumoLucroPorMes,
            'meses' => $meses,
            //Saldos
            'saldo' => $saldo,
            'creditoSMSPendente' => $creditoSMSPendente,
            'mensagensPeriodicas' => $mensagensPeriodicas,
        ]);

    }

    public function notificacaoSaldo()
    {
        $userActual = Auth::user();
        $saldo = SaldoSMS::where('codigo','saldo')->first();
        $contactos = NotificarSaldoSMS::all();

        $escolas = Escola::all();
        
        // Retorna a view com os dados carregados
        return view('mensagem.notificarSaldo', [
            'saldo' => $saldo,
            'escolas' => $escolas,
            'contactos' => $contactos,
            'escola' => Auth::user()->escola_id,
        ]);
    }

    public function apagarContacto($id)
    {
        $contacto = NotificarSaldoSms::findOrFail($id);

        $contacto->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Contacto removido com sucesso.'
        ]);
    }

    public function atualizarLimite(Request $request)
    {
        $request->validate([
            'saldo_limite' => 'required|numeric|min:0'
        ]);

        $saldo = SaldoSms::firstOrFail();

        $saldo->saldo_limite = $request->saldo_limite;
        $saldo->save();

        return response()->json([
            'status' => 1,
            'message' => 'Saldo de aviso atualizado com sucesso.',
            'saldo_limite' => number_format($saldo->saldo_limite, 2,',','.')
        ]);
    }

    public function guardarContacto(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'contacto' => ['required','regex:/^(82|83|84|85|86|87)[0-9]{7}$/']
        ]);

        $contacto = new NotificarSaldoSMS();

        $contacto->nome = $request->nome;
        // Usa o nome real da tua coluna
        $contacto->contacto_anotificar = $request->contacto;
        $contacto->save();

        return response()->json([
            'status' => 1,
            'message' => 'Contacto registado com sucesso.'
        ]);
    }

    public function create()
    {
        $userActual = Auth::user();
        $saldo = SaldoSMS::where('codigo','saldo')->first();

        $escolas = Escola::all();
        
        // Retorna a view com os dados carregados
        return view('mensagem.create', [
            'credito' => $saldo,
            'escolas' => $escolas,
            'escola' => Auth::user()->escola_id,
        ]);
    }

    public function show($contacto)
    {
        $mensagens = Mensagem::where('telefone',$contacto)->orderBy('updated_at', 'asc')->get();
        $contatos = MensagemSessao::orderBy('updated_at', 'desc')->get();

        // Retorna a view com os dados carregados
        return view('mensagem.show', [
            'mensagens' => $mensagens,
            'contatos' => $contatos,
            'contacto' => $contacto,
        ]);
    }

    public function storeSMS(Request $request)
    {

        try {

            DB::beginTransaction();

            $userActual = Auth::user();
            $smsDescricao = $request->input('mensagem');
            $escola = $request->input('escola');
            $data = $request->input('data_envio');

            if($request->destinatario=='alunos'){

                $alunos = Aluno::where('desistiu',0)->get();


                if($alunos->isEmpty()){
                    return response()->json(['status' => 0, 'message' => 'Nenhum Aluno Registado']);
                }

                foreach ($alunos as $aluno) {

                    $telefoneEncarregado = AlunoRelacaoPessoa::where('aluno_id', $aluno->id)
                        ->where('relacao_id', 1)
                        ->with('pessoa') // já carrega a relação
                        ->first();

                    $telefone = substr($telefoneEncarregado->pessoa->telefone, -9); 

                    //Gerar SMS de Recibo
                    $sms = new Mensagem();
                    $sms->descricao = $smsDescricao;
                    $sms->telefone = $telefone;
                    $sms->tipo = 'Pendente';
                    $sms->qtd = SMSService::quantidadeSMS($smsDescricao);
                    $sms->credito = SMSService::quantidadeSMS($smsDescricao)*1.85;
                    $sms->custo_real = SMSService::quantidadeSMS($smsDescricao)*1.35;
                    $sms->escola_id = Auth::user()->escola_id;
                    $sms->data_envio = $data;
                    $sms->save();

                }

                DB::commit();
                return response()->json(['status' => 1, 'message' => 'Mensagem Enviada Para Clientes']);
            }

            if($request->destinatario=='professores'){

                $professores = Professor::where('estado',1)->get();

                if($professores->isEmpty()){
                    return response()->json(['status' => 0, 'message' => 'Professores Registado']);
                }

                foreach ($professores as $professor) {

                    //Gerar SMS de Recibo
                    $sms = new Mensagem();
                    $sms->descricao = $smsDescricao;
                    $sms->tipo = 'Pendente';
                    $sms->telefone = $professor->pessoa->telefone;
                    $sms->qtd = SMSService::quantidadeSMS($smsDescricao);
                    $sms->credito = SMSService::quantidadeSMS($smsDescricao)*1.85;
                    $sms->custo_real = SMSService::quantidadeSMS($smsDescricao)*1.35;
                    $sms->escola_id = Auth::user()->escola_id;
                    $sms->data_envio = $data;
                    $sms->save();

                }

                DB::commit();
                return response()->json(['status' => 1, 'message' => 'Mensagem Enviada Para utilizadores']);

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

    public function storeCompraSMS(Request $request)
    {

        try {

            DB::beginTransaction();
   
              $telefone = $request->input('telefone');
              $valor = $request->input('valor');
              $totalCompra = CompraCredito::count();
              $anoActual = Carbon::now()->year;
              $codigo1 = Str::random(8);

              $pacoteNome = null;

                if($valor < 100){
                    $pacoteNome = 'GRÁTIS';
                }elseif($valor >= 100 && $valor < 1000){
                    $pacoteNome = 'STANDARD';
                }elseif($valor >= 1000 && $valor < 5000){
                    $pacoteNome = 'PLUS';
                }elseif($valor >= 5000 && $valor < 10001){
                    $pacoteNome = 'PROFESSIONAL';
                }

                 // Expressão regular: começa com 84 ou 85 e tem 9 dígitos no total
                if (!preg_match('/^8[45]\d{7}$/', $telefone)) {

                    return response()->json(['status' => 0, 'message' => 'Número inválido. Deve começar com 84 ou 85']);

                }

               $numeroFatura = str_pad($totalCompra + 1, 5, '0', STR_PAD_LEFT).'LHAYSSO'.$anoActual;
               $referencia = $numeroFatura;

               //Faturacao da Encomenda
               $pacote = new CompraCredito();
               $pacote->numero_credito = $valor;
               $pacote->preco_por_credito = 1;
               $pacote->valor = $valor; 
               $pacote->tipo_pacote = $pacoteNome;
               $pacote->factura = $numeroFatura;
   
               if($pacote->save()){

                  $credito = SaldoSMS::where('codigo','saldo')->first();
                  $credito->saldo = $credito->saldo + $valor;
                  $credito->notificado = 0;
                  $credito->notificacao_limite = 0;

                  if($credito->save()){

                        $mpesa = new \Karson\MpesaPhpSdk\Mpesa();
                        // Credenciais  -- $credenciaisMpesa  
                        /*$mpesa->setApiKey('eotig3de5fbkdw0w0j68y12uhn77wy6b');
                        $mpesa->setPublicKey('MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAmptSWqV7cGUUJJhUBxsMLonux24u+FoTlrb+4Kgc6092JIszmI1QUoMohaDDXSVueXx6IXwYGsjjWY32HGXj1iQhkALXfObJ4DqXn5h6E8y5/xQYNAyd5bpN5Z8r892B6toGzZQVB7qtebH4apDjmvTi5FGZVjVYxalyyQkj4uQbbRQjgCkubSi45Xl4CGtLqZztsKssWz3mcKncgTnq3DHGYYEYiKq0xIj100LGbnvNz20Sgqmw/cH+Bua4GJsWYLEqf/h/yiMgiBbxFxsnwZl0im5vXDlwKPw+QnO2fscDhxZFAwV06bgG0oEoWm9FnjMsfvwm0rUNYFlZ+TOtCEhmhtFp+Tsx9jPCuOd5h2emGdSKD8A6jtwhNa7oQ8RtLEEqwAn44orENa1ibOkxMiiiFpmmJkwgZPOG/zMCjXIrrhDWTDUOZaPx/lEQoInJoE2i43VN/HTGCCw8dKQAwg0jsEXau5ixD0GUothqvuX3B9taoeoFAIvUPEq35YulprMM7ThdKodSHvhnwKG82dCsodRwY428kg2xM/UjiTENog4B6zzZfPhMxFlOSFX4MnrqkAS+8Jamhy1GgoHkEMrsT5+/ofjCx0HjKbT5NuA2V/lmzgJLl3jIERadLzuTYnKGWxVJcGLkWXlEPYLbiaKzbJb2sYxt+Kt5OxQqC1MCAwEAAQ==');
                        $mpesa->setServiceProviderCode('171717');
                        $mpesa->setEnv('test'); */

                        $mpesa->setApiKey('ft9aqaazj4vtjla61qze9lglcycwwtet');
                        $mpesa->setPublicKey('MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAyrOP7fgXIJgJyp6nP/Vtlu8kW94Qu+gJjfMaTNOSd/mQJChqXiMWsZPH8uOoZGeR/9m7Y8vAU83D96usXUaKoDYiVmxoMBkfmw8DJAtHHt/8LWDdoAS/kpXyZJ5dt19Pv+rTApcjg7AoGczT+yIU7xp4Ku23EqQz70V5Rud+Qgerf6So28Pt3qZ9hxgUA6lgF7OjoYOIAKPqg07pHp2eOp4P6oQW8oXsS+cQkaPVo3nM1f+fctFGQtgLJ0y5VG61ZiWWWFMOjYFkBSbNOyJpQVcMKPcfdDRKq+9r5DFLtFGztPYIAovBm3a1Q6XYDkGYZWtnD8mDJxgEiHWCzog0wZqJtfNREnLf1g2ZOanTDcrEFzsnP2MQwIatV8M6q/fYrh5WejlNm4ujnKUVbnPMYH0wcbXQifSDhg2jcnRLHh9CF9iabkxAzjbYkaG1qa4zG+bCidLCRe0cEQvt0+/lQ40yESvpWF60omTy1dLSd10gl2//0v4IMjLMn9tgxhPp9c+C2Aw7x2Yjx3GquSYhU6IL41lrURwDuCQpg3F30QwIHgy1D8xIfQzno3XywiiUvoq4YfCkN9WiyKz0btD6ZX02RRK6DrXTFefeKjWf0RHREHlfwkhesZ4X168Lxe9iCWjP2d0xUB+lr10835ZUpYYIr4Gon9NTjkoOGwFyS5ECAwEAAQ==');
                        $mpesa->setServiceProviderCode('903003');
                        $mpesa->setEnv('live'); 

                        $invoice_id = str_replace('-', '', $referencia); // Eg: Invoice number
                        $phone_number = '258' . preg_replace('/^(\+?258)?/', '', $telefone); // Prefixed with country code (258)
                        $amount = $valor; // Payment amount
                        $reference_id = $invoice_id.''.$codigo1; // Should be unique for each transaction

                        $result = $mpesa->c2b($invoice_id, $phone_number, $amount, $reference_id);

                        $mensagem1 = 'Venda de SMS efectuado por INFORDATA para a Escola Lhaysso no valor de: '.$valor;


                        if ($result->response->output_ResponseCode == 'INS-0') {

                            SMSService::sendSMS('874870386', $mensagem1, 'MOZOTP');
                            SMSService::sendSMS('843981699', $mensagem1, 'MOZOTP');

                            DB::commit();
                            return response()->json(['status' => 1, 'message' => 'Crédito Comprado com Sucesso!']);

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

                    DB::commit();
   
                    return response()->json(['status' => 1, 'message' => 'Crédito Comprado com Sucesso!']);
                  }
   
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
