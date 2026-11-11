<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReclamacaoElogioPedido extends Model
{
    use HasFactory;

    protected $table = 'reclamacao_elogio';

    protected $fillable = [
        'telefone',
        'mensagem',
        'tipo',
        'estado',
        'resposta',
        'resposta_meio',
        'resposta_user_id',
        'updated_at',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'resposta_user_id');
    }
}
