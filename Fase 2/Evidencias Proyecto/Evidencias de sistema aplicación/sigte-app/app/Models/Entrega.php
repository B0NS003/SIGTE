<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Entrega extends Model
{
    protected $fillable = [
        'codigo',
        'servicio',
        'recibe',
        'recibe_rol',
        'material',
        'observacion',
        'user_id',
        'entregado_at',
    ];

    protected function casts(): array
    {
        return [
            'entregado_at' => 'datetime',
        ];
    }

    public function quienEntrega(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EntregaCaja::class);
    }

    public static function siguienteCodigo(?Carbon $cuando = null): string
    {
        $cuando ??= now()->timezone('America/Santiago');
        $prefijo = 'ENT-'.$cuando->format('ymd');
        $ultimo = static::query()
            ->where('codigo', 'like', $prefijo.'%')
            ->orderByDesc('codigo')
            ->value('codigo');
        $secuencial = 1;
        if (is_string($ultimo) && preg_match('/-(\d+)$/', $ultimo, $partes)) {
            $secuencial = ((int) $partes[1]) + 1;
        }

        return $prefijo.'-'.str_pad((string) $secuencial, 3, '0', STR_PAD_LEFT);
    }

    /** @return array{id: string, fecha: string, hora: string, cuando: string, servicio: string, entrega: string, entrega_rol: string, recibe: string, recibe_rol: string, cajas: list<array{codigo: string, nombre: string}>, materiales: list<string>} */
    public function paraConsulta(): array
    {
        $momento = $this->entregado_at?->timezone('America/Santiago') ?? now('America/Santiago');
        $hoy = now('America/Santiago')->startOfDay();
        $dia = $momento->copy()->startOfDay();
        if ($dia->equalTo($hoy)) {
            $cuando = 'hoy';
        } elseif ($dia->equalTo($hoy->copy()->subDay())) {
            $cuando = 'ayer';
        } elseif ($dia->greaterThanOrEqualTo($hoy->copy()->subDays(6))) {
            $cuando = 'semana';
        } else {
            $cuando = 'anterior';
        }

        $actor = $this->quienEntrega;
        $materiales = [];
        if (filled($this->material)) {
            $materiales[] = (string) $this->material;
        }

        return [
            'id' => $this->codigo,
            'fecha' => $momento->format('d/m/Y'),
            'hora' => $momento->format('H:i'),
            'cuando' => $cuando,
            'servicio' => $this->servicio,
            'entrega' => $actor?->name ?? 'Sin registrar',
            'entrega_rol' => $actor?->rol?->etiqueta() ?? 'Operadora',
            'recibe' => $this->recibe,
            'recibe_rol' => $this->recibe_rol ?: 'Personal del servicio',
            'cajas' => $this->items
                ->map(fn (EntregaCaja $item) => [
                    'codigo' => $item->codigo,
                    'nombre' => $item->nombre ?: (str_starts_with(mb_strtoupper($item->codigo), 'SET-') ? 'Set quirúrgico' : 'Caja'),
                ])
                ->values()
                ->all(),
            'materiales' => $materiales,
        ];
    }
}
