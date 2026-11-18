<?php

namespace App\Http\Controllers;

use Karson\MpesaPhpSdk\Mpesa;
use App\Models\Aluno;
use App\Models\AlunoRelacaoPessoa;
use App\Models\AnoAcademico;
use App\Models\Classe;
use App\Models\CoCurricular;
use App\Models\DisciplinaCurriculo;
use App\Models\Disciplina;
use App\Models\Encarregado;
use App\Models\Escola;
use App\Models\Irmao;
use App\Models\Matricula;
use App\Models\Medico;
use App\Models\Mensagem;
use App\Models\CicloAno;
use App\Services\SMSService;
use App\Models\Transferencia;
use App\Models\Mensalidade;
use App\Models\Meses;
use App\Models\MetodoPagamento;
use App\Models\Pagamento;
use App\Models\MensagemSessao;
use App\Models\Pauta;
use App\Models\Periodo;
use App\Models\Pessoa;
use App\Models\PlanoEducativoIndividualizado;
use App\Models\Provincia;
use App\Models\Relacao;
use App\Models\Saude;
use App\Models\Mpesa1;
use App\Models\Taxa;
use App\Models\Turma;
use App\Models\User;
use App\Services\EmailService;
use App\Services\EntidadeReferenciaService;
use App\Services\MultaService;
use App\Services\PautaFinalService;
use App\Services\ReceitaDespesaService;
use App\Models\ProgressoCobranca;
use App\Services\Progresso\FacturaService;
use App\Services\NotificacaoSmsService;
use App\Services\ProgressoFacturaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\ProgressoCodigoService;
use App\Services\ProgressoCobrancasMatriculaService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Barryvdh\DomPDF\Facade\Pdf;

class MatriculaController extends Controller
{
    public function index()
    {
        $cicloActivo = CicloAno::where('tipo','MATRICULA')->first();
        $anoAcademicoAtivo = AnoAcademico::where('id', $cicloActivo->ano_academico_id)->first();
        $currentDate = Carbon::now();

        $alunosMatriculados = null;
        $anoAcademicoProximo = null;
        $anoAcademico = null;
        $turmas = null;
        $renovacao = false;
        $anoAcademicoActual = null;

        if ($anoAcademicoAtivo->data_inicio_renovacao < $currentDate) {
            $anoAcademico = $anoAcademicoAtivo->nome;
            $anoAcademicoActual = $anoAcademicoAtivo->id;
            $turmas = Turma::where('ano_academico_id', $anoAcademicoAtivo->id)->get();
            $alunosMatriculados = Matricula::where('ano_academico_id', $anoAcademicoAtivo->id)->where('desistiu', 0)->where('escola_id', Auth::user()->escola_id)->orderBy('id', 'desc')->get();
        } else {
            $anoAcademicoProximo = AnoAcademico::where('id', $anoAcademicoAtivo->id)->first();
            $anoAcademico = $anoAcademicoProximo->nome;
            $anoAcademicoActual = $anoAcademicoProximo->id;
            $renovacao = true;
            $turmas = Turma::where('ano_academico_id', $anoAcademicoProximo->id)->get();
            $alunosMatriculados = Matricula::where('ano_academico_id', $anoAcademicoProximo->id)->where('desistiu', 0)->where('escola_id', Auth::user()->escola_id)->orderBy('id', 'desc')->get();
        }

        // dd($alunosMatriculados);

        $disciplinas = Disciplina::where('co_curricular', 1)->get();

        // Buscar alunos sem turma
        $alunosSemTurmas = Matricula::with('aluno.pessoa') // Certifique-se de carregar a relação 'pessoa' também
                ->where('turma_id', null)
                ->where('desistiu', 0)
                ->where('escola_id', Auth::user()->escola_id)
                ->get();

        // Buscar Alunos Que nao pagaram Matricula
        $alunosNaoPagouMatriculas = Matricula::with('aluno') // Certifique-se de carregar a relação 'pessoa' também
                ->where('escola_id', Auth::user()->escola_id)
                ->where('desistiu', 0)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->where('pagou', 0)
                ->get();

        // Retornar os alunos no formato necessário para o Tagify
        $alunosFormatados = $alunosSemTurmas->map(function ($matricula) {
            return [
                'value' => $matricula->aluno->pessoa->nome,
                'id' => $matricula->aluno->id,
            ];
        });

        $alunosNaoMatriculados = Aluno::whereNotIn('id', function ($query) use ($anoAcademicoActual) {
            $query->select('aluno_id')
                ->from('matriculas')
                ->where('ano_academico_id', $anoAcademicoActual+1);
        })->where('escola_id', Auth::user()->escola_id)->get();

        // Retorna a view com os dados carregados
        return view('matricula.index', [
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'anoAcademico' => $anoAcademico,
            'anoAcademicoActual' => $anoAcademicoActual,
            'renovacao' => $renovacao,
            'anoAcademicoProximo' => $anoAcademicoProximo,
            'alunosMatriculados' => $alunosMatriculados,
            'alunosNaoMatriculados' => $alunosNaoMatriculados,
            'disciplinas' => $disciplinas,
            'turmas' => $turmas,
            'alunosSemTurmas' => $alunosSemTurmas,
            'alunosNaoPagouMatriculas' => $alunosNaoPagouMatriculas,
        ]);
    }

    public function referenciaSMS($codigo)
    {

        try {

            DB::beginTransaction();

                $aluno = Aluno::where('codigo', $codigo)->first();
                $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
                $telefoneEncarregado = AlunoRelacaoPessoa::where('aluno_id', $aluno->id)
                    ->where('relacao_id', 1)
                    ->with('pessoa') // já carrega a relação
                    ->first();
                $matriculaActual = Matricula::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAcademicoAtivo->id)->first();

                $telefone = substr($telefoneEncarregado->pessoa->telefone, -9);

                $dataFormatada = Carbon::parse('2026-02-13')->format('d-m-Y');
                $valorFormatada = number_format($matriculaActual->valor_total, 2, ',', '.');

                $sms = 'Prezado encarregado do(a) '.$aluno->pessoa->nome.' para pagar a matricula de ano lectivo '.$anoAcademicoAtivo->ano.' usa a ENTIDADE: 90028, REFERENCIA: '.$matriculaActual->referencia.', VALOR: '.$valorFormatada.' MZN. Banco: Millennium BIM. Prazo '.$dataFormatada.' Evita multas.';

                $qtd = SMSService::quantidadeSMS($sms);

                $mensagem = new Mensagem();
                $mensagem->telefone = $telefone;
                $mensagem->descricao = $sms;
                $mensagem->qtd = $qtd;
                $mensagem->canal = 'SMS';
                $mensagem->tipo = 'Pendente';
                $mensagem->custo_real = $qtd *1.35;
                $mensagem->credito = $qtd *1.85;

                if($mensagem->save())
                {
                    // Atualizando a sessão do usuário
                    MensagemSessao::updateOrCreate(
                        ['telefone' => substr($telefone, -9)], // Critério de busca
                        [
                            'ultima_conversa' => $sms,
                            'conversas' => DB::raw('conversas + 1'), // Incrementar conversas
                            'created_at' => now() // Atualizar data de criação quando necessário
                        ]
                    );

                    DB::commit();  

                    return response()->json(['status' => 1, 'message' => 'Pagamento efectuado com sucesso!']);
                }

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function create()
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $periodos = Periodo::all();
        $classes = Classe::all();
        $metodoPagamentos = MetodoPagamento::all();
        $provincias = Provincia::all();
        $turmas = Turma::where('ano_academico_id', $anoAcademicoAtivo->id)->get();
        $taxas = Taxa::all();
        $disciplinas = Disciplina::where('co_curricular', 1)->get();
        $alunos = Aluno::all();
        $relacoes = Relacao::where('id', '<>', 5)->get();
        $pesssoaAlunos = AlunoRelacaoPessoa::all();

        $atividades = DB::table('progresso_atividades')
            ->where('estado', 'ACTIVO')
            ->whereIn('tipo', ['SERVICO', 'AULA_EXTRA'])
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        $valoresClasses = DB::table('progresso_valores_classes')
            ->where('estado', 'ACTIVO')
            ->get();

        $nacionalidades = DB::table('nacionalidades')
            ->where('estado', 1)
            ->orderBy('nome')
            ->get();

        $nacionalidadeMZ = $nacionalidades->firstWhere('codigo_iso', 'MZ');

        // Retorna a view com os dados carregados
        return view('matricula.create', [
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'periodos' => $periodos,
            'classes' => $classes,
            'metodoPagamentos' => $metodoPagamentos,
            'provincias' => $provincias,
            'turmas' => $turmas,
            'taxas' => $taxas,
            'alunos' => $alunos,
            'disciplinas' => $disciplinas,
            'relacoes' => $relacoes,
            'pesssoasExistentes' => $pesssoaAlunos,
            'atividades' => $atividades,
            'valoresClasses' => $valoresClasses,
            'nacionalidades' => $nacionalidades,
            'nacionalidadeMZ' => $nacionalidadeMZ,
        ]);
    }

