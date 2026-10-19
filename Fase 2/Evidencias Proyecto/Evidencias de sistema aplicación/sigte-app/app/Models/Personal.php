<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Personal extends Model
{
    protected $table = 'personal';

    public const TURNOS = [
        'manana' => ['etiqueta' => 'Mañana', 'horario' => '08:00 a 16:00'],
        'tarde' => ['etiqueta' => 'Tarde', 'horario' => '16:00 a 00:00'],
        'noche' => ['etiqueta' => 'Noche', 'horario' => '00:00 a 08:00'],
    ];

    protected $fillable = [
        'user_id',
        'nombre',
        'email',
        'grupo',
        'turno',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'grupo' => 'integer',
        ];
    }

    public static function turnoVigente(?Carbon $momento = null): string
    {
        $hora = ($momento ?? now())->timezone('America/Santiago')->hour;
        if ($hora >= 8 && $hora < 16) {
            return 'manana';
        }
        if ($hora >= 16) {
            return 'tarde';
        }

        return 'noche';
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class, 'responsable_id', 'user_id');
    }
}
