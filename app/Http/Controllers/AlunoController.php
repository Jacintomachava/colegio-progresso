<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\AlunoRelacaoPessoa;
use App\Models\AnoAcademico;
use App\Models\Classe;
use App\Models\CoCurricular;
use App\Models\ContactoUrgencia;
use App\Models\Encarregado;
use App\Models\Escola;
use App\Models\Irmao;
use App\Models\Matricula;
use App\Models\Medico;
use App\Models\Mensalidade;
use App\Models\Mensagem;
use App\Models\Pauta;
use App\Models\Turma;
use App\Models\Pessoa;
use App\Models\PlanoEducativoIndividualizado;
use App\Models\Provincia;
use App\Models\RecolherCrianca;
use App\Models\Relacao;
use App\Models\Saude;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class AlunoController extends Controller
{
    public function index()
    {
        $alunos = Aluno::with('encarregado')->where('escola_id', Auth::user()->escola_id)->orderBy('id', 'desc')->get();

        // Retorna a view com os dados carregados
        return view('aluno.index', [
            'alunos' => $alunos,
        ]);
    }

    public function indexPautas()
    {
        // Buscar o ano académico activo
        $anoAcademico = AnoAcademico::where('estado', true)->first();

        if (!$anoAcademico) {
            return redirect()->back()->with(
                'error',
                'Não existe um ano lectivo activo.'
            );
        }

        // Buscar apenas alunos matriculados no ano académico activo
        $alunos = Aluno::with([
                'encarregado',

                // Carregar como relação singular somente a matrícula
                // correspondente ao ano académico activo
                'matriculaAtiva1' => function ($query) use ($anoAcademico) {
                    $query->where('ano_academico_id', $anoAcademico->id);
                }
            ])
            ->where('escola_id', Auth::user()->escola_id)

            // Garantir que o aluno realmente possui matrícula
            // no ano académico activo
            ->whereHas('matriculas', function ($query) use ($anoAcademico) {
                $query->where('ano_academico_id', $anoAcademico->id);
            })

            ->orderBy('id', 'desc')
            ->get();

        return view('aluno.indexPautas', [
            'alunos' => $alunos,
            'anoAcademico' => $anoAcademico,
        ]);
    }

    public function cartao($codigo)
    {
        $aluno = Aluno::where('codigo', $codigo)->first();
        $escola = Escola::where('id', $aluno->escola_id)->first();

        $pdf = \PDF::loadView('aluno.cartao', [
            'aluno' => $aluno,
            'escola' => $escola,
        ])->setPaper([0, 0, 210, 300], 'landscape'); // Define o papel como A4 e a orientação como paisagem (landscape)

        $fileName = 'Cartao -'.$codigo;

        return $pdf->stream($fileName.'.pdf');
    }

    public function mudarTelefoneEncarregado(Request $request)
    {
        try {

             DB::beginTransaction();

             $aluno = Aluno::where('codigo', $request->input('codigo'))->first();
             $pessoasContactar = AlunoRelacaoPessoa::where('aluno_id', $aluno->id)->where('relacao_id',1)->first();

             $pessoa = Pessoa::where('id', $pessoasContactar->pessoa_id)->first();
             $pessoa->nome = $request->input('nome');
             $pessoa->telefone = $request->input('telefone');

             if ($pessoa->save()) {

                DB::commit();
                return response()->json(['status' => 1, 'message' => 'Contacto Encarregado Alterado com sucesso!']);
             } 


        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function mudarTelefone($codigo)
    {
        $aluno = Aluno::where('codigo', $codigo)->first();
        $pessoasContactar = AlunoRelacaoPessoa::where('aluno_id', $aluno->id)->where('relacao_id',1)->first();

        // Retorna a view com os dados carregados
        return view('aluno.editarTelefone', [
            'aluno' => $aluno,
            'pessoasContactar' => $pessoasContactar,
        ]);


    }

    public function show($codigo)
    {
        $anoAtual = Carbon::now()->year;

        $aluno = Aluno::where('codigo', $codigo)->first();
        $matriculas = Matricula::where('aluno_id', $aluno->id)->get();
        $irmaos = Irmao::where('aluno_id', $aluno->id)->get();
        $saude = Saude::where('aluno_id', $aluno->id)->first();
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $pessoasContactar = AlunoRelacaoPessoa::where('aluno_id', $aluno->id)->get();
        $coCurriculares = CoCurricular::where('aluno_id', $aluno->id)->get();
        $plano = PlanoEducativoIndividualizado::where('aluno_id', $aluno->id)->first();
        $classes = Classe::all();

        //Matricula Actual
        $matriculaActual = Matricula::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAcademicoAtivo->id)->first();

        //Escolas
        $escolas = Escola::where('id','<>',Auth::user()->escola_id)->get();
        $turmas = Turma::where('classe_id',$matriculaActual->classe_id)->where('id','<>',Auth::user()->escola_id)->where('ano_academico_id', $anoAcademicoAtivo->id)->get();

        // Nao estao a ser usado
        $medico = Medico::where('aluno_id', $aluno->id)->first();
        $encarregados = Encarregado::where('aluno_id', $aluno->id)->get();
        $contactosUrgencias = ContactoUrgencia::where('aluno_id', $aluno->id)->get();
        $recolherCriancas = RecolherCrianca::where('aluno_id', $aluno->id)->get();

        $telefoneEncarregado = AlunoRelacaoPessoa::where('aluno_id', $aluno->id)
            ->where('relacao_id', 1)
            ->with('pessoa') // já carrega a relação
            ->first();

        $telefone = substr($telefoneEncarregado->pessoa->telefone, -9);
        //Mensagem Enviado ao Encarregado desse aluno
        $mensagens = Mensagem::where('telefone',$telefone)->whereYear('created_at', $anoAtual)->orderBy('created_at', 'desc')->get();

        $mensalidades = Mensalidade::where('aluno_id', $aluno->id)->where('pagou',0)->get();
        $totalMensalidadeActual = Mensalidade::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAcademicoAtivo->id)->get();

        // Retorna a view com os dados carregados
        return view('aluno.show', [
            'aluno' => $aluno,
            'matriculas' => $matriculas,
            'medico' => $medico,
            'encarregados' => $encarregados,
            'irmaos' => $irmaos,
            'contactosUrgencias' => $contactosUrgencias,
            'recolherCriancas' => $recolherCriancas,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'saude' => $saude,
            'pessoasContactar' => $pessoasContactar,
            'coCurriculares' => $coCurriculares,
            'plano' => $plano,
            'mensagens' => $mensagens,
            'mensalidades' => $mensalidades,
            'matriculaActual' => $matriculaActual,
            'totalMensalidadeActual' => $totalMensalidadeActual,
            'classes' => $classes,
            'escolas' => $escolas,
            'turmas' => $turmas,
        ]);
    }

    public function mudarFoto(Request $request)
    {
        $request->validate([
            'aluno_id' => ['required', 'string'],
            'foto' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $aluno = Aluno::where(
            'codigo',
            $request->input('aluno_id')
        )->firstOrFail();

        // Exige uma Policy configurada para este recurso.
        $this->authorize('update', $aluno);

        $caminhoAntigo = $aluno->avatar;

        $caminhoNovo = $request
            ->file('foto')
            ->store('avatares/alunos', 'local');

        try {
            $aluno->avatar = $caminhoNovo;
            $aluno->save();
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($caminhoNovo);
            throw $e;
        }

        // Apaga apenas fotografias antigas do disco privado.
        if (
            $caminhoAntigo &&
            str_starts_with($caminhoAntigo, 'avatares/alunos/')
        ) {
            Storage::disk('local')->delete($caminhoAntigo);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fotografia actualizada com sucesso.'
        ]);
    }

    public function alunoPautaPDF($codigo)
    {
        $aluno = Aluno::where('codigo', $codigo)->first();
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();

        $matricula = Matricula::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAcademicoAtivo->id)->first();
        $pautas = Pauta::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAcademicoAtivo->id)->get();
        $anoAtivo = AnoAcademico::where('id', $anoAcademicoAtivo->id)->first();
        $anosAcademicos = AnoAcademico::all(); // Buscar anos acadêmicos
        $mensalidades = Mensalidade::where('aluno_id', $aluno->id)->where('pagou', 0)->where('multa', '>', 0)->get();
        $escola = Escola::where('id', $aluno->escola_id)->first();

        $pdf = \PDF::loadView('secretaria.pauta.aluno.pdf', [
            'pautas' => $pautas,
            'aluno' => $aluno,
            'matricula' => $matricula,
            'anosAcademicos' => $anosAcademicos,
            'anoAtivo' => $anoAtivo,
            'mensalidades' => $mensalidades,
            'escola' => $escola,
        ])->setPaper('a4', 'landscape'); // Define o papel como A4 e a orientação como paisagem (landscape)

        $fikeName = 'Pauta-'.$codigo;

        return $pdf->stream($fikeName.'.pdf');
    }

    public function edit($codigo)
    {
        $aluno = Aluno::where('codigo', $codigo)->first();
        $alunos = Aluno::all();
        $irmaos = Irmao::where('aluno_id', $aluno->id)->get();
        $saude = Saude::where('aluno_id', $aluno->id)->first();
        $provincias = Provincia::all();
        $relacoes = Relacao::where('id', '<>', 5)->get();
        $medico = AlunoRelacaoPessoa::where('relacao_id', 5)->where('aluno_id', $aluno->id)->first();
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $planoEducativoIndividualizada = PlanoEducativoIndividualizado::where('aluno_id', $aluno->id)->first();

        $pessoasRelacionadas = AlunoRelacaoPessoa::with(['pessoa', 'relacao'])->where('aluno_id', $aluno->id)->where('relacao_id', '<>', 5)
                            ->get()
                            ->groupBy('pessoa_id');

        // Para cada pessoa, extraia os IDs das relações
        $pessoasRelacionadas->each(function ($relacoes) {
            $relacoes->relacoesPessoa = $relacoes->pluck('relacao_id')->toArray();
        });

        // Retorna a view com os dados carregados
        return view('aluno.edit', [
            'aluno' => $aluno,
            'alunos' => $alunos,
            'medico' => $medico,
            'irmaos' => $irmaos,
            'provincias' => $provincias,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'saude' => $saude,
            'relacoes' => $relacoes,
            'planoEducativoIndividualizada' => $planoEducativoIndividualizada,
            'pessoasRelacionadas' => $pessoasRelacionadas,
        ]);
    }

    public function mudarClasse(Request $request)
    {

     try {
            DB::beginTransaction();

            $classe = $request->input('classe_id');
            $aluno = Aluno::where('codigo', $request->input('aluno_id'))->first();
            $aluno->classe_id = $classe;
            $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
            $matricula = Matricula::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAcademicoAtivo->id)->first();
            $matricula->classe_id = $classe;
            

            if($matricula->save() && $aluno->save()){

                DB::commit();

                return response()->json(['status' => 1, 'message' => 'Aluno Actualizado com sucesso!']);

            }else{
                DB::rollBack();

                return response()->json(['status' => 0, 'message' => 'Ocorreu um erro']);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }


    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            if ($request->input('nomealuno') == null) {
                return response()->json(['status' => 0, 'message' => 'O nome de Aluno é obrigatorio']);
            }

            if ($request->input('genero') == null) {
                return response()->json(['status' => 0, 'message' => 'O genero é obrigatorio']);
            }

            if ($request->input('data_nascimento') == null) {
                return response()->json(['status' => 0, 'message' => 'A data de nascimento é obrigatorio']);
            }

            if ($request->input('provincia') == null) {
                return response()->json(['status' => 0, 'message' => 'A provincia é obrigatorio']);
            }

            if ($request->input('tipo_documento') == null) {
                return response()->json(['status' => 0, 'message' => 'O tipo de documento é obrigatorio']);
            }

            if ($request->input('numero_documento') == null) {
                return response()->json(['status' => 0, 'message' => 'O número de Documento é obrigatorio']);
            }

            if ($request->input('endereco') == null) {
                return response()->json(['status' => 0, 'message' => 'O endereço é obrigatorio']);
            }

            $current_year = Carbon::now()->year;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);
            $escola = Escola::where('id', Auth::user()->escola_id)->first();
            $medico = $request->input('medico');

            $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();

            // Registo de Aluno
            $pessoaAluno = Pessoa::where('id', $request->input('aluno_pessoa'))->first();
            $pessoaAluno->nome = $request->input('nomealuno');
            $pessoaAluno->data_nascimento = $request->input('data_nascimento');
            // $pessoaAluno->avatar = $request->input('classe');  //Outra forna de enviar Imagem
            $pessoaAluno->genero = $request->input('genero');
            $pessoaAluno->tipo_documento = $request->input('tipo_documento');
            $pessoaAluno->numero_documento = $request->input('numero_documento');
            $pessoaAluno->endereco = $request->input('endereco');
            $pessoaAluno->naturalidade = $request->input('naturalidade');
            $pessoaAluno->pai = $request->input('pai');
            $pessoaAluno->mae = $request->input('mae');

            if ($pessoaAluno->save()) {
                // Registar Encarregado
                $aluno = Aluno::where('id', $request->input('aluno'))->first();
                $aluno->nome_casa = $request->input('nome_casa');
                $aluno->pessoa_id = $request->input('aluno_pessoa');
                $aluno->escola_id = Auth::user()->escola_id;
                $aluno->provincia_id = $request->input('provincia');

                // Registar Aluno Como Utilizador
                $userAluno = User::where('pessoa_id', $request->input('aluno_pessoa'))->first();
                $userAluno->nome = $request->input('nomealuno');

                if ($request->has('imagem')) {
                    $imgBase64 = $request->input('imagem');
                    $imgBase64 = str_replace('data:image/png;base64,', '', $imgBase64);
                    $imgBase64 = str_replace(' ', '+', $imgBase64);
                    $imageData = base64_decode($imgBase64);

                    $file = $aluno->codigo.'.png';
                    $path = public_path('avatar/'.$file);

                    file_put_contents($path, $imageData);

                    $userAluno->avatar = $file;
                    $aluno->avatar = $file;
                }

                if ($aluno->save() && $userAluno->save()) {
                    if ($request->input('plano') == 2) {
                        $planoEducativoIndividualizada = PlanoEducativoIndividualizado::where('aluno_id', $request->input('aluno'))->first();

                        if ($planoEducativoIndividualizada == null) {
                            $planoEducativoIndividualizada = new PlanoEducativoIndividualizado();
                        }

                        $planoEducativoIndividualizada->motivo_plano = $request->input('motivo_plano_individualizado');
                        $planoEducativoIndividualizada->indicacoes_especial = $request->input('indicacoes_especiais');
                        $planoEducativoIndividualizada->d_autismo = $request->input('autismo');
                        $planoEducativoIndividualizada->d_dislexia = $request->input('dislexia');
                        $planoEducativoIndividualizada->d_hiperatividade = $request->input('hiperactividade');
                        $planoEducativoIndividualizada->outras_condicoes = $request->input('outras_condicoes');
                        $planoEducativoIndividualizada->especifica = $request->input('especifica_outras_condicoes');
                        $planoEducativoIndividualizada->comunicacao_verbal = $request->input('comunicacao_verbal');
                        $planoEducativoIndividualizada->jorgao = $request->input('jargao');
                        $planoEducativoIndividualizada->ecolalia = $request->input('ecolalia');
                        $planoEducativoIndividualizada->avaliado_otorrino = $request->input('otorrino');
                        $planoEducativoIndividualizada->notas_especial = $request->input('notas_especiais');
                        $planoEducativoIndividualizada->suporta_barrulho = $request->input('barrulho');
                        $planoEducativoIndividualizada->hipersencibilidade_toque = $request->input('hipersencibilidade');
                        $planoEducativoIndividualizada->estoratipia = $request->input('estereotipia');
                        $planoEducativoIndividualizada->estoratipia_especifique = $request->input('estereotpia_especifique');
                        $planoEducativoIndividualizada->aluno_id = $aluno->id;
                        $planoEducativoIndividualizada->save();
                    }

                    // Registar Pessoa Medico
                    if ($request->input('nomemedico') != null) {
                        if ($request->input('telefonemedico') == null) {
                            return response()->json(['status' => 0, 'message' => 'O telefone do Medico é obrigatorio']);
                        }

                        if ($medico == null) {
                            $pessoaMedico = new Pessoa();
                        } else {
                            $pessoaMedico = Pessoa::where('id', $medico)->first();
                        }
                        $pessoaMedico->nome = $request->input('nomemedico');
                        $pessoaMedico->endereco = $request->input('moradamedico');
                        $pessoaMedico->telefone = $request->input('telefonemedico');
                        $pessoaMedico->email = $request->input('emailmedico');
                        $pessoaMedico->profissao = 'Medico';
                        $pessoaMedico->local_trabalho = $request->input('consultor');

                        if ($pessoaMedico->save()) {
                            if ($medico == null) {
                                AlunoRelacaoPessoa::create([
                                    'pessoa_id' => $pessoaMedico->id,
                                    'relacao_id' => 5,
                                    'aluno_id' => $aluno->id,
                                    'descricao' => $request->input('consultor'),
                                    'grau' => 'Medico',
                                ]);
                            }
                        }
                    }

                    // Processar pessoas relacionadas
                    if ($request->has('pessoas')) {
                        foreach ($request->pessoas as $pessoaData) {
                            // Atualizar ou criar pessoa
                            $pessoa = Pessoa::updateOrCreate(
                                ['id' => $pessoaData['id'] ?? null],
                                [
                                    'nome' => $pessoaData['nome'],
                                    'telefone' => $pessoaData['telefone'],
                                    'email' => $pessoaData['email'] ?? null,
                                ]
                            );

                            // Sincronizar relações
                            if (isset($pessoaData['relacoes'])) {
                                // Primeiro deleta todas as relações existentes para esta pessoa-aluno
                                AlunoRelacaoPessoa::where('aluno_id', $aluno->id)
                                    ->where('pessoa_id', $pessoa->id)
                                    ->delete();

                                // Depois cria apenas as novas relações (evitando duplicatas)
                                $relacoesParaCriar = array_unique($pessoaData['relacoes']);

                                foreach ($relacoesParaCriar as $relacaoId) {
                                    AlunoRelacaoPessoa::create([
                                        'aluno_id' => $aluno->id,
                                        'pessoa_id' => $pessoa->id,
                                        'relacao_id' => $relacaoId,
                                        'grau' => $pessoaData['grau'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }

                    // Saude
                    $saude = Saude::where('aluno_id', $request->input('aluno'))->first();
                    $saude->centro_saude = $request->input('nomecentrosaude');
                    $saude->sofre_alguma_doenca = $request->input('algumadoenca');
                    $saude->qual = $request->input('qual');
                    $saude->aluno_id = $aluno->id;
                    $saude->indicacao_especial = $request->input('indicacaoespecial');
                    $saude->teve_sarampo = $request->input('sarampo');
                    $saude->teve_varicela = $request->input('varicela');
                    $saude->teve_papaira = $request->input('papeira');
                    $saude->teve_outras = $request->input('outrasdoencas');
                    $saude->especifique_outras = $request->input('especificaoutras');
                    $saude->tem_alergia = $request->input('alergia');
                    $saude->especifique_alergia = $request->input('especificaalergia');
                    $saude->tem_restricoes_alimentares = $request->input('restricaoalimentar');
                    $saude->especifique_restricoes_alimentares = $request->input('especifiquerestricaoalimentar');

                    $alunos1 = $request->input('alunos', []);
                    // Apagar Irmao e Pauta para registar Novamente
                    Irmao::where('aluno_id', $request->input('aluno'))->delete();

                    if (!empty($alunos1)) {
                        foreach ($alunos1 as $alunoID) {
                            $irmao = new Irmao();
                            $irmao->aluno_id = $aluno->id;
                            $irmao->aluno_irmao_id = $alunoID;
                            $irmao->save();
                        }
                    }

                    if ($saude->save()) {
                        DB::commit();

                        return response()->json(['status' => 1, 'message' => 'Aluno Actualizado com sucesso!']);
                    } else {
                        return response()->json(['status' => 0, 'message' => 'Ocorreu um erro']);
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function apiBuscarAlunos(Request $request)
    {
        $query = $request->input('q');

        $alunos = Aluno::whereHas('pessoa', function ($q) use ($query) {
            $q->where('nome', 'LIKE', "%$query%");
        })->get();

        return response()->json($alunos->map(function ($aluno) {
            return [
                'id' => $aluno->id,
                'nome' => $aluno->pessoa->nome,
            ];
        }));
    }

    public function home()
    {
        $aluno = Aluno::where('pessoa_id', Auth::user()->pessoa_id)->first();
        $anoAcademicoAtivo = AnoAcademico::where('estado', 1)->first();
        $mensalidades = Mensalidade::where('aluno_id', $aluno->id)->where('pagou', 0)->where('multa', '>', 0)->get();
        $matricula = Matricula::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAcademicoAtivo->id)->first();

        // Retorna a view com os dados carregados
        return view('home.aluno.index', [
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'mensalidades' => $mensalidades,
            'matricula' => $matricula,
        ]);
    }

    public function retirarAluno($alunoID)
    {
        try {
            // Ativar o ano acadêmico selecionado
            $aluno = Aluno::where('id', $alunoID)->first();
            $anoAcademicoAtivo = AnoAcademico::where('estado', 1)->first();
            $matricula = Matricula::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAcademicoAtivo->id)->first();

            $estado = $aluno->desistiu;

            if ($estado == 1) {
                $aluno->desistiu = false;

                // Busca mensalidades vencidas e não pagas
                Mensalidade::where('aluno_id', $aluno->id)
                           ->where('pagou', 0)
                           ->where('ano_academico_id', $anoAcademicoAtivo->id)
                           ->update(['disistiu' => false]);
            }

            if ($estado == 0) {
                $aluno->desistiu = true;

                // Busca mensalidades vencidas e não pagas
                Mensalidade::where('aluno_id', $aluno->id)
                           ->where('pagou', 0)
                           ->where('ano_academico_id', $anoAcademicoAtivo->id)
                           ->update(['disistiu' => true]);
            }

            if ($matricula != null) {
                if ($estado == 1) {
                    $matricula->desistiu = false;
                }

                if ($estado == 0) {
                    $matricula->desistiu = true;
                }

                $matricula->save();
            }

            $aluno->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Aluno Retirado com sucesso!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro ao retirar a Aluno.'.$e->getMessage(),
            ]);
        }
    }

    public function retirarPauta($alunoID)
    {
        try {

            DB::beginTransaction();

            // Buscar aluno
            $aluno = Aluno::findOrFail($alunoID);

            // Buscar ano académico activo
            $anoAcademicoAtivo = AnoAcademico::where('estado', 1)->first();

            if (!$anoAcademicoAtivo) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Não existe um ano académico activo.',
                ]);
            }

            // Buscar matrícula do aluno no ano académico activo
            $matricula = Matricula::where('aluno_id', $aluno->id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->first();

            if (!$matricula) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'O aluno não possui matrícula no ano académico activo.',
                ]);
            }

            /*
            * Contrariar o estado actual:
            *
            * true  -> false
            * false -> true
            */
            $novoEstado = !$matricula->pauta;

            // Actualizar matrícula
            $matricula->pauta = $novoEstado;
            $matricula->save();

            // Actualizar TODOS os registos da pauta deste aluno
            // no ano académico activo
            Pauta::where('aluno_id', $aluno->id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->update([
                    'pauta' => $novoEstado
                ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $novoEstado
                    ? 'Aluno colocado na pauta com sucesso!'
                    : 'Aluno retirado da pauta com sucesso!',
                'pauta' => $novoEstado
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro ao actualizar o aluno. ' . $e->getMessage(),
            ], 500);
        }
    }
}
