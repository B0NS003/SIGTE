<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instrumento extends Model
{
    protected $fillable = [
        'nombre',
        'tamano',
        'codigo_fabricante',
        'tipo_conjunto_id',
    ];

    public function tipoConjunto(): BelongsTo
    {
        return $this->belongsTo(TipoConjunto::class, 'tipo_conjunto_id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(FichaInstrumento::class, 'instrumento_id');
    }
}
