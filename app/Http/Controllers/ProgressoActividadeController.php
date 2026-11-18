<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProgressoActividadeController extends Controller
{
    public function index()
    {
        $escolaId = (int) auth()->user()->escola_id;

        $actividades = DB::table('progresso_atividades')
            //->where('escola_id', $escolaId)
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        return view(
            'progresso.actividades.index',
            compact('actividades')
        );
    }

    public function store(Request $request)
    {
        $escolaId = (int) auth()->user()->escola_id;

        $dados = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:150',
                Rule::unique('progresso_atividades', 'nome')
                    ->where('escola_id', $escolaId),
            ],
            'tipo' => [
                'required',
                Rule::in(['SERVICO', 'AULA_EXTRA', 'CURSO_FERIAS']),
            ],
            'periodicidade' => [
                'required',
                Rule::in(['MENSAL', 'UNICA']),
            ],
            'descricao' => ['nullable', 'string'],
            'valor_mensal' => ['required', 'numeric', 'min:0'],
            'estado' => [
                'required',
                Rule::in(['ACTIVO', 'INACTIVO']),
            ],
        ]);

        DB::table('progresso_atividades')->insert([
            'escola_id' => $escolaId,
            ...$dados,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Actividade registada com sucesso.',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $escolaId = (int) auth()->user()->escola_id;

        $actividade = DB::table('progresso_atividades')
            ->where('id', $id)
            //->where('escola_id', $escolaId)
            ->first();

        if (!$actividade) {
            return response()->json([
                'status' => 0,
                'message' => 'Actividade não encontrada.',
            ], 404);
        }

        $dados = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:150',
                Rule::unique('progresso_atividades', 'nome')
                    //->where('escola_id', $escolaId)
                    ->ignore($id),
            ],
            'tipo' => [
                'required',
                Rule::in(['SERVICO', 'AULA_EXTRA', 'CURSO_FERIAS']),
            ],
            'periodicidade' => [
                'required',
                Rule::in(['MENSAL', 'UNICA']),
            ],
            'descricao' => ['nullable', 'string'],
            'valor_mensal' => ['required', 'numeric', 'min:0'],
            'estado' => [
                'required',
                Rule::in(['ACTIVO', 'INACTIVO']),
            ],
        ]);

        DB::table('progresso_atividades')
            ->where('id', $id)
            //->where('escola_id', $escolaId)
            ->update([
                ...$dados,
                'updated_at' => now(),
            ]);

        return response()->json([
            'status' => 1,
            'message' => 'Actividade actualizada com sucesso.',
        ]);
    }
}