<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aluno;
use App\Models\AnoAcademico;
use App\Models\Classe;
use App\Models\DisciplinaCurriculo;
use App\Models\Curriculo;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Models\Pauta;
use App\Models\PautaExame;
use App\Models\Professor;
use App\Models\Mensagem;
use App\Models\ProfessorTurma;
use App\Models\PautaFinal;
use App\Models\ProfessorTurmaDisciplina;
use App\Models\Turma;
use App\Models\Pagamento;
use App\Models\Meses;
use App\Models\Taxa;
use Carbon\Carbon;
use App\Services\EntidadeReferenciaService;
use App\Services\ReceitaDespesaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AlunoPautaController extends Controller
{
    public function show()
    {
        $aluno = Aluno::where('pessoa_id', Auth::user()->pessoa_id)->first();

        $anosIds = Matricula::where('aluno_id', $aluno->id)->pluck('ano_academico_id');
        $anosAcademicos = AnoAcademico::whereIn('id', $anosIds)->orderBy('id', 'desc')->get();

        //$anoAtivo = AnoAcademico::where('estado', 1)->first();
        $anoAtivo = AnoAcademico::where('ano', 2026)->first();
        $pautas = Pauta::where('aluno_id', $aluno->id) ->where('pauta',1)->where('ano_academico_id', $anoAtivo->id)->get();
        $mensalidades = Mensalidade::where('aluno_id', $aluno->id)->where('pagou', 0)->where('multa', '>', 0)->get();
        $matricula = Matricula::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAtivo->id)->first();

        // Retorna a view com os dados carregados
        return view('secretaria.pauta.show', [
            'pautas' => $pautas,
            'aluno' => $aluno,
            'anosAcademicos' => $anosAcademicos,
            'anoAtivo' => $anoAtivo,
            'mensalidades' => $mensalidades,
            'matricula' => $matricula,
        ]);
    }

    public function pautasTurmas($classe_ID,$ano)
    {
        // Obter a turma específica
        // $turma = Turma::findOrFail($turmaId);

        // Obter a classe associada à turma
        $classeId = $classe_ID; // $turma->classe_id;

        $minhaClasse = Classe::where('id', $classe_ID)->first();
        $anoAtivo = AnoAcademico::where('ano', $ano)->first();
        //$anoAtivo = AnoAcademico::where('ano', 2025)->first();

        // Obter o currículo da classe (disciplinas associadas)
        $disciplinas = DisciplinaCurriculo::where('classe_id', $classeId)
                    ->with('disciplina')  // Relacionamento com a tabela `disciplina`
                    ->orderBy('pauta_ordem', 'asc')
                    ->get()
                    ->map(function ($curriculo) {
                        return [
                            'id' => $curriculo->disciplina->id,
                            'nome' => $curriculo->disciplina->nome,
                            'ordem' => $curriculo->pauta_ordem,
                        ];
                    });

        // Obter os alunos da turma
        $matriculaAlunos = Matricula::where('classe_id', $classeId)->where('ano_academico_id', $anoAtivo->id) ->where('pauta',1)->where('escola_id', Auth::user()->escola_id)->get();

        // Dados para a tabela final
        $dadosTabela = [];

        foreach ($matriculaAlunos as $matriculaAluno) {
            $alunoId = $matriculaAluno->aluno->id;

            // Busca o número da pauta (qualquer disciplina serve, pois o número é por aluno)
            $pautaNumero = Pauta::where('aluno_id', $alunoId)
                ->where('escola_id', Auth::user()->escola_id)
                ->where('classe_id', $classe_ID)
                ->where('pauta',1)
                ->value('numero'); // <-- só pega o campo 'numero'

            // Inicializa o aluno na tabela
            $dadosTabela[$alunoId] = [
                'codigo' => $matriculaAluno->aluno->codigo,
                'nome' => $matriculaAluno->aluno->pessoa->nome,
                'genero' => $matriculaAluno->aluno->pessoa->genero, // Exemplo: H ou M
                'numero' => $pautaNumero ?? null, // ✅ agora vem da pauta diretamente
                'disciplinas' => [],
                'mediaGeral' => 0,
                'resultadoFinal' => '',
                'cop1' => null,
                'cop2' => null,
                'cop3' => null,
                'sin1' => null,
                'sin2' => null,
                'sin3' => null,
            ];

            foreach ($disciplinas as $disciplina) {
                        $disciplinaId = $disciplina['id'];
                        $disciplinaNome = $disciplina['nome'];

                // Obter as notas da pauta para o aluno e a disciplina
                $pauta = Pauta::where('aluno_id', $alunoId)
                    ->where('disciplina_id', $disciplinaId)
                    ->where('ano_academico_id', $anoAtivo->id)
                    ->where('escola_id', Auth::user()->escola_id)
                    ->where('classe_id', $classe_ID)
                    ->where('pauta',1)
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
                            $pauta->{'teste1Tr'.$trimestre},
                            $pauta->{'teste2Tr'.$trimestre},
                            $pauta->{'teste3Tr'.$trimestre},
                            $pauta->{'teste4Tr'.$trimestre},
                            $pauta->{'mmTr'.$trimestre},
                        ];

                        $testesValidos = array_filter($testes, fn ($t) => $t > 0);
                        $mediaTestes = count($testesValidos) > 0
                            ? array_sum($testesValidos) / count($testesValidos)
                            : 0;

                        $notaFinalTrimestre = (2 * $mediaTestes + $pauta->{'apTr'.$trimestre}) / 3;

                        $dadosTabela[$alunoId]['disciplinas'][$disciplinaNome]['mediaTr'.$trimestre] = round($notaFinalTrimestre, 2);
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

                    // ✅ Adiciona comportamento e sinalética (apenas 1 vez por aluno)
                    $dadosTabela[$alunoId]['cop1'] = $pauta->cop1 ?? $dadosTabela[$alunoId]['cop1'];
                    $dadosTabela[$alunoId]['cop2'] = $pauta->cop2 ?? $dadosTabela[$alunoId]['cop2'];
                    $dadosTabela[$alunoId]['cop3'] = $pauta->cop3 ?? $dadosTabela[$alunoId]['cop3'];
                    $dadosTabela[$alunoId]['sin1'] = $pauta->sin1 ?? $dadosTabela[$alunoId]['sin1'];
                    $dadosTabela[$alunoId]['sin2'] = $pauta->sin2 ?? $dadosTabela[$alunoId]['sin2'];
                    $dadosTabela[$alunoId]['sin3'] = $pauta->sin3 ?? $dadosTabela[$alunoId]['sin3'];
                }

                // Atualiza a média geral do aluno
                $mediasAnuais = array_map(fn ($d) => $d['mediaAnual'], $dadosTabela[$alunoId]['disciplinas']);

                $totalDisciplinas = count($mediasAnuais);

                // ⚠️ Verifica se a classe é a 7ª
                if ($minhaClasse->numero == 7) {
                    $divisor = max($totalDisciplinas - 3, 1); // impede divisão por zero
                } else {
                    $divisor = $totalDisciplinas;
                }

                $dadosTabela[$alunoId]['mediaGeral'] = $divisor > 0
                    ? round(array_sum($mediasAnuais) / $divisor, 0)
                    : 0;

                if($minhaClasse->tem_exame==0){
                    // Resultado final
                    $dadosTabela[$alunoId]['resultadoFinal'] = $dadosTabela[$alunoId]['mediaGeral'] >= 10 ? 'Progride' : 'Não Progride';
                }else{
                    // Resultado final
                    $dadosTabela[$alunoId]['resultadoFinal'] = $dadosTabela[$alunoId]['mediaGeral'] >= 10 ? 'Admitido' : 'Não Admitido';
                }  
            }
        }

        // ✅ Ordenar os alunos por numero (ordem numerica crescente)
        uasort($dadosTabela, function ($a, $b) {
            return ($a['numero'] ?? 9999) <=> ($b['numero'] ?? 9999);
        });

        // Retorna a view com os dados carregados
        return view('secretaria.pauta.turma.show1', [
            'dadosTabela' => $dadosTabela,
            'minhaClasse' => $minhaClasse,
            'disciplinas' => $disciplinas,
            'anoAtivo' => $anoAtivo,
        ]);
    }

    //View de Pauta de Aluno
    public function pautaTurma($classe_ID,$ano)
    {
        // Obter a turma específica
        // $turma = Turma::findOrFail($turmaId);
        $professor = Professor::where('pessoa_id', Auth::user()->pessoa_id)->first();
        // $professorTurma = ProfessorTurma::where('professor_id', $professor->id)->where('turma_id', $turmaId)->first();

        // Obter a classe associada à turma
        $classeId = $classe_ID;

        $minhaClasse = Classe::where('id', $classe_ID)->first();

        $anoAtivo = AnoAcademico::where('ano', $ano)->first();
        //$anoAtivo = AnoAcademico::where('ano', 2025)->first();

        // Obter o currículo da classe (disciplinas associadas)
        $disciplinas = DisciplinaCurriculo::where('classe_id', $classeId)
                    ->with('disciplina')  // Relacionamento com a tabela `disciplina`
                    ->orderBy('pauta_ordem', 'asc')
                    ->get()
                    ->map(function ($curriculo) {
                        return [
                            'id' => $curriculo->disciplina->id,
                            'nome' => $curriculo->disciplina->nome,
                            'ordem' => $curriculo->pauta_ordem,
                        ];
                    });

        // Obter os alunos da turma
        $matriculaAlunos = Matricula::where('classe_id', $classe_ID)->where('ano_academico_id', $anoAtivo->id) ->where('pauta',1)->where('escola_id', Auth::user()->escola_id)->get();

        // Dados para a tabela final
        $dadosTabela = [];

        foreach ($matriculaAlunos as $matriculaAluno) {
            $alunoId = $matriculaAluno->aluno->id;

            // Busca o número da pauta (qualquer disciplina serve, pois o número é por aluno)
            $pautaNumero = Pauta::where('aluno_id', $alunoId)
                ->where('escola_id', Auth::user()->escola_id)
                ->where('classe_id', $classe_ID)
                 ->where('pauta',1)
                ->value('numero'); // <-- só pega o campo 'numero'

            // Inicializa o aluno na tabela
            $dadosTabela[$alunoId] = [
                'codigo' => $matriculaAluno->aluno->codigo,
                'nome' => $matriculaAluno->aluno->pessoa->nome,
                'genero' => $matriculaAluno->aluno->pessoa->genero, // Exemplo: H ou M
                'numero' => $pautaNumero ?? null, // ✅ agora vem da pauta diretamente
                'disciplinas' => [],
                'mediaGeral' => 0,
                'resultadoFinal' => '',
                'cop1' => null,
                'cop2' => null,
                'cop3' => null,
                'sin1' => null,
                'sin2' => null,
                'sin3' => null,
            ];

            foreach ($disciplinas as $disciplina) {
                    $disciplinaId = $disciplina['id'];
                    $disciplinaNome = $disciplina['nome'];

                // Obter as notas da pauta para o aluno e a disciplina
                $pauta = Pauta::where('aluno_id', $alunoId)
                    ->where('disciplina_id', $disciplinaId)
                    ->where('ano_academico_id', $anoAtivo->id)
                    ->where('escola_id', Auth::user()->escola_id)
                    ->where('classe_id', $classe_ID)
                    ->where('pauta',1)
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
                            $pauta->{'teste1Tr'.$trimestre},
                            $pauta->{'teste2Tr'.$trimestre},
                            $pauta->{'teste3Tr'.$trimestre},
                            $pauta->{'teste4Tr'.$trimestre},
                            $pauta->{'mmTr'.$trimestre},
                        ];

                        $testesValidos = array_filter($testes, fn ($t) => $t > 0);
                        $mediaTestes = count($testesValidos) > 0
                            ? array_sum($testesValidos) / count($testesValidos)
                            : 0;

                        $notaFinalTrimestre = (2 * $mediaTestes + $pauta->{'apTr'.$trimestre}) / 3;

                        $dadosTabela[$alunoId]['disciplinas'][$disciplinaNome]['mediaTr'.$trimestre] = round($notaFinalTrimestre, 2);
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

                    // ✅ Adiciona comportamento e sinalética (apenas 1 vez por aluno)
                    $dadosTabela[$alunoId]['cop1'] = $pauta->cop1 ?? $dadosTabela[$alunoId]['cop1'];
                    $dadosTabela[$alunoId]['cop2'] = $pauta->cop2 ?? $dadosTabela[$alunoId]['cop2'];
                    $dadosTabela[$alunoId]['cop3'] = $pauta->cop3 ?? $dadosTabela[$alunoId]['cop3'];
                    $dadosTabela[$alunoId]['sin1'] = $pauta->sin1 ?? $dadosTabela[$alunoId]['sin1'];
                    $dadosTabela[$alunoId]['sin2'] = $pauta->sin2 ?? $dadosTabela[$alunoId]['sin2'];
                    $dadosTabela[$alunoId]['sin3'] = $pauta->sin3 ?? $dadosTabela[$alunoId]['sin3'];
                }

                // Atualiza a média geral do aluno
                $mediasAnuais = array_map(fn ($d) => $d['mediaAnual'], $dadosTabela[$alunoId]['disciplinas']);

                $totalDisciplinas = count($mediasAnuais);

                // ⚠️ Verifica se a classe é a 7ª
                if ($minhaClasse->numero == 7) {
                    $divisor = max($totalDisciplinas - 3, 1); // impede divisão por zero
                } else {
                    $divisor = $totalDisciplinas;
                }

                $dadosTabela[$alunoId]['mediaGeral'] = $divisor > 0
                    ? round(array_sum($mediasAnuais) / $divisor, 0)
                    : 0;

                if($minhaClasse->tem_exame==0){
                    // Resultado final
                    $dadosTabela[$alunoId]['resultadoFinal'] = $dadosTabela[$alunoId]['mediaGeral'] >= 10 ? 'Progride' : 'Não Progride';
                }else{
                    // Resultado final
                    $dadosTabela[$alunoId]['resultadoFinal'] = $dadosTabela[$alunoId]['mediaGeral'] >= 10 ? 'Admitido' : 'Não Admitido';
                }  

            }
        }

        // ✅ Ordenar os alunos por numero (ordem numerica crescente)
        uasort($dadosTabela, function ($a, $b) {
            return ($a['numero'] ?? 9999) <=> ($b['numero'] ?? 9999);
        });

        // Retorna a view com os dados carregados
        return view('secretaria.pauta.turma.show', [
            'dadosTabela' => $dadosTabela,
            'minhaClasse' => $minhaClasse,
            'disciplinas' => $disciplinas,
            'anoAtivo' => $anoAtivo,
            // 'professorTurma' => $professorTurma,
        ]);
    }

    //Chamar a segunda vez
    public function putasExames($classe_id,$disciplina_id1,$ano1)
    {

        $ano = AnoAcademico::where('ano', $ano1)->first();
        //$ano = AnoAcademico::where('ano', 2025)->first();

        $classe = Classe::find($classe_id);
        $curriculo = Curriculo::where('classe_id', $classe->id)->where('estado', 1)->first();
        $disciplina_id = $disciplina_id1;

        $disciplinasComExame = DisciplinaCurriculo::where('curriculo_id', $curriculo->id)
            ->where('classe_id', $classe->id)
            ->where('tem_exame', 1)
            ->get();

        foreach ($disciplinasComExame as $dc) {

            // Buscar pautas (frequência)
            $pautas = Pauta::where('disciplina_id', $dc->disciplina_id)
                ->where('classe_id', $classe->id)
                ->where('ano_academico_id', $ano->id)
                ->where('escola_id', Auth::user()->escola_id)
                ->where('pauta',1)
                ->get();


            foreach ($pautas as $pauta) {

                // Calcular NCD (média anual)
                $ncd = $this->calcularNCD($pauta);

                $nf = 0;

                if($dc->faz_exame==0){
                    $nf = $ncd;
                }

                // Verificar se já existe na pauta de exame
                $existe = PautaExame::where('aluno_id', $pauta->aluno_id)
                    ->where('disciplina_id', $pauta->disciplina_id)
                    ->where('classe_id', $classe->id)
                    ->where('escola_id', Auth::user()->escola_id)
                    ->where('ano_academico_id', $ano->id)
                    ->exists();


                if (!$existe) {
                    PautaExame::create([
                        'aluno_id'         => $pauta->aluno_id,
                        'pauta_id'         => $pauta->id,
                        'disciplina_id'    => $pauta->disciplina_id,
                        'classe_id'        => $pauta->classe_id,
                        'escola_id'        => $pauta->escola_id,
                        'ano_academico_id' => $ano->id,
                        'ncd'              => $ncd,  // 🎯 AQUI ESTÁ A MÉDIA FINAL
                        'nf'               => $nf,  // 🎯 AQUI ESTÁ A MÉDIA FINAL
                    ]);
                }
            }
        }

        $pautas = PautaExame::where('classe_id', $classe->id)->where('disciplina_id',$disciplina_id)->where('escola_id',Auth::user()->escola_id)->where('ano_academico_id', $ano->id)->get();
        $disciplinas = DisciplinaCurriculo::where('curriculo_id', $curriculo->id)->where('classe_id', $classe->id)->where('tem_exame', 1)->get();
        $classes = Classe::where('tem_exame', 1)->get();
        $classe = Classe::where('id', $classe->id)->first();
        $disciplina = Disciplina::where('id', $disciplina_id)->first();


        // Retorna a view com os dados carregados
        return view('secretaria.pauta.exame.create', [
            'pautas' => $pautas,
            'disciplinas' => $disciplinas,
            'classes' => $classes,
            'disciplina_id' => $disciplina_id,
            'classe' => $classe,
            'disciplina' => $disciplina,
            'ano' => $ano,
        ]);
    }

    public function putaExameAluno($codigoAluno,$ano)
    {
        $anoAcademico = AnoAcademico::where('ano', $ano)->first();
        $aluno = Aluno::where('codigo', $codigoAluno)->first();
        $matricula = Matricula::where('ano_academico_id', $anoAcademico->id)->where('escola_id', Auth::user()->escola_id)->where('aluno_id', $aluno->id)->first();

        // Buscar notas de exame
        $pautasExames = PautaExame::where('aluno_id', $aluno->id)
            ->where('ano_academico_id', $anoAcademico->id)
            ->where('escola_id',Auth::user()->escola_id)
            ->get();

             // Retorna a view com os dados carregados
        return view('secretaria.pauta.exame.pautaAluno', [
            'pautasExames' => $pautasExames,
            'matricula' => $matricula,
        ]);
    }

    public function putaExameAlunoPDF($codigoAluno,$ano)
    {
        $anoAcademico = AnoAcademico::where('ano', $ano)->first();
        $aluno = Aluno::where('codigo', $codigoAluno)->first();
        $escola = Escola::where('id', Auth::user()->escola_id)->first();
        $matricula = Matricula::where('ano_academico_id', $anoAcademico->id)->where('escola_id', Auth::user()->escola_id)->where('aluno_id', $aluno->id)->first();
        $classe = Classe::where('id', $matricula->classe_id)->first();

        // Buscar notas de exame
        $pautasExames = PautaExame::where('aluno_id', $aluno->id)
            ->where('ano_academico_id', $anoAcademico->id)
            ->where('escola_id',Auth::user()->escola_id)
            ->get();

            
        $pdf = \PDF::loadView('secretaria.pauta.exame.pautaAlunoPDF', [
            'escola' => $escola,
            'pautasExames' => $pautasExames,
            '$classe' => $classe,
            'matricula' => $matricula,
            'anoAcademico' => $anoAcademico,
        ])->setPaper('a4', 'landscape'); // Define o papel como A4 e a orientação como paisagem (landscape)

        $fileName = 'Pauta de Exame da - '.$matricula->classe->nome;

        return $pdf->stream($fileName.'.pdf');

    }

    //Chamar a primeira vez
    public function pautaExame($classe_id,$ano)
    {

        $classe = Classe::findOrFail($classe_id);
        $anoAcademico = AnoAcademico::where('ano', $ano)->first();
        //$anoAcademico = AnoAcademico::where('ano', 2025)->first();

        // Buscar disciplinas com exame
        $disciplinasComExame = DisciplinaCurriculo::where('classe_id', $classe->id)
            ->where('tem_exame', 1)
            ->with('disciplina')
            ->get();

        // Buscar alunos da classe
        $alunos = Matricula::where('classe_id', $classe->id)
            ->where('ano_academico_id', $anoAcademico->id)
            ->where('escola_id',Auth::user()->escola_id)
            ->where('pauta',1)
            ->with('aluno.pessoa')
            ->get()
            ->unique('aluno_id');

        // Buscar notas de exame
        $pautasExame = PautaExame::where('classe_id', $classe->id)
            ->where('ano_academico_id', $anoAcademico->id)
            ->where('escola_id',Auth::user()->escola_id)
            ->get();

        return view('secretaria.pauta.exame.pauta', compact(
            'classe',
            'disciplinasComExame',
            'alunos',
            'pautasExame',
            'anoAcademico'
        ));

    }

    //Chamar a primeira vez
    public function pautaPDFExame($classe_id,$ano)
    {

        $classe = Classe::findOrFail($classe_id);
        $anoAcademico = AnoAcademico::where('ano', $ano)->first();
        //$anoAcademico = AnoAcademico::where('ano', 2025)->first();
        $escola = Escola::where('id', Auth::user()->escola_id)->first();

        // Buscar disciplinas com exame
        $disciplinasComExame = DisciplinaCurriculo::where('classe_id', $classe->id)
            ->where('tem_exame', 1)
            ->with('disciplina')
            ->get();

        // Buscar alunos da classe
        $alunos = Matricula::where('classe_id', $classe->id)
            ->where('ano_academico_id', $anoAcademico->id)
            ->where('pauta',1)
            ->with('aluno.pessoa')
            ->get()
            ->unique('aluno_id');

        // Buscar notas de exame
        $pautasExame = PautaExame::where('classe_id', $classe->id)
            ->where('ano_academico_id', $anoAcademico->id)
            ->get();

        $pdf = \PDF::loadView('secretaria.pauta.exame.pautaPDF', [
            'escola' => $escola,
            'classe' => $classe,
            'disciplinasComExame' => $disciplinasComExame,
            'alunos' => $alunos,
            'pautasExame' => $pautasExame,
            'anoAcademico' => $anoAcademico,
        ])->setPaper('a3', 'landscape'); // Define o papel como A4 e a orientação como paisagem (landscape)

        $fileName = 'Pauta de Exame da -'.$classe->nome;

        return $pdf->stream($fileName.'.pdf');

    }

    //Calcula a media anual de disciplina
    private function calcularNCD($pauta)
    {
        $mediasTrimestres = [];

        // Calcular média de cada trimestre
        foreach (range(1, 3) as $trimestre) {

            $testes = [
                $pauta->{'teste1Tr'.$trimestre},
                $pauta->{'teste2Tr'.$trimestre},
                $pauta->{'teste3Tr'.$trimestre},
                $pauta->{'teste4Tr'.$trimestre},
                $pauta->{'mmTr'.$trimestre},
            ];

            // Retirar notas nulas ou zero
            $testesValidos = array_filter($testes, fn($t) => $t > 0);

            $mediaTestes = count($testesValidos) > 0
                ? array_sum($testesValidos) / count($testesValidos)
                : 0;

            // Nota do trimestre
            $notaFinalTrimestre = (2 * $mediaTestes + $pauta->{'apTr'.$trimestre}) / 3;

            $mediasTrimestres[] = $notaFinalTrimestre;
        }

        // Média anual (NCD)
        if (count($mediasTrimestres) === 0) {
            return 0;
        }

        return round(array_sum($mediasTrimestres) / 3, 0);
    }

    public function salvarNotasExame(Request $request)
    {


        foreach ($request->exame as $pautaExameId => $dados) {

            $p = PautaExame::find($pautaExameId);

            $ncd = floatval($dados['ncd'] ?? 0);
            $primeira = floatval($dados['primeira'] ?? 0);
            $segunda = floatval($dados['segunda'] ?? 0);

            // escolher nota do exame
            $notaExame = 0;
            $nf = 0;

            if($segunda != 0){
                $notaExame = $segunda;
            }else{
                $notaExame = $primeira;
            }

            if($primeira!=0 || $segunda!=0){
                // calcular nf
                //$nf = round((2 * $ncd + $notaExame) / 3, 0);
                $nf = round($notaExame, 0);
            }

            

            $p->update([
                'proveniencia'      => $dados['proveniencia'] ?? null,
                'ncd'               => $ncd,
                'primeira_chamada'  => $primeira,
                'numero'            => $dados['numero'] ?? 1,
                'segunda_chamada'   => $segunda,
                'nf'                => $nf, 
                'voto'              => $dados['voto'] ?? null,
            ]);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Notas atualizadas com sucesso.'
        ]);
    }



    public function deckPDF($turma, $disciplina)
    {
        $classeId = $turma;

        $minhaClasse = Classe::where('id', $classeId)->first();
        $anoAtivo = AnoAcademico::where('estado', 1)->first();
        //$anoAtivo = AnoAcademico::where('ano', 2025)->first();

        $disciplinaPauta = Disciplina::where('id', $disciplina)->first();
        $escola = Escola::where('id', Auth::user()->escola_id)->first();
        // $turmaProfessor = ProfessorTurma::where('turma_id', $turma)->first();
        $turmaProfessorDisciplinas = ProfessorTurmaDisciplina::where('classe_id', $classeId)->get();
        $pautas = Pauta::where('classe_id', $classeId)
                        ->where('disciplina_id', $disciplina)
                        ->where('escola_id', Auth::user()->escola_id)
                        ->where('ano_academico_id', $anoAtivo->id)
                        ->where('pauta',1)
                        ->where('pauta',1)
                        ->orderby('numero', 'asc')
                        ->get();

        $pdf = \PDF::loadView('turma.professor.notas.pdf.deck', [
            'escola' => $escola,
            'disciplina' => $disciplinaPauta,
            // 'turmaProfessor' => $turmaProfessor,
            'minhaClasse' => $minhaClasse,
            'turmaProfessorDisciplinas' => $turmaProfessorDisciplinas,
            'pautas' => $pautas,
            'anoAtivo' => $anoAtivo,
        ])->setPaper('a4', 'landscape'); // Define o papel como A4 e a orientação como paisagem (landscape)

        $fileName = 'Deck-'.$minhaClasse->nome;

        return $pdf->stream($fileName.'.pdf');
    }

    public function pautaTurmaPDF($classe_ID,$ano)
    {
        // Obter a turma específica
        // $turma = Turma::findOrFail($turmaId);

        // $classeId = $classe_ID; // $turma->classe_id;

        // Obter a classe associada à turma
        $classeId = $classe_ID;

        $minhaClasse = Classe::where('id', $classe_ID)->first();

        $anoAtivo = AnoAcademico::where('ano', $ano)->first();
        //$anoAtivo = AnoAcademico::where('ano', 2025)->first();

        // Obter o currículo da classe (disciplinas associadas)
        $disciplinas = DisciplinaCurriculo::where('classe_id', $classeId)
                    ->with('disciplina')  // Relacionamento com a tabela `disciplina`
                    ->orderBy('pauta_ordem', 'asc')
                    ->get()
                    ->map(function ($curriculo) {
                        return [
                            'id' => $curriculo->disciplina->id,
                            'nome' => $curriculo->disciplina->nome,
                            'ordem' => $curriculo->pauta_ordem,
                        ];
                    });

        // Obter os alunos da turma
        $matriculaAlunos = Matricula::where('classe_id', $classeId)->where('ano_academico_id', $anoAtivo->id) ->where('pauta',1)->where('escola_id', Auth::user()->escola_id)->get();

        // Dados para a tabela final
        $dadosTabela = [];

        foreach ($matriculaAlunos as $matriculaAluno) {
            $alunoId = $matriculaAluno->aluno->id;

            // Busca o número da pauta (qualquer disciplina serve, pois o número é por aluno)
            $pautaNumero = Pauta::where('aluno_id', $alunoId)
                ->where('escola_id', Auth::user()->escola_id)
                ->value('numero'); // <-- só pega o campo 'numero'

            // Inicializa o aluno na tabela
            $dadosTabela[$alunoId] = [
                'codigo' => $matriculaAluno->aluno->codigo,
                'nome' => $matriculaAluno->aluno->pessoa->nome,
                'genero' => $matriculaAluno->aluno->pessoa->genero, // Exemplo: H ou M
                'numero' => $pautaNumero ?? null, // ✅ agora vem da pauta diretamente
                'disciplinas' => [],
                'mediaGeral' => 0,
                'resultadoFinal' => '',
                'cop1' => null,
                'cop2' => null,
                'cop3' => null,
                'sin1' => null,
                'sin2' => null,
                'sin3' => null,
            ];

            foreach ($disciplinas as $disciplina) {
                        $disciplinaId = $disciplina['id'];
                        $disciplinaNome = $disciplina['nome'];

                // Obter as notas da pauta para o aluno e a disciplina
                $pauta = Pauta::where('aluno_id', $alunoId)
                    ->where('disciplina_id', $disciplinaId)
                    ->where('ano_academico_id', $anoAtivo->id)
                    ->where('escola_id', Auth::user()->escola_id)
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
                            $pauta->{'teste1Tr'.$trimestre},
                            $pauta->{'teste2Tr'.$trimestre},
                            $pauta->{'teste3Tr'.$trimestre},
                            $pauta->{'teste4Tr'.$trimestre},
                            $pauta->{'mmTr'.$trimestre},
                        ];

                        $testesValidos = array_filter($testes, fn ($t) => $t > 0);
                        $mediaTestes = count($testesValidos) > 0
                            ? array_sum($testesValidos) / count($testesValidos)
                            : 0;

                        $notaFinalTrimestre = (2 * $mediaTestes + $pauta->{'apTr'.$trimestre}) / 3;

                        $dadosTabela[$alunoId]['disciplinas'][$disciplinaNome]['mediaTr'.$trimestre] = round($notaFinalTrimestre, 0);
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

                    // ✅ Adiciona comportamento e sinalética (apenas 1 vez por aluno)
                    $dadosTabela[$alunoId]['cop1'] = $pauta->cop1 ?? $dadosTabela[$alunoId]['cop1'];
                    $dadosTabela[$alunoId]['cop2'] = $pauta->cop2 ?? $dadosTabela[$alunoId]['cop2'];
                    $dadosTabela[$alunoId]['cop3'] = $pauta->cop3 ?? $dadosTabela[$alunoId]['cop3'];
                    $dadosTabela[$alunoId]['sin1'] = $pauta->sin1 ?? $dadosTabela[$alunoId]['sin1'];
                    $dadosTabela[$alunoId]['sin2'] = $pauta->sin2 ?? $dadosTabela[$alunoId]['sin2'];
                    $dadosTabela[$alunoId]['sin3'] = $pauta->sin3 ?? $dadosTabela[$alunoId]['sin3'];
                }

                // Atualiza a média geral do aluno
                $mediasAnuais = array_map(fn ($d) => $d['mediaAnual'], $dadosTabela[$alunoId]['disciplinas']);

                $totalDisciplinas = count($mediasAnuais);

                // ⚠️ Verifica se a classe é a 7ª
                if ($minhaClasse->numero == 7) {
                    $divisor = max($totalDisciplinas - 3, 1); // impede divisão por zero
                } else {
                    $divisor = $totalDisciplinas;
                }

                $dadosTabela[$alunoId]['mediaGeral'] = $divisor > 0
                    ? round(array_sum($mediasAnuais) / $divisor, 0)
                    : 0;

                if($minhaClasse->tem_exame==0){
                    // Resultado final
                    $dadosTabela[$alunoId]['resultadoFinal'] = $dadosTabela[$alunoId]['mediaGeral'] >= 10 ? 'Progride' : 'Não Progride';
                }else{
                    // Resultado final
                    $dadosTabela[$alunoId]['resultadoFinal'] = $dadosTabela[$alunoId]['mediaGeral'] >= 10 ? 'Admitido' : 'Não Admitido';
                }    

            }
        }

        $escola = Escola::where('id', Auth::user()->escola_id)->first();


        // ✅ Ordenar os alunos por numero (ordem numerica crescente)
        uasort($dadosTabela, function ($a, $b) {
            return ($a['numero'] ?? 9999) <=> ($b['numero'] ?? 9999);
        });

        $pdf = \PDF::loadView('turma.professor.notas.pdf.pauta', [
            'dadosTabela' => $dadosTabela,
            'minhaClasse' => $minhaClasse,
            'disciplinas' => $disciplinas,
            'anoAcademico' => $anoAtivo,
            'escola' => $escola,
        ])->setPaper('a3', 'landscape'); // Define o papel como A4 e a orientação como paisagem (landscape)

        $fileName = 'Pauta da - '.$minhaClasse->nome;

        return $pdf->stream($fileName.'.pdf');
    }

    public function InvalidarPauta($classeId,$ano){


        try {

            DB::beginTransaction();

            $classe = Classe::findOrFail($classeId);
            $anoAcademico = AnoAcademico::where('ano', $ano)->first();


            /* =====================================================
                MARCAR TODAS AS TURMAS COMO FINALIZADAS
            =====================================================*/
            DB::table('professor_turma')
                ->join('turmas', 'professor_turma.turma_id', '=', 'turmas.id')
                ->where('turmas.classe_id', $classeId)
                ->where('turmas.escola_id', Auth::user()->escola_id)
                ->where('professor_turma.ano_academico_id', $anoAcademico->id)
                ->update([
                    'pauta_finalizada' => 0
            ]);

             DB::commit();

            return response()->json([
                'status' => 'success',
                'title' => 'Sucesso',
                'message' => 'Pauta validada com sucesso.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
           
            return response()->json([
                'status' => 'error',
                'title' => 'Erro',
                'message' => 'Falha ao validar pauta: ' . $e->getMessage()
            ], 500);
        }

    }

    public function validarPauta($classeId,$ano){


        try {

            DB::beginTransaction();

            $classe = Classe::findOrFail($classeId);
            $anoAcademico = AnoAcademico::where('ano', $ano)->first();


            /* =====================================================
                MARCAR TODAS AS TURMAS COMO FINALIZADAS
            =====================================================*/
            DB::table('professor_turma')
                ->join('turmas', 'professor_turma.turma_id', '=', 'turmas.id')
                ->where('turmas.classe_id', $classeId)
                ->where('turmas.escola_id', Auth::user()->escola_id)
                ->where('professor_turma.ano_academico_id', $anoAcademico->id)
                ->update([
                    'pauta_finalizada' => 1
            ]);

             DB::commit();

            return response()->json([
                'status' => 'success',
                'title' => 'Sucesso',
                'message' => 'Pauta validada com sucesso.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
           
            return response()->json([
                'status' => 'error',
                'title' => 'Erro',
                'message' => 'Falha ao validar pauta: ' . $e->getMessage()
            ], 500);
        }

    }


    /* =====================================================
        BOTÃO → VALIDAÇÃO FINAL DA PAUTA
    ======================================================*/
    public function validarPauta1($classeId,$ano)
    {
        try {

            DB::beginTransaction();

            $classe = Classe::findOrFail($classeId);
            $anoAcademico = AnoAcademico::where('ano', $ano)->first();

            if (!$anoAcademico) {
                
                return response()->json([
                    'status' => 'error',
                    'title' => 'Erro',
                    'message' => 'Ano académico não encontrado.'
                ]);
            }

            // 🔴 Se a classe tem exame → validar exames primeiro
            if ($classe->tem_exame == 1) {

                $disciplinasComExame = DisciplinaCurriculo::where('classe_id', $classeId)
                    ->where('tem_exame', 1)
                    ->pluck('disciplina_id');
                
                $totalNotas = 0;

                // Existe disciplina com exame, mas exames não lançados?
                foreach ($disciplinasComExame as $disciplinaId) {
                    $faltamExames = PautaExame::where('classe_id', $classeId)
                        ->where('disciplina_id', $disciplinaId)
                        ->where('ano_academico_id', $anoAcademico->id)
                        ->sum('primeira_chamada', 0);

                    $totalNotas = $totalNotas + $faltamExames;
                    
                }

                if($totalNotas==0){

                    return response()->json([
                        'status' => 'warning',
                        'title' => 'Atenção',
                        'message' => 'Existem disciplinas com exame sem notas lançadas.'
                    ]);
                }

            }

            // ✅ Validação final da pauta
            //$this->pautaFinalManual($classeId, $anoAcademico);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'title' => 'Sucesso',
                'message' => 'Pauta validada com sucesso.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
           
            return response()->json([
                'status' => 'error',
                'title' => 'Erro',
                'message' => 'Falha ao validar pauta: ' . $e->getMessage()
            ], 500);
        }
    }


    /* =====================================================
        PROCESSO FINAL DA PAUTA (COM / SEM EXAME)
    ======================================================*/
  private function pautaFinalManual($classeId, $anoAcademico)
    {
        // 🔹 Disciplinas do currículo
        $disciplinas = DisciplinaCurriculo::where('classe_id', $classeId)->get();

        // 🔹 Verifica se EXISTE alguma disciplina com exame
        $disciplinasComExame = $disciplinas->where('tem_exame', 1)->pluck('disciplina_id');

        $classeTemExame = $disciplinasComExame->count() > 0;

        // 🔹 Alunos matriculados
        $matriculas = Matricula::where('classe_id', $classeId)
            ->where('ano_academico_id', $anoAcademico->id)
            ->where('desistiu', 0)
            ->where('escola_id', Auth::user()->escola_id)
            ->get();

        foreach ($matriculas as $matricula) {

            $alunoId = $matricula->aluno_id;

            $somaNotas = 0;
            $totalDisciplinas = 0;

            /* =====================================================
                CASO 1 — CLASSE COM EXAME
            =====================================================*/
            if ($classeTemExame) {

                // 👉 Usa APENAS pauta_exame.nf
                $nfs = PautaExame::where('aluno_id', $alunoId)
                    ->where('ano_academico_id', $anoAcademico->id)
                    ->where('escola_id', Auth::user()->escola_id)
                    ->whereIn('disciplina_id', $disciplinasComExame)
                    ->pluck('nf')
                    ->toArray();

                $totalDisciplinas = count($disciplinasComExame);

                // Segurança
                if ($totalDisciplinas > 0 && count($nfs) > 0) {
                    $mediaFinal = round(array_sum($nfs) / $totalDisciplinas, 0);
                } else {
                    $mediaFinal = 0;
                }

            /* =====================================================
                CASO 2 — CLASSE SEM EXAME
            =====================================================*/
            } else {

                foreach ($disciplinas as $dc) {

                    $disciplinaId = $dc->disciplina_id;
                    $notaFinal = 0;

                    $pauta = Pauta::where('aluno_id', $alunoId)
                        ->where('disciplina_id', $disciplinaId)
                        ->where('ano_academico_id', $anoAcademico->id)
                        ->where('escola_id', Auth::user()->escola_id)
                        ->first();

                    if ($pauta) {
                        // 👉 MESMA lógica usada na pauta de frequência
                        $notaFinal = $this->calcularMediaAnualDisciplina($pauta);
                    }

                    $somaNotas += $notaFinal;
                    $totalDisciplinas++;
                }

                $mediaFinal = $totalDisciplinas > 0
                    ? round($somaNotas / $totalDisciplinas, 0)
                    : 0;
            }

            /* =====================================================
                RESULTADO FINAL (ÚNICO CRITÉRIO)
            =====================================================*/
            $resultado = $mediaFinal >= 9.5 ? 'Aprovado' : 'Reprovado';

            $novaClasse = $resultado === 'Aprovado'
                ? $matricula->classe_id + 1
                : $matricula->classe_id;

            // 🔹 Guardar pauta final
            PautaFinal::updateOrCreate(
                [
                    'aluno_id' => $alunoId,
                    'ano_academico_id' => $anoAcademico->id
                ],
                [
                    'classe_actual_id' => $matricula->classe_id,
                    'classe_nova_id' => $novaClasse,
                    'nota_final' => $mediaFinal,
                    'resulta' => $resultado
                ]
            );

            //Actualizar Aluno
            $alunoClasse = Aluno::where('id',$alunoId)->first();
            $alunoClasse->classe_id = $novaClasse;
            $alunoClasse->save();

            // 🔹 Atualizar renovação
            $this->actualizarRenovacaoMatricula(
                $alunoId,
                $anoAcademico->id + 1,
                $novaClasse,
                $matricula->plano,
                $matricula->periodo->tipo
            );
        }

        /* =====================================================
            MARCAR TODAS AS TURMAS COMO FINALIZADAS
        =====================================================*/
        DB::table('professor_turma')
            ->join('turmas', 'professor_turma.turma_id', '=', 'turmas.id')
            ->where('turmas.classe_id', $classeId)
            ->where('turmas.escola_id', Auth::user()->escola_id)
            ->where('professor_turma.ano_academico_id', $anoAcademico->id)
            ->update([
                'pauta_finalizada' => 1
            ]);
    }

    private function calcularMediaAnualDisciplina($pauta)
    {
        $medias = [];

        foreach (range(1, 3) as $trimestre) {

            $testes = [
                $pauta->{'teste1Tr'.$trimestre},
                $pauta->{'teste2Tr'.$trimestre},
                $pauta->{'teste3Tr'.$trimestre},
                $pauta->{'teste4Tr'.$trimestre},
                $pauta->{'mmTr'.$trimestre},
            ];

            $testesValidos = array_filter($testes, fn ($t) => $t > 0);

            $mediaTestes = count($testesValidos) > 0
                ? array_sum($testesValidos) / count($testesValidos)
                : 0;

            $notaFinalTrimestre = (2 * $mediaTestes + $pauta->{'apTr'.$trimestre}) / 3;

            $medias[] = round($notaFinalTrimestre, 2);
        }

        return round(array_sum($medias) / 3, 0);
    }


    public static function actualizarRenovacaoMatricula($alunoId,$anoAcademico,$novaClasse,$planoAtivo,$periodo)
    {
        $matricula = Matricula::where('aluno_id', $alunoId)->where('ano_academico_id',$anoAcademico)->first();

        $classe = Classe::where('id', $novaClasse)->first();
        // Pagar na taxa selecionado pelo utser
        $taxa = null;

        if($classe->id==9){
            $taxa = Taxa::where('ciclo_id', 2)->first();
        }else{
            $taxa = Taxa::where('ciclo_id', $classe->ciclo_id)->first();  
        }

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

            self::gerarMensalidades($alunoId, $anoAcademico, $valorMensalidadeCurricular, $valorMensalidadeCoCurricular);

        }

        
    }


    /* Esse metodo gera a mensalidade anual de cada estudante
     Onde recebe o id de estudante, o Ano Academico, e a TaxaID
     Para preencher mensalidades que devem ser pagos
    */
    public static function gerarMensalidades($alunoID, $anoAcademicoID, $valor, $valorCoCurricular)
    {


        $months = Meses::all();
        $anoAcademicoAtivo = AnoAcademico::where('id', $anoAcademicoID)->first();

        $current_year = $anoAcademicoAtivo->ano;
        $current_month = Carbon::now()->format('m');
        $year_last_two_digits = substr($current_year, -2);

        $valorTotal = $valor + $valorCoCurricular;

        DB::beginTransaction();

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

                $verificaMensalidade = Mensalidade::where('aluno_id',$alunoID)->where('ano_academico_id',$anoAcademicoID)->where('mes_id',$month->id)->first();

                if($verificaMensalidade==null){

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

                    DB::commit();

                }else{

                    DB::rollBack();
                }


            }
        }
    }

}
