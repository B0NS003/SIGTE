<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'urgente',
        'responsable_id',
        'etapa_desde',
    ];

    protected function casts(): array
    {
        return [
            'urgente' => 'boolean',
            'etapa_desde' => 'datetime',
        ];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
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
        $indice = array_search($this->etapa, self::etapas(), true);

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
}
