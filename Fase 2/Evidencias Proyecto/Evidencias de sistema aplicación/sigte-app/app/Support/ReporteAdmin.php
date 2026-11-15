<?php

namespace App\Support;

use App\Models\Caja;
use App\Models\CajaRetroceso;
use App\Models\Entrega;
use App\Models\RegistroAuditoria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReporteAdmin
{
    /** Datos para las pestañas de reportes del panel de Administradora. */
    public static function vista(Request $request, bool $soloProduccion = false): array
    {
        $filtros = self::filtros($request);
        $falla = $request->boolean('falla');
        $datos = [
            'movimientos' => collect(),
            'por_tipo' => collect(),
            'movimientos_por_dia' => collect(),
            'total_movimientos' => 0,
            'retrocesos_periodo' => 0,
            'entregas' => collect(),
            'total_cajas_entregadas' => 0,
            'operaciones_entrega' => 0,
            'entregas_por_servicio' => collect(),
            'entregas_por_fecha' => collect(),
            'en_flujo' => collect(),
            'cajas_por_etapa' => collect(),
            'recibidas_periodo' => 0,
            'produccion' => self::produccionVacia(),
        ];

        if (! $falla) {
            try {
                $datos = self::datos($filtros);
            } catch (Throwable $e) {
                report($e);
                $falla = true;
            }
        }

        $momento = now()->timezone('America/Santiago');
        $tab = $soloProduccion ? 'produccion' : self::pestana($request);

        return array_merge($datos, [
            'falla' => $falla,
            'filtros' => $filtros,
            'tab_activo' => $tab,
            'reportes_solo_produccion' => $soloProduccion,
            'fecha' => $momento->format('d/m/Y'),
            'hora' => $momento->format('H:i'),
            'servicios_produccion' => DB::table('servicios')->orderBy('nombre')->get(['id', 'nombre']),
            'tipos_auditoria' => RegistroAuditoria::TIPOS,
        ]);
    }

    public static function pestana(Request $request): string
    {
        $tab = (string) $request->input('tab', 'produccion');

        return in_array($tab, ['produccion', 'trazabilidad', 'entregas'], true) ? $tab : 'produccion';
    }

    /** @return array{desde: string, hasta: string, tipo: string, servicio: ?int} */
    public static function filtros(Request $request): array
    {
        $hoy = now()->timezone('America/Santiago')->toDateString();
        $sinFechas = ! $request->filled('desde') && ! $request->filled('hasta');

        if ($sinFechas) {
            [$desde, $hasta] = self::rangoPorDefectoConDatos($hoy);
        } else {
            $desde = (string) $request->input('desde', now()->timezone('America/Santiago')->subDays(13)->toDateString());
            $hasta = (string) $request->input('hasta', $hoy);
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) || ! self::fechaValida($desde)) {
                $desde = now()->timezone('America/Santiago')->subDays(13)->toDateString();
            }
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta) || ! self::fechaValida($hasta)) {
                $hasta = $hoy;
            }
        }

        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }
        $tipo = (string) $request->input('tipo', '');
        if ($tipo !== '' && ! array_key_exists($tipo, RegistroAuditoria::TIPOS)) {
            $tipo = '';
        }

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'tipo' => $tipo,
            'servicio' => $request->filled('servicio') && ctype_digit((string) $request->input('servicio'))
                ? (int) $request->input('servicio')
                : null,
        ];
    }

    /**
     * Últimos ~30 días con datos reales de producción (max fecha).
     * Si no hay filas, cae a los últimos 14 días hasta hoy.
     *
     * @return array{0: string, 1: string}
     */
    private static function rangoPorDefectoConDatos(string $hoy): array
    {
        $maxFecha = DB::table('registros_produccion')->max('fecha');
        if ($maxFecha === null) {
            return [
                now()->timezone('America/Santiago')->subDays(13)->toDateString(),
                $hoy,
            ];
        }

        $hasta = Carbon::parse((string) $maxFecha)->toDateString();
        $desde = Carbon::parse($hasta)->subDays(29)->toDateString();
        $minFecha = DB::table('registros_produccion')->min('fecha');
        if ($minFecha !== null && $desde < (string) $minFecha) {
            $desde = Carbon::parse((string) $minFecha)->toDateString();
        }

        return [$desde, $hasta];
    }

    private static function fechaValida(string $fecha): bool
    {
        $fechaParseada = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);

        return $fechaParseada !== false && $fechaParseada->format('Y-m-d') === $fecha;
    }

    /**
     * Calcula los indicadores sobre TODOS los registros filtrados.
     * Las tablas web se paginan a 15 filas; las exportaciones usan $completo=true.
     *
     * @param array{desde: string, hasta: string, tipo: string, servicio: ?int} $filtros
     * @return array<string, mixed>
     */
    /**
     * @param  string|null  $solo  null = todo (panel); pestana o 'csv' = solo bloques necesarios
     */
    public static function datos(array $filtros, bool $completo = false, ?string $solo = null): array
    {
        $inicio = Carbon::parse($filtros['desde'], 'America/Santiago')->startOfDay()->timezone('UTC');
        $fin = Carbon::parse($filtros['hasta'], 'America/Santiago')->endOfDay()->timezone('UTC');
        $quiereTrazabilidad = $solo === null || in_array($solo, ['trazabilidad', 'csv'], true);
        $quiereEntregas = $solo === null || in_array($solo, ['entregas', 'csv'], true);
        $quiereProduccion = $solo === null || $solo === 'produccion';

        $movimientos = collect();
        $porTipo = collect();
        $movimientosPorDia = collect();
        $totalMovimientos = 0;
        $retrocesosPeriodo = 0;

        if ($quiereTrazabilidad) {
            // Trazabilidad: indicadores en SQL, no sobre la página mostrada.
            $consultaAuditoria = RegistroAuditoria::query()
                ->whereBetween('created_at', [$inicio, $fin]);
            if ($filtros['tipo'] !== '') {
                $consultaAuditoria->where('tipo', $filtros['tipo']);
            }
            $totalMovimientos = (clone $consultaAuditoria)->count();
            $porTipo = (clone $consultaAuditoria)
                ->select('tipo')
                ->selectRaw('COUNT(*) AS cantidad')
                ->groupBy('tipo')
                ->pluck('cantidad', 'tipo')
                ->map(fn ($cantidad) => (int) $cantidad);

            $diaAuditoria = "(created_at AT TIME ZONE 'UTC' AT TIME ZONE 'America/Santiago')::date";
            $movimientosPorDia = (clone $consultaAuditoria)
                ->selectRaw($diaAuditoria.' AS dia')
                ->selectRaw('COUNT(*) AS cantidad')
                ->groupByRaw($diaAuditoria)
                ->orderBy('dia')
                ->get()
                ->mapWithKeys(fn ($fila) => [Carbon::parse($fila->dia)->format('d/m/Y') => (int) $fila->cantidad]);

            $listaAuditoria = (clone $consultaAuditoria)
                ->with('actor')
                ->latest('created_at')
                ->latest('id');
            $transformarMovimiento = fn (RegistroAuditoria $r) => array_merge($r->paraConsulta(), [
                'tipo_etiqueta' => RegistroAuditoria::TIPOS[$r->tipo] ?? $r->tipo,
            ]);
            $movimientos = $completo
                ? $listaAuditoria->get()->map($transformarMovimiento)
                : $listaAuditoria->paginate(15, ['*'], 'pagina_movimientos')
                    ->withQueryString()->through($transformarMovimiento);

            $retrocesosPeriodo = CajaRetroceso::query()
                ->whereBetween('created_at', [$inicio, $fin])
                ->count();
        }

        $entregas = collect();
        $totalCajasEntregadas = 0;
        $operacionesEntrega = 0;
        $entregasPorServicio = collect();
        $entregasPorFecha = collect();

        if ($quiereEntregas) {
            // Entregas: servicio = DESTINO (entrega_cajas + entregas).
            $consultaEntregas = DB::table('entrega_cajas as ec')
                ->join('entregas as e', 'e.id', '=', 'ec.entrega_id')
                ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
                ->whereBetween('e.entregado_at', [$inicio, $fin]);
            $nombreServicio = self::nombreServicio($filtros['servicio']);
            if ($nombreServicio !== null) {
                $consultaEntregas->where('e.servicio', $nombreServicio);
            }
            $totalCajasEntregadas = (clone $consultaEntregas)->count();
            $consultaOperaciones = Entrega::query()
                ->whereBetween('entregado_at', [$inicio, $fin]);
            if ($nombreServicio !== null) {
                $consultaOperaciones->where('servicio', $nombreServicio);
            }
            $operacionesEntrega = $consultaOperaciones->count();
            $entregasPorServicio = (clone $consultaEntregas)
                ->select('e.servicio')
                ->selectRaw('COUNT(*) AS cantidad')
                ->groupBy('e.servicio')
                ->orderByDesc('cantidad')
                ->pluck('cantidad', 'e.servicio')
                ->map(fn ($cantidad) => (int) $cantidad);

            $diaEntrega = "(e.entregado_at AT TIME ZONE 'UTC' AT TIME ZONE 'America/Santiago')::date";
            $entregasPorFecha = (clone $consultaEntregas)
                ->selectRaw($diaEntrega.' AS dia')
                ->selectRaw('COUNT(*) AS cantidad')
                ->groupByRaw($diaEntrega)
                ->orderBy('dia')
                ->get()
                ->mapWithKeys(fn ($fila) => [(string) $fila->dia => (int) $fila->cantidad]);

            $listaEntregas = (clone $consultaEntregas)
                ->select([
                    'ec.codigo',
                    'e.codigo as operacion',
                    'e.servicio',
                    'e.recibe',
                    'e.entregado_at as entregado_utc',
                ])
                ->selectRaw("COALESCE(u.name, 'Sin registrar') AS responsable")
                ->orderByDesc('e.entregado_at')
                ->orderByDesc('ec.id');
            $transformarEntrega = fn ($fila) => [
                'codigo' => $fila->codigo,
                'operacion' => $fila->operacion,
                'servicio' => $fila->servicio,
                'cuando' => Carbon::parse($fila->entregado_utc, 'UTC')
                    ->timezone('America/Santiago')->format('d/m/Y H:i'),
                'responsable' => $fila->responsable,
                'recibe' => $fila->recibe,
            ];
            $entregas = $completo
                ? $listaEntregas->get()->map($transformarEntrega)
                : $listaEntregas->paginate(15, ['*'], 'pagina_entregas')
                    ->withQueryString()->through($transformarEntrega);
        }

        $enFlujo = collect();
        $cajasPorEtapa = collect();
        $recibidasPeriodo = 0;
        $produccion = self::produccionVacia();

        if ($quiereProduccion) {
            $consultaCajas = Caja::query();
            if ($filtros['servicio'] !== null) {
                $consultaCajas->where('servicio_id', $filtros['servicio']);
            }
            $recibidasPeriodo = (clone $consultaCajas)
                ->whereBetween('created_at', [$inicio, $fin])
                ->count();
            $enFlujo = (clone $consultaCajas)
                ->whereNull('entregado_at')
                ->select('etapa')
                ->selectRaw('COUNT(*) AS cantidad')
                ->groupBy('etapa')
                ->get()
                ->map(fn ($fila) => [
                    'etapa' => Caja::etiquetaEtapa((string) $fila->etapa),
                    'cantidad' => (int) $fila->cantidad,
                ]);

            $cajasPorEtapa = self::cajasPorEtapa($inicio, $fin, $filtros['servicio']);
            $produccion = self::produccion($filtros);
        }

        return [
            'movimientos' => $movimientos,
            'por_tipo' => $porTipo,
            'movimientos_por_dia' => $movimientosPorDia,
            'total_movimientos' => $totalMovimientos,
            'retrocesos_periodo' => $retrocesosPeriodo,
            'entregas' => $entregas,
            'total_cajas_entregadas' => $totalCajasEntregadas,
            'operaciones_entrega' => $operacionesEntrega,
            'entregas_por_servicio' => $entregasPorServicio,
            'entregas_por_fecha' => $entregasPorFecha,
            'en_flujo' => $enFlujo,
            'cajas_por_etapa' => $cajasPorEtapa,
            'recibidas_periodo' => $recibidasPeriodo,
            'produccion' => $produccion,
        ];
    }

    /**
     * Cuenta ciclos (filas de cajas), no códigos físicos.
     * Así el mismo código que reinicia el flujo en el período suma más de una vez.
     */
    private static function cajasPorEtapa(Carbon $inicio, Carbon $fin, ?int $servicioId = null): Collection
    {
        $etapas = [
            'Recepción',
            'Lavado',
            'Preparación',
            'Esterilización',
            'Almacén',
            'Entrega',
        ];

        $conteos = [];

        foreach ($etapas as $etapa) {
            $conteos[$etapa] = [];
        }

        $filtrarServicio = function ($consulta) use ($servicioId) {
            if ($servicioId !== null) {
                $consulta->where('servicio_id', $servicioId);
            }

            return $consulta;
        };

        // Recepción: cada ciclo creado en el período.
        $recepciones = $filtrarServicio(Caja::query())
            ->whereBetween('created_at', [$inicio, $fin])
            ->get(['id']);

        foreach ($recepciones as $caja) {
            $conteos['Recepción'][(string) $caja->id] = true;
        }

        // Avances: etapa destino, asociando la auditoría al ciclo vigente en ese momento.
        $avances = RegistroAuditoria::query()
            ->where('tipo', 'etapa')
            ->where('accion', 'Avanzó etapa')
            ->whereBetween('created_at', [$inicio, $fin])
            ->get(['id', 'objeto', 'detalle', 'created_at']);

        $ciclosPorCodigo = $filtrarServicio(Caja::query())
            ->whereIn('codigo', $avances->pluck('objeto')->filter()->unique()->all())
            ->get(['id', 'codigo', 'created_at', 'entregado_at'])
            ->groupBy('codigo');

        // Si hay filtro de servicio, solo contar avances de códigos con ciclo de ese servicio.
        $codigosPermitidos = $servicioId === null
            ? null
            : $ciclosPorCodigo->keys()->all();

        foreach ($avances as $avance) {
            if (! $avance->objeto || ! str_contains((string) $avance->detalle, '→')) {
                continue;
            }
            if ($codigosPermitidos !== null && ! in_array((string) $avance->objeto, $codigosPermitidos, true)) {
                continue;
            }

            $partes = explode('→', (string) $avance->detalle);
            $etapaDestino = trim((string) end($partes));

            if (! isset($conteos[$etapaDestino])) {
                continue;
            }

            $cicloId = self::cicloIdEnMomento(
                $ciclosPorCodigo->get((string) $avance->objeto, collect()),
                $avance->created_at
            ) ?? 'a:'.$avance->id;

            $conteos[$etapaDestino][$cicloId] = true;
        }

        // Retrocesos: etapa a la que regresó (por caja_id = ciclo).
        $consultaRetrocesos = CajaRetroceso::query()
            ->whereBetween('created_at', [$inicio, $fin]);
        if ($servicioId !== null) {
            $consultaRetrocesos->whereHas('caja', fn ($q) => $q->where('servicio_id', $servicioId));
        }
        $retrocesos = $consultaRetrocesos->get(['caja_id', 'etapa_hacia']);

        foreach ($retrocesos as $retroceso) {
            if ($retroceso->caja_id === null) {
                continue;
            }

            $etapaDestino = Caja::etiquetaEtapa((string) $retroceso->etapa_hacia);

            if (isset($conteos[$etapaDestino])) {
                $conteos[$etapaDestino][(string) $retroceso->caja_id] = true;
            }
        }

        // Entrega: cada ciclo cerrado en el período.
        $entregas = $filtrarServicio(Caja::query())
            ->whereNotNull('entregado_at')
            ->whereBetween('entregado_at', [$inicio, $fin])
            ->get(['id']);

        foreach ($entregas as $caja) {
            $conteos['Entrega'][(string) $caja->id] = true;
        }

        return collect($etapas)->map(fn (string $etapa) => [
            'etapa' => $etapa,
            'cantidad' => count($conteos[$etapa]),
        ]);
    }

    private static function nombreServicio(?int $servicioId): ?string
    {
        if ($servicioId === null) {
            return null;
        }

        $nombre = DB::table('servicios')->where('id', $servicioId)->value('nombre');

        return is_string($nombre) && $nombre !== '' ? $nombre : null;
    }

    /**
     * @param  Collection<int, Caja>  $ciclos
     */
    private static function cicloIdEnMomento(Collection $ciclos, ?Carbon $momento): ?string
    {
        if ($momento === null || $ciclos->isEmpty()) {
            return null;
        }

        $ciclo = $ciclos
            ->sortByDesc(fn (Caja $caja) => $caja->created_at?->getTimestamp() ?? $caja->id)
            ->first(function (Caja $caja) use ($momento): bool {
                if ($caja->created_at !== null && $caja->created_at->greaterThan($momento)) {
                    return false;
                }
                if ($caja->entregado_at !== null && $caja->entregado_at->lessThan($momento)) {
                    return false;
                }

                return true;
            });

        return $ciclo !== null ? (string) $ciclo->id : null;
    }

    private static function produccionVacia(): array
    {
        return [
            'servicios' => 0, 'cantidad' => 0, 'litros' => 0, 'materiales' => 0,
            'por_servicio' => collect(), 'por_mes' => collect(), 'detalle' => collect(),
        ];
    }

    private static function produccion(array $filtros): array
    {
        // Exclusivamente registros_produccion: evita duplicar consumos_servicio históricos.
        $base = DB::table('registros_produccion as rp')
            ->join('servicios as s', 's.id', '=', 'rp.servicio_id')
            ->whereBetween('rp.fecha', [$filtros['desde'], $filtros['hasta']]);

        if ($filtros['servicio'] !== null) {
            $base->where('rp.servicio_id', $filtros['servicio']);
        }

        $resumen = (clone $base)->selectRaw(
            'COUNT(DISTINCT rp.servicio_id) AS servicios, '.
            'COUNT(DISTINCT rp.material_id) AS materiales, '.
            'COALESCE(SUM(rp.cantidad), 0) AS cantidad, '.
            'COALESCE(SUM(rp.cantidad * rp.factor_litros), 0) AS litros'
        )->first();

        $porServicio = (clone $base)
            ->select('s.nombre')
            ->selectRaw('SUM(rp.cantidad) AS cantidad, SUM(rp.cantidad * rp.factor_litros) AS litros')
            ->groupBy('s.id', 's.nombre')
            ->orderByDesc('litros')
            ->get();

        $porMes = (clone $base)
            ->selectRaw("TO_CHAR(rp.fecha, 'YYYY-MM') AS mes")
            ->selectRaw('SUM(rp.cantidad) AS cantidad, SUM(rp.cantidad * rp.factor_litros) AS litros')
            ->groupByRaw("TO_CHAR(rp.fecha, 'YYYY-MM')")
            ->orderBy('mes')
            ->get();

        $detalle = (clone $base)
            ->selectRaw("TO_CHAR(rp.fecha, 'YYYY-MM') AS mes")
            ->addSelect('s.nombre as servicio')
            ->selectRaw('SUM(rp.cantidad) AS cantidad, SUM(rp.cantidad * rp.factor_litros) AS litros')
            ->groupByRaw("TO_CHAR(rp.fecha, 'YYYY-MM'), s.id, s.nombre")
            ->orderByDesc('mes')
            ->orderBy('s.nombre')
            ->get();

        return [
            'servicios' => (int) ($resumen->servicios ?? 0),
            'materiales' => (int) ($resumen->materiales ?? 0),
            'cantidad' => (int) ($resumen->cantidad ?? 0),
            'litros' => (float) ($resumen->litros ?? 0),
            'por_servicio' => $porServicio,
            'por_mes' => $porMes,
            'detalle' => $detalle,
        ];
    }
}
