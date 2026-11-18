<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoPagamento extends Model
{
    protected $table = 'progresso_pagamentos';

    protected $guarded = ['id'];

    protected $casts = [
        'valor_cobrancas' => 'decimal:2',
        'valor_aplicado' => 'decimal:2',
        'valor_recebido' => 'decimal:2',
        'valor_credito_utilizado' => 'decimal:2',
        'valor_troco' => 'decimal:2',
        'valor_credito_gerado' => 'decimal:2',
        'confirmado_em' => 'datetime',
    ];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function meios()
    {
        return $this->hasMany(ProgressoPagamentoMeio::class,'pagamento_id');
    }

    public function itens()
    {
        return $this->hasMany(ProgressoPagamentoItem::class,'pagamento_id');
    }

    public function recibo()
    {
        return $this->hasOne(ProgressoRecibo::class,'pagamento_id');
    }
}