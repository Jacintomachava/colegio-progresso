<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MensagemSessao extends Model
{
    use HasFactory;

    protected $table = 'mensagem_contacto';

    protected $fillable = [
        'telefone',
        'conversas',
        'ultima_conversa',
        'updated_at',
        'created_at',
    ];
}
