<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\ProgressoPagamentoService;
use Illuminate\Validation\ValidationException;
use App\Services\ProgressoCobrancaMultaService;

class ProgressoPagamentoController extends Controller
{

    public function create(int $alunoId, ProgressoCobrancaMultaService $multaService) {

        $escolaId = (int) auth()->user()->escola_id;

        $aluno = DB::table('alunos')
            ->where('id', $alunoId)
            ->where('escola_id', $escolaId)
            ->first();

        if (!$aluno) {
            abort(404, 'Aluno não encontrado nesta escola.');
        }

        $escola = DB::table('escolas')
            ->where('id', $escolaId)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | FACTURAS PENDENTES DO ALUNO
        |--------------------------------------------------------------------------
        */
        $facturasPendentes = DB::table('progresso_facturas as f')
            ->where('f.escola_id', $escolaId)
            ->where('f.aluno_id', $alunoId)
            ->whereIn('f.estado', ['PENDENTE', 'PARCIAL'])
            ->select('f.*')
            ->orderBy('f.data_emissao', 'desc')
            ->get();

        foreach ($facturasPendentes as $factura) {

            $valorPago = DB::table('progresso_factura_itens as fi')
                ->join(
                    'progresso_pagamento_itens as pi',
                    'pi.cobranca_id',
                    '=',
                    'fi.cobranca_id'
                )
                ->join(
                    'progresso_pagamentos as p',
                    'p.id',
                    '=',
                    'pi.pagamento_id'
                )
                ->where('fi.factura_id', $factura->id)
                ->where('p.escola_id', $escolaId)
                ->where('p.aluno_id', $alunoId)
                ->where('p.estado', 'CONFIRMADO')
                ->sum('pi.valor_aplicado');

            $factura->valor_pago = round((float) $valorPago, 2);

            $factura->saldo_pendente = max(
                0,
                round((float) $factura->total - $factura->valor_pago, 2)
            );
        }

        // Não mostrar facturas já liquidadas, mesmo que o estado
        // ainda não tenha sido actualizado.
        $facturasPendentes = $facturasPendentes->filter(fn ($factura) => $factura->saldo_pendente > 0.001)->values();

        $cobrancasFacturadas = DB::table('progresso_factura_itens as fi')
            ->join('progresso_facturas as f', 'f.id', '=', 'fi.factura_id')
            ->join('progresso_cobrancas as c', 'c.id', '=', 'fi.cobranca_id')
            ->where('f.escola_id', $escolaId)
            ->where('f.aluno_id', $alunoId)
            ->whereIn('f.estado', ['PENDENTE', 'PARCIAL'])
            ->whereIn('c.estado', ['PENDENTE', 'PARCIAL'])
            ->select(
                'c.*',
                'fi.factura_id',
                'fi.valor_total as valor_facturado'
            )
            ->orderBy('c.data_vencimento')
            ->get();

        foreach ($cobrancasFacturadas as $cobranca) {

            $pago = DB::table('progresso_pagamento_itens as pi')
                ->join('progresso_pagamentos as p', 'p.id', '=', 'pi.pagamento_id')
                ->where('pi.cobranca_id', $cobranca->id)
                ->where('p.estado', 'CONFIRMADO')
                ->sum('pi.valor_aplicado');

            $cobranca->valor_pago = (float) $pago;

            $cobranca->saldo_principal = max(
                0,
                round((float) $cobranca->valor_facturado - (float) $pago, 2)
            );

            // A factura já foi emitida; não acrescentar multa simulada.
            $cobranca->multa_simulada = 0;
        }

        $cobrancasFacturadas = $cobrancasFacturadas
            ->filter(fn ($c) => $c->saldo_principal > 0.001)
            ->values();

        $cobrancas = DB::table('progresso_cobrancas as c')
        ->where('c.escola_id', $escolaId)
        ->where('c.aluno_id', $alunoId)
        ->whereIn('c.estado', ['PENDENTE', 'PARCIAL'])
        ->whereNotExists(function ($query) {
            $query->selectRaw('1')
                ->from('progresso_factura_itens as fi')
                ->join(
                    'progresso_facturas as f',
                    'f.id',
                    '=',
                    'fi.factura_id'
                )
                ->whereColumn('fi.cobranca_id', 'c.id');
        })
        ->select('c.*')
        ->orderBy('c.data_vencimento')
        ->get();

        foreach ($cobrancas as $cobranca) {

            $pago = DB::table('progresso_pagamento_itens as pi')
                ->join(
                    'progresso_pagamentos as p',
                    'p.id',
                    '=',
                    'pi.pagamento_id'
                )
                ->where('pi.cobranca_id', $cobranca->id)
                ->where('p.estado', 'CONFIRMADO')
                ->sum('pi.valor_aplicado');

            $simulacao = $multaService->simular($cobranca->id);

            $cobranca->valor_pago = (float) $pago;
            $cobranca->multa_simulada = (float) $simulacao['multa'];

            $cobranca->saldo_principal = max(
                0,
                round(
                    (float) $cobranca->valor_total - (float) $pago,
                    2
                )
            );

            $cobranca->total_simulado = round(
                $cobranca->saldo_principal + $cobranca->multa_simulada,
                2
            );
        }

        $saldoAluno = DB::table('progresso_aluno_creditos')
            ->where('escola_id', $escolaId)
            ->where('aluno_id', $alunoId)
            ->value('saldo') ?? 0;

        $contas = DB::table('progresso_contas_financeiras')
            ->where('escola_id', $escolaId)
            ->where('activo', 1)
            ->orderBy('nome')
            ->get();

        return view('progresso.pagamentos.create', compact(
            'contas',
            'aluno',
            'escola',
            'cobrancas',
            'facturasPendentes',
            'saldoAluno',
            'contas',
            'cobrancasFacturadas'
        ));
    }
   
