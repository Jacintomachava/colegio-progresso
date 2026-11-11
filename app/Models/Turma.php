<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turma extends Model
{
    use HasFactory;

    protected $table = 'turmas';

    public function anoAcademico()
    {
        return $this->belongsTo(AnoAcademico::class, 'ano_academico_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function matricula()
    {
        return $this->belongsTo(Taxa::class, 'taxa_id');
    }

    public function propina()
    {
        return $this->belongsTo(Taxa::class, 'propina_id');
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
