<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProgressoFacturaService
{
    public function __construct(
        private ProgressoDocumentoNumeroService $numeroService
    ) {}

    public function emitirMatricula(int $cobrancaId): object
    {
        // Este método deve ser chamado dentro de DB::transaction().

        $cobranca = DB::table('progresso_cobrancas')
            ->where('id', $cobrancaId)
            ->lockForUpdate()
            ->first();

        if (!$cobranca || $cobranca->tipo !== 'MATRICULA') {
            throw new RuntimeException(
                'Cobrança de matrícula não encontrada.'
            );
        }

        // Impedir emissão duplicada.
        $itemExistente = DB::table('progresso_factura_itens')
            ->where('cobranca_id', $cobrancaId)
            ->first();

        if ($itemExistente) {
            return DB::table('progresso_facturas')
                ->where('id', $itemExistente->factura_id)
                ->first();
        }

        $numero = $this->numeroService->gerar(
            (int) $cobranca->escola_id,
            'FT'
        );

        $valor = (float) $cobranca->valor_total;

        $facturaId = DB::table('progresso_facturas')
            ->insertGetId([
                'escola_id' => $cobranca->escola_id,
                'aluno_id' => $cobranca->aluno_id,
                'matricula_id' => $cobranca->matricula_id,
                'numero' => $numero,
                'data_emissao' => now()->toDateString(),
                'data_vencimento' => $cobranca->data_vencimento
                    ?? now()->toDateString(),
                'subtotal' => $cobranca->valor_original,
                'desconto' => $cobranca->valor_desconto,
                'iva' => 0,
                'total' => $valor,
                'estado' => 'PENDENTE',
                'observacoes' => 'Factura de matrícula',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('progresso_factura_itens')->insert([
            'factura_id' => $facturaId,
            'cobranca_id' => $cobrancaId,
            'descricao' => $cobranca->descricao,
            'quantidade' => 1,
            'preco_unitario' => $valor,
            'valor_total' => $valor,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('progresso_facturas')
            ->where('id', $facturaId)
            ->first();
    }

    public function emitir(
        int $alunoId,
        array $cobrancaIds,
        int $escolaId,
        ?string $observacoes = null,
        array $descontoManual = []
    ): object {

        $ids = array_values(array_unique(
            array_map('intval', $cobrancaIds)
        ));

        sort($ids);

        if (empty($ids) || in_array(0, $ids, true)) {
            throw new RuntimeException(
                'Seleccione pelo menos uma cobrança válida.'
            );
        }

        return DB::transaction(function () use (
            $alunoId,
            $ids,
            $escolaId,
            $observacoes,
            $descontoManual
        ) {

            /*
            |--------------------------------------------------------------------------
            | 1. ESCOLA E IVA
            |--------------------------------------------------------------------------
            */

            $escola = DB::table('escolas')
                ->where('id', $escolaId)
                ->first();

            if (!$escola) {
                throw new RuntimeException(
                    'Escola não encontrada.'
                );
            }

            // Exemplo: escolas.iva = 16 significa 16%.
            $ivaPercentagem = (float) ($escola->iva ?? 0);

            if (
                !is_finite($ivaPercentagem) ||
                $ivaPercentagem < 0 ||
                $ivaPercentagem > 100
            ) {
                throw new RuntimeException(
                    'A percentagem do IVA da escola é inválida.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 2. VALIDAR ALUNO
            |--------------------------------------------------------------------------
            */

            $aluno = DB::table('alunos')
                ->where('id', $alunoId)
                ->where('escola_id', $escolaId)
                ->lockForUpdate()
                ->first();

            if (!$aluno) {
                throw new RuntimeException(
                    'Aluno não encontrado nesta escola.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 3. CARREGAR COBRANÇAS
            |--------------------------------------------------------------------------
            */

            $cobrancas = DB::table('progresso_cobrancas')
                ->where('escola_id', $escolaId)
                ->where('aluno_id', $alunoId)
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($cobrancas->count() !== count($ids)) {
                throw new RuntimeException(
                    'Uma ou mais cobranças são inválidas.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 4. PREPARAR VALORES ORIGINAIS
            |--------------------------------------------------------------------------
            */

            $subtotalCent = 0;
            $automaticoCent = 0;

            $itens = [];

            foreach ($cobrancas as $c) {

                if ($c->estado !== 'PENDENTE') {
                    throw new RuntimeException(
                        "Cobrança {$c->id} não está disponível."
                    );
                }

                $jaFacturada = DB::table(
                    'progresso_factura_itens'
                )
                    ->where('cobranca_id', $c->id)
                    ->exists();

                if ($jaFacturada) {
                    throw new RuntimeException(
                        "Cobrança {$c->id} já foi facturada."
                    );
                }

                $original = (int) round(
                    (float) $c->valor_original * 100
                );

                $automatico = (int) round(
                    (float) $c->valor_desconto * 100
                );

                $ivaAntigo = (int) round(
                    (float) $c->valor_iva * 100
                );

                $totalAntigo = (int) round(
                    (float) $c->valor_total * 100
                );

                if (
                    $original <= 0 ||
                    $automatico < 0 ||
                    $automatico > $original ||
                    $ivaAntigo < 0 ||
                    $original - $automatico + $ivaAntigo
                        !== $totalAntigo
                ) {
                    throw new RuntimeException(
                        "Valores inconsistentes na cobrança {$c->id}."
                    );
                }

                $subtotalCent += $original;
                $automaticoCent += $automatico;

                $itens[] = [
                    'cobranca_id' => $c->id,
                    'descricao' => $c->descricao,
                    'original_cent' => $original,
                    'automatico_cent' => $automatico,
                    'desconto_cent' => 0,
                    'iva_cent' => 0,
                    'total_cent' => 0,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | 5. DESCONTO MANUAL OU AUTOMÁTICO
            |--------------------------------------------------------------------------
            |
            | Manual substitui totalmente o automático.
            | Nunca acumulamos os dois.
            |
            */

            $tipo = $descontoManual['tipo']
                ?? 'PERCENTUAL';

            $valorInformado = (float) (
                $descontoManual['valor'] ?? 0
            );

            $motivo = trim(
                (string) ($descontoManual['motivo'] ?? '')
            );

            if (!in_array($tipo, [
                'PERCENTUAL',
                'VALOR'
            ], true)) {
                throw new RuntimeException(
                    'Tipo de desconto inválido.'
                );
            }

            if (
                !is_finite($valorInformado) ||
                $valorInformado < 0
            ) {
                throw new RuntimeException(
                    'Valor de desconto inválido.'
                );
            }

            $temManual = $valorInformado > 0;

            if ($temManual) {

                if ($motivo === '') {
                    throw new RuntimeException(
                        'Informe o motivo do desconto manual.'
                    );
                }

                if ($tipo === 'PERCENTUAL') {

                    if ($valorInformado > 100) {
                        throw new RuntimeException(
                            'O desconto não pode ultrapassar 100%.'
                        );
                    }

                    $descontoCent = (int) round(
                        $subtotalCent
                        * $valorInformado
                        / 100
                    );

                } else {

                    $descontoCent = (int) round(
                        $valorInformado * 100
                    );

                    if ($descontoCent > $subtotalCent) {
                        throw new RuntimeException(
                            'O desconto manual ultrapassa o subtotal.'
                        );
                    }
                }

                $origemDesconto = 'MANUAL';

            } else {

                $descontoCent = $automaticoCent;

                $origemDesconto = $descontoCent > 0
                    ? 'AUTOMATICO'
                    : null;
            }

            /*
            |--------------------------------------------------------------------------
            | 6. DISTRIBUIR DESCONTO PELOS ITENS
            |--------------------------------------------------------------------------
            */

            $descontoRestante = $descontoCent;
            $baseRestante = $subtotalCent;

            $ultimoElegivel = null;

            foreach ($itens as $indice => $item) {
                if ($item['original_cent'] > 0) {
                    $ultimoElegivel = $indice;
                }
            }

            foreach ($itens as $indice => &$item) {

                $original = $item['original_cent'];

                if (!$temManual) {

                    // Manter o desconto automático do item.
                    $descontoItem = $item['automatico_cent'];

                } elseif ($indice === $ultimoElegivel) {

                    // Último item recebe o restante.
                    $descontoItem = $descontoRestante;

                } elseif ($baseRestante > 0) {

                    $descontoItem = (int) round(
                        $descontoRestante
                        * $original
                        / $baseRestante
                    );

                } else {

                    $descontoItem = 0;
                }

                $descontoItem = min(
                    $descontoItem,
                    $original,
                    $descontoRestante
                );

                $item['desconto_cent'] = $descontoItem;

                $descontoRestante -= $descontoItem;
                $baseRestante -= $original;
            }

            unset($item);

            if ($descontoRestante !== 0) {
                throw new RuntimeException(
                    'Não foi possível distribuir o desconto.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 7. CALCULAR IVA DA ESCOLA
            |--------------------------------------------------------------------------
            |
            | O IVA é aplicado sobre o subtotal
            | depois do desconto efectivo.
            |
            */

            $baseTributavelCent = max(
                0,
                $subtotalCent - $descontoCent
            );

            $ivaCent = (int) round(
                $baseTributavelCent
                * $ivaPercentagem
                / 100
            );

            $totalCent = $baseTributavelCent + $ivaCent;

            /*
            |--------------------------------------------------------------------------
            | 8. DISTRIBUIR IVA PELOS ITENS
            |--------------------------------------------------------------------------
            */

            $ivaRestante = $ivaCent;
            $baseIvaRestante = $baseTributavelCent;

            $ultimoItemTributavel = null;

            foreach ($itens as $indice => $item) {

                $baseItem = $item['original_cent']
                    - $item['desconto_cent'];

                if ($baseItem > 0) {
                    $ultimoItemTributavel = $indice;
                }
            }

            foreach ($itens as $indice => &$item) {

                $baseItem = max(
                    0,
                    $item['original_cent']
                    - $item['desconto_cent']
                );

                if ($indice === $ultimoItemTributavel) {

                    $ivaItem = $ivaRestante;

                } elseif ($baseIvaRestante > 0) {

                    $ivaItem = (int) round(
                        $ivaRestante
                        * $baseItem
                        / $baseIvaRestante
                    );

                } else {

                    $ivaItem = 0;
                }

                $ivaItem = min(
                    $ivaItem,
                    $ivaRestante
                );

                $item['iva_cent'] = $ivaItem;

                $item['total_cent'] =
                    $baseItem + $ivaItem;

                $ivaRestante -= $ivaItem;
                $baseIvaRestante -= $baseItem;
            }

            unset($item);

            if ($ivaRestante !== 0) {
                throw new RuntimeException(
                    'Não foi possível distribuir o IVA.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 9. VALIDAR TOTAIS
            |--------------------------------------------------------------------------
            */

            $somaItensCent = array_sum(
                array_column($itens, 'total_cent')
            );

            if ($somaItensCent !== $totalCent) {
                throw new RuntimeException(
                    'O total dos itens não corresponde ao total da factura.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 10. GERAR NUMERAÇÃO
            |--------------------------------------------------------------------------
            */

            $numero = $this->numeroService->gerar(
                $escolaId,
                'FT'
            );

            /*
            |--------------------------------------------------------------------------
            | 11. MATRÍCULA E VENCIMENTO
            |--------------------------------------------------------------------------
            */

            $matriculaIds = $cobrancas
                ->pluck('matricula_id')
                ->filter()
                ->unique();

            $matriculaId = $matriculaIds->count() === 1
                ? $matriculaIds->first()
                : null;

            $dataVencimento = $cobrancas
                ->pluck('data_vencimento')
                ->filter()
                ->sort()
                ->first() ?? now()->toDateString();

            /*
            |--------------------------------------------------------------------------
            | 12. REGISTAR FACTURA
            |--------------------------------------------------------------------------
            */

            $facturaId = DB::table('progresso_facturas')
                ->insertGetId([

                    'escola_id' => $escolaId,
                    'aluno_id' => $alunoId,
                    'matricula_id' => $matriculaId,

                    'numero' => $numero,

                    'data_emissao' => now()->toDateString(),
                    'data_vencimento' => $dataVencimento,

                    'subtotal' => $subtotalCent / 100,
                    'desconto' => $descontoCent / 100,
                    'iva' => $ivaCent / 100,
                    'total' => $totalCent / 100,

                    'origem_desconto' => $origemDesconto,

                    'motivo_desconto' => $temManual
                        ? $motivo
                        : null,

                    'desconto_autorizado_por' => $temManual
                        ? ($descontoManual['user_id'] ?? null)
                        : null,

                    'estado' => 'PENDENTE',

                    // Utilizador que emitiu a factura
                    'user_id' => auth()->id(),

                    'observacoes' => $observacoes,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | 13. REGISTAR ITENS
            |--------------------------------------------------------------------------
            */

            foreach ($itens as $item) {

                DB::table('progresso_factura_itens')
                    ->insert([

                        'factura_id' => $facturaId,

                        'cobranca_id' => $item['cobranca_id'],

                        'descricao' => $item['descricao'],

                        'quantidade' => 1,

                        'preco_unitario' =>
                            $item['original_cent'] / 100,

                        'valor_total' =>
                            $item['total_cent'] / 100,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 14. DEVOLVER FACTURA
            |--------------------------------------------------------------------------
            */

            return DB::table('progresso_facturas')
                ->where('id', $facturaId)
                ->first();

        }, 3);
    }



}