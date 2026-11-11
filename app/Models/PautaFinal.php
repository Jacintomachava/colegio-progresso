<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PautaFinal extends Model
{
    use HasFactory;

    protected $table = 'pauta_final';

    protected $fillable = [
        'aluno_id',
        'ano_academico_id',
        'classe_actual_id',
        'resulta',
        'classe_nova_id',
        'nota_final',
    ];
}
