<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoPagamentoItem extends Model
{
    protected $table = 'progresso_pagamento_itens';

    protected $guarded = ['id'];

    protected $casts = [
        'valor_aplicado' => 'decimal:2',
    ];

    public function pagamento()
    {
        return $this->belongsTo(ProgressoPagamento::class,'pagamento_id');
    }

    public function cobranca()
    {
        return $this->belongsTo(ProgressoCobranca::class,'cobranca_id');
    }
}