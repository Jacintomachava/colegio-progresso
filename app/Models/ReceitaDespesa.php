<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceitaDespesa extends Model
{
    use HasFactory;

    protected $table = 'receita_despesa';

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
