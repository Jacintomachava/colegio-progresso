<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    use HasFactory;

    protected $table = 'alunos';

    protected $casts = [
        'data_nascimento' => 'datetime',
    ];

    public function encarregado()
    {
        return $this->belongsToMany(Pessoa::class, 'aluno_relacao_pessoa', 'aluno_id', 'pessoa_id')
                    ->wherePivot('relacao_id', 1);
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'provincia_id');
    }

    public function mensalidades()
    {
        return $this->hasMany(Mensalidade::class);
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class);
    }

    public function matriculaAtiva($anoAcademicoId)
    {
        return $this->hasOne(Matricula::class)->where('ano_academico_id', $anoAcademicoId);
    }

    // Nova relação para usar com with()
    public function matriculaAtiva1()
    {
        return $this->hasOne(Matricula::class);
    }

    public function encarregado1()
    {
        return $this->belongsTo(Encarregado::class, 'encarregado_id');
    }

    public function pautas()
    {
        return $this->hasMany(Pauta::class, 'aluno_id');
    }


     // Última mensalidade paga
    public function ultimaMensalidade()
    {
        return $this->hasOne(Mensalidade::class)
            ->where('pagou', 1)
            ->latestOfMany();
    }
}
