<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoConjunto extends Model
{
    protected $table = 'tipo_conjunto';

    protected $fillable = ['codigo', 'nombre_conjunto', 'servicio_id'];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /** El catálogo real une por FK directa, no por pivot. */
    public function instrumentos(): HasMany
    {
        return $this->hasMany(Instrumento::class, 'tipo_conjunto_id');
    }

    public function fichas(): HasMany
    {
        return $this->hasMany(Ficha::class, 'tipo_conjunto_id');
    }
}
