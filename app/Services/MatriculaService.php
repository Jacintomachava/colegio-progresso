<?php

namespace App\Services;

use App\Models\AlunoRelacaoPessoa;
use App\Models\AnoAcademico;
use App\Models\SaldoSMS;
use App\Models\SMSDestinatario;
use App\Models\CoCurricular;
use App\Models\MetodoPagamento;
use App\Models\Pagamento;
use App\Models\Aluno;
use App\Models\Classe;
use App\Models\DisciplinaCurriculo;
use App\Models\Disciplina;
use App\Models\Encarregado;
use App\Models\Escola;
use App\Models\Irmao;
use App\Models\Matricula;
use App\Models\Medico;
use App\Models\Mensagem;
use App\Services\SMSService;
use App\Models\Mensalidade;
use App\Models\Meses;
use App\Models\Pauta;
use App\Models\Periodo;
use App\Models\Pessoa;
use App\Models\PlanoEducativoIndividualizado;
use App\Models\Provincia;
use App\Models\Relacao;
use App\Models\Saude;
use App\Models\Taxa;
use App\Models\Turma;
use App\Models\User;
use App\Services\EmailService;
use App\Services\EntidadeReferenciaService;
use App\Services\MultaService;
use App\Services\PautaFinalService;
use App\Services\ReceitaDespesaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MatriculaService
{

    public static function renovacao($alunoID, $anoAcademicoID, $valorNormal, $valorGold) {
        
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. Buscar aluno
            |--------------------------------------------------------------------------
            */
            $aluno = Aluno::find($alunoID);

            if (!$aluno) {
                throw new \Exception(
                    'Aluno não encontrado. ID: '.$alunoID
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Não renovar aluno desistente
            |--------------------------------------------------------------------------
            */
            if ($aluno->desistiu == 1) {

                DB::rollBack();

                \Log::info(
                    'Renovação ignorada: aluno '.$alunoID.' desistiu.'
                );

                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Buscar ano académico da renovação
            |--------------------------------------------------------------------------
            */
            $anoAcademico = AnoAcademico::find($anoAcademicoID);

            if (!$anoAcademico) {
                throw new \Exception(
                    'Ano académico não encontrado. ID: '.$anoAcademicoID
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 4. PROTEÇÃO CONTRA DUPLICAÇÃO
            |--------------------------------------------------------------------------
            |
            | Se o aluno já possui matrícula no ano configurado,
            | não cria pagamento, referência ou nova matrícula.
            |
            */
            $jaRenovou = Matricula::where('aluno_id', $alunoID)->where('ano_academico_id', $anoAcademicoID)->exists();

            if ($jaRenovou) {

                DB::rollBack();

                \Log::info(
                    'Renovação ignorada: aluno '
                    .$alunoID
                    .' já possui matrícula no ano '
                    .$anoAcademico->ano
                );

                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Buscar última matrícula do aluno
            |--------------------------------------------------------------------------
            |
            | Não usamos esta matrícula para decidir se deve renovar.
            |
            | Ela serve somente para obter:
            |
            | - plano
            | - classe
            | - turma
            | - período
            |
            */
            $ultimaMatricula = Matricula::where('aluno_id', $alunoID)->where('ano_academico_id', '!=', $anoAcademicoID)->orderByDesc('id')->first();

            if (!$ultimaMatricula) {
                throw new \Exception(
                    'Aluno '.$alunoID.' não possui matrícula anterior.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Determinar plano e valor da renovação
            |--------------------------------------------------------------------------
            |
            | plano = 1 → NORMAL
            | plano = 2 → GOLD
            |
            */
            $plano = (int) $ultimaMatricula->plano;

            if ($plano === 1) {

                $valorTotalMatricula = (float) $valorNormal;

            } elseif ($plano === 2) {

                $valorTotalMatricula = (float) $valorGold;

            } else {

                throw new \Exception(
                    'Plano inválido para o aluno '.$alunoID
                    .'. Plano encontrado: '.$plano
                );
            }

            if ($valorTotalMatricula <= 0) {

                throw new \Exception(
                    'Valor de renovação inválido para o aluno '
                    .$alunoID
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 7. Buscar utilizador associado ao aluno
            |--------------------------------------------------------------------------
            */
            $user = User::where('pessoa_id', $aluno->pessoa_id)->first();

            if (!$user) {
                throw new \Exception(
                    'Utilizador não encontrado para o aluno '.$alunoID
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 8. Gerar códigos
            |--------------------------------------------------------------------------
            */
            $currentMonth = Carbon::now()->format('m');

            $yearLastTwoDigits = substr($anoAcademico->ano, -2);

            /*
            * Total de matrículas no ano da renovação.
            */
            $totalMatriculas = Matricula::where('ano_academico_id', $anoAcademicoID)->count();

            $numeroMatricula = $totalMatriculas + 1;

            /*
            * Factura
            *
            * Ex:
            * 00001 + 10 + 26
            */
            $fatura = str_pad($numeroMatricula, 5, '0', STR_PAD_LEFT).$currentMonth.$yearLastTwoDigits;

            /*
            * Código da matrícula
            *
            * Ex:
            * 0001.26
            */
            $codigoMatricula = str_pad($numeroMatricula, 4, '0', STR_PAD_LEFT).'.'.$yearLastTwoDigits;

            /*
            * Recibo
            */
            $totalPagamentos = Pagamento::count();
            $recibo = str_pad($totalPagamentos + 1, 5, '0', STR_PAD_LEFT).$yearLastTwoDigits;

            /*
            |--------------------------------------------------------------------------
            | 9. Gerar referência bancária
            |--------------------------------------------------------------------------
            */
            $referencia = EntidadeReferenciaService::GerarReferencia($fatura, $valorTotalMatricula);

            if (!$referencia) {
                throw new \Exception(
                    'Não foi possível gerar referência para o aluno '
                    .$alunoID
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 10. Criar pagamento
            |--------------------------------------------------------------------------
            */
            $pagamento = new Pagamento();

            $pagamento->recibo = $recibo;
            $pagamento->factura = $fatura;
            $pagamento->desconto = 0;
            $pagamento->entidade = 90028;
            $pagamento->referencia = $referencia;
            $pagamento->metodo_pagamento_id = 3;
            $pagamento->aluno_id = $aluno->id;
            $pagamento->user_id = $user->id;
            $pagamento->escola_id = $aluno->escola_id;
            // Categoria 1 = Matrícula
            $pagamento->categoria_id = 1;
            /*
            * NORMAL → valorNormal
            * GOLD   → valorGold
            */
            $pagamento->valor = $valorTotalMatricula;
            $pagamento->prazo_pagamento = $anoAcademico->data_fim_renovacao;
            $pagamento->descricao = 'Renovação da Matrícula do Aluno '.$aluno->codigo.' para o ano '.$anoAcademico->ano;

            $pagamento->save();

            /*
            |--------------------------------------------------------------------------
            | 11. Criar nova matrícula
            |--------------------------------------------------------------------------
            */
            $matricula = new Matricula();

            $matricula->codigo = $codigoMatricula;
            $matricula->desconto = 0;
            $matricula->entidade = 90028;
            $matricula->referencia = $referencia;
            $matricula->valor_matricula = $valorTotalMatricula;
            $matricula->valor_co_curricular = 0;
            $matricula->valor_total = $valorTotalMatricula;
            $matricula->co_curricular = false;
            $matricula->pagou = false;
            $matricula->classe_actualizado = false;
            /*
            * Mantém o plano anterior.
            *
            * 1 = NORMAL
            * 2 = GOLD
            */
            $matricula->plano = $plano;
            /*
            * Mantém os dados académicos da última matrícula.
            */
            $matricula->periodo_id = $ultimaMatricula->periodo_id;
            $matricula->classe_id = $ultimaMatricula->classe_id;
            $matricula->turma_id = $ultimaMatricula->turma_id;
            /*
            * Novo ano académico.
            */
            $matricula->ano_academico_id = $anoAcademicoID;
            $matricula->aluno_id = $aluno->id;
            $matricula->pagamento_id = $pagamento->id;
            $matricula->user_id = $user->id;
            $matricula->escola_id = $aluno->escola_id;

            $matricula->save();

            /*
            |--------------------------------------------------------------------------
            | 12. Registar receita
            |--------------------------------------------------------------------------
            */
            ReceitaDespesaService::receita($valorTotalMatricula, 1, 'Factura da Renovação da Matrícula', $pagamento->id, false, $referencia);

            /*
            |--------------------------------------------------------------------------
            | 13. Preparar SMS para o encarregado
            |--------------------------------------------------------------------------
            */
            $encarregado = AlunoRelacaoPessoa::with('pessoa')->where('aluno_id', $aluno->id)->where('relacao_id', 1)->first();

            if ($encarregado && $encarregado->pessoa && $encarregado->pessoa->telefone) {
                /*
                * Últimos 9 dígitos do telefone
                */
                $telefone = substr($encarregado->pessoa->telefone, -9);
                $valorFormatado = number_format($valorTotalMatricula, 2,',','.');
                $nomeAluno = $aluno->pessoa->nome;
                /*
                * Prazo dinâmico
                */
                $prazo = Carbon::parse($anoAcademico->data_fim_renovacao)->format('d-m-Y');
                /*
                * Nome do plano apenas para informação.
                */
                $nomePlano = $plano === 1 ? 'NORMAL' : 'GOLD';
                $sms =
                    'Prezado Encarregado do(a) '
                    .$nomeAluno
                    .', para pagar renovacao de matricula '
                    .$anoAcademico->ano
                    .' (Plano '.$nomePlano.')'
                    .' use ENTIDADE: 90028'
                    .', REFERENCIA: '.$referencia
                    .', VALOR: '.$valorFormatado
                    .' MZN. Banco: Millennium BIM.'
                    .' Prazo '.$prazo
                    .'. Cumpra com o prazo para evitar perder a vaga.';
                $qtd = SMSService::quantidadeSMS($sms);
                /*
                * Evita também duplicação da mensagem.
                */
                Mensagem::firstOrCreate(
                    [
                        'descricao' => $sms,
                        'telefone' => $telefone,
                        'canal' => 'SMS',
                    ],
                    [
                        'tipo' => 'Pendente',
                        'qtd' => $qtd,
                        'custo_real' => 1.35 * $qtd,
                        'credito' => 1.8 * $qtd,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 14. Confirmar transação
            |--------------------------------------------------------------------------
            */
            DB::commit();


            \Log::info(
                'Renovação efectuada com sucesso. '
                .'Aluno: '.$alunoID
                .' | Ano: '.$anoAcademico->ano
                .' | Plano: '.($plano === 1 ? 'NORMAL' : 'GOLD')
                .' | Valor: '.$valorTotalMatricula
            );


            return $matricula;


        } catch (\Throwable $e) {

            DB::rollBack();


            \Log::error(
                'Erro na renovação da matrícula. '
                .'Aluno: '.$alunoID
                .' | Ano académico ID: '.$anoAcademicoID
                .' | Erro: '.$e->getMessage()
            );


            return false;
        }
    }

    public static function renovacao2($alunoID,$anoAcademicoID)
    {
        try {
            DB::beginTransaction();

            $aluno = Aluno::where('id', $alunoID)->first();
            $anoAcademicoAtivo = AnoAcademico::where('id', $anoAcademicoID)->first();

            $matriculaExistente = Matricula::where('aluno_id', $alunoID)
                ->where('ano_academico_id', $anoAcademicoID)
                ->first();

            if ($matriculaExistente) {

                \Log::info(
                    'Renovação ignorada. Aluno '
                    . $alunoID
                    . ' já possui matrícula no ano '
                    . $anoAcademicoID
                );

                return $matriculaExistente;
            }

            $totalAlunos = Aluno::all();
            $current_year = $anoAcademicoAtivo->ano;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);
            $escola = Escola::where('id', $aluno->escola_id)->first();
            $user = User::where('pessoa_id', $aluno->pessoa_id)->first();

            $totalMatriculas = Matricula::where('ano_academico_id', $anoAcademicoAtivo->id)->get();

            // Pagar Matricula de Ano Anterior
            $matriculaAnterior = Matricula::where('ano_academico_id', $anoAcademicoID)->where('aluno_id', $aluno->id)->first();
            $classe = Classe::where('id', $matriculaAnterior->classe_id)->first();
            // Pagar na taxa selecionado pelo utser
            $taxa = Taxa::where('ciclo_id', $classe->ciclo_id)->first();

            $todosPagamento = Pagamento::all();
            $fatura = str_pad(count($totalMatriculas) + 1, 5, '0', STR_PAD_LEFT).''.$current_month.''.$year_last_two_digits;
            $codigoMatricula = str_pad(count($totalMatriculas) + 1, 4, '0', STR_PAD_LEFT).'.'.$year_last_two_digits;
            $recibo = str_pad(count($todosPagamento) + 1, 5, '0', STR_PAD_LEFT).''.$year_last_two_digits;

            $referencia = null;
            $valorTotalMatricula = 0;
            $valorTotalMatriculaCoCurricular = 0;
            $valorTotalMensalidade = 0;
            $valorMensalidadeCoCurricular = 0;
            $valorMensalidadeCurricular = 0;
            $valorTotal = 0;
            $coCurricular = false;
            $plano = 0;

            $coCurriculares = null;

            if (!empty($coCurriculares)) {
                $coCurricular = true;
            } else {
                $coCurricular = false;
            }

            if ($matriculaAnterior->plano == 1) {
                $valorTotalMatricula = $taxa->matricula_normal;
                $plano = 1;
                $valorTotalMatricula = 3500;
            } else {
                $valorTotalMatricula = $taxa->matricula_gold;
                $plano = 2;
                $valorTotalMatricula = 4500;
            }

            
            $referencia = EntidadeReferenciaService::GerarReferencia($fatura, $valorTotalMatricula);

            $pagamento = new Pagamento();
            $pagamento->recibo = $recibo;
            $pagamento->factura = $fatura;
            $pagamento->desconto = 0;
            $pagamento->entidade = 90028;
            $pagamento->entidade = 90028;
            $pagamento->referencia = $referencia;
            $pagamento->metodo_pagamento_id = 3;
            $pagamento->aluno_id = $aluno->id;
            $pagamento->user_id = $user->id;
            $pagamento->escola_id = $aluno->escola_id;
            $pagamento->categoria_id = 1;
            $pagamento->valor = $valorTotalMatricula;
            $pagamento->prazo_pagamento = $anoAcademicoAtivo->data_fim_renovacao;
            $pagamento->descricao = 'Confirmado Pagamento da Renovaca da Matricula do Aluno '.$aluno->codigo;

            if ($pagamento->save()) {
                $matricula = new Matricula();
                $matricula->codigo = $codigoMatricula;
                $matricula->desconto = 0;
                $matricula->entidade = 90028;
                $matricula->entidade = 90028;
                $matricula->referencia = $referencia;
                $matricula->valor_matricula = $valorTotalMatricula;
                $matricula->valor_co_curricular = $valorTotalMatriculaCoCurricular;
                $matricula->valor_total = $valorTotalMatricula;
                $matricula->co_curricular = $coCurricular;
                $matricula->pagou = false;
                $matricula->classe_actualizado = false;
                $matricula->plano = $plano;
                $matricula->periodo_id = $matriculaAnterior->periodo_id;
                $matricula->ano_academico_id = $anoAcademicoAtivo->id;
                $matricula->classe_id = $matriculaAnterior->classe->id;
                $matricula->aluno_id = $aluno->id;
                $matricula->turma_id = $matriculaAnterior->turma->id;
                $matricula->pagamento_id = $pagamento->id;
                $matricula->user_id = $user->id;
                $matricula->escola_id = $aluno->escola_id;

                if (!empty($coCurriculares)) {
                    $quantidade = count($coCurriculares);

                    foreach ($coCurriculares as $coCurricularID) {
                        $coCurricular1 = new CoCurricular();
                        $coCurricular1->aluno_id = $aluno->id;
                        $coCurricular1->disciplina_id = $coCurricularID;
                        $coCurricular1->mes_id = 1;
                        $coCurricular1->mes_fim_id = 10;
                        $coCurricular1->ano_academico_id = $anoAcademicoAtivo->id;
                        $coCurricular1->save();
                    }
                }

                // categoria 1 = Matricula 2 = Propinas
                $descricao1 = 'Factura da Matricula';
                ReceitaDespesaService::receita($valorTotalMatricula, 1, $descricao1, $pagamento->id, false, $referencia);

                if ($matricula->save()) {

                    $encarregado = AlunoRelacaoPessoa::with('pessoa')->where('aluno_id', $aluno->id)->where('relacao_id', 1)->first();

                    // padroniza telefone
                    $telefone = substr($encarregado->pessoa->telefone, -9);
                    $valorFormatada = number_format($valorTotalMatricula, 2, ',', '.');
                    $nomeAluno = $aluno->pessoa->nome;
                    $sms = 'Prezado Encarregado do(a) '.$nomeAluno.' para pagar renovacao de matricula 2026 usa  ENTIDADE: 90028, REFERENCIA: '.$referencia.', VALOR: '.$valorFormatada.' MZN. Banco: Millennium BIM. Prazo 30-Out-2025, cumpra com prazo evite perder vaga.';
                    $qtd = SMSService::quantidadeSMS($sms);

                    if($encarregado->pessoa->telefone){
                        
                        Mensagem::firstOrCreate(
                            [
                                'descricao' => $sms,
                                'telefone'  => $telefone,
                                'canal'     => 'SMS',
                            ],
                            [
                                'tipo'       => 'Pendente',
                                'qtd'        => $qtd,
                                'custo_real' => 1.2 * $qtd,
                                'credito'    => 1.8 * $qtd,
                            ]
                        );
                    }

                    DB::commit();

                    \Log::info('Renovacao da Matricula Efectuado com Sucesso!: '.$alunoID);

                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::info('Erro: '.$e->getMessage());

        }
    }

}
