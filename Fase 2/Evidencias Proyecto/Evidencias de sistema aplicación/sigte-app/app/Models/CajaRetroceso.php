<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CajaRetroceso extends Model
{
    protected $table = 'caja_retrocesos';

    protected $fillable = [
        'caja_id',
        'user_id',
        'etapa_desde',
        'etapa_hacia',
        'motivo',
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
