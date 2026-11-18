<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoCreditoMovimento extends Model
{
    protected $table = 'progresso_credito_movimentos';

    protected $guarded = ['id'];

    protected $casts = [
        'valor' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_posterior' => 'decimal:2',
    ];

    public function credito()
    {
        return $this->belongsTo(
            ProgressoAlunoCredito::class,
            'credito_id'
        );
    }

    public function pagamento()
    {
        return $this->belongsTo(
            ProgressoPagamento::class,
            'pagamento_id'
        );
    }
}