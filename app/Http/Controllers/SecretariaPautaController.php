<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Disciplina;
use App\Models\AnoAcademico;
use App\Models\ProfessorTurma;
use App\Models\Mensalidade;
use App\Models\Aluno;
use App\Models\Pauta;
use App\Models\Escola;
use App\Models\Matricula;
use App\Services\PautaFinalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SecretariaPautaController extends Controller
{
    public function index()
    {
        $alunos = Aluno::orderBy('id', 'desc')->get();

        // Retorna a view com os dados carregados
        return view('secretaria.pauta.index', [
            'alunos' => $alunos,
        ]);

    }

    public function show($alunoID)
    {
        $anosAcademicos = AnoAcademico::all(); // Buscar anos acadêmicos
        $anoAtivo = AnoAcademico::where('estado', 'ativo')->first();
        $pautas = Pauta::where('aluno_id', $alunoID)->where('ano_academico_id',$anoAtivo->id)->get();
        $aluno = Aluno::where('id', $alunoID)->first();

        // Retorna a view com os dados carregados
        return view('secretaria.pauta.show', [
            'pautas' => $pautas,
            'aluno' => $aluno,
            'anosAcademicos' => $anosAcademicos,
            'anoAtivo' => $anoAtivo,
        ]);
    }

    public function buscar(Request $request)
    {

        $pautas = Pauta::where('aluno_id', $request->input('aluno'))->where('ano_academico_id',$request->input('ano'))->get();
        $aluno = Aluno::where('id', $request->input('aluno'))->first();
        $anoAtivo = AnoAcademico::where('id', $request->input('ano'))->first();

        $anosIds = Matricula::where('aluno_id', $aluno->id)->pluck('ano_academico_id');
        $anosAcademicos = AnoAcademico::whereIn('id', $anosIds)->orderBy('id', 'desc')->get();

        $mensalidades = Mensalidade::where('aluno_id', $aluno->id)->where('pagou',0)->where('multa','>',0)->get();
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

    public function pautaAlunoPDF($alunoCodigo,$ano)
    {

        $aluno = Aluno::where('codigo', $alunoCodigo)->first();
        $ano1 = AnoAcademico::where('ano',$ano)->first();
        $escola = Escola::where('id',Auth::user()->escola_id)->first();
        $pautas = Pauta::where('aluno_id', $aluno->id)->where('ano_academico_id',$ano1->id)->get();
        $anoAtivo = AnoAcademico::where('id', $ano1->id)->first();
        $anosAcademicos = AnoAcademico::all(); // Buscar anos acadêmicos
        $mensalidades = Mensalidade::where('aluno_id', $aluno->id)->where('pagou',0)->where('multa','>',0)->get();
        $matricula = Matricula::where('aluno_id', $aluno->id)->where('ano_academico_id', $anoAtivo->id)->first();

        $pdf = \PDF::loadView('turma.professor.notas.pdf.pautaAluno', [
            'pautas' => $pautas,
            'aluno' => $aluno,
            'escola' => $escola,
            'anosAcademicos' => $anosAcademicos,
            'anoAtivo' => $anoAtivo,
            'mensalidades' => $mensalidades,
            'matricula' => $matricula,
        ])->setPaper('a4', 'landscape'); // Define o papel como A4 e a orientação como paisagem (landscape)

        $fileName = 'Pauta de Ano -'.$ano;

        return $pdf->stream($fileName.'.pdf');

    }

    public function finalizarPauta($turmaID)
    {
        try {

            DB::beginTransaction();

            ProfessorTurma::where('turma_id', $turmaID)
            ->update(['pauta_finalizada' => 1]);

            PautaFinalService::pautaFinalManual($turmaID);

            DB::commit();
                            
            return response()->json([
                'status' => 'success',
                'message' => 'Ano acadêmico ativado com sucesso!'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro ao Finalizar a Pauta da Turma.'.$e->getMessage()
            ]);
        }
    }
}
