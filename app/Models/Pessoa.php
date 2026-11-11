<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    use HasFactory;

    protected $table = 'pessoas';

    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'avatar',
        'data_nascimento',
        'genero',
        'endereco',
        'tipo_documento',
        'numero_documento',
        'naturalidade',
        'nuit',
    ];

    public function nacionalidade()
    {
        return $this->belongsTo(Nacionalidade::class, 'nacionalidade_id');
    }

    public function alunosEncarregado()
    {
        return $this->belongsToMany(Aluno::class, 'aluno_relacao_pessoa', 'pessoa_id', 'aluno_id')
                    ->wherePivot('relacao_id', 1);
    }

}
