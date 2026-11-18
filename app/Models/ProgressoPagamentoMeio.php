<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoPagamentoMeio extends Model
{
    protected $table = 'progresso_pagamento_meios';

    protected $guarded = ['id'];

    protected $casts = [
        'valor' => 'decimal:2',
        'troco' => 'decimal:2',
        'data_operacao' => 'date',
    ];

    public function pagamento()
    {
        return $this->belongsTo(
            ProgressoPagamento::class,
            'pagamento_id'
        );
    }

    public function contaFinanceira()
    {
        return $this->belongsTo(
            ProgressoContaFinanceira::class,
            'conta_financeira_id'
        );
    }
}