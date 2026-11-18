<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoRecibo extends Model
{
    protected $table = 'progresso_recibos';

    protected $guarded = ['id'];

    protected $casts = [
        'data_emissao' => 'date',
        'valor' => 'decimal:2',
    ];

    public function pagamento()
    {
        return $this->belongsTo(
            ProgressoPagamento::class,
            'pagamento_id'
        );
    }
}