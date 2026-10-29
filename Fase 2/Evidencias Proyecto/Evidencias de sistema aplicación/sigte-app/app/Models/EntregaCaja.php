<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaCaja extends Model
{
    protected $table = 'entrega_cajas';

    protected $fillable = [
        'entrega_id',
        'caja_id',
        'codigo',
        'nombre',
    ];

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(Entrega::class);
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }
}
