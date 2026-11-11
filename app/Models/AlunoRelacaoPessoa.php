<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlunoRelacaoPessoa extends Model
{
    use HasFactory;

    protected $table = 'aluno_relacao_pessoa';

    protected $fillable = [
        'aluno_id',
        'pessoa_id',
        'relacao_id',
        'grau',
        'descricao'
    ];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function relacao()
    {
        return $this->belongsTo(Relacao::class, 'relacao_id');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
