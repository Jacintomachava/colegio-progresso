<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessorTurma extends Model
{
    use HasFactory;

    protected $table = 'professor_turma';

    protected $fillable = [
        'activo',
        'director_turma',
        'director_turma_id',
        'pauta_finalizada',
        'pauta_finalizada_automaticamente',
        'turma_id',
        'ano_academico_id',
        'todas_disciplina',
        'professor_id',
        'user_id',
    ];

    public function anoAcademico()
    {
        return $this->belongsTo(AnoAcademico::class, 'ano_academico_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function professor()
    {
        return $this->belongsTo(Professor::class, 'professor_id');
    }

    public function directorTurma()
    {
        return $this->belongsTo(Professor::class, 'director_turma_id');
    }
}
