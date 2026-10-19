<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElementoInventario extends Model
{
    protected $table = 'elementos_inventario';

    protected $fillable = [
        'sala_id',
        'codigo',
        'nombre',
        'ubicacion',
        'stock',
        'minimo',
        'en_proceso',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'minimo' => 'integer',
            'en_proceso' => 'integer',
        ];
    }

    public function salaFisica(): BelongsTo
    {
        return $this->belongsTo(Sala::class, 'sala_id');
    }

    public function codigoSala(): string
    {
        return $this->salaFisica?->codigo ?? '';
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInsumo::class, 'elemento_inventario_id');
    }

    public function reposiciones(): HasMany
    {
        return $this->hasMany(Reposicion::class, 'elemento_inventario_id');
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

    public function tieneAlertaStock(): bool
    {
        return $this->estadoStock() !== 'ok';
    }
}
