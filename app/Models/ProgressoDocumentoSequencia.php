<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoDocumentoSequencia extends Model
{
    protected $table = 'progresso_documento_sequencias';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'escola_id';

    protected $fillable = [
        'escola_id',
        'ano',
        'tipo',
        'ultimo',
    ];

    protected $casts = [
        'escola_id' => 'integer',
        'ano' => 'integer',
        'ultimo' => 'integer',
    ];
}