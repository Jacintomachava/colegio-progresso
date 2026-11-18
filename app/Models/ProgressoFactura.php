<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoFactura extends Model
{
    protected $table = 'progresso_facturas';

    protected $fillable = [
        'escola_id',
        'aluno_id',
        'matricula_id',
        'numero',
        'data_emissao',
        'data_vencimento',
        'subtotal',
        'desconto',
        'iva',
        'total',
        'estado',
        'observacoes',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_vencimento' => 'date',
        'subtotal' => 'decimal:2',
        'desconto' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function itens()
    {
        return $this->hasMany(
            ProgressoFacturaItem::class,
            'factura_id'
        );
    }
}