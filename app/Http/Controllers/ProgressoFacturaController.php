<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ProgressoFacturaService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ProgressoFacturaController extends Controller
{
    public function index(Request $request)
    {
        $dados = $request->validate([
            'pesquisa' => 'nullable|string|max:150',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        ]);

        $escolaId = (int) auth()->user()->escola_id;

        $query = DB::table('progresso_facturas as f')
            ->join('alunos as a', function ($join) use ($escolaId) {
                $join->on('a.id', '=', 'f.aluno_id')
                    ->where('a.escola_id', '=', $escolaId);
            })
            ->leftJoin('pessoas as p', 'p.id', '=', 'a.pessoa_id')
            ->where('f.escola_id', $escolaId)
            ->select(
                'f.*',
                'a.codigo as aluno_codigo',
                'p.nome as aluno_nome'
            );

        if (!empty($dados['pesquisa'])) {
            $pesquisa = $dados['pesquisa'];

            $query->where(function ($q) use ($pesquisa) {
                $q->where('f.numero', 'like', "%{$pesquisa}%")
                    ->orWhere('a.codigo', 'like', "%{$pesquisa}%")
                    ->orWhere('p.nome', 'like', "%{$pesquisa}%");
            });
        }

        if (!empty($dados['data_inicio'])) {
            $query->whereDate(
                'f.created_at',
                '>=',
                $dados['data_inicio']
            );
        }

        if (!empty($dados['data_fim'])) {
            $query->whereDate(
                'f.created_at',
                '<=',
                $dados['data_fim']
            );
        }

        $facturas = $query
            ->orderByDesc('f.id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'progresso.facturas.index',
            compact('facturas')
        );
    }

    public function imprimir(int $id)
    {
        $escolaId = (int) auth()->user()->escola_id;

        $factura = DB::table('progresso_facturas')
            ->where('id', $id)
            ->where('escola_id', $escolaId)
            ->first();

        abort_if(!$factura, 404, 'Factura não encontrada.');

        $escola = DB::table('escolas')
            ->where('id', $escolaId)
            ->first();

        $aluno = DB::table('alunos as a')
            ->leftJoin('pessoas as p', 'p.id', '=', 'a.pessoa_id')
            ->where('a.id', $factura->aluno_id)
            ->where('a.escola_id', $escolaId)
            ->select('a.id', 'a.codigo', 'p.nome')
            ->first();

        abort_if(!$aluno, 404, 'Aluno não encontrado.');

        $itens = DB::table('progresso_factura_itens')
            ->where('factura_id', $factura->id)
            ->get();

        $cobrancasIds = $itens->pluck('cobranca_id')->filter()->unique()->values();

        $pagamentosPorCobranca = DB::table('progresso_pagamento_itens as pi')
            ->join('progresso_pagamentos as pg', 'pg.id', '=', 'pi.pagamento_id')
            ->where('pg.escola_id', $escolaId)
            ->where('pg.aluno_id', $factura->aluno_id)
            ->where('pg.estado', 'CONFIRMADO')
            ->whereIn('pi.cobranca_id', $cobrancasIds)
            ->select('pi.cobranca_id')
            ->selectRaw('SUM(pi.valor_aplicado) as total_pago')
            ->groupBy('pi.cobranca_id')
            ->pluck('total_pago', 'pi.cobranca_id');

        $cobrancas = DB::table('progresso_cobrancas')
            ->where('escola_id', $escolaId)
            ->where('aluno_id', $factura->aluno_id)
            ->whereIn('id', $cobrancasIds)
            ->get()
            ->keyBy('id');

        $totalPago = 0;

        foreach ($itens as $item) {
            $cobrancaId = $item->cobranca_id ?? null;

            if ($cobrancaId) {
                $totalPago += min(
                    (float) ($cobrancas[$cobrancaId]->valor_total ?? 0),
                    (float) ($pagamentosPorCobranca[$cobrancaId] ?? 0)
                );
            }
        }

        $totalFactura = (float) ($factura->valor_total ?? $factura->total ?? 0);
        $saldoPendente = max(0, $totalFactura - $totalPago);

        $emissor = !empty($factura->user_id)
            ? DB::table('users')->where('id', $factura->user_id)->first()
            : null;

        return view('progresso.facturas.imprimir', compact(
            'factura',
            'escola',
            'aluno',
            'itens',
            'cobrancas',
            'totalPago',
            'totalFactura',
            'saldoPendente',
            'emissor'
        ));
    }


    public function create(int $alunoId)
    {
        $escolaId = (int) auth()->user()->escola_id;

        $aluno = DB::table('alunos as a')
            ->join('pessoas as p', 'p.id', '=', 'a.pessoa_id')
            ->where('a.id', $alunoId)
            ->where('a.escola_id', $escolaId)
            ->select(
                'a.id',
                'a.codigo',
                'p.nome as aluno_nome'
            )
            ->first();

        abort_if(!$aluno, 404, 'Aluno não encontrado.');

        $ivaEscola = (float) DB::table('escolas')->where('id', $escolaId)->value('iva');

        $cobrancas = DB::table('progresso_cobrancas as c')
            ->where('c.escola_id', $escolaId)
            ->where('c.aluno_id', $alunoId)
            ->where('c.estado', 'PENDENTE')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('progresso_factura_itens as fi')
                    ->whereColumn('fi.cobranca_id', 'c.id');
            })
            ->select(
                'c.id',
                'c.tipo',
                'c.descricao',
                'c.ano',
                'c.mes',
                'c.valor_original',
                'c.valor_desconto',
                'c.valor_iva',
                'c.valor_total',
                'c.data_vencimento'
            )
            ->orderBy('c.ano')
            ->orderBy('c.mes')
            ->orderBy('c.tipo')
            ->get();

        $grupos = [
            'MATRICULA' => collect(),
            'FERIAS' => collect(),
            'PROPINA' => collect(),
            'SERVICO' => collect(),
            'EXTRA' => collect(),
            'OUTROS' => collect(),
        ];

        foreach ($cobrancas as $cobranca) {
            $grupo = match (true) {
                in_array($cobranca->tipo, ['MATRICULA', 'RENOVACAO'])
                    => 'MATRICULA',

                $cobranca->tipo === 'AULA_EXTRA' && $cobranca->mes === null
                    => 'FERIAS',

                $cobranca->tipo === 'PROPINA'
                    => 'PROPINA',

                $cobranca->tipo === 'SERVICO'
                    => 'SERVICO',

                $cobranca->tipo === 'AULA_EXTRA'
                    => 'EXTRA',

                default => 'OUTROS',
            };

            $grupos[$grupo]->push($cobranca);
        }

        return view(
            'progresso.facturas.create',
            compact('aluno', 'grupos','ivaEscola')
        );
    }


    public function store(
        Request $request,
        ProgressoFacturaService $facturaService
    ) {
        $escolaId = (int) auth()->user()->escola_id;

        $dados = $request->validate([
            'aluno_id' => [
                'required',
                'integer',
                Rule::exists('alunos', 'id')
                    ->where('escola_id', $escolaId),
            ],
            'cobrancas' => ['required', 'array', 'min:1'],
            'cobrancas.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('progresso_cobrancas', 'id')
                    ->where('escola_id', $escolaId),
            ],
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'tipo_desconto_manual' => [
                'required',
                Rule::in(['PERCENTUAL', 'VALOR']),
            ],

            'valor_desconto_manual' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'motivo_desconto' => [
                Rule::requiredIf(
                    (float) $request->input('valor_desconto_manual', 0) > 0
                ),
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        try {

            $factura = $facturaService->emitir(
                alunoId: (int) $dados['aluno_id'],
                cobrancaIds: $dados['cobrancas'],
                escolaId: $escolaId,
                observacoes: $dados['observacoes'] ?? null,
                descontoManual: [
                    'tipo' => $dados['tipo_desconto_manual'],
                    'valor' => (float) $dados['valor_desconto_manual'],
                    'motivo' => $dados['motivo_desconto'] ?? null,
                    'user_id' => (int) auth()->id(),
                ]
            );

            return response()->json([
                'status' => 1,
                'message' => 'Factura emitida com sucesso.',
                'factura_id' => $factura->id,
                'factura_numero' => $factura->numero,
                'factura_total' => $factura->total,
                'factura_url' => route(
                    'progresso.facturas.imprimir',
                    ['id' => $factura->id]
                ),
            ]);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            Log::error('Falha na emissão da factura', [
                'escola_id' => $escolaId,
                'aluno_id' => $dados['aluno_id'],
                'erro' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 0,
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Não foi possível emitir a factura.',
            ], 500);
        }
    }

}