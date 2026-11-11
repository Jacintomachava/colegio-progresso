<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transferencia extends Model
{
    use HasFactory;

    protected $table = 'transferencia_aluno';

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function escolaOrigem()
    {
        return $this->belongsTo(Escola::class, 'escola_origem_id');
    }

    public function escolaDestino()
    {
        return $this->belongsTo(Escola::class, 'escola_destino_id');
    }
}
