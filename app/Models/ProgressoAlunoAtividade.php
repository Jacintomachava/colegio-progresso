<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoAlunoAtividade extends Model
{
    protected $table = 'progresso_aluno_atividades';

    protected $fillable = [
        'escola_id',
        'aluno_id',
        'atividade_id',
        'ano_academico_id',
        'data_inicio',
        'data_fim',
        'valor_mensal',
        'estado',
        'motivo_cancelamento',
        'cancelado_em',
        'cancelado_por',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'valor_mensal' => 'decimal:2',
        'cancelado_em' => 'datetime',
    ];

    public function atividade()
    {
        return $this->belongsTo(
            ProgressoAtividade::class,
            'atividade_id'
        );
    }

    public function aluno()
    {
        return $this->belongsTo(
            Aluno::class,
            'aluno_id'
        );
    }
}