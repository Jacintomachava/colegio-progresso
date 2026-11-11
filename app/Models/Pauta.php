<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pauta extends Model
{
    use HasFactory;

    protected $table = 'pautas';

    protected $fillable = [
        'aluno_id',
        'escola_id',
        'classe_id',
        'turma_id',
        'ano_academico_id',
        'disciplina_id',
        'activo',
        'apTr3',
        'mtTr3',
        'mmTr3',
        'teste4Tr3',
        'teste3Tr3',
        'teste2Tr3',
        'teste1Tr3',
        'apTr2',
        'mtTr1',
        'mmTr1',
        'teste4Tr2',
        'teste3Tr2',
        'teste2Tr2',
        'teste1Tr2',
        'apTr1',
        'mtTr2',
        'mmTr2',
        'teste4Tr1',
        'teste3Tr1',
        'teste2Tr1',
        'teste1Tr1'
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

    public function turma()
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }
}
