<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instrumento extends Model
{
    protected $fillable = [
        'nombre',
        'tamano',
        'codigo_fabricante',
    ];

    public function lineas(): HasMany
    {
        return $this->hasMany(FichaInstrumento::class, 'instrumento_id');
    }
}
