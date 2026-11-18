<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressoValorClasse extends Model
{
    use HasFactory;

    protected $table = 'progresso_valores_classes';

    public function anoAcademico()
    {
        return $this->belongsTo(AnoAcademico::class, 'ano_academico_id');
    }

    
}
