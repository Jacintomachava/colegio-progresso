<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relacao extends Model
{
    use HasFactory;

    protected $table = 'relacao';

    protected $fillable = [
        'aluno_id',
        'aluno_irmao_id	',
    ];

}
