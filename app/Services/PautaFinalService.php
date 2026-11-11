<?php

namespace App\Services;

use App\Models\SMSDestinatario;
use App\Models\AnoAcademico;
use App\Models\Turma;
use App\Models\ProfessorTurma;
use App\Models\DisciplinaCurriculo;
use App\Models\Matricula;
use App\Models\Pauta;
use App\Models\Classe;
use App\Models\PautaFinal;
use App\Models\AlunoRelacaoPessoa;
use App\Services\PautaFinalService;
use App\Services\SMSService;
use App\Services\MultaService;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PautaFinalService
{

    public static function decrimentarVagaTurma($turmaId, $numero)
    {
        $turma = Turma::find($turmaId); // Busca direta pelo ID

        if (!$turma) {
            return false; // Retorna false se a turma não existir
        }

        // Verifica se ainda há vagas disponíveis antes de matricular
        if ($turma->vaga >= ($turma->matriculado + $numero)) {
            $turma->matriculado += $numero; // Incrementa o número de matriculados
            return $turma->save(); // Retorna true se salvar com sucesso, false se falhar
        }

        return false; // Sem vagas suficientes
    }


    public static function pautaFinalAutomatico()
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', 1)->first();
        $turmas = Turma::where('classe_id','>',3)->where('pauta_finalizada',0)->where('ano_academico_id',$anoAcademicoAtivo->id)->get();

        foreach ($turmas as $turma) {

            self::pautaFinalManual($turma->id);
            $turmaProfessor = ProfessorTurma::where('turma_id', $turma->id)->first();
            // Obter a turma específica
            $turma1 = Turma::findOrFail($turma->id);

            if($turma1!=null){
                $turma1->pauta_finalizada = true;
                $turma1->save();
            }

            if($turmaProfessor!=null){
                $turmaProfessor->pauta_finalizada = true;
                $turmaProfessor->pauta_finalizada_automaticamente = true;
                $turmaProfessor->save();
            }
        }
    }

    public static function pautaFinalManual($classeID)
    {
        $anoAcademicoAtivo = AnoAcademico::where('ano', 2025)->first();
    
        // Obter a classe associada à turma
        $classeId = $classeID;

        $classe = Classe::where('id', $classeId)->first();

        if($classe->tem_exame==0){  //Quando nao tem Exame

            // Obter o currículo da classe (disciplinas associadas)
            $disciplinas = DisciplinaCurriculo::where('classe_id', $classeId)
                ->with('disciplina') // Relacionamento com a tabela `disciplina`
                ->get()
                ->pluck('disciplina');
        
            // Obter os alunos da turma
            $matriculaAlunos = Matricula::where('classe_id', $classeId)->where('ano_academico_id', $anoAcademicoAtivo->id)->get();

            // Dados para a tabela final
            $dadosTabela = [];
        
            foreach ($matriculaAlunos as $matriculaAluno) {
                $alunoId = $matriculaAluno->aluno->id;
                $escolaId = $matriculaAluno->aluno->escola_id;
                $planoAtivo = $matriculaAluno->plano;
                $periodo = $matriculaAluno->periodo->tipo;
        
                // Inicializa o aluno na tabela
                $dadosTabela[$alunoId] = [
                    'codigo' => $matriculaAluno->aluno->codigo,
                    'nome' => $matriculaAluno->aluno->pessoa->nome,
                    'genero' => $matriculaAluno->aluno->pessoa->genero, // Exemplo: H ou M
                    'disciplinas' => [],
                    'mediaGeral' => 0,
                    'resultadoFinal' => '',
                ];
        
                foreach ($disciplinas as $disciplina) {
                    $disciplinaId = $disciplina->id;
                    $disciplinaNome = $disciplina->nome;
        
                    // Obter as notas da pauta para o aluno e a disciplina
                    $pauta = Pauta::where('aluno_id', $alunoId)
                        ->where('disciplina_id', $disciplinaId)
                        ->first();
        
                    // Inicializa a disciplina com valores padrão
                    $dadosTabela[$alunoId]['disciplinas'][$disciplinaNome] = [
                        'mediaTr1' => 0,
                        'mediaTr2' => 0,
                        'mediaTr3' => 0,
                        'mediaAnual' => 0,
                    ];
        
                    if ($pauta) {
                        // Cálculo das médias trimestrais
                        foreach (range(1, 3) as $trimestre) {
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
        
                            $notaFinalTrimestre = (2 * $mediaTestes + $pauta->{'apTr' . $trimestre}) / 3;
        
                            $dadosTabela[$alunoId]['disciplinas'][$disciplinaNome]['mediaTr' . $trimestre] = round($notaFinalTrimestre, 2);
                        }
        
                        // Cálculo da média anual
                        $dadosTabela[$alunoId]['disciplinas'][$disciplinaNome]['mediaAnual'] = round(
                            array_sum([
                                $dadosTabela[$alunoId]['disciplinas'][$disciplinaNome]['mediaTr1'],
                                $dadosTabela[$alunoId]['disciplinas'][$disciplinaNome]['mediaTr2'],
                                $dadosTabela[$alunoId]['disciplinas'][$disciplinaNome]['mediaTr3'],
                            ]) / 3,
                            0
                        );
                    }
                }
        
                // Atualiza a média geral do aluno
                $mediasAnuais = array_map(fn($d) => $d['mediaAnual'], $dadosTabela[$alunoId]['disciplinas']);
                $mediaGeral = count($mediasAnuais) > 0
                    ? round(array_sum($mediasAnuais) / count($mediasAnuais), 0)
                    : 0;
        
                $dadosTabela[$alunoId]['mediaGeral'] = $mediaGeral;

                $novaClasse='';
                $sms = '';
                $resultadoFinal='';
                // Define o resultado final com validação explícita
                if ($mediaGeral < 9.5) {
                    $resultadoFinal = 'Reprovado';
                    $sms = "Ja esta Disponivel o Resultado Final";
                    $novaClasse = $matriculaAluno->classe_id;
                } else {
                    $resultadoFinal = 'Aprovado';
                    $sms = "Parabens foi aprovado com media ".$mediaGeral;
                    $novaClasse = $matriculaAluno->classe_id + 1;
                }

                $dadosTabela[$alunoId]['resultadoFinal'] = $resultadoFinal;

                // Definir o resultado com base na nota final
                $resultadoFinal1 = ($mediaGeral >= 9.5) ? 'Aprovado' : 'Reprovado';
        
                // Salvar na tabela pautaFinal
                PautaFinal::updateOrCreate(
                    ['aluno_id' => $alunoId],
                    [
                        'nota_final' => $mediaGeral,
                        'resulta' => $resultadoFinal1,
                        'ano_academico_id' => $anoAcademicoAtivo->id,
                        'classe_actual_id' => $matriculaAluno->classe_id,
                        'classe_nova_id' => $novaClasse,
                    ]
                );
                
                //Aqui adiciono o ano academico +1 para actualizar o novo ano pois eles ja fizeram renovacao da matricula antes de saber se ja aprovaram ou nao
                self::actualizarRenovacaoMatricula($alunoId,$anoAcademicoAtivo->id+1,$novaClasse,$planoAtivo,$periodo);

            }


            // Obter a turma específica
            $turma = Turma::where('classe_id')->where('ano_academico_id',$anoAcademicoAtivo->id)->get();
            $turma->pauta_finalizada = 1;
            $turma->save();
            

        }elseif($classe->tem_exame==1){  // Quando tem Exame

        }
    

    
    }


    public static function actualizarRenovacaoMatricula($alunoId,$anoAcademico,$novaClasse,$planoAtivo,$periodo)
    {
        $matricula = Matricula::where('aluno_id', $alunoId)->where('ano_academico_id',$anoAcademico)->first();

        $classe = Classe::where('id', $novaClasse)->first();
        // Pagar na taxa selecionado pelo utser
        $taxa = Taxa::where('ciclo_id', $classe->ciclo_id)->first();

        //No inicios os alunos nao tem aulas CoCurricular
        $valorMensalidadeCoCurricular = 0; 
        $valorTotalMensalidade = 0;
        $valorMensalidadeCurricular = 0;
        $tipoPlano = $planoAtivo;

        if($matricula!=null){
            $matricula->classe_id = $novaClasse;
            $matricula->classe_actualizado = true;
            $matricula->save();


            if ($tipoPlano == 1) {

                if ($periodo == 'meio_dia') {
                    $valorMensalidadeCurricular = $taxa->propina_meio_dia_normal;
                    $valorTotalMensalidade = $taxa->propina_meio_dia_normal;
                } elseif ($periodo == 'todo_dia') {
                    $valorMensalidadeCurricular = $taxa->propina_todo_dia_normal;
                    $valorTotalMensalidade = $taxa->propina_todo_dia_normal;
                }

            } elseif ($tipoPlano == 2) {

                if ($periodo == 'meio_dia') {
                    $valorMensalidadeCurricular = $taxa->propina_meio_dia_gold;
                    $valorTotalMensalidade = $taxa->propina_meio_dia_gold;
                } elseif ($periodo == 'todo_dia') {
                    $valorMensalidadeCurricular = $taxa->propina_todo_dia_gold;
                    $valorTotalMensalidade = $taxa->propina_todo_dia_gold;
                }
            }

            $this->gerarActulizacaoMensalidades($alunoId, $anoAcademico, $valorMensalidadeCurricular, $valorMensalidadeCoCurricular);

        }

        
    }


    /* Esse metodo gera a mensalidade anual de cada estudante
     Onde recebe o id de estudante, o Ano Academico, e a TaxaID
     Para preencher mensalidades que devem ser pagos
    */
    public function gerarMensalidades($alunoID, $anoAcademicoID, $valor, $valorCoCurricular)
    {
        $months = Meses::all();
        $anoAcademicoAtivo = AnoAcademico::where('id', $anoAcademicoID)->first();

        $current_year = $anoAcademicoAtivo->ano;
        $current_month = Carbon::now()->format('m');
        $year_last_two_digits = substr($current_year, -2);

        $valorTotal = $valor + $valorCoCurricular;

        foreach ($months as $month) {
            $todosPagamento = Pagamento::all();
            $fatura = str_pad(count($todosPagamento) + 1, 5, '0', STR_PAD_LEFT).''.$current_month.''.$year_last_two_digits;
            $recibo = str_pad(count($todosPagamento) + 1, 5, '0', STR_PAD_LEFT).''.$year_last_two_digits;
            $referencia = EntidadeReferenciaService::GerarReferencia($fatura, $valorTotal);

            $pagamento = new Pagamento();
            $pagamento->recibo = $recibo;
            $pagamento->factura = $fatura;
            $pagamento->desconto = 0;
            $pagamento->entidade = 90028;
            $pagamento->entidade = 90028;
            $pagamento->referencia = $referencia;
            $pagamento->metodo_pagamento_id = 3;
            $pagamento->aluno_id = $alunoID;
            $pagamento->user_id = Auth::user()->id;
            $pagamento->escola_id = Auth::user()->escola_id;
            $pagamento->categoria_id = 2;
            $pagamento->valor = $valorTotal;
            $pagamento->prazo_pagamento = Carbon::createFromFormat('Y-m-d', $month->data_inicio)->setYear($anoAcademicoAtivo->ano);
            $pagamento->descricao = 'Confirmado Pagamento da propina de '.$month->nome.' '.$anoAcademicoAtivo->ano;

            if ($pagamento->save()) {
                // categoria 1 = Matricula 2 = Propinas
                $descricao1 = 'Factura da Propina '.$month->nome;
                $descricao1 = 'Factura da Propina '.$month->nome;
                ReceitaDespesaService::receita($valorTotal, 2, $descricao1, $pagamento->id, false, $referencia);

                $mensalidade = new Mensalidade();
                $mensalidade->valor = $valor;
                $mensalidade->valor_co_curricular = $valorCoCurricular;
                $mensalidade->mes_id = $month->id;
                $mensalidade->ano_academico_id = $anoAcademicoID;
                $mensalidade->data_inicio = Carbon::createFromFormat('Y-m-d', $month->data_inicio)->setYear($anoAcademicoAtivo->ano);
                $mensalidade->data_fim = Carbon::createFromFormat('Y-m-d', $month->data_fim)->setYear($anoAcademicoAtivo->ano);
                $mensalidade->aluno_id = $alunoID;
                $mensalidade->escola_id = Auth::user()->escola_id;
                $mensalidade->pagamento_id = $pagamento->id;
                $mensalidade->save();
            }
        }
    }

    
}
