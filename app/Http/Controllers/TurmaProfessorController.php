<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnoAcademico;
use App\Models\DisciplinaCurriculo;
use App\Models\Turma;
use App\Models\ProfessorTurma;
use App\Models\Professor;
use App\Models\ProfessorTurmaDisciplina;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TurmaProfessorController extends Controller
{
    public function index()
    {
        $professor = Professor::where('pessoa_id',Auth::user()->pessoa_id)->first();
        //$professorTurmas = ProfessorTurma::where('professor_id',$professor->id)->where('turma.escola_id',Auth::user()->escola_id)->orderBy('id', 'desc')->get();
        /*$professorTurmas = ProfessorTurma::where('professor_id', $professor->id)
            ->whereHas('turma', function ($query) {
                $query->where('escola_id', Auth::user()->escola_id);
            })
            ->orderBy('id', 'desc')
            ->orderBy('ano_academico_id', 'desc')
            ->get();*/

        $professorTurmas = ProfessorTurma::with(['turma.classe', 'turma.escola'])
            ->join('turmas', 'turmas.id', '=', 'professor_turma.turma_id')
            ->where('professor_turma.professor_id', $professor->id)
            ->where('turmas.escola_id', Auth::user()->escola_id)
            ->select('professor_turma.*')
            ->orderBy('ano_academico_id', 'desc')
            ->orderBy('turmas.classe_id', 'asc')
            ->get();

        $anoAtivo = AnoAcademico::where('estado', 1)->first();

         // Retorna a view com os dados carregados
         return view('professor.turma.index', [
            'professorTurmas' => $professorTurmas,
            'anoAtivo' => $anoAtivo,
        ]);

    }

    public function notas()
    {
        $professor = Professor::where('pessoa_id',Auth::user()->pessoa_id)->first();
        //$professorTurmas = ProfessorTurma::where('professor_id',$professor->id)->where('turma.escola_id',Auth::user()->escola_id)->orderBy('id', 'desc')->get();
        /*$professorTurmas = ProfessorTurma::where('professor_id', $professor->id)
            ->whereHas('turma', function ($query) {
                $query->where('escola_id', Auth::user()->escola_id);
            })
            ->orderBy('id', 'desc')
            ->orderBy('ano_academico_id', 'desc')
            ->get();*/

        $anoAtivo = AnoAcademico::where('estado', 1)->first();

        $professorTurmas = ProfessorTurma::with(['turma.classe', 'turma.escola'])
            ->join('turmas', 'turmas.id', '=', 'professor_turma.turma_id')
            ->where('professor_turma.professor_id', $professor->id)
            ->where('turmas.escola_id', Auth::user()->escola_id)
            ->where('professor_turma.ano_academico_id', $anoAtivo->id)
            ->select('professor_turma.*')
            ->orderBy('turmas.classe_id', 'asc')
            ->get();



         // Retorna a view com os dados carregados
         return view('professor.turma.index', [
            'professorTurmas' => $professorTurmas,
            'anoAtivo' => $anoAtivo,
        ]);

    }

    public function arquivoPauta($ano)
    {
        $professor = Professor::where('pessoa_id',Auth::user()->pessoa_id)->first();
        $anoAtivo = AnoAcademico::where('ano', $ano)->first();

        $professorTurmas = ProfessorTurma::with(['turma.classe', 'turma.escola'])
            ->join('turmas', 'turmas.id', '=', 'professor_turma.turma_id')
            ->where('professor_turma.professor_id', $professor->id)
            ->where('turmas.escola_id', Auth::user()->escola_id)
            ->where('professor_turma.ano_academico_id', $anoAtivo->id)
            ->select('professor_turma.*')
            ->orderBy('turmas.classe_id', 'asc')
            ->get();

         // Retorna a view com os dados carregados
         return view('professor.turma.arquivo', [
            'professorTurmas' => $professorTurmas,
            'anoAtivo' => $anoAtivo,
        ]);
    }

}
