<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgressoPagamentoService
{
    private function erro(string $mensagem): never
    {
        throw ValidationException::withMessages(['pagamento' => $mensagem]);
    }

    private function centavos($valor): int
    {
        $valor = trim((string) $valor);
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/D', $valor)) {
            $this->erro('Valor monetário inválido: ' . $valor);
        }
        [$inteiro, $decimais] = array_pad(explode('.', $valor, 2), 2, '');
        if (strlen($inteiro) > 13) {
            $this->erro('Valor monetário demasiado elevado.');
        }
        return (int) $inteiro * 100 + (int) str_pad($decimais, 2, '0');
    }

    private function decimal(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }

    /**
     * Regista pagamento e recibo numa única transacção.
     * Entrada: aluno_id, cobrancas (IDs), meios (conta_financeira_id, meio, valor),
     * chave_idempotencia, destino_excedente (TROCO|SALDO), observacoes.
     * Não aceita descontos ou multas ainda não facturados.
     */
    public function pagar(array $dados, int $escolaId, int $userId): object
    {
        if ($escolaId <= 0 || $userId <= 0 || empty($dados['aluno_id']) ||
            empty($dados['cobrancas']) || !is_array($dados['cobrancas']) ||
            !isset($dados['meios']) || !is_array($dados['meios']) ||
            empty($dados['chave_idempotencia'])) {
            $this->erro('Dados obrigatórios do pagamento em falta.');
        }

        $chave = trim((string) $dados['chave_idempotencia']);
        if (strlen($chave) > 100 || strlen($chave) < 16) {
            $this->erro('Chave de idempotência inválida.');
        }

        if (($dados['tipo_desconto'] ?? 'AUTOMATICO') !== 'AUTOMATICO') {
            $this->erro('Desconto manual requer rectificação e emissão da factura antes do pagamento.');
        }

        $destino = $dados['destino_excedente'] ?? 'TROCO';
        if (!in_array($destino, ['TROCO', 'SALDO'], true)) {
            $this->erro('Destino do excedente inválido.');
        }

        $ids = [];
        foreach ($dados['cobrancas'] as $item) {
            if (!is_scalar($item) || !ctype_digit((string) $item) || (int) $item <= 0) {
                $this->erro('Identificador de cobrança inválido.');
            }
            $ids[] = (int) $item;
        }
        if (count($ids) !== count(array_unique($ids))) {
            $this->erro('Existem cobranças repetidas.');
        }
        sort($ids);

        return DB::transaction(function () use ($dados, $escolaId, $userId, $chave, $destino, $ids) {
            // Bloquear o aluno serializa pagamentos concorrentes deste aluno.
            $aluno = DB::table('alunos')
                ->where('id', (int) $dados['aluno_id'])
                ->where('escola_id', $escolaId)
                ->lockForUpdate()->first();
            if (!$aluno) {
                $this->erro('Aluno não pertence à escola seleccionada.');
            }

            $anterior = DB::table('progresso_pagamentos')
                ->where('chave_idempotencia', $chave)->first();
            if ($anterior) {
                if ((int) $anterior->escola_id !== $escolaId ||
                    (int) $anterior->aluno_id !== (int) $aluno->id ||
                    $anterior->estado !== 'CONFIRMADO') {
                    $this->erro('Chave de pagamento já utilizada.');
                }
                return $anterior;
            }

            $cobrancas = DB::table('progresso_cobrancas')
                ->whereIn('id', $ids)->where('escola_id', $escolaId)
                ->where('aluno_id', $aluno->id)
                ->orderBy('id')->lockForUpdate()->get();
            if ($cobrancas->count() !== count($ids)) {
                $this->erro('Uma ou mais cobranças são inválidas.');
            }

            /*
            |--------------------------------------------------------------------------
            | EMISSÃO AUTOMÁTICA DE FACTURA NO PAGAMENTO DIRECTO
            |--------------------------------------------------------------------------
            | Executado dentro da transacção de pagamento.
            | Só são facturadas cobranças que ainda não possuem factura.
            |--------------------------------------------------------------------------
            */
            $idsSemFactura = [];

            foreach ($cobrancas as $cobranca) {

                if (!in_array($cobranca->estado, ['PENDENTE', 'PARCIAL'], true)) {
                    $this->erro(
                        "Cobrança {$cobranca->id} não está disponível."
                    );
                }

                if ($cobranca->tipo === 'MULTA') {
                    $this->erro(
                        'Liquidação de multas ainda não activada.'
                    );
                }

                $facturaExistente = DB::table('progresso_factura_itens')
                    ->where('cobranca_id', $cobranca->id)
                    ->first();

                if ($facturaExistente) {

                    $factura = DB::table('progresso_facturas')
                        ->where('id', $facturaExistente->factura_id)
                        ->where('escola_id', $escolaId)
                        ->where('aluno_id', $aluno->id)
                        ->first();

                    if (
                        !$factura ||
                        !in_array($factura->estado, ['PENDENTE', 'PARCIAL'], true)
                    ) {
                        $this->erro(
                            "Cobrança {$cobranca->id} associada a uma factura inválida ou anulada."
                        );
                    }

                    continue;
                }

                if ($cobranca->tipo === 'PROPINA') {

                    $simulacao = app(
                        ProgressoCobrancaMultaService::class
                    )->simular((int) $cobranca->id);

                    if ($this->centavos($simulacao['multa'] ?? '0') > 0) {
                        $this->erro(
                            "Cobrança {$cobranca->id} possui multa pendente de facturação."
                        );
                    }
                }

                // Não emitir automaticamente uma cobrança já parcialmente paga.
                if ($cobranca->estado !== 'PENDENTE') {
                    $this->erro(
                        "Cobrança {$cobranca->id} parcialmente paga sem factura. Requer reconciliação."
                    );
                }

                $idsSemFactura[] = (int) $cobranca->id;
            }

            /*
            |--------------------------------------------------------------------------
            | EMITIR FACTURA APENAS QUANDO NECESSÁRIO
            |--------------------------------------------------------------------------
            */
            if (!empty($idsSemFactura)) {

                $facturaNova = app(ProgressoFacturaService::class)->emitir(
                    (int) $aluno->id,
                    $idsSemFactura,
                    $escolaId,
                    'Factura emitida automaticamente no pagamento directo'
                );

                if (!$facturaNova || empty($facturaNova->id)) {
                    $this->erro(
                        'Não foi possível emitir a factura automaticamente.'
                    );
                }
            }

            $pendencias = [];
            $facturasIds = [];

            foreach ($cobrancas as $cobranca) {
                if (!in_array($cobranca->estado, ['PENDENTE', 'PARCIAL'], true)) {
                    $this->erro("Cobrança {$cobranca->id} não está disponível.");
                }
                if ($cobranca->tipo === 'MULTA') {
                    $this->erro('Liquidação de multas ainda não activada.');
                }

                $facturaItem = DB::table('progresso_factura_itens as fi')
                    ->join('progresso_facturas as f', 'f.id', '=', 'fi.factura_id')
                    ->where('fi.cobranca_id', $cobranca->id)
                    ->where('f.escola_id', $escolaId)
                    ->where('f.aluno_id', $aluno->id)
                    ->whereIn('f.estado', ['PENDENTE', 'PARCIAL'])
                    ->select('fi.*', 'f.total as factura_total')
                    ->first();
                if (!$facturaItem) {
                    $this->erro("Cobrança {$cobranca->id} sem factura válida.");
                }

                $somaItens = DB::table('progresso_factura_itens')
                    ->where('factura_id', $facturaItem->factura_id)
                    ->sum('valor_total');
                if ($this->centavos($somaItens) !== $this->centavos($facturaItem->factura_total)) {
                    $this->erro("Factura {$facturaItem->factura_id} requer reconciliação de IVA/desconto.");
                }

                $pago = DB::table('progresso_pagamento_itens as pi')
                    ->join('progresso_pagamentos as p', 'p.id', '=', 'pi.pagamento_id')
                    ->where('pi.cobranca_id', $cobranca->id)
                    ->where('p.estado', 'CONFIRMADO')
                    ->sum('pi.valor_aplicado');
                $pendente = $this->centavos($facturaItem->valor_total) - $this->centavos($pago);
                if ($pendente <= 0) {
                    $this->erro("Cobrança {$cobranca->id} já liquidada.");
                }
                $pendencias[] = [
                    'id' => (int) $cobranca->id,
                    'vencimento' => $cobranca->data_vencimento ?? '9999-12-31',
                    'pendente' => $pendente,
                ];
                $facturasIds[] = (int) $facturaItem->factura_id;
            }

            usort($pendencias, static fn ($a, $b) =>
                strcmp((string) $a['vencimento'], (string) $b['vencimento'])
                ?: ($a['id'] <=> $b['id']));
            $totalPendente = array_sum(array_column($pendencias, 'pendente'));

            DB::table('progresso_aluno_creditos')->insertOrIgnore([
                'escola_id' => $escolaId, 'aluno_id' => $aluno->id,
                'saldo' => '0.00', 'created_at' => now(), 'updated_at' => now(),
            ]);
            $credito = DB::table('progresso_aluno_creditos')
                ->where('escola_id', $escolaId)->where('aluno_id', $aluno->id)
                ->lockForUpdate()->first();
            if (!$credito) {
                $this->erro('Não foi possível obter o saldo do aluno.');
            }
            $saldoAnterior = $this->centavos($credito->saldo);
            $creditoUsado = min($saldoAnterior, $totalPendente);

            $meios = [];
            $totalRecebido = 0;
            $numerario = 0;
            $permitidos = [
                'CAIXA' => ['NUMERARIO'],
                'BANCO' => ['DEPOSITO', 'TRANSFERENCIA', 'CARTAO'],
                'CARTEIRA_MOVEL' => ['MPESA', 'EMOLA', 'MKESH'],
            ];
            foreach ($dados['meios'] as $meio) {
                $valor = $this->centavos($meio['valor'] ?? '0');
                if ($valor === 0) {
                    // Linha vazia do formulário: ignorar se não foi preenchida.
                    if (empty($meio['conta_financeira_id']) && empty($meio['meio'])) {
                        continue;
                    }
                    $this->erro('O valor de cada meio preenchido deve ser positivo.');
                }
                $conta = DB::table('progresso_contas_financeiras')
                    ->where('id', $meio['conta_financeira_id'] ?? null)
                    ->where('escola_id', $escolaId)->where('activo', 1)->first();
                if (!$conta) {
                    $this->erro('Conta financeira inválida.');
                }
                $tipo = (string) ($meio['meio'] ?? '');
                if (!in_array($tipo, $permitidos[$conta->tipo] ?? [], true)) {
                    $this->erro('Meio incompatível com a conta financeira.');
                }
                $meios[] = [
                    'conta_financeira_id' => $conta->id,
                    'meio' => $tipo,
                    'valor' => $valor,
                    'referencia' => !empty($meio['referencia']) ? $meio['referencia'] : null,
                ];
                $totalRecebido += $valor;
                if ($tipo === 'NUMERARIO') {
                    $numerario += $valor;
                }
            }

            // Permite pagamento parcial, mas não um pagamento sem liquidação.
            $totalAplicado = min($totalPendente, $creditoUsado + $totalRecebido);
            if ($totalAplicado <= 0) {
                $this->erro('Não existe valor disponível para liquidar as cobranças.');
            }
            $excedente = max(0, $totalRecebido + $creditoUsado - $totalAplicado);
            $troco = $destino === 'TROCO' ? $excedente : 0;
            $creditoGerado = $destino === 'SALDO' ? $excedente : 0;
            if ($troco > $numerario) {
                $this->erro('Troco superior ao numerário recebido. Escolha guardar no saldo.');
            }

            $pagamentoId = DB::table('progresso_pagamentos')->insertGetId([
                'escola_id' => $escolaId,
                'aluno_id' => $aluno->id,
                'valor_cobrancas' => $this->decimal($totalAplicado),
                'valor_aplicado' => $this->decimal($totalAplicado),
                'valor_recebido' => $this->decimal($totalRecebido),
                'valor_credito_utilizado' => $this->decimal($creditoUsado),
                'valor_troco' => $this->decimal($troco),
                'valor_credito_gerado' => $this->decimal($creditoGerado),
                'estado' => 'CONFIRMADO',
                'chave_idempotencia' => $chave,
                'observacoes' => $dados['observacoes'] ?? null,
                'user_id' => $userId,
                'confirmado_em' => now(),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            $restante = $totalAplicado;
            $idsPagos = [];
            foreach ($pendencias as $pendencia) {
                $valor = min($restante, $pendencia['pendente']);
                if ($valor <= 0) {
                    break;
                }
                DB::table('progresso_pagamento_itens')->insert([
                    'pagamento_id' => $pagamentoId,
                    'cobranca_id' => $pendencia['id'],
                    'valor_aplicado' => $this->decimal($valor),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $idsPagos[] = $pendencia['id'];
                $restante -= $valor;
            }

            $trocoRestante = $troco;
            foreach ($meios as $meio) {
                $trocoMeio = 0;
                if ($meio['meio'] === 'NUMERARIO') {
                    $trocoMeio = min($trocoRestante, $meio['valor']);
                    $trocoRestante -= $trocoMeio;
                }
                DB::table('progresso_pagamento_meios')->insert([
                    'pagamento_id' => $pagamentoId,
                    'conta_financeira_id' => $meio['conta_financeira_id'],
                    'meio' => $meio['meio'],
                    'valor' => $this->decimal($meio['valor']),
                    'troco' => $this->decimal($trocoMeio),
                    'referencia' => $meio['referencia'],
                    'data_operacao' => now()->toDateString(),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            $saldoActual = $saldoAnterior;
            if ($creditoUsado > 0) {
                $saldoActual -= $creditoUsado;
                DB::table('progresso_credito_movimentos')->insert([
                    'credito_id' => $credito->id, 'pagamento_id' => $pagamentoId,
                    'tipo' => 'UTILIZACAO', 'valor' => $this->decimal($creditoUsado),
                    'saldo_anterior' => $this->decimal($saldoAnterior),
                    'saldo_posterior' => $this->decimal($saldoActual),
                    'descricao' => 'Utilização automática de saldo no pagamento',
                    'user_id' => $userId, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            if ($creditoGerado > 0) {
                $antes = $saldoActual;
                $saldoActual += $creditoGerado;
                DB::table('progresso_credito_movimentos')->insert([
                    'credito_id' => $credito->id, 'pagamento_id' => $pagamentoId,
                    'tipo' => 'ENTRADA', 'valor' => $this->decimal($creditoGerado),
                    'saldo_anterior' => $this->decimal($antes),
                    'saldo_posterior' => $this->decimal($saldoActual),
                    'descricao' => 'Crédito gerado por excedente de pagamento',
                    'user_id' => $userId, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            DB::table('progresso_aluno_creditos')->where('id', $credito->id)->update([
                'saldo' => $this->decimal($saldoActual), 'updated_at' => now(),
            ]);

            foreach ($idsPagos as $id) {
                $facturado = DB::table('progresso_factura_itens')
                    ->where('cobranca_id', $id)->value('valor_total');
                $liquidado = DB::table('progresso_pagamento_itens as pi')
                    ->join('progresso_pagamentos as p', 'p.id', '=', 'pi.pagamento_id')
                    ->where('pi.cobranca_id', $id)->where('p.estado', 'CONFIRMADO')
                    ->sum('pi.valor_aplicado');
                DB::table('progresso_cobrancas')->where('id', $id)->update([
                    'estado' => $this->centavos($liquidado) >= $this->centavos($facturado)
                        ? 'PAGO' : 'PARCIAL',
                    'updated_at' => now(),
                ]);
            }

            foreach (array_unique($facturasIds) as $facturaId) {
                $factura = DB::table('progresso_facturas')->where('id', $facturaId)
                    ->lockForUpdate()->first();
                $pago = DB::table('progresso_pagamento_itens as pi')
                    ->join('progresso_pagamentos as p', 'p.id', '=', 'pi.pagamento_id')
                    ->join('progresso_factura_itens as fi', 'fi.cobranca_id', '=', 'pi.cobranca_id')
                    ->where('fi.factura_id', $facturaId)->where('p.estado', 'CONFIRMADO')
                    ->sum('pi.valor_aplicado');
                DB::table('progresso_facturas')->where('id', $facturaId)->update([
                    'estado' => $this->centavos($pago) >= $this->centavos($factura->total)
                        ? 'PAGA' : 'PARCIAL',
                    'updated_at' => now(),
                ]);
            }

            app(ProgressoReciboService::class)->emitir($pagamentoId);


            return DB::table('progresso_pagamentos')->where('id', $pagamentoId)->first();
        }, 3);
    }

    public function itensFactura(int $facturaId)
    {
        $escolaId = (int) auth()->user()->escola_id;

        $factura = DB::table('progresso_facturas')
            ->where('id', $facturaId)
            ->where('escola_id', $escolaId)
            ->whereIn('estado', ['PENDENTE', 'PARCIAL'])
            ->first();

        if (!$factura) {
            abort(404, 'Factura não encontrada.');
        }

        $itens = DB::table('progresso_factura_itens as fi')
            ->join('progresso_cobrancas as c', 'c.id', '=', 'fi.cobranca_id')
            ->where('fi.factura_id', $facturaId)
            ->where('c.escola_id', $escolaId)
            ->where('c.aluno_id', $factura->aluno_id)
            ->select(
                'fi.cobranca_id',
                'fi.descricao',
                'fi.valor_total'
            )
            ->get();

        return response()->json([
            'status' => 1,
            'itens' => $itens
        ]);
    }
}
