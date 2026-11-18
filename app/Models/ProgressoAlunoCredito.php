<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoAlunoCredito extends Model
{
    protected $table = 'progresso_aluno_creditos';

    protected $guarded = ['id'];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    public function movimentos()
    {
        return $this->hasMany(
            ProgressoCreditoMovimento::class,
            'credito_id'
        );
    }

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }
}