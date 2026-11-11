<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curriculo extends Model
{
    use HasFactory;

    protected $table = 'curriculos';

    protected $fillable = [
        'disciplina_id',
        
        'classe_id',

        //Esta variavel permite definir quais as disciplinas com exame
        'disciplina_is_exame',

        //Permite saber se for corriculo com exame e ou admtido ou nao, nao nao ter exame e
        //progride ou nao progride
        'curriculo_is_exame'
    ];

    public function disciplina()
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }
}
