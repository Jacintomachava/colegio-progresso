<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProgressoValoresClassesController extends Controller
{
    public function index()
    {
        $escolaId = (int) Auth::user()->escola_id;

        $valores = DB::table('progresso_valores_classes as v')
            ->join('classes as c', 'c.id', '=', 'v.classe_id')
            ->join('ano_academico as a', 'a.id', '=', 'v.ano_academico_id')
            //->where('v.escola_id', $escolaId)
            ->select('v.*', 'c.nome as classe_nome', 'a.ano as ano_nome')
            ->orderByDesc('a.ano')
            ->orderBy('c.nome')
            ->get();

        $classes = DB::table('classes')->select('id', 'nome')->orderBy('nome')->get();
        $anos = DB::table('ano_academico')
            ->select('id', 'ano')
            ->orderByDesc('ano')
            ->get();

        return view('progresso.valores-classes.index', compact('valores', 'classes', 'anos'));
    }

    public function store(Request $request)
    {
        $escolaId = (int) Auth::user()->escola_id;
        $dados = $this->validar($request, $escolaId);
        $this->verificarDuplicado($escolaId, $dados);

        DB::table('progresso_valores_classes')->insert([
            'escola_id' => $escolaId,
            ...$dados,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 1, 'message' => 'Valores registados com sucesso.']);
    }

    public function update(Request $request, int $id)
    {
        $escolaId = (int) Auth::user()->escola_id;

        $registo = DB::table('progresso_valores_classes')
            ->where('id', $id)
            //->where('escola_id', $escolaId)
            ->first();

        if (!$registo) {
            return response()->json([
                'status' => 0,
                'message' => 'Configuração não encontrada.'
            ], 404);
        }

        $dados = $this->validar($request, $escolaId);

        $this->verificarDuplicado($escolaId, $dados, $id);

        DB::table('progresso_valores_classes')
            ->where('id', $id)
            ->where('escola_id', $escolaId)
            ->update([
                'ano_academico_id' => $dados['ano_academico_id'],
                'classe_id' => $dados['classe_id'],
                'valor_matricula' => $dados['valor_matricula'],
                'valor_renovacao' => $dados['valor_renovacao'],
                'valor_propina' => $dados['valor_propina'],
                'mes_inicio' => $dados['mes_inicio'],
                'mes_fim' => $dados['mes_fim'],
                'estado' => $dados['estado'],
                'updated_at' => now(),
            ]);

        $actualizado = DB::table('progresso_valores_classes')
            ->where('id', $id)
            ->where('escola_id', $escolaId)
            ->first();

        return response()->json([
            'status' => 1,
            'message' => 'Actualização processada.',
            'dados_recebidos' => $dados,
            'dados_anteriores' => $registo,
            'dados_actuais' => $actualizado,
        ]);
    }

    private function validar(Request $request, int $escolaId): array
    {
        return $request->validate([
            'ano_academico_id' => [
                'required', 'integer',
                Rule::exists('ano_academico', 'id'),
            ],
            'classe_id' => ['required', 'integer', Rule::exists('classes', 'id')],
            'valor_matricula' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'valor_renovacao' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'valor_propina' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'mes_inicio' => ['required', 'integer', 'between:1,12', 'lte:mes_fim'],
            'mes_fim' => ['required', 'integer', 'between:1,12', 'gte:mes_inicio'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);
    }

    private function verificarDuplicado(int $escolaId, array $dados, ?int $ignorarId = null): void
    {
        $query = DB::table('progresso_valores_classes')
            ->where('escola_id', $escolaId)
            ->where('ano_academico_id', $dados['ano_academico_id'])
            ->where('classe_id', $dados['classe_id']);

        if ($ignorarId !== null) {
            $query->where('id', '<>', $ignorarId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'classe_id' => 'Já existe uma configuração para esta classe e ano académico.',
            ]);
        }
    }
}
