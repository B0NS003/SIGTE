<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ficha extends Model
{
    protected $table = 'fichas';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'servicio_id',
        'tipo_conjunto_id',
        'guia_visual',
        'imagen',
        'imagen_formato',
    ];

    protected function casts(): array
    {
        return [
            'guia_visual' => 'boolean',
            'servicio_id' => 'integer',
            'tipo_conjunto_id' => 'integer',
        ];
    }

    public function servicioClinico(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function tipoConjunto(): BelongsTo
    {
        return $this->belongsTo(TipoConjunto::class, 'tipo_conjunto_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FichaInstrumento::class, 'ficha_id')
            ->orderBy('orden');
    }

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class, 'ficha_id');
    }
}