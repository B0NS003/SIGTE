<?php

namespace App\Support;

use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use App\Models\RegistroProduccion;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Datos reales de la página de inicio de Secretaría.
 * La producción diaria y el histórico mensual se mantienen separados.
 */
class ResumenSecretaria
{
    public static function construir(mixed $periodoSolicitado = null): array
    {
        $ahora = Carbon::now('America/Santiago');
        $mes = self::resolverMes($periodoSolicitado, $ahora);
        $desde = $mes->copy()->startOfMonth()->toDateString();
        $hasta = $mes->copy()->endOfMonth()->toDateString();

        // Indicadores calculados sobre registros diarios, igual que el reporte diario.
        $produccionMes = RegistroProduccion::query()
            ->whereBetween('fecha', [$desde, $hasta])
            ->get(['id', 'fecha', 'servicio_id', 'cantidad', 'factor_litros']);

        $insumos = Insumo::query()
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre', 'stock', 'minimo']);

        $conAlerta = $insumos
            ->filter(fn (Insumo $insumo) => $insumo->estadoStock() !== 'ok')
            ->sort(function (Insumo $a, Insumo $b) {
                $nivelA = $a->estadoStock() === 'critico' ? 0 : 1;
                $nivelB = $b->estadoStock() === 'critico' ? 0 : 1;

                return ($nivelA <=> $nivelB) ?: ($a->stock <=> $b->stock);
            })
            ->values();

        // El gráfico muestra seis meses terminando en el período elegido.
        $primerMesGrafico = $mes->copy()->subMonthsNoOverflow(5)->startOfMonth();
        $fechasGrafico = RegistroProduccion::query()
            ->whereBetween('fecha', [$primerMesGrafico->toDateString(), $hasta])
            ->get(['fecha', 'cantidad', 'factor_litros']);

        $meses = [];
        for ($i = 0; $i < 6; $i++) {
            $fecha = $primerMesGrafico->copy()->addMonthsNoOverflow($i);
            $clave = $fecha->format('Y-m');
            $meses[$clave] = [
                'clave' => $clave,
                'etiqueta' => ucfirst($fecha->locale('es')->isoFormat('MMM')) . ' ' . $fecha->format('Y'),
                'litros' => 0.0,
            ];
        }

        foreach ($fechasGrafico as $registro) {
            $clave = $registro->fecha->format('Y-m');
            if (isset($meses[$clave])) {
                $meses[$clave]['litros'] += (float) $registro->litros;
            }
        }

        $maximo = max(0.0, ...array_column($meses, 'litros'));
        foreach ($meses as &$fila) {
            $fila['porcentaje'] = $maximo > 0
                ? round(($fila['litros'] / $maximo) * 100, 2)
                : 0;
        }
        unset($fila);

        $registrosRecientes = RegistroProduccion::query()
            ->with(['servicio:id,nombre', 'material:id,nombre', 'usuario:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(7)
            ->get();

        $movimientosRecientes = MovimientoInsumo::query()
            ->with(['insumo:id,codigo,nombre', 'autor:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(7)
            ->get();

        $actividad = collect();
        foreach ($registrosRecientes as $registro) {
            $actividad->push([
                'tipo' => 'produccion',
                'titulo' => 'Producción registrada',
                'detalle' => ($registro->servicio?->nombre ?? 'Sin servicio') . ' · '
                    . ($registro->material?->nombre ?? 'Material') . ' · '
                    . number_format((float) $registro->litros, 2, ',', '.') . ' L',
                'autor' => $registro->usuario?->name ?? 'Sin registrar',
                'momento' => $registro->created_at?->copy()->timezone('America/Santiago'),
                'orden' => $registro->created_at?->getTimestamp() ?? 0,
            ]);
        }
        foreach ($movimientosRecientes as $movimiento) {
            $actividad->push([
                'tipo' => 'insumo',
                'titulo' => $movimiento->etiquetaTipo() . ' de insumo',
                'detalle' => ($movimiento->insumo?->nombre ?? 'Insumo') . ' · '
                    . number_format((int) $movimiento->cantidad, 0, ',', '.') . ' unidades',
                'autor' => $movimiento->autor?->name ?? 'Sin registrar',
                'momento' => $movimiento->created_at?->copy()->timezone('America/Santiago'),
                'orden' => $movimiento->created_at?->getTimestamp() ?? 0,
            ]);
        }

        return [
            'periodo' => $mes->format('Y-m'),
            'periodo_etiqueta' => ucfirst($mes->locale('es')->isoFormat('MMMM YYYY')),
            'servicios' => $produccionMes->pluck('servicio_id')->filter()->unique()->count(),
            'litros' => round((float) $produccionMes->sum(fn ($r) => (float) $r->litros), 2),
            'consumo' => (int) $produccionMes->sum('cantidad'),
            'registros' => $produccionMes->count(),
            'bajo_minimo' => $conAlerta->count(),
            'insumos_alerta' => $conAlerta->take(5)->map(fn (Insumo $i) => [
                'codigo' => $i->codigo,
                'nombre' => $i->nombre,
                'stock' => $i->stock,
                'minimo' => $i->minimo,
                'estado' => $i->estadoStock(),
            ])->all(),
            'actividad' => $actividad->sortByDesc('orden')->take(5)->values()->all(),
            'meses' => array_values($meses),
            'hay_datos_grafico' => $maximo > 0,
        ];
    }

    private static function resolverMes(mixed $valor, Carbon $ahora): Carbon
    {
        if (! is_string($valor) || ! preg_match('/^(\d{4})-(\d{2})$/', $valor, $coincidencias)) {
            return $ahora->copy()->startOfMonth();
        }

        $anio = (int) $coincidencias[1];
        $numeroMes = (int) $coincidencias[2];
        if (! checkdate($numeroMes, 1, $anio)) {
            return $ahora->copy()->startOfMonth();
        }

        return Carbon::create($anio, $numeroMes, 1, 0, 0, 0, 'America/Santiago')->startOfMonth();
    }
}
