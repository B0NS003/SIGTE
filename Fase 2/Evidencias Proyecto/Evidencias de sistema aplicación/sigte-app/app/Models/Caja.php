<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Caja extends Model
{
    public const ETAPA_RECEPCION = 'recepcion';
    public const ETAPA_LAVADO = 'lavado';
    public const ETAPA_PREPARACION = 'preparacion';
    public const ETAPA_ESTERILIZACION = 'esterilizacion';
    public const ETAPA_ALMACEN = 'almacen';
    public const ETAPA_ENTREGA = 'entrega';

    protected $fillable = [
        'codigo',
        'servicio',
        'etapa',
        'ubicacion',
        'estado',
        'actividad',
        'urgente',
        'responsable_id',
        'etapa_desde',
        'proceso_desde',
        'proceso_hasta',
    ];

    protected function casts(): array
    {
        return [
            'urgente' => 'boolean',
            'etapa_desde' => 'datetime',
            'proceso_desde' => 'datetime',
            'proceso_hasta' => 'datetime',
        ];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function anotaciones(): HasMany
    {
        return $this->hasMany(CajaAnotacion::class);
    }

    public function retrocesos(): HasMany
    {
        return $this->hasMany(CajaRetroceso::class);
    }

    /** Un paso atrás, y solo antes de que el material entre a esterilización. */
    public function etapaAnteriorPermitida(): ?string
    {
        return match (trim((string) $this->etapa)) {
            self::ETAPA_LAVADO => self::ETAPA_RECEPCION,
            self::ETAPA_PREPARACION => self::ETAPA_LAVADO,
            default => null,
        };
    }

    /** @return list<string> */
    public static function etapas(): array
    {
        return [
            self::ETAPA_RECEPCION,
            self::ETAPA_LAVADO,
            self::ETAPA_PREPARACION,
            self::ETAPA_ESTERILIZACION,
            self::ETAPA_ALMACEN,
            self::ETAPA_ENTREGA,
        ];
    }

    public static function etiquetaEtapa(string $etapa): string
    {
        return [
            self::ETAPA_RECEPCION => 'Recepción',
            self::ETAPA_LAVADO => 'Lavado',
            self::ETAPA_PREPARACION => 'Preparación',
            self::ETAPA_ESTERILIZACION => 'Esterilización',
            self::ETAPA_ALMACEN => 'Almacén',
            self::ETAPA_ENTREGA => 'Entrega',
        ][$etapa] ?? $etapa;
    }

    public function indiceEtapa(): int
    {
        $indice = array_search(trim($this->etapa), self::etapas(), true);

        return $indice === false ? 0 : $indice;
    }

    public function tiempoEnEtapa(): string
    {
        if ($this->etapa_desde === null) {
            return 'Sin registro';
        }

        $minutos = (int) abs($this->etapa_desde->diffInMinutes(Carbon::now()));

        if ($minutos < 60) {
            return $minutos.' min';
        }

        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;

        return $resto === 0 ? $horas.' h' : $horas.' h '.$resto.' min';
    }

    public function nombreResponsable(): string
    {
        return $this->responsable?->name ?? 'Sin responsable';
    }

    public function sumarProceso(int $minutos): void
    {
        $ahora = Carbon::now();
        $sigue = $this->proceso_hasta !== null && $this->proceso_hasta->greaterThan($ahora);

        if ($sigue) {
            $this->proceso_hasta = $this->proceso_hasta->copy()->addMinutes($minutos);

            return;
        }

        $this->proceso_desde = $ahora;
        $this->proceso_hasta = $ahora->copy()->addMinutes($minutos);
    }

    public function procesoListo(): bool
    {
        return $this->proceso_hasta !== null && $this->proceso_hasta->lessThanOrEqualTo(Carbon::now());
    }
}
