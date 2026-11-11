<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AnoAcademico;
use App\Models\Mensalidade;
use App\Models\Pagamento;
use App\Models\Aluno;
use App\Models\Mensagem;
use App\Models\AlunoRelacaoPessoa;
use App\Services\SMSService;
use Carbon\Carbon;

class CancelarMulta implements ShouldQueue
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
        $this->cancelarMulta(1, 3);
    }

    /* Esse metodo gera a Pauta anual de cada estudante
     Onde recebe o id de estudante, o Ano Academico, e a TaxaID
     Para preencher mensalidades que devem ser pagos
    */
    public function cancelarMulta($mesID, $anoAcademicoID)
    {
        $mensalidades = Mensalidade::where('disistiu',0)->where('pagou',0)->where('ano_academico_id', $anoAcademicoID)->where('mes_id', $mesID)->get();

        foreach ($mensalidades as $mensalidade) 
        {
            $verificarPagamento = Pagamento::where('id',$mensalidade->pagamento_id)->first();
            $verificarPagamento->valor = $mensalidade->valor;
            $verificarPagamento->save();

            $verificarMensalidade = Mensalidade::where('id',$mensalidade->id)->first();
            $verificarMensalidade->multa = 0 ;
            $verificarMensalidade->save();

            $telefoneEncarregado = AlunoRelacaoPessoa::where('aluno_id', $mensalidade->aluno_id)->where('relacao_id', 1)->first();

            $nomeAluno = Aluno::where('id',$mensalidade->aluno_id)->first();

            $dataFormatada = Carbon::parse('2026-02-28')->format('d-m-Y');
            $mesMaiusculo = strtoupper($mensalidade->mes->nome);
            $valorFormatada = number_format($mensalidade->valor, 2, ',', '.');

            $sms = 'Prezado, a multa de '.$mesMaiusculo.' do aluno '.$nomeAluno->pessoa->nome.' foi cancelada. O novo prazo '.$dataFormatada.'. Paga via BIM, Entidade: 90028, Referencia: '.$mensalidade->pagamento->referencia.', Valor: '.$valorFormatada.' MZN';

            $qtd = SMSService::quantidadeSMS($sms);

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
