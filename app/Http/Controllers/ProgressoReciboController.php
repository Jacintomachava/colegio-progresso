<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ProgressoReciboController extends Controller
{
    public function imprimir(int $id)
    {
        $escolaId = (int) auth()->user()->escola_id;

        /*
        |--------------------------------------------------------------------------
        | RECIBO E PAGAMENTO
        |--------------------------------------------------------------------------
        */
        $recibo = DB::table('progresso_recibos as r')
            ->join(
                'progresso_pagamentos as p',
                'p.id',
                '=',
                'r.pagamento_id'
            )
            ->where('r.id', $id)
            ->where('r.escola_id', $escolaId)
            ->where('p.escola_id', $escolaId)
            ->where('r.estado', 'EMITIDO')
            ->where('p.estado', 'CONFIRMADO')
            ->select(
                'r.*',
                'p.aluno_id',
                'p.valor_recebido',
                'p.valor_aplicado',
                'p.valor_credito_utilizado',
                'p.valor_troco',
                'p.valor_credito_gerado',
                'p.confirmado_em'
            )
            ->first();

        abort_if(
            !$recibo,
            404,
            'Recibo não encontrado.'
        );

        // Funcionário que emitiu originalmente o recibo
        $emissor = DB::table('users')
            ->where('id', $recibo->user_id)
            ->first();

        // Logótipo da escola
        $logoUrl = null;

        if (!empty($escola->logotipo)) {
            $logoUrl = asset(
                'storage/' . ltrim($escola->logotipo, '/')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ESCOLA
        |--------------------------------------------------------------------------
        */
        $escola = DB::table('escolas')
            ->where('id', $escolaId)
            ->first();

        abort_if(!$escola, 404);

        /*
        |--------------------------------------------------------------------------
        | ALUNO E PESSOA
        |--------------------------------------------------------------------------
        */
        $aluno = DB::table('alunos as a')
            ->leftJoin(
                'pessoas as pe',
                'pe.id',
                '=',
                'a.pessoa_id'
            )
            ->where('a.id', $recibo->aluno_id)
            ->where('a.escola_id', $escolaId)
            ->select(
                'a.id',
                'a.codigo',
                'pe.nome'
            )
            ->first();

        abort_if(!$aluno, 404);

        /*
        |--------------------------------------------------------------------------
        | ITENS LIQUIDADOS
        |--------------------------------------------------------------------------
        */
        $itens = DB::table('progresso_pagamento_itens as pi')
            ->join(
                'progresso_cobrancas as c',
                'c.id',
                '=',
                'pi.cobranca_id'
            )
            ->where('pi.pagamento_id', $recibo->pagamento_id)
            ->where('c.escola_id', $escolaId)
            ->where('c.aluno_id', $recibo->aluno_id)
            ->select(
                'c.descricao',
                'c.tipo',
                'pi.valor_aplicado'
            )
            ->orderBy('pi.id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | MEIOS DE PAGAMENTO
        |--------------------------------------------------------------------------
        */
        $meios = DB::table('progresso_pagamento_meios as pm')
            ->join(
                'progresso_contas_financeiras as cf',
                'cf.id',
                '=',
                'pm.conta_financeira_id'
            )
            ->where('pm.pagamento_id', $recibo->pagamento_id)
            ->where('cf.escola_id', $escolaId)
            ->select(
                'pm.meio',
                'pm.valor',
                'pm.troco',
                'cf.nome as conta_nome'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | FACTURAS ASSOCIADAS
        |--------------------------------------------------------------------------
        */
        $facturas = DB::table('progresso_pagamento_itens as pi')
            ->join(
                'progresso_factura_itens as fi',
                'fi.cobranca_id',
                '=',
                'pi.cobranca_id'
            )
            ->join(
                'progresso_facturas as f',
                'f.id',
                '=',
                'fi.factura_id'
            )
            ->where('pi.pagamento_id', $recibo->pagamento_id)
            ->where('f.escola_id', $escolaId)
            ->where('f.aluno_id', $recibo->aluno_id)
            ->distinct()
            ->pluck('f.numero');

        /*
        |--------------------------------------------------------------------------
        | VISUALIZAÇÃO PARA IMPRESSÃO
        |--------------------------------------------------------------------------
        */
        return view(
            'progresso.recibos.imprimir',
            compact(
                'recibo',
                'escola',
                'aluno',
                'itens',
                'meios',
                'facturas',
                'emissor',
                'logoUrl'
            )
        );
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $escolaId = (int) auth()->user()->escola_id;

        $pesquisa = trim((string) $request->input('pesquisa', ''));
        $estado = $request->input('estado');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        $request->validate([
            'pesquisa' => 'nullable|string|max:150',
            'estado' => 'nullable|in:EMITIDO,ANULADO',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        ]);

        $query = \Illuminate\Support\Facades\DB::table('progresso_recibos as r')
            ->join('progresso_pagamentos as pg', function ($join) use ($escolaId) {
                $join->on('pg.id', '=', 'r.pagamento_id')
                    ->where('pg.escola_id', '=', $escolaId);
            })
            ->join('alunos as a', function ($join) use ($escolaId) {
                $join->on('a.id', '=', 'pg.aluno_id')
                    ->where('a.escola_id', '=', $escolaId);
            })
            ->leftJoin('pessoas as pe', 'pe.id', '=', 'a.pessoa_id')
            ->where('r.escola_id', $escolaId)
            ->select(
                'r.id',
                'r.numero',
                'r.data_emissao',
                'r.valor',
                'r.estado',
                'r.user_id',
                'r.pagamento_id',
                'a.codigo as aluno_codigo',
                'pe.nome as aluno_nome'
            );

        if ($pesquisa !== '') {
            $query->where(function ($q) use ($pesquisa) {
                $q->where('r.numero', 'like', "%{$pesquisa}%")
                ->orWhere('a.codigo', 'like', "%{$pesquisa}%")
                ->orWhere('pe.nome', 'like', "%{$pesquisa}%");
            });
        }

        if (in_array($estado, ['EMITIDO', 'ANULADO'], true)) {
            $query->where('r.estado', $estado);
        }

        if ($dataInicio) {
            $query->whereDate('r.data_emissao', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->whereDate('r.data_emissao', '<=', $dataFim);
        }

        $recibos = $query
            ->orderByDesc('r.id')
            ->paginate(20)
            ->withQueryString();

        // Carregar os nomes dos funcionários emissores
        $idsEmissores = $recibos->getCollection()
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $utilizadores = \Illuminate\Support\Facades\DB::table('users')
            ->whereIn('id', $idsEmissores)
            ->get()
            ->keyBy('id');

        $recibos->getCollection()->transform(function ($recibo) use ($utilizadores) {
            $utilizador = $utilizadores->get($recibo->user_id);

            $recibo->emissor_nome = $utilizador
                ? ($utilizador->nome ?? $utilizador->name ?? 'Utilizador #'.$recibo->user_id)
                : '—';

            return $recibo;
        });

        return view(
            'progresso.recibos.index',
            compact('recibos')
        );
    }
    
}
