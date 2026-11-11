<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $table = 'classes';

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_id');
    }

    // Relacionamento com Matricula
    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'classe_id');
    }

    // Método para buscar dados formatados para o gráfico
    public static function getTreemapData($anoAcademicoId)
    {
        return self::withCount(['matriculas' => function ($query) use ($anoAcademicoId) {
            $query->where('ano_academico_id', $anoAcademicoId);
        }])->get()->map(function ($classe) {
            return [
                'name' => $classe->nome,
                'value' => $classe->matriculas_count,
            ];
        });
    }
}
