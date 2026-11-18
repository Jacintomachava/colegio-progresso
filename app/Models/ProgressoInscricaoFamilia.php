<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoInscricaoFamilia extends Model
{
    protected $table = 'progresso_inscricao_familia';

    protected $fillable = [
        'inscricao_id',
        'tipo',
        'nome',
        'parentesco',
        'nacionalidade',
        'formacao',
        'profissao',
        'local_trabalho',
        'telefone',
        'telefone2',
        'email',
        'residencia',
    ];

    public function inscricao()
    {
        return $this->belongsTo(
            ProgressoInscricaoDetalhe::class,
            'inscricao_id'
        );
    }
}