    public function edit($codigo)
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $periodos = Periodo::all();
        $classes = Classe::all();  //$classes
        $metodoPagamentos = MetodoPagamento::all();
        $provincias = Provincia::all();
        $turmas = Turma::where('ano_academico_id', $anoAcademicoAtivo->id)->get();
        $taxas = Taxa::all();
        $disciplinas = Disciplina::where('co_curricular', 1)->get();
        $alunos = Aluno::all();
        $relacoes = Relacao::where('id', '<>', 5)->get();
        $pesssoaAlunos = AlunoRelacaoPessoa::all();

        // Dados da Matricula
        $matricula = Matricula::with('aluno.pessoa', 'classe', 'turma', 'escola', 'user')->where('ano_academico_id', $anoAcademicoAtivo->id)->where('id', $codigo)->first();
        $alunosRelacaoPessoas = AlunoRelacaoPessoa::where('aluno_id', $matricula->aluno_id)->get();
        $planoEducativoIndividualizada = PlanoEducativoIndividualizado::where('aluno_id', $matricula->aluno_id)->first();
        $saude = Saude::where('aluno_id', $matricula->aluno_id)->first();
        $medico = AlunoRelacaoPessoa::where('aluno_id', $matricula->aluno_id)->where('relacao_id', 5)->first();

        // Irmao e Disciplinas CoCurriculares
        $irmaos = Irmao::where('aluno_id', $matricula->aluno_id)->get();
        $coCurriculares = CoCurricular::where('aluno_id', $matricula->aluno_id)->get();

