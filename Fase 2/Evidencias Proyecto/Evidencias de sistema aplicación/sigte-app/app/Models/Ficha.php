<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ficha extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'numero',
        'codigo_referencia',
        'tipo',
        'area',
        'servicio_id',
    ];

    public function servicioClinico(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FichaInstrumento::class)->orderBy('orden');
    }

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class, 'ficha_id');
    }
}
