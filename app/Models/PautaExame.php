<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PautaExame extends Model
{
    use HasFactory;

    protected $table = 'pauta_exame';

    protected $fillable = [
        'aluno_id',
        'escola_id',
        'classe_id',
        'pauta_id',
        'ano_academico_id',
        'disciplina_id',
        'ncd',
        'numero',
        'proveniencia',
        'primeira_chamada',
        'segunda_chamada',
        'voto',
        'ncd',
        'nf'
    ];

    public function anoAcademico()
    {
        return $this->belongsTo(AnoAcademico::class, 'ano_academico_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function disciplina()
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

}
