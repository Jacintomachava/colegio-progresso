<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ProgressoPrecarioPdfController extends Controller
{
    public function gerar()
    {
        $escolaId = (int) auth()->user()->escola_id;

        $anoAcademicoId = 4;

        $escola = DB::table('escolas')
            ->where('id', $escolaId)
            ->first();

        abort_unless($escola, 404, 'Escola não encontrada.');

        $ano = DB::table('ano_academico')
            ->where('id', $anoAcademicoId)
            //->where('escola_id', $escolaId)
            ->first();

        abort_unless($ano, 404, 'Ano académico não encontrado.');

        /*
        |--------------------------------------------------------------------------
        | PREÇOS POR CLASSE
        |--------------------------------------------------------------------------
        | Substituir esta consulta pela mesma usada no index()
        | do teu Controller de Valores por Classe.
        |--------------------------------------------------------------------------
        */
        $valores = DB::table('progresso_valores_classes as v')
            ->join('classes as c', 'c.id', '=', 'v.classe_id')
            //->where('v.escola_id', $escolaId)
            //->where('v.ano_academico_id', $anoAcademicoId)
            ->where('v.estado', 'ACTIVO')
            ->select(
                'c.nome as classe_nome',
                'v.valor_matricula',
                'v.valor_renovacao',
                'v.valor_propina'
            )
            ->orderBy('c.id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ACTIVIDADES
        |--------------------------------------------------------------------------
        */
        $actividades = DB::table('progresso_atividades')
            //->where('escola_id', $escolaId)
            ->where('estado', 'ACTIVO')
            ->orderByRaw("
                FIELD(tipo, 'SERVICO', 'AULA_EXTRA', 'CURSO_FERIAS')
            ")
            ->orderBy('nome')
            ->get();

        $pdf = Pdf::loadView(
            'progresso.precario.pdf',
            compact(
                'escola',
                'ano',
                'valores',
                'actividades'
            )
        )->setPaper('a4', 'portrait');

        return $pdf->stream(
            'precario-escolar-' . $ano->ano . '.pdf'
        );
    }
}