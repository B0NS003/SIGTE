<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entrega extends Model
{
    protected $fillable = [
        'servicio_id',
        'user_id',
        'retira',
        'entregado_at',
    ];

    protected function casts(): array
    {
        return [
            'entregado_at' => 'datetime',
        ];
    }

    public function servicioClinico(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class, 'entrega_id');
    }
}
