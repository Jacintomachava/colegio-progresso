<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoCurricular extends Model
{
    use HasFactory;

    protected $table = 'co_curricular';

    protected $fillable = [
        'aluno_id',
        'ano_academico_id',
        'mes_id',
        'estado',
        'disciplina_id',
    ];

    public function disciplina()
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    public function anoAcademico()
    {
        return $this->belongsTo(AnoAcademico::class, 'ano_academico_id');
    }

    public function mes()
    {
        return $this->belongsTo(Meses::class, 'mes_id');
    }

    public function mesfim()
    {
        return $this->belongsTo(Meses::class, 'mes_fim_id');
    }
}
