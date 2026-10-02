<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insumo extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'ubicacion',
        'stock',
        'minimo',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'minimo' => 'integer',
        ];
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInsumo::class);
    }

    public function estadoStock(): string
    {
        if ($this->stock <= 0 || ($this->minimo > 0 && $this->stock <= (int) floor($this->minimo / 2))) {
            return 'critico';
        }

        if ($this->stock < $this->minimo) {
            return 'bajo';
        }

        return 'ok';
    }
}
