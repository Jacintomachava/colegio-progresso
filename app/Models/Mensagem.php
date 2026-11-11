<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensagem extends Model
{
    use HasFactory;

    protected $table = 'mensagem';

    protected $fillable = [
        'telefone',
        'descricao',
        'tipo',
        'qtd',
        'credito',
        'custo_real',
        'canal',
        'data_envio',
    ];
}
