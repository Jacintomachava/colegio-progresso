<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    use HasFactory;

    protected $table = 'pagamentos';

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function referenciaBanco()
    {
        return $this->belongsTo(BIM::class, 'referencia', 'reference');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mensalidade()
    {
        return $this->hasMany(Mensalidade::class, 'pagamento_id');
    }

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function metodo()
    {
        return $this->belongsTo(MetodoPagamento::class, 'metodo_pagamento_id');
    }
}
