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
    public static function vista(Request $request): array
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

        return array_merge($datos, [
            'falla' => $falla,
            'filtros' => $filtros,
            'tab_activo' => self::pestana($request),
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
        $desde = (string) $request->input('desde', now()->timezone('America/Santiago')->subDays(13)->toDateString());
        $hasta = (string) $request->input('hasta', $hoy);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) || ! self::fechaValida($desde)) {
            $desde = now()->timezone('America/Santiago')->subDays(13)->toDateString();
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta) || ! self::fechaValida($hasta)) {
            $hasta = $hoy;
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

    private static function fechaValida(string $fecha): bool
    {
        $fechaParseada = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);

        return $fechaParseada !== false && $fechaParseada->format('Y-m-d') === $fecha;
    }

    /**
     * Calcula los indicadores sobre TODOS los registros filtrados.
     * Las tablas web se paginan a 25 filas; las exportaciones usan $completo=true.
     *
     * @param array{desde: string, hasta: string, tipo: string, servicio: ?int} $filtros
     * @return array<string, mixed>
     */
    public static function datos(array $filtros, bool $completo = false): array
    {
        $inicio = Carbon::parse($filtros['desde'], 'America/Santiago')->startOfDay()->timezone('UTC');
        $fin = Carbon::parse($filtros['hasta'], 'America/Santiago')->endOfDay()->timezone('UTC');

        // Trazabilidad: los indicadores se calculan en SQL y NO sobre la página mostrada.
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

        // El cast a hora chilena permite agrupar fechas correctamente cerca de medianoche.
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
            : $listaAuditoria->paginate(25, ['*'], 'pagina_movimientos')
                ->withQueryString()->through($transformarMovimiento);

        // Los retrocesos tienen una tabla propia. Este total depende solo del período.
        $retrocesosPeriodo = CajaRetroceso::query()
            ->whereBetween('created_at', [$inicio, $fin])
            ->count();

        // Entregas: una operación puede tener varias cajas o solamente material.
        // Se usa EntregaCaja para que el servicio sea el DESTINO, no el origen de la caja.
        $consultaEntregas = DB::table('entrega_cajas as ec')
            ->join('entregas as e', 'e.id', '=', 'ec.entrega_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->whereBetween('e.entregado_at', [$inicio, $fin]);
        $totalCajasEntregadas = (clone $consultaEntregas)->count();
        $operacionesEntrega = Entrega::query()
            ->whereBetween('entregado_at', [$inicio, $fin])
            ->count();
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
            : $listaEntregas->paginate(25, ['*'], 'pagina_entregas')
                ->withQueryString()->through($transformarEntrega);

        // Flujo actual y recepciones: solo el segundo indicador usa fechas.
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
            'recibidas_periodo' => $recibidasPeriodo,
            'produccion' => self::produccion($filtros),
        ];
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
