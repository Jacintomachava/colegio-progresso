<?php

namespace App\Jobs;

use App\Models\Mensagem;
use App\Models\MensagemSessao;
use App\Models\SaldoSMS;
use App\Models\NotificarSaldoSMS;
use App\Services\SMSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnviarSMS implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
    }

    public function handle(): void
    {
        $this->enviarSMS();
    }

    private function enviarSMS()
    {
        $hoje = now()->toDateString();

        $horaAtual = now()->format('H:i');

        $mensagens = Mensagem::where('tipo', 'Pendente')
            ->whereDate('data_envio', '<=', $hoje)
            ->where('canal', 'SMS')
            ->get();


        if (
            $horaAtual < '08:00' ||
            $horaAtual > '20:00' ||
            $mensagens->count() == 0
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Buscar saldo uma única vez
        |--------------------------------------------------------------------------
        */
        $saldo = SaldoSMS::where(
            'codigo',
            'saldo'
        )->first();


        if (!$saldo) {

            Log::error(
                'Registo de saldo SMS não encontrado.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Verificar alertas antes dos envios
        |--------------------------------------------------------------------------
        */

        $this->verificarSaldo($saldo);


        /*
        |--------------------------------------------------------------------------
        | Processar mensagens
        |--------------------------------------------------------------------------
        */

        foreach ($mensagens as $mensagem) {

            /*
            |--------------------------------------------------------------------------
            | Se acabou o saldo, parar
            |--------------------------------------------------------------------------
            */

            if ($saldo->saldo <= 0) {

                $this->verificarSaldo($saldo);

                Log::warning(
                    'Interação bloqueada. Saldo SMS esgotado.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Verificar se existe saldo suficiente para ESTA mensagem
            |--------------------------------------------------------------------------
            */

            $custoSMS =
                $mensagem->qtd * 1.85;


            if ($saldo->saldo < $custoSMS) {

                /*
                 * Consideramos que já não existe saldo suficiente
                 * para continuar os envios.
                 */

                $this->notificarSaldoInsuficiente(
                    $saldo
                );

                Log::warning(
                    'Saldo insuficiente para enviar a mensagem ID '
                    .$mensagem->id
                    .'. Saldo: '.$saldo->saldo
                    .' | Necessário: '.$custoSMS
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Enviar SMS
            |--------------------------------------------------------------------------
            */

            $resultado = SMSService::sendSMS(
                $mensagem->telefone,
                $mensagem->descricao,
                'LHAYSSO'
            );


            if ($resultado == 200) {

                /*
                |--------------------------------------------------------------------------
                | Diminuir saldo
                |--------------------------------------------------------------------------
                */

                $saldo->saldo =
                    $saldo->saldo - $custoSMS;


                $saldo->save();


                /*
                |--------------------------------------------------------------------------
                | Atualizar mensagem
                |--------------------------------------------------------------------------
                */

                $mensagem->tipo = 'Enviada';

                $mensagem->credito =
                    $mensagem->qtd * 1.85;

                $mensagem->custo_real =
                    $mensagem->qtd * 1.35;

                $mensagem->save();


                /*
                |--------------------------------------------------------------------------
                | Atualizar sessão
                |--------------------------------------------------------------------------
                */

                MensagemSessao::updateOrCreate(
                    [
                        'telefone' =>
                            $mensagem->telefone
                    ],
                    [
                        'ultima_conversa' =>
                            $mensagem->descricao,

                        'conversas' =>
                            DB::raw('conversas + 1'),

                        'created_at' =>
                            now(),
                    ]
                );


                /*
                |--------------------------------------------------------------------------
                | IMPORTANTE
                |--------------------------------------------------------------------------
                |
                | Verificamos novamente porque o envio que acabámos
                | de fazer pode ter colocado o saldo abaixo do limite.
                |
                */

                $this->verificarSaldo($saldo);


            } elseif ($resultado == 402) {

                Log::warning(
                    'Interação bloqueada pela MOZSMS. '
                    .'Saldo insuficiente no gateway.'
                );


            } else {

                $mensagem->tipo = 'Inválido';

                $mensagem->save();
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR ESTADO DO SALDO
    |--------------------------------------------------------------------------
    */

    private function verificarSaldo(
        SaldoSMS $saldo
    ): void {

        /*
        |--------------------------------------------------------------------------
        | 1. SEM SALDO
        |--------------------------------------------------------------------------
        */

        if ($saldo->saldo <= 0) {

            /*
             * Envia apenas uma vez.
             */
            if ($saldo->notificado == 0) {

                $this->notificarSaldoInsuficiente(
                    $saldo
                );

                $saldo->notificado = 1;

                $saldo->save();
            }


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | 2. SALDO BAIXO
        |--------------------------------------------------------------------------
        |
        | Exemplo:
        |
        | saldo        = 350
        | saldo_limite = 400
        |
        | 350 <= 400
        |
        */

        if (
            $saldo->saldo <= $saldo->saldo_limite
            &&
            $saldo->notificacao_limite == 0
        ) {

            $this->notificarSaldoBaixo(
                $saldo
            );


            $saldo->notificacao_limite = 1;

            $saldo->save();


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | 3. SALDO VOLTOU AO NORMAL
        |--------------------------------------------------------------------------
        |
        | Se recarregarem a conta e o saldo voltar acima
        | do limite, rearmamos os alertas.
        |
        | Assim, numa futura queda abaixo do limite,
        | poderá ser enviada nova notificação.
        |
        */

        if ($saldo->saldo > $saldo->saldo_limite) {

            $alterou = false;


            if ($saldo->notificacao_limite == 1) {

                $saldo->notificacao_limite = 0;

                $alterou = true;
            }


            if ($saldo->notificado == 1) {

                $saldo->notificado = 0;

                $alterou = true;
            }


            if ($alterou) {
                $saldo->save();
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | NOTIFICAR SALDO BAIXO
    |--------------------------------------------------------------------------
    */

    private function notificarSaldoBaixo(
        SaldoSMS $saldo
    ): void {

        $contactos =
            NotificarSaldoSMS::all();


        if ($contactos->isEmpty()) {

            Log::warning(
                'Não existem contactos configurados '
                .'para notificação de saldo SMS.'
            );

            return;
        }


        $saldoAtual = number_format(
            $saldo->saldo,
            2,
            ',',
            '.'
        );


        $limite = number_format(
            $saldo->saldo_limite,
            2,
            ',',
            '.'
        );


        $mensagem =
            'Alerta LHAYSSO: o saldo de SMS esta baixo. '
            .'Saldo actual: '.$saldoAtual.' MT. '
            .'Limite definido: '.$limite.' MT. '
            .'Por favor, efectue a recarga.';


        foreach ($contactos as $contacto) {

            if (!$contacto->contacto_anotificar) {
                continue;
            }


            $telefone = substr(
                $contacto->contacto_anotificar,
                -9
            );


            try {

                SMSService::sendSMS(
                    $telefone,
                    $mensagem,
                    'LHAYSSO'
                );

            } catch (\Throwable $e) {

                Log::error(
                    'Erro ao notificar saldo baixo para '
                    .$telefone
                    .': '.$e->getMessage()
                );
            }
        }


        Log::info(
            'Notificação de saldo baixo enviada. '
            .'Saldo: '.$saldo->saldo
            .' | Limite: '.$saldo->saldo_limite
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NOTIFICAR SALDO ESGOTADO
    |--------------------------------------------------------------------------
    */
    private function notificarSaldoInsuficiente(SaldoSMS $saldo): void {

        $contactos = NotificarSaldoSMS::all();

        if ($contactos->isEmpty()) {

            Log::warning(
                'Não existem contactos configurados '
                .'para notificação de saldo insuficiente.'
            );

            return;
        }


        $saldoAtual = number_format(
            $saldo->saldo,
            2,
            ',',
            '.'
        );


        $mensagem =
            'Alerta LHAYSSO: a escola esta sem credito '
            .'suficiente para enviar SMS e interagir via WhatsApp. '
            .'Saldo actual: '.$saldoAtual.' MT. '
            .'Por favor, recarregue a conta.';


        foreach ($contactos as $contacto) {

            if (!$contacto->contacto_anotificar) {
                continue;
            }

            $telefone = substr($contacto->contacto_anotificar, -9);

            try {

                SMSService::sendSMS($telefone, $mensagem, 'LHAYSSO');

            } catch (\Throwable $e) {

                Log::error(
                    'Erro ao notificar saldo insuficiente para '
                    .$telefone
                    .': '.$e->getMessage()
                );
            }
        }


        Log::info(
            'Notificação de saldo insuficiente enviada.'
        );
    }
}