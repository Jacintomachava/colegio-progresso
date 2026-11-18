<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoCobranca extends Model
{
    protected $table = 'progresso_cobrancas';

    protected $fillable = [
        'escola_id',
        'aluno_id',
        'matricula_id',
        'tipo',
        'origem_id',
        'descricao',
        'ano',
        'mes',
        'valor_original',
        'valor_desconto',
        'valor_total',
        'data_vencimento',
        'estado',
    ];

    protected $casts = [
        'valor_original' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'data_vencimento' => 'date',
    ];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function facturaItem()
    {
        return $this->hasOne(
            ProgressoFacturaItem::class,
            'cobranca_id'
        );
    }
}