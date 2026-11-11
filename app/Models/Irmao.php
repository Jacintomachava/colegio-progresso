<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Irmao extends Model
{
    use HasFactory;

    protected $table = 'irmaos';

    protected $fillable = [
        'aluno_id',
        'aluno_irmao_id	',
    ];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }
}
