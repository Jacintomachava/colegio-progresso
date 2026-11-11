<?php

namespace App\Http\Controllers;

use Karson\MpesaPhpSdk\Mpesa;
use App\Models\Aluno;
use App\Models\AnoAcademico;
use App\Models\Disciplina;
use App\Models\Encarregado;
use App\Models\Funcionario;
use App\Models\Informacao;
use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Models\Pagamento;
use App\Models\Pauta;
use App\Models\Mpesa1;
use App\Models\User;
use App\Models\ReclamacaoElogioPedido;
use App\Models\SaldoSMS;
use App\Models\Mensagem;
use App\Services\MensagemService;
use App\Services\PineconeService;
use App\Services\SMSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppController extends Controller
{
    public function verificar(Request $request)
    {
        $verify_token = 'machava1631'; // use o mesmo token que colocou no Facebook

        if ($request->input('hub_mode') === 'subscribe' && $request->input('hub_verify_token') === $verify_token) {
            return response($request->input('hub_challenge'), 200);
        }

        return response('Token inválido 2025', 403);
    }

    public function receber(Request $request)
    {
        // Registra tudo no log para debug
        Log::info('🔥 Recebido do WhatsApp:', $request->all());

        // Verifica se há uma mensagem recebida
        if (isset($request['entry'][0]['changes'][0]['value']['messages'][0])) {
            $mensagem = $request['entry'][0]['changes'][0]['value']['messages'][0];
            $numero = $mensagem['from'];
            $texto = $mensagem['text']['body'] ?? '';

            Log::info("📩 Mensagem recebida de $numero: $texto");
        }

        return response()->json(['status' => 'mensagem recebida']);
    }

    public function enviarTextoWhatsApp($numero, $mensagem)
    {
        $token = env('WHATSAPP_TOKEN');
        $phoneId = env('WHATSAPP_PHONE_ID');

        Http::withToken($token)->post("https://graph.facebook.com/v22.0/{$phoneId}/messages", [
            'messaging_product' => 'whatsapp',
            'to' => $numero,
            'type' => 'text',
            'text' => [
                'body' => $mensagem,
            ],
        ]);
    }

    public function enviarImagemWhatsApp($numero, $urlImagem, $legenda = '')
    {
        $token = env('WHATSAPP_TOKEN');
        $phoneId = env('WHATSAPP_PHONE_ID');

        Http::withToken($token)->post("https://graph.facebook.com/v22.0/{$phoneId}/messages", [
            'messaging_product' => 'whatsapp',
            'to' => $numero,
            'type' => 'image',
            'image' => [
                'link' => $urlImagem,
                'caption' => $legenda,
            ],
        ]);
    }

    public function responderChat(Request $request)
    {
        try {

            DB::beginTransaction();


            $funcionario = Funcionario::where('pessoa_id', Auth::user()->pessoa_id)->first();

            $agora = now();
            $mensagem = $request->input('mensagem');
            $telefone = $request->input('telefone');

            $resposta = $mensagem;
            $cargo = $funcionario->cargo;
            $nomeFuncionario = $funcionario->pessoa->nome;

            $saldo = SaldoSMS::where('codigo', 'saldo')->first();

            if (!$saldo || $saldo->saldo <= 0) {
                \Log::warning('❌ Interação bloqueada. Saldo insuficiente.');
                //$this->notificarSaldoInsuficiente();

                if($saldo->notificado == 0){
                    
                    $this->notificarSaldoInsuficiente();
                }

                $saldo->notificado = 1;
                $saldo->save();

                return response()->json([
                    'status' => 0,
                    'message' => 'Saldo Insuficiente de Credito para Responder',
                ]);
            }

            $ultimaMensagem = Mensagem::where('telefone', $telefone)
                ->orderBy('id', 'desc')
                ->first();

            if ($ultimaMensagem->created_at && $agora->diffInHours($ultimaMensagem->created_at) <= 24) {
                // Dentro da janela de 24h: enviar mensagem normalmente (sem template)

                $mensagemParaWhatsApp = <<<MSG
                                📝 $resposta
                                
                                🤝 *Atenciosamente*,
                                $cargo: $nomeFuncionario
                                MSG;

                MensagemService::mensagem($telefone, $mensagemParaWhatsApp, 1, 'Enviada', 'WhatsApp', 1);
                $this->enviarTextoWhatsApp($telefone, $mensagemParaWhatsApp);

                DB::commit();

                return response()->json([
                    'status' => 1,
                    'message' => 'Resposta enviada com sucesso e notificação enviada no WhatsApp!',
                ]);
            } else {
                // Fora da janela de 24h: só enviar se for um template aprovado pela Meta
                // ou aguardar autorização do usuário (ex: envie SMS ou outra ação)
                $qtd = SMSService::quantidadeSMS($resposta);
                SMSService::sendSMS(substr($telefone, -9), $resposta, 'LHAYSSO');

                MensagemService::mensagem($telefone, $resposta, $qtd, 'Enviada', 'SMS', 1);

                DB::commit();

                return response()->json([
                    'status' => 1,
                    'message' => 'Resposta enviada com sucesso e notificação enviada por SMS!',
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // $errorMessage = DatabaseErrorHandler::handle($e);
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }


    public function responder(Request $request)
    {
        try {
            DB::beginTransaction();

            $id = $request->input('reclamacao_id');
            $reclamacao = ReclamacaoElogioPedido::findOrFail($id);
            $reclamacao->resposta = $request->input('resposta');
            $reclamacao->estado = 'Respondido';
            $reclamacao->resposta_user_id = Auth::user()->id;

            $funcionario = Funcionario::where('pessoa_id', Auth::user()->pessoa_id)->first();

            $agora = now();
            $mensagem = $request->input('resposta');
            $pedidoReclamacao = $reclamacao->mensagem;
            $telefone = $reclamacao->telefone;

            $tipo = $reclamacao->tipo;
            $mensagemOriginal = $pedidoReclamacao;
            $resposta = $mensagem;
            $cargo = $funcionario->cargo;
            $nomeFuncionario = $funcionario->pessoa->nome;

            $saldo = SaldoSMS::where('codigo', 'saldo')->first();
            if (!$saldo || $saldo->saldo <= 0) {
                \Log::warning('❌ Interação bloqueada. Saldo insuficiente.');
                //$this->notificarSaldoInsuficiente();

                if($saldo->notificado == 0){
                    
                    $this->notificarSaldoInsuficiente();
                }
                $saldo->notificado = 1;
                $saldo->save();

                return response()->json([
                    'status' => 0,
                    'message' => 'Saldo Insuficiente de Credito para Responder',
                ]);
            }

            if ($reclamacao->created_at && $agora->diffInHours($reclamacao->created_at) <= 24) {
                // Dentro da janela de 24h: enviar mensagem normalmente (sem template)
                $reclamacao->resposta_meio = 'WhatsApp';
                $reclamacao->save();

                $mensagemParaWhatsApp = <<<MSG
                                📩 *$tipo recebido*:
                                _"$mensagemOriginal"_

                                📝 *Resposta*:
                                $resposta

                                🤝 *Atenciosamente*,
                                $cargo: $nomeFuncionario
                                MSG;

                MensagemService::mensagem($telefone, $mensagemParaWhatsApp, 1, 'Enviada', 'WhatsApp', 1);
                $this->enviarTextoWhatsApp($telefone, $mensagemParaWhatsApp);

                DB::commit();

                return response()->json([
                    'status' => 1,
                    'message' => 'Resposta enviada com sucesso e notificação enviada no WhatsApp!',
                ]);
            } else {
                // Fora da janela de 24h: só enviar se for um template aprovado pela Meta
                // ou aguardar autorização do usuário (ex: envie SMS ou outra ação)
                $reclamacao->resposta_meio = 'SMS';
                $reclamacao->save();
                $qtd = SMSService::quantidadeSMS($mensagem);
                SMSService::sendSMS(substr($telefone, -9), $mensagem, 'LHAYSSO');

                MensagemService::mensagem($telefone, $mensagem, $qtd, 'Enviada', 'SMS', 1);

                DB::commit();

                return response()->json([
                    'status' => 1,
                    'message' => 'Resposta enviada com sucesso e notificação enviada por SMS!',
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // $errorMessage = DatabaseErrorHandler::handle($e);
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {
        $menuInicial = implode("\n", [
            '1️⃣ Matrícula',
            '2️⃣ Notas',
            '3️⃣ Propinas',
            '4️⃣ Reclamação, ou Elogio',
            '5️⃣ Pagar via M-Pesa',
            '',
            "✍️ Ou simplesmente envie sua pergunta ou mensagem",
        ]);

        $dados = $request->all();

        // Verifica se é uma mensagem válida
        if (!data_get($dados, 'entry.0.changes.0.value.messages.0.from')) {
            \Log::error('❌ Número de telefone não encontrado na requisição.');

            return response()->noContent(); // Ignora requisições inválidas
        }

        $numero = data_get($dados, 'entry.0.changes.0.value.messages.0.from');
        $mensagem = trim(data_get($dados, 'entry.0.changes.0.value.messages.0.text.body'));
        $nome = data_get($dados, 'entry.0.changes.0.value.contacts.0.profile.name');

        \Log::info("🔁 Número recebido: {$numero}");
        \Log::info("🔁 Mensagem recebida: {$mensagem}");

        if (!$numero) {
            \Log::error('❌ Número de telefone não encontrado na requisição.');

            return response()->noContent();
        }

        $estado = Cache::get("whatsapp_estado_{$numero}", 'inicio');
        $codigo = Cache::get("whatsapp_codigo_{$numero}");

        MensagemService::mensagem($numero, $mensagem, 1, 'Recebida', 'WhatsApp', 1);

        $saldo = SaldoSMS::where('codigo', 'saldo')->first();
        if (!$saldo || $saldo->saldo <= 0) {
            \Log::warning('❌ Interação bloqueada. Saldo insuficiente.');
            //$this->notificarSaldoInsuficiente();

            if($saldo->notificado == 0){
                    
                $this->notificarSaldoInsuficiente();
            }

            $saldo->notificado = 1;
            $saldo->save();

            return response()->noContent();
        }

        // Envia mensagem via API oficial do WhatsApp
        $enviarMensagem = function ($numero, $texto) {
            $token = env('WHATSAPP_TOKEN'); // Defina no .env
            $phoneId = env('WHATSAPP_PHONE_ID'); // Defina no .env

            $response = Http::withToken($token)->post("https://graph.facebook.com/v22.0/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $numero,
                'type' => 'text',
                'text' => [
                    'body' => $texto,
                ],
            ]);

            Log::info('📤 Corpo da requisição:', ['numero' => $numero, 'mensagem' => $texto]);
            Log::info('📬 Resposta oficial da API do WhatsApp:', $response->json());

            return $response;
        };

        if ($estado === 'inicio') {
            
            Cache::put("whatsapp_estado_{$numero}", 'aguardando_opcao', 300);
            $msg = '👋 Olá *'.$nome."*, Sou o *ASSISTENTE VIRTUAL LHAYSSO/ORIENTE*.\n\n"
                ."Escolha uma das opções abaixo para continuar: \n\n(Para voltar ao menu digite *menu* para sair digite *sair*)\n\n".$menuInicial;

            $enviarMensagem($numero, $msg);
            MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

            return response()->noContent();
        }

        if ($estado === 'aguardando_opcao') {
            switch ($mensagem) {
                case '1':
                case '2':
                case '3':
                    // Opções que exigem código de aluno
                    Cache::put("whatsapp_estado_{$numero}", 'aguardando_codigo', 300);
                    Cache::put("whatsapp_opcao_selecionada_{$numero}", $mensagem, 300);
                    $msg = "🔒 Esta informação requer o *código do aluno* (Ex: 02.A047.25).\nPor favor, envie o código.";
                    $enviarMensagem($numero, $msg);
                    MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

                    return response()->noContent();

                /* case '4':
                    Cache::put("whatsapp_estado_{$numero}", 'aguardando_referencia', 300);
                    $msg = '🔍 Envie agora a *referência* para consulta.';
                    $enviarMensagem($numero, $msg);
                    MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

                    return response()->noContent();

                case '5':
                    Cache::put("whatsapp_estado_{$numero}", 'agente_virtual', 600);
                    $msg = "🤖 Olá *$nome*! Agora você está falando com o *Agente Virtual Escolar* para terminar digite *sair*, para voltar ao menu digite *menu*.\n\n".
                        'Digite sua pergunta e eu responderei...';
                    $enviarMensagem($numero, $msg);
                    MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

                    return response()->noContent(); */

                case '4':
                    Cache::put("whatsapp_estado_{$numero}", 'reclamacao_ou_elogio', 300);
                    $msg = '📬 Envie sua *reclamação ou elogio* que será encaminhada à direção da escola.';
                    $enviarMensagem($numero, $msg);
                    MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

                    return response()->noContent();

                case '5':
                    // Nova opção: Pagamento via M-Pesa
                    // Cache::put("whatsapp_estado_{$numero}", 'aguardando_codigo_pagamento', 300);
                    // $msg = "💰 Para iniciar o pagamento via *M-Pesa*, por favor envie o *código do aluno* (Ex: 02.A047.25).";
                    $msg = "Brevimento pagamento com M-Pesa, fique atento...";
                    $enviarMensagem($numero, $msg);
                    MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

                    return response()->noContent();

                default:
                    // 🔹 Agente de IA
                    Cache::put("whatsapp_estado_{$numero}", 'agente_virtual', 600);

                    $pergunta = $mensagem;

                    // 🔎 1. Buscar contexto em Pinecone
                    $pinecone = app(PineconeService::class)->query($pergunta);
                    // Historico da Conversa
                    $historico = DB::table('mensagem')
                                    ->where('telefone', $numero)
                                    ->where('canal', 'WhatsApp')
                                    ->whereIn('tipo', ['Recebida', 'Enviada'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get()
                                    ->reverse(); // para deixar em ordem cronológica

                    $chat_history = [];

                    foreach ($historico as $msg) {
                        $chat_history[] = [
                            'role' => $msg->tipo === 'recebido' ? 'user' : 'assistant',
                            'content' => $msg->descricao,
                        ];
                    }

                    $contexto = $pinecone['text'] ?? '';
                    $score = $pinecone['score'] ?? 0;

                    if (strtolower(trim($mensagem)) === 'menu' || strtolower(trim($mensagem)) === 'voltar') {
                        Cache::forget("whatsapp_estado_{$numero}");

                        $estado = Cache::get("whatsapp_estado_{$numero}", 'inicio');
                        $msg = "👋 Olá ! Sou o *ASSISTENTE VIRTUAL LHAYSSO/ORIENTE*.\n\n"
                            ."Escolha uma das opções abaixo para continuar:\n\n".$menuInicial;

                        $enviarMensagem($numero, $msg);
                        MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

                        return response()->noContent();
                    }

                    if ($score < 0.10 || empty($contexto)) {
                        // 👉 Classificar a intenção
                        $promptClassificacao = "Dado a mensagem abaixo:\n$mensagem\n\nClassifica a menagem em Reclamação, Pedido, Elogio ou Outro:\n\"$mensagem\"\n\nResponda apenas com uma das Classificações: Reclamação, Pedido, Elogio ou Outro.";
                        $intencaoClassificacao = $this->analisarMensagemClassificacao($promptClassificacao);

                        // 👉 Gravar no banco
                        ReclamacaoElogioPedido::create([
                            'telefone' => $numero,
                            'mensagem' => $mensagem,
                            'tipo' => $intencaoClassificacao,
                        ]);

                        // 👉 Avisar o usuário
                        $msg = "📩 Sua mensagem foi encaminhada para a nossa equipe.\nAlguém irá entrar em contacto com você em breve para responder a pergunta acima.\n\nSe desejar, pode enviar outra pergunta.";

                        $enviarMensagem($numero, $msg);
                        MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

                        return response()->noContent();
                    }

                    $chat_history[] = [
                        'role' => 'user',
                        'content' => <<<EOT

                            Use o texto abaixo como referência, se necessário, para responder à pergunta da escola de forma clara, natural e sem repetir que está usando o contexto.

                            Contexto:
                            "$contexto"

                            Pergunta:
                            "$pergunta"

                        EOT
                    ];

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer '.env('DEEPSEEK_API_KEY'),
                        'Content-Type' => 'application/json',
                    ])->post('https://api.deepseek.com/v1/chat/completions', [
                        'model' => 'deepseek-chat',
                        'messages' => array_merge([
                            ['role' => 'system', 'content' => 'Você é um assistente escolar simpático e claro. Responda às dúvidas como se estivesse conversando com os pais dos alunos, com base no documento fornecido. Evite repetir "com base no contexto" ou "com base no documento". Fale naturalmente.'],
                        ], $chat_history),
                    ]);

                    $resposta = $response->json('choices.0.message.content') ?? 'Desculpe, não consegui entender sua pergunta.';

                    $resposta = str_replace(['*', '_', '`'], '', $resposta);

                    // ✂️ 4. Dividir em partes se for muito grande (WhatsApp limita mensagens)
                    $partes = str_split($resposta, 1000); // pode ajustar para 1600 se desejar

                    foreach ($partes as $parte) {
                        $enviarMensagem($numero, $parte);
                        MensagemService::mensagem($numero, $parte, 1, 'Enviada', 'WhatsApp', 1);
                        sleep(2); // atraso leve para parecer natural
                    }

                    return response()->noContent();

                    
            }
        }

        if ($estado === 'aguardando_referencia') {
            $referencia = trim($mensagem); // o usuário enviou a referência
            // Consulta os dados
            $resposta = $this->consultarReferencia($referencia);
            // Envia a resposta
            Cache::forget("whatsapp_estado_{$numero}");
            $enviarMensagem($numero, $resposta);
            MensagemService::mensagem($numero, $resposta, 1, 'Enviada', 'WhatsApp', 1);

            return response()->noContent();
        }

        if ($estado === 'agente_virtual') {
            $pergunta = $mensagem;

            // 🔎 1. Buscar contexto em Pinecone
            $pinecone = app(PineconeService::class)->query($pergunta);
            // Historico da Conversa
            $historico = DB::table('mensagem')
                            ->where('telefone', $numero)
                            ->where('canal', 'WhatsApp')
                            ->whereIn('tipo', ['Recebida', 'Enviada'])
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get()
                            ->reverse(); // para deixar em ordem cronológica

            $chat_history = [];

            foreach ($historico as $msg) {
                $chat_history[] = [
                    'role' => $msg->tipo === 'recebido' ? 'user' : 'assistant',
                    'content' => $msg->descricao,
                ];
            }

            $contexto = $pinecone['text'] ?? '';
            $score = $pinecone['score'] ?? 0;

            if (strtolower(trim($mensagem)) === 'sair') {
                Cache::forget("whatsapp_estado_{$numero}");

                $msg = "✅ *'.$nome.'*, você saiu da conversa atual.\n\n".
                    "👋 Obrigado por entrar em contacto connosco.\n\n";

                $enviarMensagem($numero, $msg);
                MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

                return response()->noContent();
            }

            if (strtolower(trim($mensagem)) === 'menu' || strtolower(trim($mensagem)) === 'voltar') {
                Cache::forget("whatsapp_estado_{$numero}");

                $estado = Cache::get("whatsapp_estado_{$numero}", 'inicio');
                $msg = "👋 Olá ! Sou o *ASSISTENTE VIRTUAL LHAYSSO/ORIENTE*.\n\n"
                    ."Escolha uma das opções abaixo para continuar:\n\n".$menuInicial;

                $enviarMensagem($numero, $msg);
                MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

                return response()->noContent();
            }

            if ($score < 0.10 || empty($contexto)) {
                // 👉 Classificar a intenção
                $promptClassificacao = "Dado a mensagem abaixo:\n$mensagem\n\nClassifica a menagem em Reclamação, Pedido, Elogio ou Outro:\n\"$mensagem\"\n\nResponda apenas com uma das Classificações: Reclamação, Pedido, Elogio ou Outro.";
                $intencaoClassificacao = $this->analisarMensagemClassificacao($promptClassificacao);

                // 👉 Gravar no banco
                ReclamacaoElogioPedido::create([
                    'telefone' => $numero,
                    'mensagem' => $mensagem,
                    'tipo' => $intencaoClassificacao,
                ]);

                // 👉 Avisar o usuário
                $msg = "📩 Sua mensagem foi encaminhada para a nossa equipe.\nAlguém irá entrar em contacto com você em breve para responder a pergunta acima.\n\nSe desejar, pode enviar outra pergunta.";

                $enviarMensagem($numero, $msg);
                MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

                return response()->noContent();
            }

            $chat_history[] = [
                'role' => 'user',
                'content' => <<<EOT

                    Use o texto abaixo como referência, se necessário, para responder à pergunta da escola de forma clara, natural e sem repetir que está usando o contexto.

                    Contexto:
                    "$contexto"

                    Pergunta:
                    "$pergunta"

                EOT
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('DEEPSEEK_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.deepseek.com/v1/chat/completions', [
                'model' => 'deepseek-chat',
                'messages' => array_merge([
                    ['role' => 'system', 'content' => 'Você é um assistente escolar simpático e claro. Responda às dúvidas como se estivesse conversando com os pais dos alunos, com base no documento fornecido. Evite repetir "com base no contexto" ou "com base no documento". Fale naturalmente.'],
                ], $chat_history),
            ]);

            $resposta = $response->json('choices.0.message.content') ?? 'Desculpe, não consegui entender sua pergunta.';

            $resposta = str_replace(['*', '_', '`'], '', $resposta);

            // ✂️ 4. Dividir em partes se for muito grande (WhatsApp limita mensagens)
            $partes = str_split($resposta, 1000); // pode ajustar para 1600 se desejar

            foreach ($partes as $parte) {
                $enviarMensagem($numero, $parte);
                MensagemService::mensagem($numero, $parte, 1, 'Enviada', 'WhatsApp', 1);
                sleep(2); // atraso leve para parecer natural
            }

            return response()->noContent();
        }

        if ($estado === 'reclamacao_ou_elogio') {
            // 🔹 Classifica a mensagem enviado por encarregado para saber se e Reclamação, Pedido, Elogio ou Outro
            $promptClassificacao = "Dado a mensagem abaixo:\n$mensagem\n\nClassifica a menagem em Reclamação, Pedido, Elogio ou Outro:\n\"$mensagem\"\n\nResponda apenas com uma das Classificações Reclamação, Pedido, Elogio ou Outro.";
            $intencaoClassificacao = $this->analisarMensagemClassificacao($promptClassificacao);

            // Grava reclamação ou elogio
            ReclamacaoElogioPedido::create([
                'telefone' => $numero,
                'mensagem' => $mensagem,
                'tipo' => $intencaoClassificacao,
            ]);

            \Log::info('Reclamacao ou Elogio ou Pedido Gravada com Mensagem no Servico');
            $msg = '✅ Sua mensagem foi recebida. Agradecemos o seu feedback!';
            Cache::forget("whatsapp_estado_{$numero}");
            $enviarMensagem($numero, $msg);
            MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);

            return response()->noContent();
        }

       if ($estado === 'aguardando_codigo_pagamento') {

            $codigoAluno = strtoupper(trim($mensagem));
            $aluno = Aluno::where('codigo', $codigoAluno)->first();

            if (!$aluno) {
                $msg = "❌ Código do aluno não encontrado. Por favor, tente novamente.";
                $enviarMensagem($numero, $msg);
                MensagemService::mensagem($numero, $msg, 1, 'Enviada', 'WhatsApp', 1);
                return response()->noContent();
            }

            $mensalidades = Mensalidade::where('aluno_id', $aluno->id)
                ->where('pagou', 0)
                ->orderBy('id', 'asc')
                ->get();

            if ($mensalidades->isEmpty()) {
                $msg = "✅ Nenhuma mensalidade pendente encontrada.";
                $enviarMensagem($numero, $msg);
                return response()->noContent();
            }

            // Monta mensagem com mensalidades
            $mensagem = "💰 *Mensalidades Pendentes*\nAluno: *".$aluno->pessoa->nome."*\n\n";

            foreach ($mensalidades as $index => $mensalidade) {
                $valorTotal = $mensalidade->valor + $mensalidade->valor_co_curricular;
                $desconto = ($mensalidade->desconto / 100) * $valorTotal;
                $multa = ($mensalidade->multa / 100) * $valorTotal;
                $valorFinal = $valorTotal - $desconto + $multa;

                $mensagem .= sprintf(
                    "%d️⃣ %s-%s  %s\n",
                    $index + 1,
                    $mensalidade->mes->nome,
                    $mensalidade->anoAcademico->ano,
                    number_format($valorFinal, 2, ',', '.')
                );
            }

            $mensagem .= "\n💡 Envie o *número do mês* que deseja pagar.";

            // Salva no cache as mensalidades para escolha futura
            Cache::put("whatsapp_mensalidades_{$numero}", $mensalidades->pluck('id')->toArray(), 300);
            Cache::put("whatsapp_estado_{$numero}", 'aguardando_escolha_mensalidade', 300);

            $enviarMensagem($numero, $mensagem);
            return response()->noContent();
        }

        // 2️⃣ Usuário escolheu o mês
        if ($estado === 'aguardando_escolha_mensalidade') {
            
            $mensalidadeEscolhida = intval($mensagem) - 1; // índice
            $mensalidadesIds = Cache::get("whatsapp_mensalidades_{$numero}", []);

            if (!isset($mensalidadesIds[$mensalidadeEscolhida])) {
                $msg = "❌ Opção inválida. Por favor, envie o número correto do mês que deseja pagar.";
                $enviarMensagem($numero, $msg);
                return response()->noContent();
            }

            $mensalidadeID = $mensalidadesIds[$mensalidadeEscolhida];
            Cache::put("whatsapp_mensalidade_selecionada_{$numero}", $mensalidadeID, 300);

            $mensalidade = Mensalidade::where('id', $mensalidadeID)->first();

            // Solicita número M-Pesa
            $msg = "Foi selecionado a propina de *".$mensalidade->mes->nome."-".$mensalidade->anoAcademico->ano."*\n\n📲 Agora, por favor envie o *número M-Pesa* para realizar o pagamento (Ex: 84XXXXXXX ou 85XXXXXXX).";
            $enviarMensagem($numero, $msg);
            Cache::put("whatsapp_estado_{$numero}", 'aguardando_numero_mpesa', 300);

            return response()->noContent();
        }

        // 3️⃣ Usuário enviou número M-Pesa
        if ($estado === 'aguardando_numero_mpesa') {
            $telefoneMpesa = preg_replace('/\D/', '', $mensagem); // remove tudo que não é número
            $mensalidadeID = Cache::get("whatsapp_mensalidade_selecionada_{$numero}");

            if (!$mensalidadeID) {
                $msg = "❌ Ocorreu um erro. Por favor, reinicie o pagamento enviando o código do aluno novamente.";
                $enviarMensagem($numero, $msg);
                Cache::forget("whatsapp_estado_{$numero}");
                return response()->noContent();
            }

            // ✅ Envia mensagem de processamento antes de chamar o método
            $msgProcessando = "Por favor, aguarde alguns segundos\n\n💰 Verifica o teu numero *M-pesa {$telefoneMpesa}* para confirmar o processamento...\n\n";
            $enviarMensagem($numero, $msgProcessando);

            // Chama método de pagamento
            $resultado = $this->pagarPropinaMpesa($mensalidadeID, $telefoneMpesa);

            // Resultado é array com status e message
            $msg = $resultado['message'] ?? "❌ Ocorreu um erro desconhecido.";
            $enviarMensagem($numero, $msg);

            // Limpa estados
            Cache::forget("whatsapp_estado_{$numero}");
            Cache::forget("whatsapp_mensalidades_{$numero}");
            Cache::forget("whatsapp_mensalidade_selecionada_{$numero}");

            return response()->noContent();
        }
    }


    public function pagarPropinaMpesa($propinaID, $telefoneMpesa)
    {
        try {
            

            // 1️⃣ Buscar a mensalidade
            $propina = Mensalidade::with('pagamento')->find($propinaID);
            if (!$propina) {
                return ['status' => 0, 'message' => '❌ Mensalidade não encontrada.'];
            }

            if (!$propina->pagamento) {
                return ['status' => 0, 'message' => '❌ Pagamento não está associado a esta mensalidade.'];
            }

            // 2️⃣ Calcular valor final
            $valorTotal = $propina->valor + $propina->valor_co_curricular;
            $desconto = ($propina->desconto / 100) * $valorTotal;
            $multa = ($propina->multa / 100) * $valorTotal;
            $valorFinal = round($valorTotal - $desconto + $multa, 2);

            // 3️⃣ Validar número M-Pesa (84 ou 85 + 7 dígitos)
            $telefoneMpesa = preg_replace('/\D/', '', $telefoneMpesa); // remove tudo que não é número
            if (!preg_match('/^8[45]\d{7}$/', $telefoneMpesa)) {
                return ['status' => 0, 'message' => '❌ Número M-Pesa inválido. Deve começar com 84 ou 85 e ter 9 dígitos.'];
            }

            // 4️⃣ Buscar dados do aluno e usuário
            $aluno = Aluno::find($propina->aluno_id);
            if (!$aluno) {
                return ['status' => 0, 'message' => '❌ Aluno não encontrado.'];
            }

            $userAluno = User::where('pessoa_id', $aluno->pessoa_id)->first();
            if (!$userAluno) {
                return ['status' => 0, 'message' => '❌ Usuário do aluno não encontrado.'];
            }

            // 5️⃣ Gerar identificadores e preparar dados
            $codigoAleatorio = Str::random(8);
            $referencia = str_replace('.', '', $propina->pagamento->recibo);
            $invoice_id = str_replace('-', '', $referencia);
            $reference_id = $invoice_id . $codigoAleatorio;
            $phone_number = '258' . $telefoneMpesa; // número com código do país

            // 6️⃣ Inicializar SDK do M-Pesa
            $mpesa = new \Karson\MpesaPhpSdk\Mpesa();
            
            $mpesa->setApiKey('eotig3de5fbkdw0w0j68y12uhn77wy6b');
            $mpesa->setPublicKey('MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAmptSWqV7cGUUJJhUBxsMLonux24u+FoTlrb+4Kgc6092JIszmI1QUoMohaDDXSVueXx6IXwYGsjjWY32HGXj1iQhkALXfObJ4DqXn5h6E8y5/xQYNAyd5bpN5Z8r892B6toGzZQVB7qtebH4apDjmvTi5FGZVjVYxalyyQkj4uQbbRQjgCkubSi45Xl4CGtLqZztsKssWz3mcKncgTnq3DHGYYEYiKq0xIj100LGbnvNz20Sgqmw/cH+Bua4GJsWYLEqf/h/yiMgiBbxFxsnwZl0im5vXDlwKPw+QnO2fscDhxZFAwV06bgG0oEoWm9FnjMsfvwm0rUNYFlZ+TOtCEhmhtFp+Tsx9jPCuOd5h2emGdSKD8A6jtwhNa7oQ8RtLEEqwAn44orENa1ibOkxMiiiFpmmJkwgZPOG/zMCjXIrrhDWTDUOZaPx/lEQoInJoE2i43VN/HTGCCw8dKQAwg0jsEXau5ixD0GUothqvuX3B9taoeoFAIvUPEq35YulprMM7ThdKodSHvhnwKG82dCsodRwY428kg2xM/UjiTENog4B6zzZfPhMxFlOSFX4MnrqkAS+8Jamhy1GgoHkEMrsT5+/ofjCx0HjKbT5NuA2V/lmzgJLl3jIERadLzuTYnKGWxVJcGLkWXlEPYLbiaKzbJb2sYxt+Kt5OxQqC1MCAwEAAQ==');
            $mpesa->setServiceProviderCode('171717');
            $mpesa->setEnv('test'); // mudar para 'live' em produção

            // 7️⃣ Executar pagamento
            $amount = number_format($valorFinal, 2, '.', '');
            $result = $mpesa->c2b($invoice_id, $phone_number, $amount, $reference_id);

            // 8️⃣ Registrar transação
            $transacao = new Mpesa1();
            $transacao->numero_factura = $propina->pagamento->recibo;
            $transacao->descricao = $referencia;
            $transacao->telefone = $telefoneMpesa;
            $transacao->valor = $valorFinal;
            $transacao->referencia = $propina->pagamento->referencia;
            $transacao->categoria_id = 2;
            $transacao->response_desc = $result->response->output_ResponseDesc ?? null;
            $transacao->response_code = $result->response->output_ResponseCode ?? null;
            $transacao->transaction_id = $result->response->output_TransactionID ?? null;
            $transacao->conversation_id = $result->response->output_ConversationID ?? null;
            $transacao->third_party_reference = $result->response->output_ThirdPartyReference ?? null;
            $transacao->save();

            DB::beginTransaction();

            // 9️⃣ Tratar resposta da M-Pesa
            $codigo = $result->response->output_ResponseCode ?? null;

            if ($codigo === 'INS-0') {
                // Pagamento bem-sucedido
                $pagamento = Pagamento::find($propina->pagamento_id);
                $pagamento->entidade = $userAluno->codigo;
                $pagamento->metodo_pagamento_id = 2;
                $pagamento->user_id = $userAluno->id;
                $pagamento->pagou = true;
                $pagamento->escola_id = $userAluno->escola_id;
                $pagamento->categoria_id = 2;
                $pagamento->valor = $valorFinal;
                $pagamento->descricao = $result->response->output_TransactionID ?? 'Transação sem ID';

                if ($pagamento->save()) {
                    $propina->pagou = true;
                    $propina->save();
                }

                DB::commit();
                return ['status' => 1, 'message' => '✅ Pagamento da propina de *'.$propina->mes->nome."-".$propina->anoAcademico->ano.'* efectuado com sucesso via *M-Pesa!* Ref.:'.$result->response->output_TransactionID];
            }

            // 10️⃣ Erros conhecidos
            DB::rollBack();

            $mensagensErro = [
                'INS-1' => 'Erro interno na transação.',
                'INS-5' => 'Transação cancelada pelo cliente.',
                'INS-6' => 'A transação falhou.',
                'INS-9' => 'Tempo de espera esgotado.',
                'INS-15' => 'Valor inválido.',
                'INS-25' => 'Credenciais de segurança inválidas.',
                'INS-2001' => 'Senha M-Pesa incorreta.',
                'INS-2006' => 'Saldo insuficiente.',
            ];

            if (isset($mensagensErro[$codigo])) {
                return ['status' => 0, 'message' => '❌ ' . $mensagensErro[$codigo]];
            }

            // Código de erro desconhecido
            return ['status' => 0, 'message' => '❌ Erro na transação: ' . ($result->response->output_ResponseDesc ?? 'desconhecido')];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 0, 'message' => '⚠️ Erro: ' . $e->getMessage()];
        }
    }

    // 🔹 Método para notificar sobre saldo insuficiente
    private function notificarSaldoInsuficiente()
    {
        $telefone = '874870386';
        $telefone1 = '827699380';
        $telefone2 = '844435586';
        $mensagem = 'A escola está sem crédito para enviar SMS e interagir via WhatsApp. Por favor, recarregue a sua conta.';
        SMSService::sendSMS($telefone, $mensagem, 'LHAYSSO');
        SMSService::sendSMS($telefone1, $mensagem, 'LHAYSSO');
        SMSService::sendSMS($telefone2, $mensagem, 'LHAYSSO');
        \Log::info('📩 Cliente notificado com sucesso.');
    }

    // Falar de Multas intencao 5
    public function multas()
    {
        $mensagem = "*Sanções pelo atraso nos pagamentos*\n\n
                    O período de pagamento da propina mensal é de 25 à 5 do mês seguinte;\n\n
                    a) O atraso de pagamento de propinas até 10 dias será sancionado com uma taxa de multa de 25%\n
                    b) O atraso de pagamento de propinas até 20 dias será sancionado com multa de 75%\n
                    c) O atraso de pagamento de propinas até 30 dias será sancionado com uma multa de 100% e implicara a suspensão do aluno das actividades lectivas";

        return $mensagem;
    }

    public function vitrinaEscolar($numero)
    {
        $mensagem = "*📚 VITRINA ESCOLAR - Informação Importante*\n\n"
               ."*Sanções por atraso no pagamento das propinas:*\n\n"
               ."📆 O pagamento deve ser feito entre *25 a 5* do mês seguinte.\n\n"
               ."🔸 Atraso até 10 dias: *multa de 25%*\n"
               ."🔸 Atraso até 20 dias: *multa de 75%*\n"
               ."🔸 Atraso até 30 dias: *multa de 100% e suspensão* do aluno.\n\n"
               ."⚠️ Evite transtornos e mantenha suas propinas em dia.\n\n";

        $mensagem .= "🧾 *Instruções de Pagamento*\n\nPara efetuar o pagamento, utilize qualquer um dos seguintes canais disponíveis pelo BIM:\nAcesse a opção Pagamentos e selecione Pagamento de Serviços.\nInsira a *Entidade*, a *Referência* e o *Valor Total*.\n\n _Os pagamentos podem ser feitos através de:_\n\n✅ IZI (*181#)\n✅ Smart IZI\n✅ BIM Internet Banking\n✅ Depósito direto em qualquer balcão do BIM\n\nCertifique-se de utilizar sempre a _entidade_, _referência_ e o _valor total_ fornecidos.\n";

        return $mensagem;
    }

    // Falar de Multas intencao 5
    public function horarios()
    {
        $mensagem = "*Horário das Aulas*\n\n
                    1° Turno das 7H00 as 12H10\n\n
                    2° Turno das 12H20 as 17H15\n
                    3° Turno das  7H00 as 16H30;\n";

        return $mensagem;
    }

    // Falar de Multas intencao 5
    public function renovacao()
    {
        $mensagem = "*A renovação da Matricula*\n\n
                    A renovação da Matricula ira decorrer de 
                    30 de Setembro à 30 de Outubro de 2025. Terminado este 
                    prazo, o colégio não se responsabilizará pela consequência 
                    de atribuição de vagas existente a novos ingressos.\n";

        return $mensagem;
    }

    // Falar de Pagamentos  intencao 9
    public function pagamentos()
    {
        $mensagem = "*As propinas são pagas durante 10 meses (Fevereiro à Novembro)*\n\n Para proceder com o pagamento, dirija-se a qualquer ATM do BIM, e procure pela opção PAGAMENTOS, e SELECCIONE
        depois a opção PAGAMENTO DE SERVIÇOS. Digite a ENTIDADE, REFERÊNCIA e o VALOR TOTAL. Podera também ser
        paga através do IZI(*181#), Smart IZI, BIM-INTERNET BANKING, ou por DEPÓSITO DIRECTO em qualquer BALCÃO do BIM, usando
        entidade, referência e o valor total\n";

        return $mensagem;
    }

    // Falar de Pagamentos  intencao 8
    public function aulasExtras()
    {
        $mensagem = "*Aulas Co-Curriculares*\n\n 
                Dança -  propina 2.625,00MZN\n
                Karaté - Propina 2.200,00MZN\n
                Música - Propina 4.200,00MZN\n
                Inglês - Propina 2.625,00MZN";

        return $mensagem;
    }

    // Mostrar o Encarregado a informacao da Matricula
    public function matricula($codigo)
    {
        $aluno = Aluno::where('codigo', $codigo)->first();
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();

        $matricula = Matricula::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAcademicoAtivo->id)->first();

        $mensagem = '';

        if ($matricula) {
            $mensagem = 'O aluno *'.$matricula->aluno->pessoa->nome.'* Matriculado para o ano Lectivo _'.$anoAcademicoAtivo->ano;
            $mensagem .= '_ na *'.$matricula->classe->nome.'* para o regime *'.$matricula->periodo->detalhe.'*';
            if ($matricula->pagou == 1) {
                $mensagem .= ' com matrícula paga na entidade _'.$matricula->entidade.'_ e referência *'.$matricula->referencia.'* no valor de *'.$matricula->valor_total.'*.';
                // Adicionando o link para mais informações
                $link = 'https://lhaysso.ac.mz/matricula/recibo/'.$matricula->referencia;
                $mensagem .= "\n\n🔗 Para mais informações, clique aqui: ".$link;
            } else {
                $mensagem .= ' mas ainda não pagou a matrícula.';
                $mensagem .= ' Por favor efectua o pagamento da matricula com a entidade *'.$matricula->entidade.'* e referência *'.$matricula->referencia.'* no valor de *'.$matricula->valor_total.'*.';
                // Adicionando o link para mais informações
                $link = 'https://lhaysso.ac.mz/matricula/factura/'.$matricula->referencia;
                $mensagem .= "\n\n🔗 Para mais informações, clique aqui: ".$link;
            }

            return $mensagem;
        }

        $mensagem = 'Aluno nao Matriculado para o ano Lectivo *'.$anoAcademicoAtivo->ano.'*';

        return $mensagem;
    }

    private function listarMensalidadesPendentes($codigo)
    {
        $aluno = Aluno::where('codigo', $codigo)->first();

        $mensalidades = Mensalidade::where('aluno_id', $aluno->id)
                        ->where('pagou', 0)
                        ->orderBy('id', 'asc')
                        ->get();

        if ($mensalidades->isEmpty()) {
            return "✅ Nenhuma mensalidade pendente encontrada.";
        }

        $mensagem = "💰 *Mensalidades Pendentes*\n\n";
        $contador = 1;

        foreach ($mensalidades as $mensalidade) {
            $valorTotal = $mensalidade->valor + $mensalidade->valor_co_curricular;
            $desconto = ($mensalidade->desconto / 100) * $valorTotal;
            $multa = ($mensalidade->multa / 100) * $valorTotal;
            $valorFinal = $valorTotal - $desconto + $multa;

            $mensagem .= sprintf(
                "%d️⃣ %s %s — *%s MT*\n",
                $contador++,
                $mensalidade->mes->nome,
                $mensalidade->anoAcademico->ano,
                number_format($valorFinal, 2, ',', '.')
            );
        }

        $mensagem .= "\n💡 Envie o *número* correspondente ao mês que deseja pagar.";
        return $mensagem;
    }


    // Propinas Pendentes a ser enviada no whatsapp
    public function propinas($codigo)
    {
        \Log::info('📩 Entrando nas propinas pendentes...');

        $aluno = Aluno::where('codigo', $codigo)->first();
        if (!$aluno) {
            return 'Aluno não encontrado!';
        }

        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        if (!$anoAcademicoAtivo) {
            return 'Nenhum ano acadêmico ativo encontrado!';
        }

        $mensalidades = Mensalidade::where('aluno_id', $aluno->id)
            ->where('ano_academico_id', $anoAcademicoAtivo->id)
            // ->where('pagou', 0)
            ->orderBy('id', 'asc')
            ->take(10) // Ou ->limit(10) (se preferir)
            ->get();

        $mensagem = '*Propinas do ano lectivo '.$anoAcademicoAtivo->ano."*\n\n";

        foreach ($mensalidades as $mensalidade) {
            $valorTotal = ($mensalidade->valor + $mensalidade->valor_co_curricular);
            $desconto = ($mensalidade->desconto / 100) * $valorTotal;
            $multa = ($mensalidade->multa / 100) * $valorTotal;
            $valorFinal = $valorTotal - $desconto + $multa;

            $hoje = \Carbon\Carbon::now();
            $prazo = \Carbon\Carbon::parse($mensalidade->data_fim);
            $dataPag = \Carbon\Carbon::parse($mensalidade->pagamento->data_pagamento);

            // Estado da mensalidade
            if ($mensalidade->pagou == 1) {
                $link = '--https://lhaysso.ac.mz/recibo/propina/'.$mensalidade->pagamento->referencia;
                $mensagem1 = ''.$link;
                $estadoTexto = "✅ Pago \n".$mensagem1;
            } elseif ($mensalidade->pagou == 0 && $mensalidade->multa == 0) {
                $estadoTexto = '🟡 Pendente';
            } elseif ($mensalidade->pagou == 0 && $mensalidade->multa > 0) {
                $estadoTexto = '❌ Em atraso';
            }

            // Sinal do mês atual
            $sinalMes = $mensalidade->mes->id == ($hoje->month - 1) ? '*(Mês Actual)*' : '';

            if ($mensalidade->pagou == 1) {
                $mensagem .= sprintf(
                    " --Mês %s %s %s\n --SubTotal %s\n --Multa %s\n --Total %s\n --Entidade %s\n --Referência %s\n --Data Pag.: %s\n --Estado: %s\n",
                    $mensalidade->mes->nome,
                    $mensalidade->anoAcademico->ano,
                    $sinalMes,
                    number_format($valorTotal, 2, ',', '.'),
                    number_format($mensalidade->multa, 0, ',', '.').'%',
                    number_format($valorFinal, 2, ',', '.'),
                    optional($mensalidade->pagamento)->entidade ?? 'N/A',
                    optional($mensalidade->pagamento)->referencia ?? 'N/A',
                    $dataPag->format('d-M-Y H:i'),
                    $estadoTexto
                );
            } else {
                $mensagem .= sprintf(
                    " --Mês %s %s %s\n --SubTotal %s\n --Multa %s\n --Total %s\n --Entidade %s\n --Referência %s\n --Prazo %s\n --Estado: %s\n",
                    $mensalidade->mes->nome,
                    $mensalidade->anoAcademico->ano,
                    $sinalMes,
                    number_format($valorTotal, 2, ',', '.'),
                    number_format($mensalidade->multa, 0, ',', '.').'%',
                    number_format($valorFinal, 2, ',', '.'),
                    optional($mensalidade->pagamento)->entidade ?? 'N/A',
                    optional($mensalidade->pagamento)->referencia ?? 'N/A',
                    $prazo->format('d-M-Y'),
                    $estadoTexto
                );
            }

            $mensagem .= "\n";
        }

        return $mensagem;
    }

    // Mostrar A Puta no WhatsApp de Aluno
    public function notas($codigo)
    {
        $aluno = Aluno::where('codigo', $codigo)->first();
        if (!$aluno) {
            return 'Aluno não encontrado!';
        }

        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        if (!$anoAcademicoAtivo) {
            return 'Nenhum ano acadêmico ativo encontrado!';
        }

        $pautas = Pauta::where('aluno_id', $aluno->id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->orderBy('updated_at', 'desc') // Ordena pelos mais recentemente atualizados
                ->take(3) // Ou ->limit(10)
                ->get();

        if ($pautas->isEmpty()) {
            return 'Nenhuma nota encontrada para o aluno no ano lectivo de '.$anoAcademicoAtivo->ano.'.';
        }

        $temMulta = Mensalidade::where('aluno_id', $aluno->id)
             ->where('ano_academico_id', $anoAcademicoAtivo->id)
             ->where('pagou', 0)
             ->where('multa', '>', 0)
             ->get();

        /*if(count($temMulta) > 0){
            return "Nao e possivel visualizar Notas com mensalidades em atraso! Regulariza a sua situacao de Mensaidade";
        }*/

        // Variáveis para o aproveitamento pedagógico
        $totalDisciplinas = 0;
        $totalNotasFinaisDisciplina = 0;
        $pautaFinal = 0;

        // Cabeçalho
        $mensagem = '*Notas do aluno no ano lectivo '.$anoAcademicoAtivo->ano."*\n";

        // Loop pelos trimestres
        foreach (range(1, 3) as $trimestre) {
            $trimestreTemNotas = false;

            // Verifica se há notas no trimestre
            foreach ($pautas as $pauta) {
                $testes = [
                    $pauta->{'teste1Tr'.$trimestre},
                    $pauta->{'teste2Tr'.$trimestre},
                    $pauta->{'teste3Tr'.$trimestre},
                    $pauta->{'teste4Tr'.$trimestre},
                    $pauta->{'mmTr'.$trimestre},
                ];

                if (array_filter($testes, fn ($t) => $t > 0)) {
                    $trimestreTemNotas = true;
                    break;
                }
            }

            // Se o trimestre tiver notas, exibe
            if ($trimestreTemNotas) {
                $mensagem .= "\n--- ".$trimestre."º Trimestre ---\n\n";

                foreach ($pautas as $pauta) {
                    $testes = [
                        $pauta->{'teste1Tr'.$trimestre},
                        $pauta->{'teste2Tr'.$trimestre},
                        $pauta->{'teste3Tr'.$trimestre},
                        $pauta->{'teste4Tr'.$trimestre},
                        $pauta->{'mmTr'.$trimestre},
                    ];

                    $testesValidos = array_filter($testes, fn ($t) => $t > 0);
                    $mediaTestes = count($testesValidos) > 0
                        ? array_sum($testesValidos) / count($testesValidos)
                        : 0;

                    $notaFinalTrimestre = (2 * $mediaTestes + $pauta->{'apTr'.$trimestre}) / 3 ?? 0;

                    // Formatação da linha
                    $mensagem .= sprintf(
                        "---Disciplina %-12s\n ---1ªACS %-6s\n ---2ªACS %-6s\n ---3ªACS %-6s\n ---4ªACS %-6s\n ---MM %-3s\n ---M.ACS %-6s\n ---AT %-6s\n ---MT %-6s\n",
                        $pauta->disciplina->nome,
                        $pauta->{'teste1Tr'.$trimestre} > 0 ? $pauta->{'teste1Tr'.$trimestre} : '',
                        $pauta->{'teste2Tr'.$trimestre} > 0 ? $pauta->{'teste2Tr'.$trimestre} : '',
                        $pauta->{'teste3Tr'.$trimestre} > 0 ? $pauta->{'teste3Tr'.$trimestre} : '',
                        $pauta->{'teste4Tr'.$trimestre} > 0 ? $pauta->{'teste4Tr'.$trimestre} : '',
                        $pauta->{'mmTr'.$trimestre} > 0 ? $pauta->{'mmTr'.$trimestre} : '',
                        number_format($mediaTestes, 2),
                        $pauta->{'apTr'.$trimestre} > 0 ? $pauta->{'apTr'.$trimestre} : '',
                        $notaFinalTrimestre > 0 ? round($notaFinalTrimestre) : ''
                    );

                    // Cálculo para aproveitamento pedagógico
                    if ($trimestre == 3 && $pauta->{'apTr'.$trimestre} > 0 && $pauta->disciplina->co_curricular == 0) {
                        ++$totalDisciplinas;
                        $totalNotasFinaisDisciplina += $notaFinalTrimestre;
                    }

                    $mensagem .= "\n\n";
                }
            }
        }

        // Aproveitamento Pedagógico
        // $mensagem .= "\n*Aproveitamento Pedagógico:*\n";
        if ($totalDisciplinas > 0) {
            $mediaFinal = $totalNotasFinaisDisciplina / $totalDisciplinas;
            $aproveitamento = $mediaFinal > 9.5 ? 'Aprovado' : 'Reprovado';
            $mensagem .= '- Status: '.$aproveitamento."\n";
            $mensagem .= '- Média Final: '.round($mediaFinal)."\n";
        } else {
            // $mensagem .= "- Nenhuma disciplina concluída.\n";
        }

        // Adicionando o link para mais informações
        $link = 'https://lhaysso.ac.mz/aluno/pauta/'.$codigo;
        $mensagem .= "\n\n🔗 Para mais informações, clique aqui: ".$link;

        return $mensagem;
    }

    // Mostrar A Puta no WhatsApp de Aluno
    public function consultarReferencia($referencia)
    {
        $estadoReferencia = Pagamento::where('referencia', $referencia)->first();

        \Log::info('📩 Entrando nas Consulta da Referencia pendentes...');

        $mensagem = 'Sem Nada';

        if (!$estadoReferencia) {
            $mensagem = '❌ Referencia não encontrado, verifica se nao falhou algum digito!';
            \Log::info('📩 Entrando nas Referencia não encontrado...');

            return $mensagem;
        }

        if ($estadoReferencia->cancelado == 1) {
            $mensagem = '🟡 A referencia *'.$referencia.'* destinado a pagamento de *'.$estadoReferencia->categoria->nome.'* no valor de *'.$estadoReferencia->valor.'MZN* foi cancelado portanto nao e possivel efectuar o pagamento dessa referencia, veja se o sistema nao gerou nova referencia.';
            \Log::info('📩 Entrando nas A referencia Cancelada...');

            return $mensagem;
        }

        if ($estadoReferencia->pagou == 1) {
            $mensagem = '✅  A referencia *'.$referencia.'* destinado a pagamento de *'.$estadoReferencia->categoria->nome.'* no valor de *'.$estadoReferencia->valor.'MZN* foi pago com sucesso no dia *'.\Carbon\Carbon::parse($estadoReferencia->data_pagamento)->format('d-M-Y').'* ';
            \Log::info('📩 Entrando nas A referencia Cancelada...');

            return $mensagem;
        }

        $mensagem = '✅ Essa referencia *'.$referencia.'* existe e esta destinado a pagamento de *'.$estadoReferencia->categoria->nome.'* no valor de *'.$estadoReferencia->valor.'MZN* e deve ser pago ate dia *'.\Carbon\Carbon::parse($estadoReferencia->prazo_pagamento)->format('d-M-Y')."* pague dentro de prazo para evitar multa.\n\n";
        $mensagem .= "🧾 *Instruções de Pagamento*\n\nPara efetuar o pagamento, utilize qualquer um dos seguintes canais disponíveis pelo BIM:\nAcesse a opção Pagamentos e selecione Pagamento de Serviços.\nInsira a *Entidade*, a *Referência* e o *Valor Total*.\n\n _Os pagamentos podem ser feitos através de:_\n\n✅ IZI (*181#)\n✅ Smart IZI\n✅ BIM Internet Banking\n✅ Depósito direto em qualquer balcão do BIM\n\nCertifique-se de utilizar sempre a _entidade_, _referência_ e o _valor total_ fornecidos.\n";

        \Log::info('📩 Entrando nas referencia Para proceder ao pagamento, dirija-se...');

        return $mensagem;
    }

    // Analisar Mensagem
    public function analisarMensagemClassificacao($mensagem)
    {
        $apiUrl = 'https://api.deepseek.com/v1/chat/completions';
        $apiKey = env('DEEPSEEK_API_KEY');

        $prompt = <<<EOT
            Classifique a seguinte mensagem como uma das seguintes categorias: Reclamação, Pedido, Elogio ou Outro.

            Mensagem:
            "$mensagem"

            Responda com apenas uma palavra: Reclamação, Pedido, Elogio ou Outro.
        EOT;

        $response = Http::withHeaders([
            'Authorization' => "Bearer $apiKey",
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um assistente que classifica mensagens de usuários.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->successful()) {
            $resposta = $response->json();

            return trim($resposta['choices'][0]['message']['content'] ?? 'Outro');
        }

        return 'Outro';
    }

    // Analisar Mensagem
    public function analisarMensagem($mensagem)
    {
        $apiUrl = 'https://api.deepseek.com/v1/chat/completions';
        $apiKey = env('DEEPSEEK_API_KEY');  // Pegando a chave do .env

        $prompt = <<<EOT
            Você é um assistente virtual escolar.
            Receberá uma pergunta de um encarregado sobre um aluno.

            Seu trabalho é associar essa pergunta a uma das opções abaixo e responder apenas com o número correspondente (apenas o número, sem texto adicional).

            Menu:
            1. Matrícula
            2. Notas
            3. Propinas
            4. Estado da Referência
            5. Multas
            6. Renovação
            7. Horários
            8. Aulas Extras
            
            Pergunta: "$mensagem"
            Responda apenas com o número da opção correspondente, sem explicações.
            EOT;

        $response = Http::withHeaders([
            'Authorization' => "Bearer $apiKey",
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um assistente que interpreta mensagens e associa a uma opção do menu. Responda apenas com um número de 1 a 8.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->successful()) {
            $resposta = $response->json();
            $respostaIA = $resposta['choices'][0]['message']['content'] ?? '';

            // Limpa a resposta, deixando apenas o número
            $numero = trim(preg_replace('/[^0-9]/', '', $respostaIA));

            return $numero;
        }

        return null;
    }

    public function reclamacoes()
    {
        $reclamacoes = ReclamacaoElogioPedido::orderBy('id', 'desc')->get();

        // Retorna a view com os dados carregados
        return view('whatsapp.index', [
            'reclamacoes' => $reclamacoes,
        ]);
    }
}
