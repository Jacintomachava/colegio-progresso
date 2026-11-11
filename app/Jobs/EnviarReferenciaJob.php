<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Aluno;
use Carbon\Carbon;
use App\Models\Mensalidade;
use App\Services\MultaService;

class EnviarReferenciaJob implements ShouldQueue
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
        //
        $this->referenciaMes();
    }

    public function referenciaMes()
    {
        $mesAtual = now()->month;
        $mensalidades = Mensalidade::where('disistiu',0)->where('pagou',0)->where('mes_id', $mesAtual)->get();
        
        foreach ($mensalidades as $mensalidade) {
            $nomeAluno = Aluno::where('id',$mensalidade->aluno_id)->first();

            $dataFormatada = Carbon::parse($mensalidade->data_fim)->format('d-m-Y');
            $mesMaiusculo = strtoupper($mensalidade->mes->nome);
            $valorFormatada = number_format($mensalidade->pagamento->valor, 2, ',', '.');

            $sms = 'Prezado encarregado do(a) '.$nomeAluno->pessoa->nome.' pague a mensalidade de '.$mesMaiusculo.' prazo '.$dataFormatada.' ENTIDADE: 90028, REFERENCIA: '.$mensalidade->pagamento->referencia.', VALOR: '.$valorFormatada.' MZN. Banco: Millennium BIM. Evita multas';
            //$sms = 'Utilize o WhatsApp Inteligente da Escola no numero 868244468 para ter ajuda no pagamento, consultar notas, propinas, deixar elogio e muito mais. Codigo de acesso do Aluno: '.$nomeAluno->codigo.' Aguardamos o seu feedback no nosso WhatsApp Inteligente (868244468).';
            if($nomeAluno->desistiu==0){
             MultaService::referenciaMesSMS($mensalidade->aluno_id, $sms);
            }
            
        }
    }
}
