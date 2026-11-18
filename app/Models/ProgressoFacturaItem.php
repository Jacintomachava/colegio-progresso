<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoFacturaItem extends Model
{
    protected $table = 'progresso_factura_itens';

    protected $fillable = [
        'factura_id',
        'cobranca_id',
        'descricao',
        'quantidade',
        'preco_unitario',
        'valor_total',
    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'preco_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function factura()
    {
        return $this->belongsTo(
            ProgressoFactura::class,
            'factura_id'
        );
    }

    public function cobranca()
    {
        return $this->belongsTo(
            ProgressoCobranca::class,
            'cobranca_id'
        );
    }
}