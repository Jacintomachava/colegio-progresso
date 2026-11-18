<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressoSequencia extends Model
{
    protected $table = 'progresso_sequencias';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'escola_id';

    protected $fillable = [
        'escola_id',
        'ano',
        'ultimo',
    ];

    protected $casts = [
        'escola_id' => 'integer',
        'ano' => 'integer',
        'ultimo' => 'integer',
    ];
}