    public function store(Request $request, ProgressoPagamentoService $pagamentoService) {

        $escolaId = (int) auth()->user()->escola_id;
        $userId = (int) auth()->id();

        if ($escolaId <= 0) {
            return response()->json([
                'status' => 0,
                'message' => 'Utilizador sem escola associada.'
            ], 403);
        }

        $dados = $request->validate([
            'aluno_id' => [
                'required',
                'integer',
                Rule::exists('alunos', 'id')
                    ->where('escola_id', $escolaId),
            ],

            'cobrancas' => [
                'required',
                'array',
                'min:1'
            ],

            'cobrancas.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('progresso_cobrancas', 'id')
                    ->where('escola_id', $escolaId),
            ],

            'meios' => [
                'present',
                'array'
            ],

            'meios.*.conta_financeira_id' => [
                'nullable',
                'integer',
                Rule::exists('progresso_contas_financeiras', 'id')
                    ->where('escola_id', $escolaId)
                    ->where('activo', 1),
            ],

            'meios.*.meio' => [
                'nullable',
                Rule::in([
                    'NUMERARIO',
                    'DEPOSITO',
                    'TRANSFERENCIA',
                    'MPESA',
                    'EMOLA',
                    'MKESH',
                    'CARTAO'
                ])
            ],

            'meios.*.valor' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'tipo_desconto' => [
                'required',
                Rule::in(['AUTOMATICO', 'MANUAL'])
            ],

            'percentagem_desconto' => [
                'nullable',
                'numeric',
                'between:0,100'
            ],

            'destino_excedente' => [
                'nullable',
                Rule::in(['TROCO', 'SALDO'])
            ],

            'chave_idempotencia' => [
                'required',
                'string',
                'min:16',
                'max:100'
            ],

            'observacoes' => [
                'nullable',
                'string',
                'max:1000'
            ],
        ]);

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. VALIDAR COBRANÇAS DO ALUNO
            |--------------------------------------------------------------------------
            */
            $cobrancas = DB::table('progresso_cobrancas')
                ->where('escola_id', $escolaId)
                ->where('aluno_id', $dados['aluno_id'])
                ->whereIn('id', $dados['cobrancas'])
                ->whereIn('estado', ['PENDENTE', 'PARCIAL'])
                ->get();

