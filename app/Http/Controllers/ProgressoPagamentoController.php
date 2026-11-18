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

        $cobrancas = DB::table('progresso_cobrancas')
            ->where('escola_id', $escolaId)
            ->where('aluno_id', $alunoId)
            ->whereIn('estado', ['PENDENTE', 'PARCIAL'])
            ->orderBy('data_vencimento')
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
            'aluno',
            'escola',
            'cobrancas',
            'saldoAluno',
            'contas'
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