        // Retorna a view com os dados carregados
        return view('matricula.edit', [
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'periodos' => $periodos,
            'classes' => $classes,
            'metodoPagamentos' => $metodoPagamentos,
            'provincias' => $provincias,
            'turmas' => $turmas,
            'taxas' => $taxas,
            'alunos' => $alunos,
            'disciplinas' => $disciplinas,
            'relacoes' => $relacoes,
            'pesssoasExistentes' => $pesssoaAlunos,
            'matricula' => $matricula,
            'alunosRelacaoPessoas' => $alunosRelacaoPessoas,
            'planoEducativoIndividualizada' => $planoEducativoIndividualizada,
            'saude' => $saude,
            'medico' => $medico,
            'irmaos' => $irmaos,
            'coCurriculares' => $coCurriculares,
        ]);
    }

    public function pagamentoMatriculaMpesa($codigo)
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        // Buscar Alunos Que nao pagaram Matricula
        $matricula = Matricula::where('codigo', $codigo) // Certifique-se de carregar a relação 'pessoa' também
                ->where('escola_id', Auth::user()->escola_id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                // ->where('pagou',0)
                ->first();

        // Retorna a view com os dados carregados
        return view('matricula.pagamento.createMpesa', [
            'matricula' => $matricula,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
        ]);
    }

    public function pagarMatricula(Request $request)
    {    
        try {
            DB::beginTransaction();

            $current_year = Carbon::now()->year;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);

            $matricula = Matricula::where('id', $request->input('codigo'))->first();

            if ($request->input('tipo_pagamento') != 0) {
                if ($request->input('referencia') == null) {
                    return response()->json(['status' => 0, 'message' => 'O Codigo de Talao é Obrigatorio']);
                }
            }

            if ($request->input('tipo_pagamento') == 1) {
                if ($request->input('desconto') == 0) {
                    return response()->json(['status' => 0, 'message' => 'O desconto é Obrigatorio']);
                }
                if ($request->input('motivo') == null) {
                    return response()->json(['status' => 0, 'message' => 'O Motivo de desconto é Obrigatorio']);
                }
            }

            if ($request->input('tipo_pagamento') == 0) {
                if ($request->input('valor_total') != 0) {
                    return response()->json(['status' => 0, 'message' => 'O Valor a Pagar deve ser igual a Zero(0)!']);
                }
                if ($request->input('motivo') == null) {
                    return response()->json(['status' => 0, 'message' => 'O Motivo da Isenção é Obrigatorio']);
                }
            }

            $pagamento = Pagamento::where('id', $matricula->pagamento_id)->first();
            $pagamento->desconto = $request->input('desconto');
            $pagamento->entidade = Auth::user()->codigo;
            $pagamento->metodo_pagamento_id = 1;
            $pagamento->user_id = Auth::user()->id;
            $pagamento->pagou = true;
            $pagamento->escola_id = Auth::user()->escola_id;
            $pagamento->categoria_id = 1;
            $pagamento->valor = $request->input('valor_total') - $request->input('desconto');
            $pagamento->descricao = $request->input('referencia');
            $pagamento->motivo = $request->input('motivo');
            $pagamento->data_pagamento = $request->input('data_pagamento');
            $pagamento->tipo = $request->input('tipo_pagamento');  // Tipo Pagamento 0 Normal 1 Com Desconto 2 Isencao

            if ($pagamento->save()) {
                $matricula->pagou = true;
                $matricula->valor_total = $request->input('valor_total');
                $matricula->desconto = $request->input('desconto');

                if ($matricula->save()) {
                    DB::commit();

                    return response()->json(['status' => 1, 'message' => 'Pagamento efectuado com sucesso!']);
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

    public function pagarMatriculaMpesa(Request $request)
    {
        try {
            DB::beginTransaction();

            $current_year = Carbon::now()->year;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);

            $matricula = Matricula::where('codigo', $request->input('codigo'))->first();

            $telefone = $request->input('telefone');
            $valor = $request->input('valor_total');
            $referencia = $request->input('referencia');
            $codigo1 = Str::random(8);

            // Expressão regular: começa com 84 ou 85 e tem 9 dígitos no total
            if (!preg_match('/^8[45]\d{7}$/', $telefone)) {

                return response()->json(['status' => 0, 'message' => 'Número inválido. Deve começar com 84 ou 85']);

            }


            $referencia = str_replace('.', '', $matricula->codigo);
           
            $mpesa = new \Karson\MpesaPhpSdk\Mpesa();

            // Credenciais
            $mpesa->setApiKey('eotig3de5fbkdw0w0j68y12uhn77wy6b');
            $mpesa->setPublicKey('MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAmptSWqV7cGUUJJhUBxsMLonux24u+FoTlrb+4Kgc6092JIszmI1QUoMohaDDXSVueXx6IXwYGsjjWY32HGXj1iQhkALXfObJ4DqXn5h6E8y5/xQYNAyd5bpN5Z8r892B6toGzZQVB7qtebH4apDjmvTi5FGZVjVYxalyyQkj4uQbbRQjgCkubSi45Xl4CGtLqZztsKssWz3mcKncgTnq3DHGYYEYiKq0xIj100LGbnvNz20Sgqmw/cH+Bua4GJsWYLEqf/h/yiMgiBbxFxsnwZl0im5vXDlwKPw+QnO2fscDhxZFAwV06bgG0oEoWm9FnjMsfvwm0rUNYFlZ+TOtCEhmhtFp+Tsx9jPCuOd5h2emGdSKD8A6jtwhNa7oQ8RtLEEqwAn44orENa1ibOkxMiiiFpmmJkwgZPOG/zMCjXIrrhDWTDUOZaPx/lEQoInJoE2i43VN/HTGCCw8dKQAwg0jsEXau5ixD0GUothqvuX3B9taoeoFAIvUPEq35YulprMM7ThdKodSHvhnwKG82dCsodRwY428kg2xM/UjiTENog4B6zzZfPhMxFlOSFX4MnrqkAS+8Jamhy1GgoHkEMrsT5+/ofjCx0HjKbT5NuA2V/lmzgJLl3jIERadLzuTYnKGWxVJcGLkWXlEPYLbiaKzbJb2sYxt+Kt5OxQqC1MCAwEAAQ==');
            $mpesa->setServiceProviderCode('171717');
            $mpesa->setEnv('test'); // 'live' production environment

            $invoice_id = str_replace('-', '', $referencia); // Eg: Invoice number
            $phone_number = '258' . preg_replace('/^(\+?258)?/', '', $telefone); // Prefixed with country code (258)
            $amount = $valor - $request->input('desconto'); // Payment amount
            $reference_id = $invoice_id.''.$codigo1; // Should be unique for each transaction

            $result = $mpesa->c2b($invoice_id, $phone_number, $amount, $reference_id);


            $transacao = new Mpesa1();
            $transacao->numero_factura = $matricula->codigo;
            $transacao->descricao = $referencia;
            $transacao->telefone = $telefone;
            $transacao->valor = $valor;
            $transacao->referencia = $matricula->referencia;
            $transacao->categoria_id = 1;
            $transacao->response_desc = $result->response->output_ResponseDesc;
            $transacao->response_code = $result->response->output_ResponseCode;
            $transacao->transaction_id = $result->response->output_TransactionID;
            $transacao->conversation_id = $result->response->output_ConversationID;
            $transacao->third_party_reference = $result->response->output_ThirdPartyReference;
            $transacao->save();

            if ($result->response->output_ResponseCode == 'INS-0') {

                    $pagamento = Pagamento::where('id', $matricula->pagamento_id)->first();
                    $pagamento->entidade = Auth::user()->codigo;
                    $pagamento->metodo_pagamento_id = 2;
                    $pagamento->user_id = Auth::user()->id;
                    $pagamento->pagou = true;
                    $pagamento->escola_id = Auth::user()->escola_id;
                    $pagamento->categoria_id = 1;
                    $pagamento->valor = $valor - $request->input('desconto');
                    $pagamento->descricao = $result->response->output_TransactionID;

                    if ($pagamento->save()) {
                        $matricula->pagou = true;
                        $matricula->valor_total = $request->input('valor_total');

                        if ($matricula->save()) {
                            DB::commit();

                            return response()->json(['status' => 1, 'message' => 'Pagamento efectuado com sucesso!']);
                        }
                    }

                } else {

                    if ($result->response->output_ResponseCode == 'INS-1') {
                        return response()->json(['status' => 0, 'message' => 'Erro Interno ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-6') {
                        return response()->json(['status' => 0, 'message' => 'A transacao Falhou ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-5') {
                        return response()->json(['status' => 0, 'message' => 'Transacao Cancelada por Cliente ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-9') {
                        return response()->json(['status' => 0, 'message' => 'Tempo de Espera Esgotado ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-15') {
                        return response()->json(['status' => 0, 'message' => 'Valor Invalido ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-25') {
                        return response()->json(['status' => 0, 'message' => 'Credencial de segurança inválida']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-25') {
                        return response()->json(['status' => 0, 'message' => 'Credencial de segurança inválida']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-2006') {
                        return response()->json(['status' => 0,  'message' => 'Saldo Insuficiente ']);
                    }
                    if ($result->response->output_ResponseCode == 'INS-2001') {
                        return response()->json(['status' => 0,  'message' => 'Senha Errada... ']);
                    }

                    return response()->json(['status' => 0, 'message' => 'Ocorreu um erro: '.$result->response->output_ResponseDesc]);
                }


        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    
    public function store(
        Request $request,
        ProgressoCodigoService $codigoService,
        ProgressoCobrancasMatriculaService $cobrancasService
    ) {

        $request->merge([
            'numero_documento' => filled($request->numero_documento)
                ? trim((string) $request->numero_documento)
                : null,
        ]);

        $dados = $request->validate([
            'nomealuno' => 'required|string|max:255',
            'genero' => 'required|in:Masculino,Femenino',
            'data_nascimento' => 'required|date|before:today',
            'classe' => 'required|integer|exists:classes,id',
            'provincia' => 'required|integer|exists:provincias,id',
            'nacionalidade' => 'nullable|string|max:100',
            'endereco' => 'required|string|max:255',
            'tipo_documento' => 'nullable|string|max:100',
            'numero_documento' => ['nullable','string','max:255',Rule::unique('pessoas', 'numero_documento'),],
            'data_emissao' => 'nullable|date',
            'escola_anterior' => 'nullable|string|max:255',
            'vive_com' => 'nullable|string|max:255',

            'familia' => 'required|array',
            'familia.encarregado.nome' => 'required|string|max:255',
            'familia.encarregado.telefone' => 'required|string|max:30',
            'familia.encarregado.residencia' => 'required|string|max:255',
            'familia.encarregado.parentesco' => 'nullable|string|max:100',
            'familia.*.nome' => 'nullable|string|max:255',
            'familia.*.nacionalidade' => 'nullable|string|max:100',
            'familia.*.formacao' => 'nullable|string|max:255',
            'familia.*.profissao' => 'nullable|string|max:255',
            'familia.*.local_trabalho' => 'nullable|string|max:255',
            'familia.*.telefone' => 'nullable|string|max:30',
            'familia.*.telefone2' => 'nullable|string|max:30',
            'familia.*.email' => 'nullable|email|max:255',
            'familia.*.residencia' => 'nullable|string|max:255',
            'familia.*.parentesco' => 'nullable|string|max:100',

            'saude' => 'nullable|array',
            'saude.condicao' => 'nullable|boolean',
            'saude.condicao_descricao' => 'nullable|string',
            'saude.medicacao' => 'nullable|boolean',
            'saude.medicacao_descricao' => 'nullable|string',
            'saude.alergia' => 'nullable|boolean',
            'saude.alergia_descricao' => 'nullable|string',
            'saude.atestado' => 'nullable|boolean',
            'saude.unidade_preferencial' => 'nullable|string|max:255',
            'emergencia_nome' => 'required|string|max:255',
            'emergencia_telefone' => 'required|string|max:30',

            'autorizados' => 'nullable|array',
            'autorizados.*.nome' => 'nullable|string|max:255',
            'autorizados.*.telefone' => 'nullable|string|max:30',
            'modalidade_pagamento' => 'required|in:MENSAL,TRES_PRESTACOES,ANUAL',
            'bolsa_percentual' => 'nullable|numeric|in:0,50,100',
            'atividades' => 'nullable|array',
            'atividades.*' => 'integer|distinct',
            'foto' => 'nullable|image|max:5120',
            'imagem' => 'nullable|string',
        ], [
            'numero_documento.unique' =>
                'Este número de documento já está associado a outra pessoa.',
            'numero_documento.max' =>
                'O número do documento não pode ultrapassar 255 caracteres.',
        ]);

        $escolaId = (int) Auth::user()->escola_id;
        $ano = 2027; // Substituir pela selecção de ano académico quando estiver implementada.
        $fotoGuardada = null;

        try {
            $resultado = DB::transaction(function () use (
                $request, $dados, $escolaId, $ano, $codigoService,
                &$fotoGuardada, $cobrancasService
            ) {
                $escola = DB::table('escolas')->where('id', $escolaId)->first();
                if (!$escola) {
                    throw ValidationException::withMessages(['escola' => 'Escola não encontrada.']);
                }

                $anoAcademico = DB::table('ano_academico')
                    ->where('ano', $ano)
                    ->first();
                if (!$anoAcademico) {
                    throw ValidationException::withMessages(['ano' => 'Ano académico não encontrado para esta escola.']);
                }

                $classe = DB::table('classes')->where('id', $dados['classe'])->first();
                $valores = DB::table('progresso_valores_classes')
                    ->where('classe_id', $dados['classe'])
                    ->first();
                if (!$classe || !$valores) {
                    throw ValidationException::withMessages([
                        'classe' => 'Classe sem valores configurados para a escola e ano académico.'
                    ]);
                }
                $valorMatricula = (float) $valores->valor_matricula;

                // Fotografia (ficheiro ou captura pela câmara).
                $avatar = 'avatar.png';
                if ($request->hasFile('foto')) {
                    $fotoGuardada = $request->file('foto')->store('alunos', 'public');
                    $avatar = $fotoGuardada;
                } elseif ($request->filled('imagem')) {
                    $imagem = $request->input('imagem');
                    if (!preg_match('/^data:image\/jpeg;base64,([A-Za-z0-9+\/=]+)$/', $imagem, $matches)) {
                        throw ValidationException::withMessages(['imagem' => 'Fotografia inválida.']);
                    }
                    $binario = base64_decode($matches[1], true);
                    if ($binario === false || strlen($binario) > 4 * 1024 * 1024 || !getimagesizefromstring($binario)) {
                        throw ValidationException::withMessages(['imagem' => 'Fotografia inválida ou demasiado grande.']);
                    }
                    $fotoGuardada = 'alunos/' . \Illuminate\Support\Str::uuid() . '.jpg';
                    if (!Storage::disk('public')->put($fotoGuardada, $binario)) {
                        throw new \RuntimeException('Não foi possível guardar a fotografia.');
                    }
                    $avatar = $fotoGuardada;
                }

                $familia = $dados['familia'];
                $pai = $familia['pai'] ?? [];
                $mae = $familia['mae'] ?? [];
                $encarregado = $familia['encarregado'];

                $pessoaId = DB::table('pessoas')->insertGetId([
                    'nome' => $dados['nomealuno'],
                    'genero' => $dados['genero'],
                    'data_nascimento' => $dados['data_nascimento'],
                    'tipo_documento' => $dados['tipo_documento'] ?? null,
                    'numero_documento' => $dados['numero_documento'] ?? null,
                    'data_emissao_documento' => $dados['data_emissao'] ?? null,
                    'endereco' => $dados['endereco'],
                    'pai' => $pai['nome'] ?? null,
                    'mae' => $mae['nome'] ?? null,
                    'avatar' => $avatar,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Se escolas possuir coluna 'codigo', substituir pelo código oficial.
                $codigoEscola = str_pad((string) $escolaId, 2, '0', STR_PAD_LEFT);
                $codigoAluno = $codigoService->gerarCodigoAluno($escolaId, $ano, $codigoEscola);

                $alunoId = DB::table('alunos')->insertGetId([
                    'codigo' => $codigoAluno,
                    'pessoa_id' => $pessoaId,
                    'escola_id' => $escolaId,
                    'classe_id' => $classe->id,
                    'provincia_id' => (int) $dados['provincia'],
                    'avatar' => $avatar,
                    'suspenso' => 0,
                    'desistiu' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Atribuição directa evita que campos sejam ignorados pelo $fillable.
                $userAluno = new User();
                $userAluno->nome = $dados['nomealuno'];
                $userAluno->codigo = $codigoAluno;
                $userAluno->email = null;
                $userAluno->telefone = null;
                $userAluno->password = Hash::make($codigoAluno);
                $userAluno->estado = 0; // Inactivo até ser definido um processo seguro de activação.
                $userAluno->funcionario = 0;
                $userAluno->todas_escolas = 0;
                $userAluno->pessoa_id = $pessoaId;
                $userAluno->escola_id = $escolaId;
                $userAluno->avatar = $avatar;
                $userAluno->save();

                $roleAluno = Role::firstOrCreate(['name' => 'Aluno', 'guard_name' => 'web']);
                $userAluno->assignRole($roleAluno);

                DB::table('progresso_matricula_sequencias')->insertOrIgnore([
                    'escola_id' => $escolaId,
                    'ano' => $ano,
                    'ultimo_numero' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sequencia = DB::table('progresso_matricula_sequencias')
                    ->where('escola_id', $escolaId)
                    ->where('ano', $ano)
                    ->lockForUpdate()
                    ->first();
                if (!$sequencia) {
                    throw new \RuntimeException('Não foi possível obter a sequência de matrículas.');
                }
                $numero = $sequencia->ultimo_numero + 1;
                DB::table('progresso_matricula_sequencias')
                    ->where('id', $sequencia->id)
                    ->update(['ultimo_numero' => $numero, 'updated_at' => now()]);
                $codigoMatricula = sprintf('%04d-%d', $numero, $ano);

                $matriculaId = DB::table('matriculas')->insertGetId([
                    'codigo' => $codigoMatricula,
                    'referencia' => $codigoMatricula,
                    'valor_matricula' => $valorMatricula,
                    'valor_co_curricular' => 0,
                    'valor_total' => $valorMatricula,
                    'desconto' => 0,
                    'bolsa_percentual' => $dados['bolsa_percentual'] ?? 0,
                    'pagou' => 0,
                    'desistiu' => 0,
                    'renovacao' => 0,
                    'classe_id' => $classe->id,
                    'ano_academico_id' => $anoAcademico->id,
                    'aluno_id' => $alunoId,
                    'escola_id' => $escolaId,
                    'turma_id' => null,
                    'pagamento_id' => null,
                    'user_id' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $inscricaoId = DB::table('progresso_inscricao_detalhes')->insertGetId([
                    'matricula_id' => $matriculaId,
                    'aluno_id' => $alunoId,
                    'escola_id' => $escolaId,
                    'tipo_inscricao' => 'NOVA',
                    'nacionalidade' => $dados['nacionalidade'] ?? null,
                    'data_emissao' => $dados['data_emissao'] ?? null,
                    'vive_com' => $dados['vive_com'] ?? null,
                    'escola_anterior' => $dados['escola_anterior'] ?? null,
                    'modalidade_pagamento' => $dados['modalidade_pagamento'],
                    'emergencia_nome' => $dados['emergencia_nome'],
                    'emergencia_telefone' => $dados['emergencia_telefone'],
                    'declaracao_aceite_em' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Grava todos os campos da família; pai e mãe vazios são ignorados.
                foreach (['pai' => 'PAI', 'mae' => 'MAE', 'encarregado' => 'ENCARREGADO'] as $chave => $tipo) {
                    $familiar = $familia[$chave] ?? [];
                    if (trim((string) ($familiar['nome'] ?? '')) === '') {
                        continue;
                    }
                    DB::table('progresso_inscricao_familia')->insert([
                        'inscricao_id' => $inscricaoId,
                        'tipo' => $tipo,
                        'nome' => trim($familiar['nome']),
                        'parentesco' => $familiar['parentesco'] ?? null,
                        'nacionalidade' => $familiar['nacionalidade'] ?? null,
                        'formacao' => $familiar['formacao'] ?? null,
                        'profissao' => $familiar['profissao'] ?? null,
                        'local_trabalho' => $familiar['local_trabalho'] ?? null,
                        'telefone' => $familiar['telefone'] ?? null,
                        'telefone2' => $familiar['telefone2'] ?? null,
                        'email' => $familiar['email'] ?? null,
                        'residencia' => $familiar['residencia'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $saude = $dados['saude'] ?? [];
                DB::table('progresso_inscricao_saude')->insert([
                    'inscricao_id' => $inscricaoId,
                    'condicao' => (int) ($saude['condicao'] ?? 0),
                    'condicao_descricao' => $saude['condicao_descricao'] ?? null,
                    'medicacao' => (int) ($saude['medicacao'] ?? 0),
                    'medicacao_descricao' => $saude['medicacao_descricao'] ?? null,
                    'alergia' => (int) ($saude['alergia'] ?? 0),
                    'alergia_descricao' => $saude['alergia_descricao'] ?? null,
                    'atestado' => (int) ($saude['atestado'] ?? 0),
                    'unidade_preferencial' => $saude['unidade_preferencial'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach (($dados['autorizados'] ?? []) as $autorizado) {
                    $nome = trim((string) ($autorizado['nome'] ?? ''));
                    $telefone = trim((string) ($autorizado['telefone'] ?? ''));
                    if ($nome === '' && $telefone === '') {
                        continue;
                    }
                    if ($nome === '' || $telefone === '') {
                        throw ValidationException::withMessages([
                            'autorizados' => 'Cada pessoa autorizada deve ter nome e telefone.'
                        ]);
                    }
                    DB::table('progresso_inscricao_autorizados')->insert([
                        'inscricao_id' => $inscricaoId,
                        'nome' => $nome,
                        'telefone' => $telefone,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $atividadesIds = $dados['atividades'] ?? [];
                if ($atividadesIds) {
                    $atividades = DB::table('progresso_atividades')
                        ->where('estado', 'ACTIVO')
                        ->whereIn('id', $atividadesIds)
                        ->get();
                    if ($atividades->count() !== count($atividadesIds)) {
                        throw ValidationException::withMessages([
                            'atividades' => 'Uma ou mais actividades são inválidas.'
                        ]);
                    }
                    foreach ($atividades as $atividade) {
                        DB::table('progresso_aluno_atividades')->insert([
                            'escola_id' => $escolaId,
                            'aluno_id' => $alunoId,
                            'atividade_id' => $atividade->id,
                            'ano_academico_id' => $anoAcademico->id,
                            'data_inicio' => now()->toDateString(),
                            'valor_mensal' => $atividade->valor_mensal,
                            'estado' => 'ACTIVO',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $cobrancaId = DB::table('progresso_cobrancas')->insertGetId([
                    'escola_id' => $escolaId,
                    'aluno_id' => $alunoId,
                    'matricula_id' => $matriculaId,
                    'tipo' => 'MATRICULA',
                    'origem_id' => $matriculaId,
                    'descricao' => 'Matrícula - ' . $classe->nome,
                    'ano' => $ano,
                    'mes' => null,
                    'valor_original' => $valorMatricula,
                    'valor_desconto' => 0,
                    'valor_total' => $valorMatricula,
                    'data_vencimento' => now()->toDateString(),
                    'estado' => 'PENDENTE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $cobrancasService->gerar(
                    escolaId: $escolaId,
                    alunoId: $alunoId,
                    matriculaId: $matriculaId,
                    ano: $ano,
                    classe: $classe,
                    valores: $valores,
                    atividades: $atividades ?? collect(),
                    bolsaPercentual: (float) ($dados['bolsa_percentual'] ?? 0)
                );

                if ((bool) ($escola->sms_activo ?? false)) {
                    $telefoneEncarregado = trim((string) $encarregado['telefone']);
                    if ($telefoneEncarregado !== '') {
                        $textoSms = sprintf(
                            'Colegio Progresso: O aluno %s foi matriculado na %s. Codigo: %s. Matricula: %s. Valor: %s MT. Pagamento pendente.',
                            $dados['nomealuno'],
                            $classe->nome,
                            $codigoAluno,
                            $codigoMatricula,
                            number_format($valorMatricula, 2, ',', '.')
                        );
                        DB::table('mensagem')->insert([
                            'escola_id' => $escolaId,
                            'telefone' => $telefoneEncarregado,
                            'descricao' => $textoSms,
                            'tipo' => 'Pendente',
                            'qtd' => 1,
                            'credito' => 1,
                            'custo_real' => 1,
                            'canal' => 'SMS',
                            'data_envio' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                return [
                    'aluno_id' => $alunoId,
                    'matricula_id' => $matriculaId,
                    'codigo_aluno' => $codigoAluno,
                    'codigo_matricula' => $codigoMatricula
                ];
            }, 3);

            return response()->json([
                'status' => 1,
                'message' => 'Matrícula ' . $resultado['codigo_matricula']
                    . ' registada com sucesso. Aluno: ' . $resultado['codigo_aluno'],
                'aluno_id' => $resultado['aluno_id'],
                'matricula_id' => $resultado['matricula_id'],
                'codigo_aluno' => $resultado['codigo_aluno'],
                'codigo_matricula' => $resultado['codigo_matricula'],
            ]);
        } catch (\Throwable $e) {
            if ($fotoGuardada) {
                Storage::disk('public')->delete($fotoGuardada);
            }
            throw $e;
        }
    }

    // Acrescentar ao ProgressoMatriculaController.
    // Imports necessários: use Barryvdh\DomPDF\Facade\Pdf;
    //                     use Illuminate\Support\Facades\DB;
    //                     use Illuminate\Support\Facades\Storage;
    public function fichaPdf(int $alunoId)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. IDENTIFICAR ESCOLA
        |--------------------------------------------------------------------------
        */
        $escolaId = (int) auth()->user()->escola_id;

        /*
        |--------------------------------------------------------------------------
        | 2. IDENTIFICAR ALUNO
        |--------------------------------------------------------------------------
        */
        $aluno = DB::table('alunos as a')
            ->join('pessoas as p', 'p.id', '=', 'a.pessoa_id')
            ->leftJoin('classes as c', 'c.id', '=', 'a.classe_id')
            ->where('a.id', $alunoId)
            ->where('a.escola_id', $escolaId)
            ->select(
                'a.id',
                'a.codigo',
                'a.avatar',
                'a.classe_id',
                'p.nome',
                'p.genero',
                'p.data_nascimento',
                'p.tipo_documento',
                'p.numero_documento',
                'p.data_emissao_documento',
                'p.endereco',
                'c.nome as classe_nome'
            )
            ->first();

        abort_unless(
            $aluno,
            404,
            'Aluno não encontrado nesta escola.'
        );

        /*
        |--------------------------------------------------------------------------
        | 3. ESCOLA
        |--------------------------------------------------------------------------
        */
        $escola = DB::table('escolas')
            ->where('id', $escolaId)
            ->first();

        abort_unless($escola, 404, 'Escola não encontrada.');

        /*
        |--------------------------------------------------------------------------
        | 4. FICHA ORIGINAL DO ALUNO
        |--------------------------------------------------------------------------
        |
        | A ficha é registada uma única vez.
        | Não depende da matrícula mais recente.
        |
        */
        $detalhes = DB::table('progresso_inscricao_detalhes')
            ->where('aluno_id', $alunoId)
            ->where('escola_id', $escolaId)
            ->orderBy('id')
            ->first();

        abort_unless(
            $detalhes,
            404,
            'Ficha de inscrição não encontrada para este aluno.'
        );

        /*
        |--------------------------------------------------------------------------
        | 5. MATRÍCULA ORIGINAL
        |--------------------------------------------------------------------------
        |
        | Utilizamos a matrícula associada à ficha,
        | não a última renovação.
        |
        */
        $matriculaOriginal = DB::table('matriculas as m')
            ->leftJoin('classes as c', 'c.id', '=', 'm.classe_id')
            ->leftJoin(
                'ano_academico as aa',
                'aa.id',
                '=',
                'm.ano_academico_id'
            )
            ->where('m.id', $detalhes->matricula_id)
            ->where('m.aluno_id', $alunoId)
            ->where('m.escola_id', $escolaId)
            ->select(
                'm.*',
                'c.nome as classe_inscricao',
                'aa.ano as ano_lectivo'
            )
            ->first();

        abort_unless(
            $matriculaOriginal,
            404,
            'Matrícula original não encontrada.'
        );

        /*
        |--------------------------------------------------------------------------
        | 6. FAMILIARES
        |--------------------------------------------------------------------------
        */
        $familia = DB::table('progresso_inscricao_familia')
            ->where('inscricao_id', $detalhes->id)
            ->get()
            ->keyBy('tipo');

        /*
        |--------------------------------------------------------------------------
        | 7. INFORMAÇÕES DE SAÚDE
        |--------------------------------------------------------------------------
        */
        $saude = DB::table('progresso_inscricao_saude')
            ->where('inscricao_id', $detalhes->id)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | 8. PESSOAS AUTORIZADAS
        |--------------------------------------------------------------------------
        */
        $autorizados = DB::table('progresso_inscricao_autorizados')
            ->where('inscricao_id', $detalhes->id)
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 9. ACTIVIDADES DA INSCRIÇÃO ORIGINAL
        |--------------------------------------------------------------------------
        */
        $atividades = DB::table('progresso_aluno_atividades as pa')
            ->join(
                'progresso_atividades as at',
                'at.id',
                '=',
                'pa.atividade_id'
            )
            ->where('pa.escola_id', $escolaId)
            ->where('pa.aluno_id', $alunoId)
            ->where(
                'pa.ano_academico_id',
                $matriculaOriginal->ano_academico_id
            )
            ->select('at.nome')
            ->distinct()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 10. FOTOGRAFIA
        |--------------------------------------------------------------------------
        */
        $foto = null;

        $avatar = (string) ($aluno->avatar ?? '');

        if (
            str_starts_with($avatar, 'alunos/') &&
            Storage::disk('public')->exists($avatar)
        ) {
            $foto = Storage::disk('public')->path($avatar);
        }

        /*
        |--------------------------------------------------------------------------
        | 11. PREPARAR DADOS PARA O BLADE
        |--------------------------------------------------------------------------
        |
        | Mantemos a variável $matricula para ser
        | compatível com o ficha_pdf.blade.php
        | que já criámos.
        |
        */
        $matricula = clone $matriculaOriginal;

        $matricula->aluno_nome = $aluno->nome;
        $matricula->codigo_aluno = $aluno->codigo;
        $matricula->aluno_avatar = $aluno->avatar;
        $matricula->genero = $aluno->genero;
        $matricula->data_nascimento = $aluno->data_nascimento;
        $matricula->tipo_documento = $aluno->tipo_documento;
        $matricula->numero_documento = $aluno->numero_documento;
        $matricula->data_emissao_documento =
            $aluno->data_emissao_documento;
        $matricula->endereco = $aluno->endereco;

        // Classe da inscrição original.
        $matricula->classe_nome =
            $matriculaOriginal->classe_inscricao;

        /*
        |--------------------------------------------------------------------------
        | 12. GERAR PDF
        |--------------------------------------------------------------------------
        */
        $pdf = Pdf::loadView(
            'matricula.ficha_pdf',
            compact(
                'matricula',
                'escola',
                'detalhes',
                'familia',
                'saude',
                'autorizados',
                'atividades',
                'foto'
            )
        )->setPaper('a4', 'portrait');

        /*
        |--------------------------------------------------------------------------
        | 13. VISUALIZAR PDF
        |--------------------------------------------------------------------------
        */
        return $pdf->stream(
            'ficha-aluno-' . $aluno->codigo . '.pdf'
        );
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

            if ($request->input('classe') == null) {
                return response()->json(['status' => 0, 'message' => 'A classe é obrigatorio']);
            }

            if ($request->input('turma') == null) {
                return response()->json(['status' => 0, 'message' => 'O regime é obrigatorio']);
            }

            if ($request->input('plano') == null) {
                return response()->json(['status' => 0, 'message' => 'O plano de estudo é obrigatorio']);
            }

            $totalAlunos = Aluno::all();
            $current_year = Carbon::now()->year;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);
            $escola = Escola::where('id', Auth::user()->escola_id)->first();
            $student_code = $escola->codigo.'.A'.str_pad(count($totalAlunos) + 1, 4, '0', STR_PAD_LEFT).'.'.$year_last_two_digits;
            $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
            $totalMatriculas = Matricula::where('ano_academico_id', $anoAcademicoAtivo->id)->where('escola_id', $escola->codigo)->get();        
            $classe = Classe::where('id', $request->input('classe'))->first();

            // Pagar na taxa selecionado pelo utser
            $taxa = null;

            if($classe->id==9){
              $taxa = Taxa::where('ciclo_id', 5)->first();
            }else{
              $taxa = Taxa::where('ciclo_id', $classe->ciclo_id)->first();  
            }

            // Pagar o Turmo para Saber Se e meio dia ou dia inteiro
            $turma = Turma::where('id', $request->input('turma'))->first();
            // Aluno ou Matricula
            $matricula1 = Matricula::where('id', $request->input('matricula'))->first();

            // Classe, Verifica se Houve Mudanca de Classe e Regime
            $classeAnterior = $matricula1->classe_id;
            $classeActual = $request->input('classe');

            $todosPagamento = Pagamento::all();
            $fatura = str_pad(count($totalMatriculas) + 1, 5, '0', STR_PAD_LEFT).''.$escola->codigo.''.$year_last_two_digits;
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

            $coCurriculares = $request->input('cocurriculares', []);

            CoCurricular::where('aluno_id', $matricula1->aluno_id)->where('ano_academico_id', $anoAcademicoAtivo->id)->delete();

            if (!empty($coCurriculares)) {
                $quantidade = count($coCurriculares);
                $propinaCoCurricular = 0;
                $coCurricular = true;

                foreach ($coCurriculares as $coCurricularID) {
                    $disciplinaCoCurricular = Disciplina::where('id', $coCurricularID)->first();
                    $valorMensalidadeCoCurricular = $valorMensalidadeCoCurricular + $disciplinaCoCurricular->propina;
                }

                if ($request->input('plano') == 1) {
                    $valorTotal = $taxa->matricula_normal;
                    $plano = 1;

                    if ($turma->periodo->tipo == 'meio_dia') {
                        $valorMensalidadeCurricular = $taxa->propina_meio_dia_normal;
                        $valorTotalMensalidade = $taxa->propina_meio_dia_normal + $propinaCoCurricular;
                    } elseif ($turma->periodo->tipo == 'todo_dia') {
                        $valorMensalidadeCurricular = $taxa->propina_todo_dia_normal;
                        $valorTotalMensalidade = $taxa->propina_todo_dia_normal + $propinaCoCurricular;
                    }
                } elseif ($request->input('plano') == 2) {
                    $valorTotal = $taxa->matricula_gold;
                    $plano = 2;

                    if ($turma->periodo->tipo == 'meio_dia') {
                        $valorMensalidadeCurricular = $taxa->propina_meio_dia_gold;
                        $valorTotalMensalidade = $taxa->propina_meio_dia_gold + $propinaCoCurricular;
                    } elseif ($turma->periodo->tipo == 'todo_dia') {
                        $valorMensalidadeCurricular = $taxa->propina_todo_dia_gold;
                        $valorTotalMensalidade = $taxa->propina_todo_dia_gold + $propinaCoCurricular;
                    }
                }
            } else {
                $valorTotalMatriculaCoCurricular = 0;
                $coCurricular = false;

                if ($request->input('plano') == 1) {
                    $valorTotal = $taxa->matricula_normal;
                    $plano = 1;

                    if ($turma->periodo->tipo == 'meio_dia') {
                        $valorMensalidadeCurricular = $taxa->propina_meio_dia_normal;
                        $valorTotalMensalidade = $taxa->propina_meio_dia_normal;
                    } elseif ($turma->periodo->tipo == 'todo_dia') {
                        $valorMensalidadeCurricular = $taxa->propina_todo_dia_normal;
                        $valorTotalMensalidade = $taxa->propina_todo_dia_normal;
                    }
                } elseif ($request->input('plano') == 2) {
                    $valorTotal = $taxa->matricula_gold;
                    $plano = 2;

                    if ($turma->periodo->tipo == 'meio_dia') {
                        $valorMensalidadeCurricular = $taxa->propina_meio_dia_gold;
                        $valorTotalMensalidade = $taxa->propina_meio_dia_gold;
                    } elseif ($turma->periodo->tipo == 'todo_dia') {
                        $valorMensalidadeCurricular = $taxa->propina_todo_dia_gold;
                        $valorTotalMensalidade = $taxa->propina_todo_dia_gold;
                    }
                }
            }

            $referencia = EntidadeReferenciaService::GerarReferencia($fatura, $valorTotal);

            // Registo de Aluno
            $pessoaAluno = Pessoa::where('id', $matricula1->aluno->pessoa_id)->first();
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
                $aluno = Aluno::where('id', $matricula1->aluno->id)->first();
                $aluno->nome_casa = $request->input('nome_casa');
                $aluno->classe_id = $request->input('classe');
                $aluno->pessoa_id = $matricula1->aluno->pessoa_id;
                $aluno->escola_id = Auth::user()->escola_id;
                $aluno->provincia_id = $request->input('provincia');

                // Registar Aluno Como Utilizador
                $userAluno = User::where('pessoa_id', $matricula1->aluno->pessoa_id)->first();
                $userAluno->nome = $request->input('nomealuno');

                if ($request->has('imagem')) {
                    $imgBase64 = $request->input('imagem');
                    $imgBase64 = str_replace('data:image/png;base64,', '', $imgBase64);
                    $imgBase64 = str_replace(' ', '+', $imgBase64);
                    $imageData = base64_decode($imgBase64);

                    $file = $student_code.'.png';
                    $path = public_path('avatar/'.$file);

                    file_put_contents($path, $imageData);

                    $userAluno->avatar = $file;
                    $aluno->avatar = $file;
                }

                if ($aluno->save() && $userAluno->save()) {
                    if ($request->input('plano') == 2) {
                        $planoEducativoIndividualizada = PlanoEducativoIndividualizado::where('aluno_id', $matricula1->aluno->id)->first();

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

                    $pagamento = Pagamento::where('id', $matricula1->pagamento_id)->first();
                    $pagamento->recibo = $recibo;
                    $pagamento->factura = $fatura;
                    $pagamento->desconto = 0;
                    $pagamento->entidade = 90028;
                    $pagamento->referencia = $matricula1->referencia;
                    $pagamento->metodo_pagamento_id = 3;
                    $pagamento->aluno_id = $aluno->id;
                    $pagamento->user_id = Auth::user()->id;
                    $pagamento->escola_id = Auth::user()->escola_id;
                    $pagamento->categoria_id = 1;
                    $pagamento->valor = $valorTotal;
                    $pagamento->prazo_pagamento = Carbon::now()->addDays(60);
                    $pagamento->descricao = 'Confirmado Pagamento Matricula do Aluno '.$aluno->codigo;

                    // Saude
                    $saude = Saude::where('aluno_id', $matricula1->aluno->id)->first();
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
                    Irmao::where('aluno_id', $matricula1->aluno_id)->delete();
                    Pauta::where('aluno_id', $matricula1->aluno_id)->where('ano_academico_id', $anoAcademicoAtivo->id)->delete();

                    if (!empty($alunos1)) {
                        foreach ($alunos1 as $alunoID) {
                            $irmao = new Irmao();
                            $irmao->aluno_id = $aluno->id;
                            $irmao->aluno_irmao_id = $alunoID;
                            $irmao->save();
                        }
                    }

                    // $coCurriculares = $request->input('cocurriculares', []);
                    if (!empty($coCurriculares)) {
                        $quantidade = count($coCurriculares);

                        foreach ($coCurriculares as $coCurricularID) {
                            $coCurricular1 = new CoCurricular();
                            $coCurricular1->aluno_id = $aluno->id;
                            $coCurricular1->disciplina_id = $coCurricularID;
                            $coCurricular1->mes_id = 1;
                            $coCurricular1->ano_academico_id = $anoAcademicoAtivo->id;
                            $coCurricular1->save();

                            $pauta = new Pauta();
                            $pauta->ano_academico_id = $anoAcademicoAtivo->id;
                            $pauta->disciplina_id = $coCurricularID;
                            $pauta->classe_id = $request->input('classe');
                            $pauta->aluno_id = $aluno->id;
                            $pauta->turma_id = $turma->id;
                            $pauta->escola_id = Auth::user()->escola_id;
                            $pauta->save();
                        }
                    }

                    if ($saude->save() && $pagamento->save()) {
                        $matricula = Matricula::where('id', $request->input('matricula'))->first();
                        $matricula->codigo = $codigoMatricula;
                        $matricula->desconto = 0;
                        $matricula->entidade = 90028;
                        $matricula->referencia = $matricula1->referencia;
                        $matricula->valor_matricula = $valorTotalMatricula;
                        $matricula->valor_co_curricular = $valorTotalMatriculaCoCurricular;
                        $matricula->valor_total = $valorTotal;
                        $matricula->plano = $plano;
                        $matricula->co_curricular = $coCurricular;
                        $matricula->pagou = false;
                        $matricula->renovacao = false;
                        $matricula->periodo_id = $turma->periodo_id;
                        $matricula->ano_academico_id = $anoAcademicoAtivo->id;
                        $matricula->classe_id = $request->input('classe');
                        $matricula->aluno_id = $aluno->id;
                        $matricula->turma_id = $turma->id;
                        $matricula->pagamento_id = $pagamento->id;
                        $matricula->user_id = Auth::user()->id;
                        $matricula->escola_id = Auth::user()->escola_id;

                        // categoria 1 = Matricula 2 = Propinas
                        $descricao1 = 'Factura da Matricula';
                        ReceitaDespesaService::receita($valorTotal, 1, $descricao1, $pagamento->id, false, $matricula->referencia);

                        $this->gerarActulizacaoMensalidades($aluno->id, $anoAcademicoAtivo->id, $valorMensalidadeCurricular, $valorMensalidadeCoCurricular);
                        $this->gerarPauta($aluno->id, $anoAcademicoAtivo->id, $request->input('classe'), $turma->id);

                        // Email
                        $corpo = 'Estimado Encarregado Foi Gerado a Fatura da Matricula do Aluno '.$aluno->codigo.' Os detalhe estao no Anexo';
                        $assunto = 'Matricula de Ano Lectivo '.$anoAcademicoAtivo->ano;
                        $tipo = 'Automatico';
                        $sender = 'AGVIAGEM';
                        $encarregados = AlunoRelacaoPessoa::with('pessoa')->where('aluno_id', $aluno->id)->where('relacao_id', 1)->get();

                        if (PautaFinalService::decrimentarVagaTurma($turma->id, 1)) {
                            if ($matricula->save()) {

                                DB::commit();

                                return response()->json(['status' => 1, 'message' => 'Aluno Matriculado com sucesso!']);
                            }
                        } else {
                            return response()->json(['status' => 0, 'message' => 'Sem Vaga Nessa Turma']);
                        }
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

    public function renovacao(Request $request)
    {
        try {
            DB::beginTransaction();

            $aluno = Aluno::where('id', $request->input('aluno'))->first();
            $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first(); //AnoAcademico::where('id', 3)->first();

            $totalAlunos = Aluno::all();
            $current_year = $anoAcademicoAtivo->ano;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);
            $escola = Escola::where('id', Auth::user()->escola_id)->first();

            $totalMatriculas = Matricula::where('ano_academico_id', $anoAcademicoAtivo->id)->get();

            // Pagar Matricula de Ano Anterior
            $matriculaAnterior = Matricula::where('ano_academico_id', 2)->where('aluno_id', $aluno->id)->first();
            $classe = Classe::where('id', $matriculaAnterior->classe_id)->first();
            // Pagar na taxa selecionado pelo utser
            $taxa = Taxa::where('ciclo_id', $classe->ciclo_id)->first();

            $todosPagamento = Pagamento::all();
            $fatura = str_pad(count($totalMatriculas) + 1, 3, '0', STR_PAD_LEFT).''.$current_month.''.$escola->codigo.''.$year_last_two_digits;
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

            $coCurriculares = $request->input('cocurriculares', []);

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
                $valorTotalMatricula = 4150;
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
            $pagamento->user_id = Auth::user()->id;
            $pagamento->escola_id = Auth::user()->escola_id;
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
                $matricula->user_id = Auth::user()->id;
                $matricula->escola_id = Auth::user()->escola_id;

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

                    // padroniza telefone
                    if($request->input('telefone')!=null){

                        $telefone = substr($request->input('telefone'), -9);
                        $valorFormatada = number_format($valorTotalMatricula, 2, ',', '.');
                        $nomeAluno = $aluno->pessoa->nome;
                        $sms = 'Prezado Encarregado do(a) '.$nomeAluno.' para pagar renovacao de matricula usa  ENTIDADE: 90028, REFERENCIA: '.$referencia.', VALOR: '.$valorFormatada.' MZN. Prazo 15-Nov-2025, cumpra com prazo evite perder vaga. Banco: Millennium BIM';
                        $qtd = SMSService::quantidadeSMS($sms);

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
                    
                    DB::commit();

                    return response()->json(['status' => 1, 'message' => 'Renovacao da Matricula Efectuado com Sucesso!']);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // $errorMessage = DatabaseErrorHandler::handle($e);
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function alunosTransferidos()
    {
        $escola = Auth::user()->escola_id;
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();

        $transferencias = Transferencia::where('ano_academico_id', $anoAcademicoAtivo->id)->get();

        // Retorna a view com os dados carregados
        return view('matricula.transferencia.index', [
            'transferencias' => $transferencias,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
        ]);
    }

    public function transferencia(Request $request)
    {
        try {

            DB::beginTransaction();

                $codigoAluno = $request->input('codigo');
                $escolaOrigem = Auth::user()->escola_id;
                $escolaDestino = $request->input('escolaDestino');
                $dataTransferencia = $request->input('data');
                $turma = $request->input('turma');
                $classe = $request->input('classe');
                $motivo = $request->input('motivo');

                $aluno = Aluno::where('codigo', $codigoAluno)->first();
                $user = User::where('codigo', $codigoAluno)->first();
                $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
                $matriculaActual = Matricula::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAcademicoAtivo->id)->first();


                $transferencia = new Transferencia();
                $transferencia->aluno_id = $aluno->id;
                $transferencia->matricula_id = $matriculaActual->id;
                $transferencia->escola_origem_id = $escolaOrigem;
                $transferencia->escola_destino_id = $escolaDestino;
                $transferencia->data_transferencia = $dataTransferencia;
                $transferencia->ano_academico_id = $anoAcademicoAtivo->id;
                $transferencia->motivo = $motivo;
                $transferencia->user_id = Auth::user()->id;

                $aluno->escola_id = $escolaDestino;
                $aluno->save();

                $user->escola_id = $escolaDestino;
                $user->save();

                Pauta::where('aluno_id',$aluno->id)
                    ->where('ano_academico_id',$anoAcademicoAtivo->id)
                    ->update([
                        'escola_id'=>$escolaDestino,
                        'classe_id'=>$classe,
                        'turma_id'=>$turma
                    ]);

                Mensalidade::where('aluno_id',$aluno->id)
                    ->where('ano_academico_id',$anoAcademicoAtivo->id)
                    ->update([
                        'escola_id'=>$escolaDestino
                    ]);    

                if($transferencia->save())
                {
                    DB::commit();  

                    return response()->json(['status' => 1, 'message' => 'Pagamento efectuado com sucesso!']);
                }

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function relatorioMatriculados()
    {
        $current_year = Carbon::now()->year;
        $today = Carbon::now();

        $anoAcademicoAtivo = AnoAcademico::where('ano', 2026)->first(); //AnoAcademico::where('estado', true)->first();
        $escola = Escola::where('id', Auth::user()->escola_id)->first();
        /*$matriculados = Matricula::where('ano_academico_id', $anoAcademicoAtivo->id)
                                    ->where('escola_id', Auth::user()->escola_id)
                                    ->get(); */

        $matriculados = Matricula::with([
            'classe',
            'aluno.pessoa',
        ])
                            ->where('ano_academico_id', $anoAcademicoAtivo->id)
                            ->where('escola_id', Auth::user()->escola_id)
                            ->where('desistiu', 0)
                            ->get()
                            ->groupBy('classe.nome')
                            ->map(function ($matriculas) {
                                return $matriculas->sortBy(function ($matricula) {
                                    return $matricula->aluno->pessoa->nome;
                                });
                            });

        $pdf = \PDF::loadView('matricula.pdf.matriculados', [
            'escola' => $escola,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'today' => $today,
            'matriculados' => $matriculados,
        ])->setPaper('a4', 'Portrait');

        $fikeName = 'Matriculados-'.$current_year;

        return $pdf->stream($fikeName.'.pdf');
    }

    public function facturaPDF($recibo)
    {
        $current_year = Carbon::now()->year;
        $today = Carbon::now();
        $pagamento = Pagamento::where('referencia', $recibo)->first();

        $matricula = Matricula::where('pagamento_id', $pagamento->id)
                                    ->first();
        $coCurriculares = CoCurricular::where('aluno_id', $matricula->aluno_id)->where('ano_academico_id', $matricula->ano_academico_id)->get();

        $pdf = \PDF::loadView('matricula.pdf.factura', ['coCurriculares' => $coCurriculares, 'today' => $today, 'matricula' => $matricula, 'pagamento' => $pagamento]);
        $fikeName = 'Fatura-'.$pagamento->factura;

        return $pdf->stream($fikeName.'.pdf');
    }

    // Factura Anexo Para Enviar Por Email
    public function facturaAnexoPDF($recibo)
    {
        $current_year = Carbon::now()->year;
        $today = Carbon::now();
        $pagamento = Pagamento::where('referencia', $recibo)->first();

        $matricula = Matricula::where('pagamento_id', $pagamento->id)
                                    ->first();

        $coCurriculares = CoCurricular::where('aluno_id', $matricula->aluno_id)->where('ano_academico_id', $matricula->ano_academico_id)->get();

        $pdf = \PDF::loadView('matricula.pdf.factura', ['coCurriculares' => $coCurriculares, 'today' => $today, 'matricula' => $matricula, 'pagamento' => $pagamento]);

        // Caminho temporário para salvar o PDF
        $path = public_path('relatorio');
        $fileName = $recibo.'.pdf';
        $fullPath = $path.'/'.$fileName;
        // Salvar o PDF no servidor
        $pdf->save($fullPath);

        return $fileName;
    }

    public function reciboPDF($recibo)
    {
        $current_year = Carbon::now()->year;
        $today = Carbon::now();
        $pagamento = Pagamento::where('referencia', $recibo)->first();

        $matricula = Matricula::where('pagamento_id', $pagamento->id)
                                    ->first();

        $coCurriculares = CoCurricular::where('aluno_id', $matricula->aluno_id)->where('ano_academico_id', $matricula->ano_academico_id)->get();

        $pdf = \PDF::loadView('matricula.pdf.recibo', ['coCurriculares' => $coCurriculares, 'today' => $today, 'matricula' => $matricula, 'pagamento' => $pagamento]);
        $fikeName = 'Fatura-'.$pagamento->factura;

        return $pdf->stream($fikeName.'.pdf');
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

    /* Esse metodo gera a mensalidade anual de cada estudante
     Onde recebe o id de estudante, o Ano Academico, e a TaxaID
     Para preencher mensalidades que devem ser pagos
    */
    public function gerarActulizacaoMensalidades($alunoID, $anoAcademicoID, $valor, $valorCoCurricular)
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

                $mensalidade = Mensalidade::where('aluno_id', $alunoID)->where('ano_academico_id', $anoAcademicoID)->where('mes_id', $month->id)->first();
                $mensalidade->valor = $valorTotal;
                $mensalidade->valor_co_curricular = $valorCoCurricular;
                $mensalidade->mes_id = $month->id;
                $mensalidade->ano_academico_id = $anoAcademicoID;
                $mensalidade->data_inicio = Carbon::createFromFormat('Y-m-d', $month->data_inicio)->setYear($anoAcademicoAtivo->ano);
                $mensalidade->data_fim = Carbon::createFromFormat('Y-m-d', $month->data_fim)->setYear($anoAcademicoAtivo->ano);
                $mensalidade->aluno_id = $alunoID;
                $mensalidade->escola_id = Auth::user()->escola_id;
                $mensalidade->pagamento_id = $pagamento->id;

                if ($mensalidade->pagou == 0) {
                    $mensalidade->save();
                }
            }
        }
    }

    public function pagamentoMatricula($codigo)
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        // Buscar Alunos Que nao pagaram Matricula
        $matricula = Matricula::where('id', $codigo) // Certifique-se de carregar a relação 'pessoa' também
                ->where('escola_id', Auth::user()->escola_id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                // ->where('pagou',0)
                ->first();

        // Retorna a view com os dados carregados
        return view('matricula.pagamento.create', [
            'matricula' => $matricula,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
        ]);
    }

    /* Esse metodo gera a Pauta anual de cada estudante
     Onde recebe o id de estudante, o Ano Academico, e a TaxaID
     Para preencher mensalidades que devem ser pagos
    */
    public function gerarPauta($alunoID, $anoAcademicoID, $classeID, $turmaID)
    {
        $curriculos = DisciplinaCurriculo::where('classe_id', $classeID)
                            ->where('estado', 1)
                            ->get();

        foreach ($curriculos as $curriculo) {
            $pauta = new Pauta();
            $pauta->ano_academico_id = $anoAcademicoID;
            $pauta->disciplina_id = $curriculo->disciplina_id;
            $pauta->classe_id = $classeID;
            $pauta->aluno_id = $alunoID;
            $pauta->turma_id = $turmaID;
            $pauta->escola_id = Auth::user()->escola_id;
            $pauta->save();
        }
    }

    //Configuracao de novo ciclo
    public function ciclo()
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $currentDate = Carbon::now();
        $current_year = Carbon::now()->year;
        $taxas = Taxa::orderBy('ciclo_id', 'asc')->get();
        $disciplinas = Disciplina::where('co_curricular',1)->orderBy('id', 'desc')->get();
        $ciclos = CicloAno::orderBy('id', 'asc')->get();

        $anos = AnoAcademico::orderBy('ano', 'desc')->where('ano','>=',$current_year)->get();

        
        // Retorna a view com os dados carregados
        return view('ciclo.index', [
            'current_year' => $current_year,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'taxas' => $taxas,
            'disciplinas' => $disciplinas, 
            'ciclos' => $ciclos,
            'anos' => $anos,
        ]);

    }

    public function cicloUpdate(Request $request)
    {
        $request->validate([
            'ano_academico_id' => ['required','integer','exists:ciclos,id'],
            'tipo' => ['required', Rule::in([ 'MATRICULA','RENOVACAO','MENSALIDADE','VER PAUTA','GERAR PAUTA'])],
            'valor_renovacao_normal' => ['nullable','numeric','min:0'],
            'valor_renovacao_gold' => ['nullable','numeric','min:0'],
        ]);


        /*
        * Renovação obrigatoriamente deve ter valor
        */
        if ($request->tipo === 'RENOVACAO' && ($request->valor_renovacao_normal === null || $request->valor_renovacao_normal === '')) {

            return response()->json([
                'errors' => [ 'valor_renovacao' => ['Informe o valor da renovação.']]
            ], 422);
        }

        /*
        * Evitar duplicação da mesma configuração
        */
        $existe = CicloAno::where('tipo', $request->tipo)->first();

        if ($existe) {

            $existe->ano_academico_id  = $request->ano_academico_id;
            $existe->valor_renovacao_normal = $request->valor_renovacao_normal;
            $existe->valor_renovacao_gold = $request->valor_renovacao_gold;
            $existe->estado = $request->estado;
            $existe->save();

            if($request->tipo === 'VER PAUTA'){

                //Desativar o ciclo em curso
                $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
                $anoAcademicoAtivo->estado = false;
                $anoAcademicoAtivo->save();

                //Activar o novo ciclo
                $anoAcademicoAtivo1 = AnoAcademico::where('id', $request->ano_academico_id)->first();
                $anoAcademicoAtivo1->estado = true;
                $anoAcademicoAtivo1->save();

                return response()->json([
                    'status' => 1,
                    'message' => 'Configuração feita com sucesso.'
                ]);

            }

            return response()->json([
                'status' => 1,
                'message' => 'Configuração feita com sucesso.'
            ]);

        }

        return response()->json([
            'errors' => [
                'tipo' => [
                    'Nao existe esta configuração do ciclo selecionado.'
                ]
            ]
        ], 422);

    }

}
