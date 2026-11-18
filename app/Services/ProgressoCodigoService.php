<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ProgressoCodigoService
{
    public function gerarCodigoAluno(
        int $escolaId,
        int $ano,
        string $codigoEscola
    ): string {

        // Deve ser executado dentro de uma transacção.
        DB::table('progresso_sequencias')->insertOrIgnore([
            'escola_id' => $escolaId,
            'ano' => $ano,
            'ultimo' => 0,
        ]);

        $sequencia = DB::table('progresso_sequencias')
            ->where('escola_id', $escolaId)
            ->where('ano', $ano)
            ->lockForUpdate()
            ->first();

        $numero = $sequencia->ultimo + 1;

        DB::table('progresso_sequencias')
            ->where('escola_id', $escolaId)
            ->where('ano', $ano)
            ->update([
                'ultimo' => $numero,
            ]);

        return $codigoEscola
            . '.A'
            . str_pad($numero, 3, '0', STR_PAD_LEFT)
            . '.'
            . $ano;
    }
}