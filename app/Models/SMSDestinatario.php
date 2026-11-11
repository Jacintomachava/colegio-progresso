<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSDestinatario extends Model
{
    use HasFactory;

    protected $table = 'sms_destinatarios';

    protected $fillable = [
        'data_envio',
        'estado',
        'relacao_id',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'destinatario_pessoa_id');
    }
}
