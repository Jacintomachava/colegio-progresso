<?php

namespace App\Jobs;

use App\Models\Aluno;
use App\Models\AlunoRelacaoPessoa;
use App\Models\Mensagem;
use Illuminate\Bus\Queueable;
use App\Services\SMSService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GerarSmsNotasAlunos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public function handle(): void
    {
        Aluno::with('pessoa')
            ->where('desistiu', 0)
            ->whereNotNull('codigo')
            ->chunkById(100, function ($alunos) {

                foreach ($alunos as $aluno) {

                    try {

                        // Nome do aluno
                        $nomeAluno = $aluno->pessoa?->nome;

                        if (!$nomeAluno) {
                            continue;
                        }

                        // Código do aluno
                        $codigo = $aluno->codigo;

                        // Buscar encarregado
                        $telefoneEncarregado = AlunoRelacaoPessoa::with('pessoa')
                            ->where('aluno_id', $aluno->id)
                            ->where('relacao_id', 1)
                            ->first();

                        if (!$telefoneEncarregado?->pessoa?->telefone) {
                            continue;
                        }

                        // Padronizar telefone
                        $telefone = substr(
                            $telefoneEncarregado->pessoa->telefone,
                            -9
                        );

                        // Validar telefone
                        if (!preg_match('/^8[2-7][0-9]{7}$/', $telefone)) {
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | CRIAR SMS
                        |--------------------------------------------------------------------------
                        */

                        $sms = 'Prezado encarregado do(a) '
                            .$nomeAluno
                            .', ja pode consultar as notas do seu educando. '
                            .'User: '.$codigo
                            .' Senha: '.$codigo
                            .' link https://lhaysso.ac.mz/logar';

                        /*
                        |--------------------------------------------------------------------------
                        | CALCULAR QUANTIDADE DE SMS
                        |--------------------------------------------------------------------------
                        */

                        // Mensagem GSM-7, sem caracteres especiais

                        $qtd = SMSService::quantidadeSMS($sms);

                        /*
                        |--------------------------------------------------------------------------
                        | REGISTAR SEM DUPLICAR
                        |--------------------------------------------------------------------------
                        */

                        $mensagem = Mensagem::firstOrCreate(
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

                        if ($mensagem->wasRecentlyCreated) {

                            Log::info('SMS notas registada', [
                                'aluno_id' => $aluno->id,
                                'telefone' => $telefone
                            ]);

                        }

                    } catch (\Throwable $e) {

                        Log::error('Erro ao gerar SMS de notas', [
                            'aluno_id' => $aluno->id,
                            'erro' => $e->getMessage()
                        ]);

                    }
                }

            });

        Log::info('Processamento de SMS de notas concluido.');
    }
}