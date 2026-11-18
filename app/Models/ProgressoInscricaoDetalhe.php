<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoInscricaoDetalhe extends Model
{
    protected $table = 'progresso_inscricao_detalhes';

    protected $fillable = [
        'matricula_id',
        'aluno_id',
        'escola_id',
        'tipo_inscricao',
        'nacionalidade',
        'data_emissao',
        'vive_com',
        'escola_anterior',
        'modalidade_pagamento',
        'emergencia_nome',
        'emergencia_telefone',
        'declaracao_aceite_em',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'declaracao_aceite_em' => 'datetime',
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function familia()
    {
        return $this->hasMany(
            ProgressoInscricaoFamilia::class,
            'inscricao_id'
        );
    }

    public function saude()
    {
        return $this->hasOne(
            ProgressoInscricaoSaude::class,
            'inscricao_id'
        );
    }

    public function autorizados()
    {
        return $this->hasMany(
            ProgressoInscricaoAutorizado::class,
            'inscricao_id'
        );
    }

    public function servicos()
    {
        return $this->hasMany(
            ProgressoInscricaoServico::class,
            'inscricao_id'
        );
    }
}
