<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificarSaldoSMS extends Model
{
    use HasFactory;

    protected $table = 'notificar_saldo_sms';

    protected $fillable = [
        'nome',
        'contacto_anotificar',
    ];
}
