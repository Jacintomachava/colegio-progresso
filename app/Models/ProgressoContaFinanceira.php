<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoContaFinanceira extends Model
{
    protected $table = 'progresso_contas_financeiras';

    protected $guarded = ['id'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function meiosPagamento()
    {
        return $this->hasMany(ProgressoPagamentoMeio::class,'conta_financeira_id');
    }
}