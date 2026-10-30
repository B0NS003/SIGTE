<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reposicion extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'elemento_inventario_id',
        'tipo',
        'cantidad',
        'observacion',
        'user_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('reposicion', function ($consulta) {
            $consulta->where('tipo', 'reposicion');
        });

        static::creating(function (Reposicion $reposicion) {
            $reposicion->tipo = 'reposicion';
        });
    }

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
        ];
    }

    public function elemento(): BelongsTo
    {
        return $this->belongsTo(ElementoInventario::class, 'elemento_inventario_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
