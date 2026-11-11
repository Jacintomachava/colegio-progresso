<?php

namespace App\Services;

use App\Models\AlunoRelacaoPessoa;
use App\Models\AnoAcademico;
use App\Models\SaldoSMS;
use App\Models\NotificarSaldoSms;
use App\Models\SMSDestinatario;

class SMSService
{

    /**
     * Envia uma mensagem SMS usando a API da MozeSMS.
     *
     * @param string $telefone Número de telefone do destinatário (com código de país, ex.: +258...)
     * @param string $mensagem conteúdo da mensagem a ser enviada
     *
     * @return array resposta da API Usando Airtrim
     */
    public static function sendSMS1($telefone, $mensagem, $sender)
    {

        $url = "https://api.airtexts.com/sms/send-message";
        $bearerToken = 'M2NiNDEzMjIxNjU4NjM2ZmUxNTc5YzNiM2M0NGQyOWZiY2VlYmY5NDE3OGE4NjA2YTUzYTIyNjdlYmY4MGVmMi0wMjkzY2E2NjNlZGY2MmMzOWQ1OTI1OWJiNzkyYmUyODI5Y2FmMThkOGY3M2FhNDdkMTBkOGMyZTkzMDg1MzYyYTJkNjg0NzQ3NGJhZGE4ZGJm';

        $data = [
            "sender" => "LHAYSSO",
            "recipient" => '+258'.$telefone,
            "message" => $mensagem
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $bearerToken",
            'Content-Type: application/json'
        ]);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        
        /*if ($httpCode === 200) {
            \Log::info("Mensagem Enviada com Sucesso $response");
            return true; // ✅ Sucesso
        } else {
            \Log::error("HTTP Error: $httpCode\nResponse: $response\n");
            return false; // ❌ Falhou
        }*/

        return $httpCode;

    }

     /**
     * Envia uma mensagem SMS usando a API da MozeSMS.
     *
     * @param string $telefone Número de telefone do destinatário (com código de país, ex.: +258...)
     * @param string $mensagem conteúdo da mensagem a ser enviada
     *
     * @return array resposta da API Usando Airtrim
     */
    public static function sendSMS($telefone, $mensagem, $sender)
    {

        //Usando MOZSMS
        $curl = curl_init();

        $data = json_encode([
            'phone' => '+258'.$telefone,
            'message' => $mensagem,
            'sender_id' => 'LHAYSSO'
        ]);

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mozesms.com/v2/sms/send',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer 144:PzUmfv-kvKEY5-dUZha3-5GkIoP',
            ],
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        /*if ($httpCode === 200) {
            \Log::info("Mensagem Enviada com Sucesso $response");
            return true; // ✅ Sucesso
        } else {
            \Log::error("HTTP Error: $httpCode\nResponse: $response\n");
            return false; // ❌ Falhou
        }*/

        return $httpCode;

    }


    public static function agendarSMS($conteudo, $dataEnvio, $quantidadeSMS, $telefone, $destinatario, $sender, $categoria, $escola)
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();

        $smsDestinatario = new SMSDestinatario();
        $smsDestinatario->conteudo = $conteudo;
        $smsDestinatario->data_envio = $dataEnvio;
        $smsDestinatario->quantidade_sms = self::quantidadeSMS($conteudo);
        $smsDestinatario->sender_id = $sender;
        $smsDestinatario->destinatario_pessoa_id = $destinatario;
        $smsDestinatario->categoria = $categoria;
        $smsDestinatario->contacto = $telefone;
        $smsDestinatario->escola_id = $escola;

        if ($anoAcademicoAtivo->sms == 1) {
            $smsDestinatario->save();
        }
    }

    public static function decrimentoSaldoSMS($quantidade)
    {
        $saldo = SaldoSMS::where('codigo', 'saldo')->first();
        $saldo->saldo = $saldo->saldo - $quantidade;

        if ($saldo->save()) {
            return true;
        }

        return false;
    }

    public static function saldoSMS()
    {
        $saldo = SaldoSMS::where('codigo', 'saldo')->first();

        return $saldo->saldo;
    }

    // Envia SMS Pendentes
    public static function notificacaoSMS()
    {
        // Buscar destinatários pendentes em campanhas ativas
        $destinatarios = Mensagem::where('tipo', 'Pendente')->where('canal', 'SMS')->where('created_at', '<=', now())->get();

        if (count($destinatarios) > 0) {
            // Processar cada destinatário
            foreach ($destinatarios as $destinatario) {
                if ((self::saldoSMS() - $destinatario->qtd) >= 0) {
                    self::sendSMS($destinatario->telefone, $destinatario->descricao, $destinatario->id);
                    // Atualizar o estado do destinatário
                    $destinatario->update(['tipo' => 'Enviada']);
                    self::decrimentoSaldoSMS($destinatario->qtd);
                }
            }
        }
    }

    public static function encarregadoAlunoTemTelefone($alunoID)
    {
        return AlunoRelacaoPessoa::where('aluno_id', $alunoID)
            ->where('relacao_id', 1)
            ->whereHas('pessoa', function ($query) {
                $query->whereNotNull('telefone'); // Verifica se o email não é nulo no relacionamento
            })
            ->exists(); // Retorna true se existir pelo menos um registro
    }

    public static function quantidadeSMS($sms)
    {
        $messageText = $sms;
        $isGSM7Bit = self::isGSM7Bit($messageText);
        $charsPerSegment = $isGSM7Bit ? 153 : 67;
        $messageCount = ceil(strlen($messageText) / $charsPerSegment);
        $encoding = $isGSM7Bit ? 'GSM_7BIT' : 'Unicode';

        return $messageCount;
    }

    // Função para verificar se o texto é GSM_7BIT
    private static function isGSM7Bit($text)
    {
        $gsm7bitRegex = '/^[\x20-\x7E\x0A\x0D\xC0-\xC6\xC8-\xCB\xCC-\xCF\xD2-\xD6\xD8-\xDD\xE0-\xE6\xE8-\xEB\xEC-\xEF\xF2-\xF6\xF8-\xFD\xDF]+$/';

        return preg_match($gsm7bitRegex, $text);
    }
}
