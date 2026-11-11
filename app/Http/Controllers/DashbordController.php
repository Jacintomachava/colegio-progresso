<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aluno;
use App\Models\AnoAcademico;
use App\Models\Funcionario;
use App\Models\Matricula;
use App\Models\Mensagem;
use App\Models\Mensalidade;
use App\Models\Meses;
use App\Models\Escola;
use App\Models\Professor;
use App\Models\ReclamacaoElogioPedido;
use App\Models\SaldoSMS;
use App\Models\Turma;
use App\Services\EmailService;
use App\Services\PautaFinalService;
use App\Services\SMSService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashbordController extends Controller
{
    public function index()
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $anoAcademicoAtivoID = $anoAcademicoAtivo->id;
        $escola = Auth::user()->escola_id;
        $data = Carbon::now();

        // Numero Total
        $totalAlunos = Matricula::where('desistiu', 0)->where('escola_id', $escola)->where('ano_academico_id', $anoAcademicoAtivo->id)->where('pagou', 1)->count(); //Aluno::where('escola_id', Auth::user()->escola_id)->where('desistiu', 0)->count();
        $totalFuncionario = Funcionario::where('escola_id', Auth::user()->escola_id)->count();
        $totalProfessor = Professor::where('escola_id', Auth::user()->escola_id)->count();
        $totalTurma = Turma::where('escola_id', Auth::user()->escola_id)->where('ano_academico_id', $anoAcademicoAtivo->id)->where('matriculado', '>', 0)->count();
        $saldoSMS = SaldoSMS::where('codigo', 'saldo')->first();
        $mensagem = Mensagem::whereYear('created_at', Carbon::now()->year)->count();

        // Grafico de Barras de Mensalidade
        $meses = Meses::orderBy('id')->get();

        $labelsMeses = [];
        $mensalidadesPagas = [];
        $mensalidadesPendentes = [];

        foreach ($meses as $mes) {
            $labelsMeses[] = $mes->nome;

            $quantidadePagas = Mensalidade::where('mes_id', $mes->id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->where('escola_id', $escola)
                ->where('pagou', true)
                ->where('disistiu', 0)
                ->count();

            $quantidadePendentes = Mensalidade::where('mes_id', $mes->id)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->where('escola_id', $escola)
                ->where('pagou', false)
                ->where('disistiu', 0)
                ->count();

            $mensalidadesPagas[] = $quantidadePagas;
            $mensalidadesPendentes[] = $quantidadePendentes;
        }

        // Grafico Pizza das Matriculas
        $dadosGraficoMatriculas = Turma::join('classes', 'turmas.classe_id', '=', 'classes.id')
            ->selectRaw('CONCAT(classes.nome) as categoria, SUM(turmas.matriculado) as total')
            ->where('turmas.escola_id', Auth::user()->escola_id)
            ->where('turmas.ano_academico_id', $anoAcademicoAtivo->id)
            ->where('turmas.matriculado', '>', 0)
            ->groupBy('categoria')
            ->get();

        // Grafico Matricula pagas vs Pendentes
        $numeroMatriculaPendentes = Matricula::where('desistiu', 0)->where('pagou', 0)->where('ano_academico_id', $anoAcademicoAtivo->id)->where('escola_id', $escola)->count();
        $numeroMatriculaPagas = Matricula::where('desistiu', 0)->where('escola_id', $escola)->where('ano_academico_id', $anoAcademicoAtivo->id)->where('pagou', 1)->count();

        // SMSService::notificacaoSMS();
        // PautaFinalService::pautaFinalAutomatico();
        // EmailService::notificacaoEmail();

        // Fazer Passou Chumbou de forma Automatico
        if ($anoAcademicoAtivo->pauta_final_automatico == 1 && $anoAcademicoAtivo->data_fim_lancamento_pauta > $data) {
            PautaFinalService::pautaFinalAutomatico();
        }

        $reclamacoes = ReclamacaoElogioPedido::where(function ($query) {
            $query->where('tipo', 'Reclamação')
                ->orWhere('tipo', 'Pedido');
        })
                        ->where('estado', 'Pendente')
                        ->latest()
                        ->take(10)
                        ->get();

        $total = ReclamacaoElogioPedido::where(function ($query) {
            $query->where('tipo', 'Reclamação')
                    ->orWhere('tipo', 'Pedido');
        })
                            ->where('estado', 'Pendente')
                            ->count();

        // Reclamacoes
        session([
            'reclamacoes' => $reclamacoes,
            'totalReclamacao' => $total,
        ]);

        // Retorna a view com os dados carregados
        return view('home.index', [
            'totalAlunos' => $totalAlunos,
            'totalFuncionario' => $totalFuncionario,
            'totalProfessor' => $totalProfessor,
            'totalTurma' => $totalTurma,
            'saldoSMS' => $saldoSMS,
            'mensagem' => $mensagem,
            // Grafico Barra de mensalidade
            'labelsMeses' => $labelsMeses,
            'mensalidadesPagas' => $mensalidadesPagas,
            'mensalidadesPendentes' => $mensalidadesPendentes,
            // Grafico Classes
            'dadosGraficoMatriculas' => $dadosGraficoMatriculas,
            // Matriculas Pagas vs Pendentes
            'numeroMatriculaPendentes' => $numeroMatriculaPendentes,
            'numeroMatriculaPagas' => $numeroMatriculaPagas,
        ]);
    }

    public function detalhesMensalidades(Request $request)
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $escola = Escola::where('id', Auth::user()->escola_id)->first();

        $mes = $request->mes;
        $tipo = $request->tipo;
        $pagamento = null;

        $query = Mensalidade::query();

        $query->where('mes_id', $this->mes($mes))->where('ano_academico_id',$anoAcademicoAtivo->id)->where('disistiu',0)->where('escola_id', Auth::user()->escola_id);

        if ($tipo == 'Pagas') {
            $query->where('pagou', 1);
        } else {
            $query->where('pagou', 0);
        }

        $dados = $query->with('aluno')
            ->get()
            ->map(function ($item) {

                return [
                    'aluno' => $item->aluno->pessoa->nome,
                    'mes' => $item->mes->nome,
                    'valor' => $item->pagamento->valor,
                    'pagamento' => $item->pagamento->referencia,
                    'multa' => $item->multa,
                    'classe' => $item->aluno->classe->nome,
                ];
            });

        return response()->json($dados);
    }

    public function imprimirMensalidades(Request $request)
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $escola = Escola::where('id', Auth::user()->escola_id)->first();

        $mes = $request->mes;
        $tipo = $request->tipo;
        $estado = 2;

        if ($tipo == 'Pagas') {
            $estado = 1;
        } else {
            $estado = 0;
        }

        $mesSelecionado = Meses::where('id',$this->mes($mes))->first();
        $today = Carbon::now();

        $mensalidadesPorMes = Mensalidade::with([
            'mes',
            'aluno.pessoa',
            'pagamento',
            'aluno.matriculas.classe' // carrega as classes
        ])
        ->where('escola_id', Auth::user()->escola_id)
        ->where('ano_academico_id', $anoAcademicoAtivo->id)
        ->where('disistiu', 0)
        ->where('pagou',$estado)
        ->where('mes_id', $mesSelecionado->id)
        ->orderBy('aluno_id')
        ->get()
        ->groupBy(function($mensalidade) use ($anoAcademicoAtivo) {
            // pega a classe da matrícula do ano ativo
            $matricula = $mensalidade->aluno->matriculas->where('ano_academico_id', $anoAcademicoAtivo->id)->first();
            return $matricula ? $matricula->classe->nome : 'Sem Classe';
        });


        $pdf = \PDF::loadView('home.relatorio.propinas', [
            'escola' => $escola,
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'mesSelecionado' => $mesSelecionado,
            'today' => $today,
            'mensalidadesPorMes' => $mensalidadesPorMes,
        ])->setPaper('a4', 'Portrait');

        $fikeName = 'Mensalidades-'.$tipo.''.$mes.''.$anoAcademicoAtivo->ano;

        return $pdf->stream($fikeName.'.pdf');

    }

    public function mes($mes){

        $numeroMes = 1;

        if($mes=='Fevereiro'){
          $numeroMes = 1;
        }elseif($mes=='Março'){
          $numeroMes = 2;
        }elseif($mes=='Abril'){
          $numeroMes = 3;
        }elseif($mes=='Maio'){
          $numeroMes = 4;
        }elseif($mes=='Junho'){
          $numeroMes = 5;
        }elseif($mes=='Julho'){
          $numeroMes = 6;
        }elseif($mes=='Agosto'){
          $numeroMes = 7;
        }elseif($mes=='Setembro'){
          $numeroMes = 8;
        }elseif($mes=='Outubro'){
          $numeroMes = 9;
        }elseif($mes=='Novembro'){
          $numeroMes = 10;
        }


        return $numeroMes;
    }
}
