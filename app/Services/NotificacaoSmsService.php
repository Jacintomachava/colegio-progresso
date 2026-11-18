<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class NotificacaoSmsService
{
    public function registar(
        int $escolaId,
        string $telefone,
        string $descricao,
        ?CarbonInterface $dataEnvio = null
    ): bool {
        $smsActivo = DB::table('escolas')
            ->where('id', $escolaId)
            ->value('sms_activo');

        if (!(bool) $smsActivo) {
            return false;
        }

        $telefone = trim($telefone);
        $descricao = trim($descricao);

        if ($telefone === '' || $descricao === '') {
            return false;
        }

        DB::table('mensagem')->insert([
            'escola_id' => $escolaId,
            'telefone' => $telefone,
            'descricao' => $descricao,
            'tipo' => 'Pendente',
            'qtd' => 1,
            'credito' => 1,
            'custo_real' => 1,
            'canal' => 'SMS',
            'data_envio' => ($dataEnvio ?? now())->toDateTimeString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }
}