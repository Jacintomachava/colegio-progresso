<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ProgressoDocumentoNumeroService
{
    public function gerar(
        int $escolaId,
        string $tipo
    ): string {

        if (!in_array($tipo, ['FT', 'RC'], true)) {
            throw new \InvalidArgumentException(
                'Tipo de documento inválido.'
            );
        }

        $ano = (int) now()->year;

        // Executar dentro de DB::transaction()
        DB::table('progresso_documento_sequencias')
            ->insertOrIgnore([
                'escola_id' => $escolaId,
                'ano' => $ano,
                'tipo' => $tipo,
                'ultimo' => 0,
            ]);

        $sequencia = DB::table('progresso_documento_sequencias')
            ->where('escola_id', $escolaId)
            ->where('ano', $ano)
            ->where('tipo', $tipo)
            ->lockForUpdate()
            ->first();

        if (!$sequencia) {
            throw new \RuntimeException(
                'Não foi possível obter a sequência.'
            );
        }

        $numero = $sequencia->ultimo + 1;

        DB::table('progresso_documento_sequencias')
            ->where('escola_id', $escolaId)
            ->where('ano', $ano)
            ->where('tipo', $tipo)
            ->update([
                'ultimo' => $numero,
            ]);

        return sprintf(
            '%s%02d%04d',
            $tipo,
            $numero,
            $ano
        );
    }
}