            if (!empty($dados['factura_id'])) {

                $factura = DB::table('progresso_facturas')
                    ->where('id', $dados['factura_id'])
                    ->where('escola_id', $escolaId)
                    ->where('aluno_id', $dados['aluno_id'])
                    ->whereIn('estado', ['PENDENTE', 'PARCIAL'])
                    ->first();

                if (!$factura) {
                    throw ValidationException::withMessages([
                        'factura_id' => ['Factura inválida ou já liquidada.']
                    ]);
                }

                $idsFactura = DB::table('progresso_factura_itens')
                    ->where('factura_id', $factura->id)
                    ->pluck('cobranca_id')
                    ->map(fn ($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->all();

                $idsRecebidos = collect($dados['cobrancas'])
                    ->map(fn ($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->all();

                if ($idsFactura !== $idsRecebidos) {
                    throw ValidationException::withMessages([
                        'cobrancas' => [
                            'As cobranças enviadas não correspondem aos itens da factura.'
                        ]
                    ]);
                }
            }

            if ($cobrancas->count() !== count($dados['cobrancas'])) {
                throw ValidationException::withMessages([
                    'cobrancas' => [
                        'Uma ou mais cobranças não pertencem ao aluno ou já foram liquidadas.'
                    ]
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 2. NORMALIZAR MEIOS DE PAGAMENTO
            |--------------------------------------------------------------------------
            */
            $meios = [];

            foreach ($dados['meios'] as $meio) {

                $valor = (float) ($meio['valor'] ?? 0);

                if ($valor <= 0) {
                    continue;
                }

                if (
                    empty($meio['conta_financeira_id']) ||
                    empty($meio['meio'])
                ) {
                    throw ValidationException::withMessages([
                        'meios' => [
                            'Informe a conta financeira e o meio de pagamento para cada valor recebido.'
                        ]
                    ]);
                }

                $meios[] = [
                    'conta_financeira_id' =>
                        (int) $meio['conta_financeira_id'],

                    'meio' => $meio['meio'],

                    'valor' => number_format(
                        $valor,
                        2,
                        '.',
                        ''
                    ),

                    'referencia' => $meio['referencia'] ?? null,
                ];
            }

            $dados['meios'] = $meios;

            /*
            |--------------------------------------------------------------------------
            | 3. REGISTAR PAGAMENTO
            |--------------------------------------------------------------------------
            */

            $pagamento = $pagamentoService->pagar(
                $dados,
                $escolaId,
                $userId
            );

            if (!$pagamento || empty($pagamento->id)) {
                throw new \RuntimeException(
                    'O serviço não devolveu um pagamento válido.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 4. CONFIRMAR REGISTO NA BASE DE DADOS
            |--------------------------------------------------------------------------
            */

            $pagamentoGravado = DB::table('progresso_pagamentos')
                ->where('id', $pagamento->id)
                ->where('escola_id', $escolaId)
                ->where('aluno_id', $dados['aluno_id'])
                ->where('estado', 'CONFIRMADO')
                ->first();

            if (!$pagamentoGravado) {
                throw new \RuntimeException(
                    'O pagamento não foi encontrado como CONFIRMADO.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 5. VERIFICAR ITENS LIQUIDADOS
            |--------------------------------------------------------------------------
            */

            $totalAplicado = DB::table('progresso_pagamento_itens')
                ->where('pagamento_id', $pagamentoGravado->id)
                ->sum('valor_aplicado');

            if (
                round((float) $totalAplicado, 2) !==
                round((float) $pagamentoGravado->valor_aplicado, 2)
            ) {
                throw new \RuntimeException(
                    'Os itens do pagamento não correspondem ao valor aplicado.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 6. CONFIRMAR RECIBO
            |--------------------------------------------------------------------------
            */

            $recibo = DB::table('progresso_recibos')
                ->where('pagamento_id', $pagamentoGravado->id)
                ->where('escola_id', $escolaId)
                ->where('estado', 'EMITIDO')
                ->first();

            if (!$recibo) {
                throw new \RuntimeException(
                    'O pagamento foi confirmado, mas o recibo não foi encontrado.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 7. DEVOLVER SUCESSO
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => 1,
                'message' => 'Pagamento confirmado com sucesso.',
                'pagamento_id' => $pagamentoGravado->id,
                'recibo_id' => $recibo->id,
                'recibo_numero' => $recibo->numero,
                'recibo_url' => route(
                    'progresso.recibos.imprimir',
                    $recibo->id
                )
            ]);

        } catch (ValidationException $e) {

            throw $e;

        } catch (\Throwable $e) {

            \Log::error('Erro no pagamento do Colégio Progresso', [
                'escola_id' => $escolaId,
                'aluno_id' => $dados['aluno_id'],
                'erro' => $e->getMessage(),
                'ficheiro' => $e->getFile(),
                'linha' => $e->getLine()
            ]);

            return response()->json([
                'status' => 0,
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Não foi possível concluir o pagamento.'
            ], 500);
        }
    }


}