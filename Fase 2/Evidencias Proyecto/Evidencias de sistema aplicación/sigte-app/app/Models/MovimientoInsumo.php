<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInsumo extends Model
{
    public const ENTRADA = 'entrada';
    public const SALIDA = 'salida';

    protected $table = 'movimientos_insumo';

    protected $fillable = [
        'insumo_id',
        'tipo',
        'cantidad',
        'observacion',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
        ];
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function etiquetaTipo(): string
    {
        return $this->tipo === self::ENTRADA ? 'Entrada' : 'Salida';
    }
}
