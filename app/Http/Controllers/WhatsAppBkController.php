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
use App\Models\Provincia;
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
use App\Models\Informacao;
use App\Models\Meses;
use App\Models\Mensalidade;
use App\Models\DisciplinaCurriculo;
use App\Models\Pauta;
use App\Models\SaldoSMS;
use App\Models\Pessoa;
use App\Models\Encarregado;
use App\Models\EscolaAnterior;
use App\Services\MensagemService;
use App\Services\SMSService;
use App\Services\WhatsAppService;
use Twilio\TwiML\MessagingResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppBkController extends Controller
{

    public function store(Request $request)
    {
        \Log::info("📩 WhatsApp chamado ...");

        $etapa1 = "👋 Olá! Sou o *ASSISTENTE VIRTUAL LHAYSSO/ORIENTE*.\n\n"
            . "Estou aqui para te ajudar a consultar matrícula, propinas, notas e muito mais.\n\n"
            . "Antes de continuarmos, por favor, digite o *código do aluno* (Exemplo: 02.A0047.25).";
        
        $menu = ""
            . "Escolha uma opção, para continuar\n\n"
            . "1️⃣ Matrícula\n"
            . "2️⃣ Notas\n"
            . "3️⃣ Propinas\n"
            . "4️⃣ Estado da Referência\n\n"
            . "✍️ Ou envie sua pergunta com palavras (ex: 'Quero saber as notas').";
        
        $menuDeepSeek = implode("\n", [
            "1. Matrícula",
            "2. Notas",
            "3. Propinas",
            "4. Estado da Referência",
            "5. Multas",
            "6. Renovação",
            "7. Horários",
            "8. Aulas Extras",
            "9. Pagamentos"
        ]);
        
        $mensagem = trim($request->input('Body'));
        $numero = $request->input('From');
        
        MensagemService::mensagem($numero, $mensagem, 1, "Recebida", "WhatsApp", 1);
        
        $estado = Cache::get("whatsapp_estado_{$numero}", 'inicio');
        $codigo = Cache::get("whatsapp_codigo_{$numero}");
        $nomeAluno = Cache::get("whatsapp_nome_{$numero}");
        
        $saldo = SaldoSMS::where('codigo', 'saldo')->first();
        if (!$saldo || $saldo->saldo <= 0) {
            \Log::warning("❌ Interação bloqueada. Saldo insuficiente.");
            $this->notificarSaldoInsuficiente();
            $saldo->notificado = 1;
            $saldo->save();
            return true;
        }
        
        $resposta = new MessagingResponse();
        
        // 🔹 Etapa 1: Boas-vindas e solicitação de código
        if ($estado === 'inicio') {
            $msgResposta = $etapa1;
            $resposta->message($msgResposta);
            MensagemService::mensagem($numero, $msgResposta, 1, "Enviada", "WhatsApp", 1);
            Cache::put("whatsapp_estado_{$numero}", 'aguardando_codigo', 300);
            return response($resposta->asXML(), 200)->header('Content-Type', 'text/xml');
        }
        
        // 🔹 Etapa 2: Validação do código
        if ($estado === 'aguardando_codigo') {
            $aluno = Aluno::where('codigo', $mensagem)->first();
            if (!$aluno) {
                $msgResposta = "❌ Código do aluno não encontrado. Por favor, tente novamente.";
                $resposta->message($msgResposta);
                MensagemService::mensagem($numero, $msgResposta, 1, "Enviada", "WhatsApp", 1);
                return response($resposta->asXML(), 200)->header('Content-Type', 'text/xml');
            }
        
            Cache::put("whatsapp_codigo_{$numero}", $mensagem, 300);
            Cache::put("whatsapp_nome_{$numero}", $aluno->pessoa->nome, 300);
            Cache::put("whatsapp_estado_{$numero}", 'menu', 300);
        
            $msgResposta = "✅ Saudações *{$aluno->pessoa->nome}* \n" . $menu;
            $resposta->message($msgResposta);
            MensagemService::mensagem($numero, $msgResposta, 1, "Enviada", "WhatsApp", 1);
            return response($resposta->asXML(), 200)->header('Content-Type', 'text/xml');
        }
        
        // 🔹 Etapa 3: Menu após verificação do aluno
        if ($estado === 'menu') {
            $opcoesValidas = ["1", "2", "3", "4"];
            
            if (in_array($mensagem, $opcoesValidas)) {
                switch ($mensagem) {
                    case "1": $msgResposta = $this->matricula($codigo); break;
                    case "2": $msgResposta = $this->notas($codigo); break;
                    case "3": $msgResposta = $this->propinas($codigo); break;
                    case "4": 
                        Cache::put("whatsapp_estado_{$numero}", 'aguardando_referencia', 300);
                        $msgResposta = "🔍 Envie agora a *referência* para consulta.";
                        break;
                }
                $resposta->message($msgResposta);
                MensagemService::mensagem($numero, $msgResposta, 1, "Enviada", "WhatsApp", 1);
                return response($resposta->asXML(), 200)->header('Content-Type', 'text/xml');
            }
        
            // 🔹 Caso digite texto livre: enviar para DeepSeek
            $prompt = "Dado o menu abaixo:\n$menuDeepSeek\n\nQual número representa melhor a seguinte pergunta:\n\"$mensagem\"\n\nResponda apenas com o número.";
            $intencao = $this->analisarMensagem($prompt); 
        
            \Log::info("🔍 DeepSeek identificou intenção: " . $intencao);
        
            if (is_numeric($intencao) && $intencao >= 1 && $intencao <= 8) {
                $mensagem = (string)$intencao;
                return redirect()->back(); // volta para o bloco de switch acima com nova $mensagem
            } else {
                $msgResposta = "🤔 Desculpe, não consegui entender sua pergunta.\n\nPor favor, escolha uma das opções abaixo ou reformule sua pergunta:\n\n" . $menu;
                $resposta->message($msgResposta);
                MensagemService::mensagem($numero, $msgResposta, 1, "Enviada", "WhatsApp", 1);
                return response($resposta->asXML(), 200)->header('Content-Type', 'text/xml');
            }
        }
    }    


    // 🔹 Método para notificar sobre saldo insuficiente
    private function notificarSaldoInsuficiente()
    {
        $telefone = '874870386';
        $mensagem = 'A escola está sem crédito para enviar SMS e interagir via WhatsApp. Por favor, recarregue a sua conta.';
        SMSService::sendSMS($telefone, $mensagem, "LHAYSSO");
        \Log::info("📩 Cliente notificado com sucesso.");
    }

    //Falar de Multas intencao 5
    public function multas(){

        $mensagem ="*Sanções pelo atraso nos pagamentos*\n\n
                    O período de pagamento da propina mensal é de 25 à 5 do mês seguinte;\n\n
                    a) O atraso de pagamento de propinas até 10 dias será sancionado com uma taxa de multa de 25%\n
                    b) O atraso de pagamento de propinas até 20 dias será sancionado com multa de 75%\n
                    c) O atraso de pagamento de propinas até 30 dias será sancionado com uma multa de 100% e implicara a suspensão do aluno das actividades lectivas";

        return $mensagem;
    }

    //Falar de Multas intencao 5
    public function horarios(){

        $mensagem ="*Horário das Aulas*\n\n
                    1° Turno das 7H00 as 12H10\n\n
                    2° Turno das 12H20 as 17H15\n
                    3° Turno das  7H00 as 16H30;\n";

        return $mensagem;
    }

    //Falar de Multas intencao 5
    public function renovacao(){

        $mensagem ="*A renovação da Matricula*\n\n
                    A renovação da Matricula ira decorrer de 
                    30 de Setembro à 30 de Outubro de 2025. Terminado este 
                    prazo, o colégio não se responsabilizará pela consequência 
                    de atribuição de vagas existente a novos ingressos.\n";

        return $mensagem;
    }

    //Falar de Pagamentos  intencao 9
    public function pagamentos(){

        $mensagem ="*As propinas são pagas durante 10 meses (Fevereiro à Novembro)*\n\n Para proceder com o pagamento, dirija-se a qualquer ATM do BIM, e procure pela opção PAGAMENTOS, e SELECCIONE
        depois a opção PAGAMENTO DE SERVIÇOS. Digite a ENTIDADE, REFERÊNCIA e o VALOR TOTAL. Podera também ser
        paga através do IZI(*181#), Smart IZI, BIM-INTERNET BANKING, ou por DEPÓSITO DIRECTO em qualquer BALCÃO do BIM, usando
        entidade, referência e o valor total\n";

        return $mensagem;
    }

     //Falar de Pagamentos  intencao 8
     public function aulasExtras(){

        $mensagem ="*Aulas Co-Curriculares*\n\n 
                Dança -  propina  2.625,00MZN\n
                Karaté - Propina 2.200,00MZN\n
                Música - Propina 4.200,00MZN\n
                Inglês - Propina 2.625,00MZN";

        return $mensagem;
    }


    //Mostrar o Encarregado a informacao da Matricula
    public function matricula($codigo){

        $aluno = Aluno::where('codigo', $codigo)->first();
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();

        $matricula = Matricula::where('aluno_id',$aluno->id)->where('ano_academico_id',$anoAcademicoAtivo->id)->first();

        $mensagem = "";

        if($matricula){
            $mensagem = "O aluno *".$matricula->aluno->pessoa->nome."* Matriculado para o ano Lectivo _".$anoAcademicoAtivo->ano;
            $mensagem .= "_ na *".$matricula->classe->nome."* para o regime *".$matricula->periodo->detalhe."*";
            if($matricula->pagou==1){
                $mensagem .= " com matrícula paga na entidade _". $matricula->entidade ."_ e referência *" . $matricula->referencia ."* no valor de *". $matricula->valor_total . "*.";
            }else{
                $mensagem .= " mas ainda não pagou a matrícula.";
                $mensagem .= " Por favor efectua o pagamento da matricula com a entidade *" . $matricula->entidade ."* e referência *" . $matricula->referencia ."* no valor de *". $matricula->valor_total . "*.";
            }
            
            
            // Adicionando o link para mais informações
            $link = "http://38.242.208.200/matricula/factura/" . $matricula->referencia;
            $mensagem .= "\n\n🔗 Para mais informações, clique aqui: " . $link;
            
            return $mensagem;

        }

        $mensagem = "Aluno nao Matriculado para o ano Lectivo *".$anoAcademicoAtivo->ano."*";

        return $mensagem;

    }

     //Propinas Pendentes a ser enviada no whatsapp
     public function propinas($codigo)
     {
         \Log::info("📩 Entrando nas propinas pendentes...");
         
         $aluno = Aluno::where('codigo', $codigo)->first();
         if (!$aluno) {
             return "Aluno não encontrado!";
         }
     
         $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
         if (!$anoAcademicoAtivo) {
             return "Nenhum ano acadêmico ativo encontrado!";
         }
     
         $mensalidades = Mensalidade::where('aluno_id', $aluno->id)
             ->where('ano_academico_id', $anoAcademicoAtivo->id)
             //->where('pagou', 0)
             ->orderBy('id', 'asc')
             ->take(9) // Ou ->limit(10) (se preferir)
             ->get();
     
         if ($mensalidades->isEmpty()) {
             return "Nenhuma Propina Pendente neste ano lectivo de " . $anoAcademicoAtivo->ano .
                 ". faca o Pagamento evite multas.";
         }
     
         $mensagem = "*Propinas do ano lectivo " . $anoAcademicoAtivo->ano . "*\n\n";

            foreach ($mensalidades as $mensalidade) {
                $valorTotal = ($mensalidade->valor + $mensalidade->valor_co_curricular);
                $desconto = ($mensalidade->desconto / 100) * $valorTotal;
                $multa = ($mensalidade->multa / 100) * $valorTotal;
                $valorFinal = $valorTotal - $desconto + $multa;

                $hoje = \Carbon\Carbon::now();
                $prazo = \Carbon\Carbon::parse($mensalidade->data_fim);

                // Estado da mensalidade
                if ($mensalidade->estado == 1) {
                    $estadoTexto = "✅ Pago";
                } elseif ($mensalidade->estado == 0 && $mensalidade->multa == 0) {
                    $estadoTexto = "🟡 Pendente";
                } elseif ($mensalidade->estado == 0 && $mensalidade->multa > 0) {
                    $estadoTexto = "❌ Em atraso";
                }

                // Sinal do mês atual
                $sinalMes = $mensalidade->mes->id == ($hoje->month-1) ? "_(Mês Actual)_" : "";

                $mensagem .= sprintf(
                    " --Mês %s %s %s\n --SubTotal %s\n --Multa %s\n --Total %s\n --Entidade %s\n --Referência %s\n --Prazo %s\n --Estado: %s\n",
                    $mensalidade->mes->nome,
                    $mensalidade->anoAcademico->ano,
                    $sinalMes,
                    number_format($valorTotal, 2, ',', '.'),
                    number_format($mensalidade->multa, 0, ',', '.') . "%",
                    number_format($valorFinal, 2, ',', '.'),
                    optional($mensalidade->pagamento)->entidade ?? 'N/A',
                    optional($mensalidade->pagamento)->referencia ?? 'N/A',
                    $prazo->format('d-M-Y'),
                    $estadoTexto
                );

                 $mensagem .= "\n";
            }
         
         // Adicionando o link para mais informações
         //$link = "http://38.242.208.200/propina/factura/" . $codigo;
         //$mensagem .= "\n\n🔗 Para mais informações, clique aqui: " . $link;
     
         return $mensagem;
     }

    //Mostrar A Puta no WhatsApp de Aluno
    public function notas($codigo){

        $aluno = Aluno::where('codigo', $codigo)->first();
        if (!$aluno) {
            return "Aluno não encontrado!";
        }
    
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        if (!$anoAcademicoAtivo) {
            return "Nenhum ano acadêmico ativo encontrado!";
        }
    
        $pautas = Pauta::where('aluno_id', $aluno->id)
            ->where('ano_academico_id', $anoAcademicoAtivo->id)
            ->get();
    
        if ($pautas->isEmpty()) {
            return "Nenhuma nota encontrada para o aluno no ano lectivo de " . $anoAcademicoAtivo->ano . ".";
        }
    
        // Variáveis para o aproveitamento pedagógico
        $totalDisciplinas = 0;
        $totalNotasFinaisDisciplina = 0;
        $pautaFinal = 0;
    
        // Cabeçalho
        $mensagem = "*Notas do aluno no ano lectivo " . $anoAcademicoAtivo->ano . "*\n";
    
        // Loop pelos trimestres
        foreach (range(1, 3) as $trimestre) {
            $trimestreTemNotas = false;
    
            // Verifica se há notas no trimestre
            foreach ($pautas as $pauta) {
                $testes = [
                    $pauta->{'teste1Tr' . $trimestre},
                    $pauta->{'teste2Tr' . $trimestre},
                    $pauta->{'teste3Tr' . $trimestre},
                    $pauta->{'teste4Tr' . $trimestre},
                    $pauta->{'mmTr' . $trimestre},
                ];
    
                if (array_filter($testes, fn($t) => $t > 0)) {
                    $trimestreTemNotas = true;
                    break;
                }
            }
    
            // Se o trimestre tiver notas, exibe
            if ($trimestreTemNotas) {
                $mensagem .= "\n--- " . $trimestre . "º Trimestre ---\n\n";
    
                foreach ($pautas as $pauta) {
                    $testes = [
                        $pauta->{'teste1Tr' . $trimestre},
                        $pauta->{'teste2Tr' . $trimestre},
                        $pauta->{'teste3Tr' . $trimestre},
                        $pauta->{'teste4Tr' . $trimestre},
                        $pauta->{'mmTr' . $trimestre},
                    ];
    
                    $testesValidos = array_filter($testes, fn($t) => $t > 0);
                    $mediaTestes = count($testesValidos) > 0
                        ? array_sum($testesValidos) / count($testesValidos)
                        : 0;
    
                    $notaFinalTrimestre = (2 * $mediaTestes + $pauta->{'apTr' . $trimestre}) / 3 ?? 0;
    
                    // Formatação da linha
                    $mensagem .= sprintf(
                        "---Disciplina %-12s\n ---1ªACS %-6s\n ---2ªACS %-6s\n ---3ªACS %-6s\n ---4ªACS %-6s\n ---MM %-3s\n ---M.ACS %-6s\n ---AT %-6s\n ---MT %-6s\n",
                        $pauta->disciplina->nome,
                        $pauta->{'teste1Tr' . $trimestre} > 0 ? $pauta->{'teste1Tr' . $trimestre} : '',
                        $pauta->{'teste2Tr' . $trimestre} > 0 ? $pauta->{'teste2Tr' . $trimestre} : '',
                        $pauta->{'teste3Tr' . $trimestre} > 0 ? $pauta->{'teste3Tr' . $trimestre} : '',
                        $pauta->{'teste4Tr' . $trimestre} > 0 ? $pauta->{'teste4Tr' . $trimestre} : '',
                        $pauta->{'mmTr' . $trimestre} > 0 ? $pauta->{'mmTr' . $trimestre} : '',
                        number_format($mediaTestes, 2),
                        $pauta->{'apTr' . $trimestre} > 0 ? $pauta->{'apTr' . $trimestre} : '',
                        $notaFinalTrimestre > 0 ? round($notaFinalTrimestre) : ''
                    );
    
                    // Cálculo para aproveitamento pedagógico
                    if ($trimestre == 3 && $pauta->{'apTr' . $trimestre} > 0 && $pauta->disciplina->co_curricular == 0) {
                        $totalDisciplinas++;
                        $totalNotasFinaisDisciplina += $notaFinalTrimestre;
                    }

                    $mensagem .= "\n\n";
                }
            }
        }
    
        // Aproveitamento Pedagógico
        $mensagem .= "\n*Aproveitamento Pedagógico:*\n";
        if ($totalDisciplinas > 0) {
            $mediaFinal = $totalNotasFinaisDisciplina / $totalDisciplinas;
            $aproveitamento = $mediaFinal > 9.5 ? 'Aprovado' : 'Reprovado';
            $mensagem .= "- Status: " . $aproveitamento . "\n";
            $mensagem .= "- Média Final: " . round($mediaFinal) . "\n";
        } else {
            $mensagem .= "- Nenhuma disciplina concluída.\n";
        }
        
        // Adicionando o link para mais informações
        $link = "https://jacintomachava.online/escola/aluno/pauta/" . $codigo;
        $mensagem .= "\n\n🔗 Para mais informações, clique aqui: " . $link;
    
        return $mensagem;

    }

     //Mostrar A Puta no WhatsApp de Aluno
     public function consultarReferencia($referencia){

        $estadoReferencia = Pagamento::where('referencia', $referencia)->first();

        $mensagem=null;

        if (!$estadoReferencia) {
            $mensagem = "❌ Referencia não encontrado, verifica se nao falhou algum digito!";
            return $mensagem;
        }

        if($estadoReferencia->cancelado==1){
            $mensagem = "🟡 A referencia ".$referencia." destinado a pagamento de ".$estadoReferencia->categoria->nome." no valor de ".$estadoReferencia->valor."MZN foi cancelado portanto nao e possivel efectuar o pagamento dessa referencia, veja se o sistema nao gerou nova referencia.";
            return $mensagem;
        }

        $mensagem = "✅ Essa referencia ".$referencia." existe e esta destinado a pagamento de ".$estadoReferencia->categoria->nome." no valor de ".$estadoReferencia->valor."MZN e deve ser pago ate dia ".\Carbon\Carbon::parse($estadoReferencia->prazo_pagamento)->format('d-M-Y')." pague dentro de prazo para evitar multa \n";
        $mensagem .=" Para proceder ao pagamento, dirija-se a qualquer ATM do BIM, e procure pela opção PAGAMENTOS, e SELECCIONE
                depois a opção PAGAMENTO DE SERVIÇOS. Digite a ENTIDADE, REFERÊNCIA e o VALOR TOTAL. A Matricula poderão também ser
                paga através do IZI(*181#), Smart IZI, BIM-INTERNET BANKING, ou por DEPÓSITO DIRECTO em qualquer BALCÃO do BIM, usando
                entidade, referência e o valor total";

        return $mensagem;

    }

    //Analisar Mensagem
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
            "model" => "deepseek-chat",
            "messages" => [
                ["role" => "system", "content" => "Você é um assistente que interpreta mensagens e associa a uma opção do menu. Responda apenas com um número de 1 a 8."],
                ["role" => "user", "content" => $prompt]
            ]
        ]);
    
        if ($response->successful()) {
            $resposta = $response->json();
            $respostaIA = $resposta["choices"][0]["message"]["content"] ?? "";
    
            // Limpa a resposta, deixando apenas o número
            $numero = trim(preg_replace('/[^0-9]/', '', $respostaIA));
            return $numero;
        }
    
        return null;
    }
    



    
}
