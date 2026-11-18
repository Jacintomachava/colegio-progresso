<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoInscricaoAutorizado extends Model
{
    protected $table = 'progresso_inscricao_autorizados';

    protected $fillable = [
        'inscricao_id',
        'nome',
        'telefone',
    ];

    public function inscricao()
    {
        return $this->belongsTo(
            ProgressoInscricaoDetalhe::class,
            'inscricao_id'
        );
    }
}
