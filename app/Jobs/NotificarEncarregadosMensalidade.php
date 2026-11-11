<?php

namespace App\Jobs;

use App\Models\Aluno;
use App\Models\Mensagem;
use App\Models\Mensalidade;
use App\Models\Pessoa;
use App\Services\SMSService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotificarEncarregadosMensalidade implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        \Log::info("Job iniciado para notificar mensalidades pendentes.");

        $mesAtual = now()->month;
        $anoAtual = now()->year;

        $mensalidades = Mensalidade::where('pagou', 0)
                ->where('mes_id', $mesAtual-1)
                ->get();

        \Log::info("Mes: ".($mesAtual-1)." Mensalidades encontradas: " . $mensalidades->count());

        foreach ($mensalidades as $mensalidade) {
            $aluno = $mensalidade->aluno;
            $encarregado = DB::table('aluno_relacao_pessoa')
                ->join('pessoas', 'pessoas.id', '=', 'aluno_relacao_pessoa.pessoa_id')
                ->where('aluno_relacao_pessoa.aluno_id', $aluno->id)
                ->where('aluno_relacao_pessoa.relacao_id', 1)
                ->first();

            if ($encarregado && $mensalidade->pagamento) {

                $sms = null;

                if($mensalidade->multa ==0){
                    $sms = "Enc. do aluno ".$mensalidade->aluno->codigo." pague a mensalidade de ".self::mes($mesAtual-2)." Valor: " . number_format($mensalidade->pagamento->valor, 2, ',', '.') . " MT. Entidade {$mensalidade->pagamento->entidade}, Referencia: {$mensalidade->pagamento->referencia}, Banco BIM, até " . Carbon::parse($mensalidade->data_fim)->format('d-m-Y') . ".";
                }elseif($mensalidade->multa ==25){
                    $sms = "Enc. do aluno ".$mensalidade->aluno->codigo." multa de 25% aplicada por atraso na mensalidade de ".self::mes($mesAtual-2)." Valor: " . number_format($mensalidade->pagamento->valor, 2, ',', '.') . " MT. Entidade {$mensalidade->pagamento->entidade}, nova referencia: {$mensalidade->pagamento->referencia}, Banco BIM, até " . Carbon::parse($mensalidade->data_fim)->format('d-m-Y') . ".";
                }elseif($mensalidade->multa ==75){
                    $sms = "Enc. do aluno ".$mensalidade->aluno->codigo." multa de 50% aplicada. Regularize a mensalidade de ".self::mes($mesAtual-2)." Valor: " . number_format($mensalidade->pagamento->valor, 2, ',', '.') . " MT. Entidade {$mensalidade->pagamento->entidade}, nova referencia: {$mensalidade->pagamento->referencia}, Banco BIM, até " . Carbon::parse($mensalidade->data_fim)->format('d-m-Y') . ".";
                }elseif($mensalidade->multa ==100){
                    $sms = "Enc. do aluno ".$mensalidade->aluno->codigo." multa de 100% aplicada por inadimplência da mensalidade ".self::mes($mesAtual-2)." Valor: " . number_format($mensalidade->pagamento->valor, 2, ',', '.') . " MT. Entidade {$mensalidade->pagamento->entidade}, nova referencia: {$mensalidade->pagamento->referencia}, Banco BIM, até " . Carbon::parse($mensalidade->data_fim)->format('d-m-Y') . ".";
                }
               

                $mensagem=Mensagem::create([
                    'telefone' => $encarregado->telefone,
                    'descricao' => $sms,
                    'tipo' => 'Pendente',
                    'canal' => 'SMS',
                    'qtd' => SMSService::quantidadeSMS($sms),
                    'data_envio' => now(),
                ]);

                \Log::info("Numero menssagem: ".$mensagem->id);
            }

            
        }
    }
 
    public static function mes($numero){

        if($numero==1){
           return "Fevereiro";
        }elseif($numero==2){
           return "Março";
        }elseif($numero==3){
           return "Abril";
        }elseif($numero==4){
           return "Maio";
        }elseif($numero==5){
           return "Junho";
        }elseif($numero==6){
           return "Julho";
        }elseif($numero==7){
            return "Agosto";
        }elseif($numero==8){
            return "Setembro";
        }elseif($numero==9){
            return "Outubro";
        }elseif($numero==10){
            return "Novembro";
        }

        return "Desconhecido";
    }
}
