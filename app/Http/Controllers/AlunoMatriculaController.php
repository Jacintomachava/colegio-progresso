<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnoAcademico;
use App\Models\Taxa;
use App\Models\Disciplina;
use App\Models\Periodo;
use App\Models\Classe;
use App\Models\Escola;
use App\Models\Aluno;
use App\Models\MetodoPagamento;
use App\Models\Provincia;
use App\Models\Turma;
use App\Models\CoCurricular;
use App\Models\Pagamento;
use App\Models\Irmao;
use App\Models\User;
use App\Models\Medico;
use App\Models\Saude;
use App\Models\RecolherCrianca;
use App\Models\ContactoUrgencia;
use App\Models\Funcionario;
use App\Models\Matricula;
use App\Models\Meses;
use App\Models\Mensagem;
use App\Models\Mensalidade;
use App\Models\DisciplinaCurriculo;
use App\Models\Pauta;
use App\Models\Pessoa;
use App\Models\Encarregado;
use App\Models\EscolaAnterior;
use App\Models\AlunoRelacaoPessoa;
use App\Models\PlanoEducativoIndividualizado;
use App\Services\ReceitaDespesaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class AlunoMatriculaController extends Controller
{
    public function index()
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $aluno = Aluno::where('pessoa_id',Auth::user()->pessoa_id)->first();
        $matriculas = Matricula::where('aluno_id', $aluno->id)->orderBy('id', 'desc')->get();

        // Retorna a view com os dados carregados
        return view('matricula.aluno.index', [
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'matriculas'=>$matriculas,
        ]);
    }

    public function edit($alunoCodigo)
    {
        $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
        $current_month = Carbon::now()->format('m');

        $aluno = Aluno::where('codigo',$alunoCodigo)->first();
        $coCurriculares = CoCurricular::where('aluno_id', $aluno->id)->get();
        $disciplinas = Disciplina::where('co_curricular',1)->get();
        $meses = Meses::where('numero','>',$current_month)->get();
        // IDs das disciplinas já associadas ao aluno
        $coCurricularesIds = CoCurricular::where('aluno_id', $aluno->id)->pluck('disciplina_id')->toArray();

        // Retorna a view com os dados carregados
        return view('matricula.coCurricular.edit', [
            'anoAcademicoAtivo' => $anoAcademicoAtivo,
            'aluno' => $aluno,
            'coCurriculares' => $coCurricularesIds,
            'disciplinas' => $disciplinas,
            'meses' => $meses,
        ]);
    }

    public function update(Request $request)
    {

        try {

            DB::beginTransaction();

            $anoAcademicoAtivo = AnoAcademico::where('estado', 1)->first();
            $matricula1 = Matricula::where('ano_academico_id', $anoAcademicoAtivo->id)->where('aluno_id', $aluno)->first();
            $coCurriculares = $request->input('cocurriculares', []);
            $aluno = $request->input('aluno');
            $mesId = $request->input('mes');
    
            // IDs de disciplinas já associadas ao aluno no mês selecionado
            $disciplinasAtuais = CoCurricular::where('aluno_id', $aluno)
                    ->where('ano_academico_id', $anoAcademicoAtivo->id)
                    ->pluck('disciplina_id')
                    ->toArray();
    
            // Adicionar novas disciplinas
            $disciplinasParaAdicionar = array_diff($coCurriculares, $disciplinasAtuais);
            foreach ($disciplinasParaAdicionar as $disciplinaId) {
                CoCurricular::create([
                    'disciplina_id' => $disciplinaId,
                    'aluno_id' => $aluno,
                    'ano_academico_id' => $anoAcademicoAtivo->id,
                    'mes_id' => $mesId,
                    'estado' => 1, // Status inicial como ativa
                ]);

                $pauta = new Pauta();
                $pauta->ano_academico_id = $anoAcademicoAtivo->id;
                $pauta->disciplina_id = $disciplinaId;
                $pauta->classe_id = $matricula1->classe_id;
                $pauta->aluno_id = $aluno;
                $pauta->turma_id = $matricula1->turma_id;
                $pauta->escola_id = Auth::user()->escola_id;
                $pauta->save();
            }
    
            // Cancelar disciplinas removidas
            $disciplinasParaRemover = array_diff($disciplinasAtuais, $coCurriculares);
            CoCurricular::where('aluno_id', $aluno)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->whereIn('disciplina_id', $disciplinasParaRemover)
                ->update(['estado' => 0,'mes_fim_id'=>$mesId]);

            Pauta::where('aluno_id', $aluno)
                ->where('ano_academico_id', $anoAcademicoAtivo->id)
                ->whereIn('disciplina_id', $disciplinasParaRemover)
                ->update(['activo' => 0]);
    
            $current_year = Carbon::now()->year;
            $current_month = Carbon::now()->format('m');
            $year_last_two_digits = substr($current_year, -2);
    
            $aulasCoCurricularesActivos = CoCurricular::where('ano_academico_id', $anoAcademicoAtivo->id)->where('aluno_id', $aluno)->where('estado',1)->get();
            $matricula = Matricula::where('ano_academico_id', $anoAcademicoAtivo->id)->where('aluno_id', $aluno)->first();
            $mensalidade = Mensalidade::where('ano_academico_id', $anoAcademicoAtivo->id)->where('aluno_id', $aluno)->where('mes_id',$mesId)->first();
    
            $todosPagamento = Pagamento::all();
            $fatura = str_pad(count($todosPagamento)+1, 5, '0', STR_PAD_LEFT).''.$current_month.''.$year_last_two_digits;
            $codigoMatricula = str_pad(count($todosPagamento)+1, 4, '0', STR_PAD_LEFT).'.'.$year_last_two_digits;
            $recibo = str_pad(count($todosPagamento)+1, 5, '0', STR_PAD_LEFT).''.$year_last_two_digits;
    
            $referencia = null;
            $valorTotalMatricula = 0;
            $valorTotalMatriculaCoCurricular = 0;
            $valorTotalMensalidade = 0;
            $valorMensalidadeCoCurricular = 0;
            $valorMensalidadeCurricular = $mensalidade->valor;
            $valorTotal = 0;
            $coCurricular = false;
            
            if (!empty($aulasCoCurricularesActivos)) {
    
                foreach ($aulasCoCurricularesActivos as $coCurricularID) {
    
                    $disciplinaCoCurricular = Disciplina::where('id', $coCurricularID->disciplina_id)->first();
                    $valorMensalidadeCoCurricular = $valorMensalidadeCoCurricular + $disciplinaCoCurricular->propina;   
                }
            }else{
                $valorMensalidadeCoCurricular = 0;
            }
    
            ReceitaDespesaService::actualizarMensalidades($aluno, $anoAcademicoAtivo->id, $valorMensalidadeCurricular ,$valorMensalidadeCoCurricular,$mesId);

            DB::commit();
    
            return response()->json(['status' => 1, 'message' => 'Renovacao da Matricula Efectuado com Sucesso!']);
            

        } catch (\Exception $e) {

            DB::rollBack();
            //$errorMessage = DatabaseErrorHandler::handle($e);
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }

       
    }

    public function show()
    {

        $anoAtual = Carbon::now()->year;

        if (Auth::user()->funcionario==1) {
            // Lógica para professor
            $funcionario = Funcionario::where('pessoa_id', Auth::user()->pessoa_id)->first();
            $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
    
            // Retorna a view com os dados carregados
            return view('funcionario.show', [
                'funcionario' => $funcionario,
                'anoAcademicoAtivo' => $anoAcademicoAtivo,
            ]);
        }
    
        if (Auth::user()->funcionario==0) {
            // Lógica para aluno
            $aluno = Aluno::where('pessoa_id',Auth::user()->pessoa_id)->first();
            $anoAcademico = AnoAcademico::where('estado', true)->first();

            $matriculas = Matricula::where('aluno_id',$aluno->id)->get();
            $matriculaActual = Matricula::where('aluno_id',$aluno->id)->where('ano_academico_id',$anoAcademico->id)->first();

            $medico = Medico::where('aluno_id',$aluno->id)->first();
            $encarregados = Encarregado::where('aluno_id',$aluno->id)->get();
            $irmaos = Irmao::where('aluno_id',$aluno->id)->get();
            $contactosUrgencias = ContactoUrgencia::where('aluno_id',$aluno->id)->get();
            $recolherCriancas = RecolherCrianca::where('aluno_id',$aluno->id)->get();
            $saude = Saude::where('aluno_id', $aluno->id)->first();
            $anoAcademicoAtivo = AnoAcademico::where('estado', true)->first();
            $coCurriculares = CoCurricular::where('aluno_id',$aluno->id)->get();
            $plano = PlanoEducativoIndividualizado::where('aluno_id',$aluno->id)->first();
            $pessoasContactar = AlunoRelacaoPessoa::where('aluno_id',$aluno->id)->get();

 
            $telefoneEncarregado = AlunoRelacaoPessoa::where('aluno_id', $aluno->id)
                                ->where('relacao_id', 1)
                                ->with('pessoa') // já carrega a relação
                                ->first();

            $telefone = substr($telefoneEncarregado->pessoa->telefone, -9);

            //Mensagem Enviado ao Encarregado desse aluno
            $mensagens = Mensagem::where('telefone',$telefone)->whereYear('created_at', $anoAtual)->orderBy('created_at', 'desc')->get();
            $mensalidades = Mensalidade::where('aluno_id', $aluno->id)->where('pagou',0)->get();
            $classes = Classe::all();

            // Retorna a view com os dados carregados
            return view('aluno.show', [
                'aluno' => $aluno,
                'matriculas' => $matriculas,
                'matriculaActual' => $matriculaActual,
                'medico' => $medico,
                'encarregados'=>$encarregados,
                'irmaos' =>$irmaos,
                'contactosUrgencias' => $contactosUrgencias,
                'recolherCriancas' => $recolherCriancas,
                'anoAcademicoAtivo' => $anoAcademicoAtivo,
                'saude' => $saude,
                'coCurriculares' => $coCurriculares,
                'plano' => $plano,
                'pessoasContactar' => $pessoasContactar,
                'mensagens' => $mensagens,
                'mensalidades' => $mensalidades,
                'classes' => $classes,
            ]);  
        }
    }
}
