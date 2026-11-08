<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInsumo extends Model
{
    public const ENTRADA = 'entrada';
    public const SALIDA = 'salida';

    // TIPOS DE ENTRADA EN INVENTARIO
    public const CONSUMO = 'consumo';
    public const USO = 'uso';
    public const DEVOLUCION = 'devolucion';

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'elemento_inventario_id',
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
        return $this->belongsTo(ElementoInventario::class, 'elemento_inventario_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function etiquetaTipo(): string
    {
        return match ($this->tipo) {
            self::CONSUMO => 'Consumo',
            self::USO => 'Uso',
            self::DEVOLUCION => 'Devolución',
            self::ENTRADA => 'Entrada',
            self::SALIDA => 'Salida',
            default => 'Movimiento',
        };
    }
}
