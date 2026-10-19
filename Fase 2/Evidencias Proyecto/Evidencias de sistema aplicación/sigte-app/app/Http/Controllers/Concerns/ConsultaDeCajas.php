<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Caja;

trait ConsultaDeCajas
{
    private function fases(): array
    {
        return array_map(
            fn (string $etapa) => Caja::etiquetaEtapa($etapa),
            Caja::etapas()
        );
    }
    /**
     * Cajas desde PostgreSQL, en el formato que usan las vistas del mockup.
     *
     * @return list<array{id: string, servicio: string, fase_idx: int, estado: string, ubicacion: string, operadora: string, fecha: string, hora: string, tiempo: string, minutos: int|null, urgente: bool}>
     */
    private function cajas(): array
    {
        return Caja::query()
            ->with(['responsable', 'servicioClinico'])
            ->whereNull('entregado_at')
            ->orderByDesc('urgente')
            ->orderBy('etapa_desde')
            ->get()
            ->map(function (Caja $caja) {
                return [
                    'id' => $caja->codigo,
                    'servicio' => $caja->servicio,
                    'fase_idx' => $caja->indiceEtapa(),
                    'estado' => $caja->estado ?? Caja::etiquetaEtapa($caja->etapa),
                    'actividad' => $caja->actividad,
                    'ubicacion' => $caja->ubicacion ?? 'Sin ubicacion',
                    'operadora' => $caja->nombreResponsable(),
                    'fecha' => $caja->etapa_desde?->timezone('America/Santiago')->format('d/m/Y') ?? '—',
                    'hora' => $caja->etapa_desde?->timezone('America/Santiago')->format('H:i') ?? '—',
                    'tiempo' => $caja->tiempoEnEtapa(),
                    'etapa_iso' => $caja->etapa_desde?->toIso8601String(),
                    'minutos' => $caja->etapa_desde === null
                        ? null
                        : (int) abs($caja->etapa_desde->diffInMinutes(now())),
                    'urgente' => $caja->urgente,
                    'proceso_desde' => $caja->proceso_desde?->toIso8601String(),
                    'proceso_hasta' => $caja->proceso_hasta?->toIso8601String(),
                    'proceso_listo' => $caja->procesoListo(),
                ];
            })
            ->all();
    }
}
