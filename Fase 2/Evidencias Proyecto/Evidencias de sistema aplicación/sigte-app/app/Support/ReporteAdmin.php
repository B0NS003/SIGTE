<?php

namespace App\Support;

use App\Models\Caja;
use App\Models\RegistroAuditoria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReporteAdmin
{
    /**
     * Variables que usa la sección de reportes del resumen de la administradora.
     *
     * @return array<string, mixed>
     */
    public static function vista(Request $request): array
    {
        $filtros = self::filtros($request);
        $falla = $request->boolean('falla');
        $datos = [
            'movimientos' => collect(),
            'por_tipo' => collect(),
            'entregas' => collect(),
            'en_flujo' => collect(),
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

        return [
            'falla' => $falla,
            'filtros' => $filtros,
            'fecha' => $momento->format('d/m/Y'),
            'hora' => $momento->format('H:i'),
            'movimientos' => $datos['movimientos'],
            'por_tipo' => $datos['por_tipo'],
            'entregas' => $datos['entregas'],
            'en_flujo' => $datos['en_flujo'],
            'produccion' => $datos['produccion'],
            'servicios_produccion' => DB::table('servicios')->orderBy('nombre')->get(['id', 'nombre']),
            'tipos_auditoria' => RegistroAuditoria::TIPOS,
        ];
    }

    /**
     * @return array{desde: string, hasta: string, tipo: string, servicio: ?int}
     */
    public static function filtros(Request $request): array
    {
        $hoy = now()->timezone('America/Santiago')->toDateString();
        $inicioAnio = now()->timezone('America/Santiago')->startOfYear()->toDateString();
        $desde = (string) $request->input('desde', $inicioAnio);
        $hasta = (string) $request->input('hasta', $hoy);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
            $desde = $inicioAnio;
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
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
            'servicio' => $request->filled('servicio') && ctype_digit((string) $request->input('servicio')) ? (int) $request->input('servicio') : null,
        ];
    }

    /**
     * @param  array{desde: string, hasta: string, tipo: string, servicio: ?int}  $filtros
     * @return array{movimientos: Collection<int, array<string, string>>, por_tipo: Collection<string, int>, entregas: Collection<int, array<string, string>>, en_flujo: Collection<int, array<string, string|int>>, produccion: array<string, mixed>}
     */
    public static function datos(array $filtros): array
    {
        $inicio = Carbon::parse($filtros['desde'], 'America/Santiago')->startOfDay()->timezone('UTC');
        $fin = Carbon::parse($filtros['hasta'], 'America/Santiago')->endOfDay()->timezone('UTC');

        $consulta = RegistroAuditoria::query()
            ->with('actor')
            ->whereBetween('created_at', [$inicio, $fin])
            ->latest('created_at')
            ->latest('id');
        if ($filtros['tipo'] !== '') {
            $consulta->where('tipo', $filtros['tipo']);
        }

        $registros = $consulta->limit(500)->get();
        $movimientos = $registros->map(fn (RegistroAuditoria $r) => array_merge($r->paraConsulta(), [
            'tipo_etiqueta' => RegistroAuditoria::TIPOS[$r->tipo] ?? $r->tipo,
        ]));

        $porTipo = $registros->groupBy('tipo')->map->count();

        $entregas = Caja::query()
            ->with(['responsable', 'persona'])
            ->whereNotNull('entregado_at')
            ->whereBetween('entregado_at', [$inicio, $fin])
            ->orderByDesc('entregado_at')
            ->limit(100)
            ->get()
            ->map(fn (Caja $caja) => [
                'codigo' => $caja->codigo,
                'servicio' => $caja->servicio,
                'cuando' => $caja->entregado_at->timezone('America/Santiago')->format('d/m/Y H:i'),
                'responsable' => $caja->nombreResponsable(),
            ]);

        $enFlujo = Caja::query()
            ->whereNull('entregado_at')
            ->get()
            ->groupBy(fn (Caja $caja) => Caja::etiquetaEtapa((string) $caja->etapa))
            ->map(fn ($grupo, $etapa) => [
                'etapa' => $etapa,
                'cantidad' => $grupo->count(),
            ])
            ->values();

        return [
            'movimientos' => $movimientos,
            'por_tipo' => $porTipo,
            'entregas' => $entregas,
            'en_flujo' => $enFlujo,
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
        // Se utiliza únicamente registros_produccion: no se suman los
        // agregados de consumos_servicio para evitar duplicar consumos.
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
