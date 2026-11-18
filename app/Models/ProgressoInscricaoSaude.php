<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoInscricaoSaude extends Model
{
    protected $table = 'progresso_inscricao_saude';

    protected $fillable = [
        'inscricao_id',
        'condicao',
        'condicao_descricao',
        'medicacao',
        'medicacao_descricao',
        'alergia',
        'alergia_descricao',
        'atestado',
        'unidade_preferencial',
    ];

    protected $casts = [
        'condicao' => 'boolean',
        'medicacao' => 'boolean',
        'alergia' => 'boolean',
        'atestado' => 'boolean',
    ];

    public function inscricao()
    {
        return $this->belongsTo(
            ProgressoInscricaoDetalhe::class,
            'inscricao_id'
        );
    }
}
