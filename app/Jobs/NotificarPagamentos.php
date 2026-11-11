<?php

namespace App\Jobs;

use App\Models\Pagamento;
use App\Models\NotificarPagamento;
use App\Models\Mensalidade;
use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\AlunoRelacaoPessoa;
use App\Models\Mensagem;
use App\Services\SMSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificarPagamentos implements ShouldQueue
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
        $this->enviarSMSPagamentoEncarregado();
    }

    private function enviarSMSPagamentoEncarregado()
    {
        $hoje = now()->toDateString(); // pega a data de hoje no formato YYYY-MM-DD

        $pagamentos = Pagamento::where('pagou', 1)
            ->whereDate('updated_at', $hoje)
            ->get();

        foreach ($pagamentos as $pagamento) {

            $verificarSeFoiNotificado = NotificarPagamento::where('referencia',$pagamento->referencia)->first();

            if(!$verificarSeFoiNotificado){

                $notificarPagamento = new NotificarPagamento();
                $notificarPagamento->referencia = $pagamento->referencia;
                $notificarPagamento->aluno_id = $pagamento->aluno_id;
                $notificarPagamento->categoria_id = $pagamento->categoria_id;

                if($notificarPagamento->save()){

                    $sms = '';

                    if($pagamento->categoria_id==2){

                        Mensalidade::where('pagamento_id', $pagamento->id)
                                    ->update(['pagou' => 1]);

                        $mensalidade = Mensalidade::where('pagamento_id', $pagamento->id)->first();
                        $nomeAluno = Aluno::where('id', $pagamento->aluno_id)->first();
                        $nome = $nomeAluno->pessoa->nome;
                        $mes = $mensalidade->mes->nome;
                        $codigo = $nomeAluno->codigo;
                        $referencia = $pagamento->referencia;

                        $sms = "Caro encarregado do(a) $nome, o pagamento da propina de $mes foi realizado com sucesso. Ref: $referencia.";

                    }elseif($pagamento->categoria_id==1){

                        Matricula::where('pagamento_id', $pagamento->id)
                                ->update(['pagou' => 1]);
                    
                        $matricula = Matricula::where('pagamento_id', $pagamento->id)->first();

                        $nomeAluno = Aluno::where('id', $pagamento->aluno_id)->first();
                        $nome = $nomeAluno->pessoa->nome;
                        $codigo = $nomeAluno->codigo;
                        $anoLectivo = $matricula->anoAcademico->ano;

                        $sms = "Caro encarregado do(a) $nome, o pagamento da matricula do ano lectivo $anoLectivo foi realizado com sucesso.";

                    }else{
                    $sms = "Pagamento efectuado com sucesso";
                    }

                    $qtd = SMSService::quantidadeSMS($sms);
                    $telefoneEncarregado = AlunoRelacaoPessoa::where('aluno_id', $pagamento->aluno_id)->where('relacao_id', 1)->first();

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
                    }

                }

            }

        }

    }
}
            