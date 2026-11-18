<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ProgressoReciboController extends Controller
{
    public function imprimir(int $id)
    {
        $escolaId = (int) auth()->user()->escola_id;

        $recibo = DB::table('progresso_recibos as r')
            ->join('progresso_pagamentos as p',
                'p.id', '=', 'r.pagamento_id')
            ->where('r.id', $id)
            ->where('r.escola_id', $escolaId)
            ->where('p.escola_id', $escolaId)
            ->where('p.estado', 'CONFIRMADO')
            ->select(
                'r.*',
                'p.aluno_id',
                'p.valor_cobrancas',
                'p.valor_aplicado',
                'p.valor_recebido',
                'p.valor_credito_utilizado',
                'p.valor_troco',
                'p.valor_credito_gerado',
                'p.confirmado_em'
            )
            ->first();

        abort_if(!$recibo, 404, 'Recibo não encontrado.');

        $escola = DB::table('escolas')
            ->where('id', $escolaId)
            ->first();

        abort_if(!$escola, 404);

        $aluno = DB::table('alunos')
            ->where('id', $recibo->aluno_id)
            ->where('escola_id', $escolaId)
            ->first();

        abort_if(!$aluno, 404);

        $itens = DB::table('progresso_pagamento_itens as pi')
            ->join('progresso_cobrancas as c',
                'c.id', '=', 'pi.cobranca_id')
            ->where('pi.pagamento_id', $recibo->pagamento_id)
            ->where('c.escola_id', $escolaId)
            ->where('c.aluno_id', $recibo->aluno_id)
            ->select(
                'c.id as cobranca_id',
                'c.descricao',
                'c.tipo',
                'pi.valor_aplicado'
            )
            ->orderBy('pi.id')
            ->get();

        $meios = DB::table('progresso_pagamento_meios as pm')
            ->join('progresso_contas_financeiras as cf',
                'cf.id', '=', 'pm.conta_financeira_id')
            ->where('pm.pagamento_id', $recibo->pagamento_id)
            ->where('cf.escola_id', $escolaId)
            ->select(
                'pm.meio',
                'pm.valor',
                'pm.troco',
                'cf.nome as conta_nome'
            )
            ->get();

        $facturas = DB::table('progresso_pagamento_itens as pi')
            ->join('progresso_factura_itens as fi',
                'fi.cobranca_id', '=', 'pi.cobranca_id')
            ->join('progresso_facturas as f',
                'f.id', '=', 'fi.factura_id')
            ->where('pi.pagamento_id', $recibo->pagamento_id)
            ->where('f.escola_id', $escolaId)
            ->where('f.aluno_id', $recibo->aluno_id)
            ->distinct()
            ->pluck('f.numero');

        return view('progresso.recibos.imprimir', compact(
            'recibo',
            'escola',
            'aluno',
            'itens',
            'meios',
            'facturas'
        ));
    }
}
