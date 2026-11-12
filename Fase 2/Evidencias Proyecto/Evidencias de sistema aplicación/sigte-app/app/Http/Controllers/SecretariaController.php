<?php

namespace App\Http\Controllers;

use App\Models\ConsumoServicio;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use App\Models\MaterialProduccion;
use App\Models\RegistroProduccion;
use App\Models\RegistroAuditoria;
use App\Models\Servicio;
use App\Support\ReporteAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class SecretariaController extends Controller
{
    public function produccion(Request $request): View
    {
        // El registro mensual agrupa los datos reales de registros_produccion,
        // incluidos los históricos importados desde 2024. No utiliza los
        // registros manuales de consumos_servicio para calcular esos totales.
        $aniosDisponibles = RegistroProduccion::query()
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM fecha)::integer AS anio')
            ->orderByDesc('anio')
            ->pluck('anio')
            ->map(fn ($anio) => (int) $anio)
            ->merge(ConsumoServicio::query()
                ->selectRaw('DISTINCT EXTRACT(YEAR FROM periodo)::integer AS anio')
                ->pluck('anio')
                ->map(fn ($anio) => (int) $anio))
            // El año actual siempre debe aparecer, aunque aún no tenga registros.
            ->push((int) now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        if ($aniosDisponibles->isEmpty()) {
            $aniosDisponibles = collect([(int) now()->year]);
        }

        $anioSolicitado = $request->query('anio');
        $anioSeleccionado = is_scalar($anioSolicitado)
            && ctype_digit((string) $anioSolicitado)
            && $aniosDisponibles->contains((int) $anioSolicitado)
                ? (int) $anioSolicitado
                : (int) now()->year;

        // Sin filtros explícitos, usar el mes actual del sistema (no el
        // último mes que tenga producción cargada).
        $mesPorDefecto = (int) now()->month;
        $mesSolicitado = $request->query('mes');
        $mesSeleccionado = is_scalar($mesSolicitado)
            && ctype_digit((string) $mesSolicitado)
            && (int) $mesSolicitado >= 1
            && (int) $mesSolicitado <= 12
                ? (int) $mesSolicitado
                : $mesPorDefecto;

        $inicioMes = Carbon::create($anioSeleccionado, $mesSeleccionado, 1)->startOfMonth()->toDateString();
        $finMes = Carbon::create($anioSeleccionado, $mesSeleccionado, 1)->endOfMonth()->toDateString();

        $sumasPorServicio = RegistroProduccion::query()
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->selectRaw('servicio_id, COUNT(*) AS registros, COALESCE(SUM(cantidad),0) AS unidades, COALESCE(SUM(cantidad * factor_litros),0) AS litros')
            ->groupBy('servicio_id')
            ->get()
            ->keyBy('servicio_id');

        // El historial manual se consulta únicamente para permitir abrir
        // su edición, sin sumar sus cifras a la producción diaria.
        $manualesMes = ConsumoServicio::query()
            ->whereDate('periodo', $inicioMes)
            ->pluck('servicio_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        // El conteo del mes comprende todos los servicios, no solo la página visible.
        $totalRegistrosMes = (int) $sumasPorServicio->sum(fn ($grupo) => (int) $grupo->registros);

        // La tabla mensual pagina servicios de 10 en 10 desde PostgreSQL.
        // El catálogo completo se conserva para los selectores y la edición.
        $serviciosCatalogo = Servicio::query()->orderBy('nombre')->get();
        $serviciosMes = Servicio::query()
            ->orderBy('nombre')
            ->paginate(10, ['*'], 'mensual_page')
            ->appends([
                'seccion' => 'mensual',
                'anio' => $anioSeleccionado,
                'mes' => $mesSeleccionado,
            ])
            ->fragment('consulta-mensual');

        $serviciosMes->through(function ($servicio) use ($sumasPorServicio, $manualesMes) {
            $grupo = $sumasPorServicio->get($servicio->id);
            return [
                'tiene_manual' => in_array((int) $servicio->id, $manualesMes, true),
                'id' => (int) $servicio->id,
                'nombre' => $servicio->nombre,
                'registros' => (int) ($grupo->registros ?? 0),
                'unidades' => (int) ($grupo->unidades ?? 0),
                'litros' => (float) ($grupo->litros ?? 0),
            ];
        });

        $idDetalleSolicitado = $request->query('detalle_servicio');
        $idServicioDetalle = is_scalar($idDetalleSolicitado) && ctype_digit((string) $idDetalleSolicitado)
            && $serviciosCatalogo->contains('id', (int) $idDetalleSolicitado)
                ? (int) $idDetalleSolicitado : null;
        $servicioDetalle = $idServicioDetalle !== null
            ? $serviciosCatalogo->firstWhere('id', $idServicioDetalle)
            : null;
        $detalleProduccion = $idServicioDetalle !== null
            ? RegistroProduccion::query()
                ->with(['material', 'servicio'])
                ->where('servicio_id', $idServicioDetalle)
                ->whereBetween('fecha', [$inicioMes, $finMes])
                ->orderBy('fecha')
                ->orderBy('id')
                ->paginate(10, ['*'], 'detalle_page')
                ->withQueryString()
                ->fragment('detalle-mensual')
            : null;

        $idEditarSolicitado = $request->query('editar_registro');
        $editandoDiario = $idServicioDetalle !== null
            && is_scalar($idEditarSolicitado) && ctype_digit((string) $idEditarSolicitado)
                ? RegistroProduccion::query()
                    ->with(['material', 'servicio'])
                    ->whereKey((int) $idEditarSolicitado)
                    ->where('servicio_id', $idServicioDetalle)
                    ->whereBetween('fecha', [$inicioMes, $finMes])
                    ->first()
                : null;

        // Mantener disponibles los antiguos consumos mensuales manuales,
        // sin mezclarlos con el resumen calculado desde producción diaria.
        $periodo = $this->periodoDesdeRequest($request->query('periodo'));
        $servicioConsultado = $request->query('servicio_id');
        $editando = null;
        if ($periodo !== null && is_scalar($servicioConsultado)
            && ctype_digit((string) $servicioConsultado)) {
            $editando = ConsumoServicio::query()
                ->where('servicio_id', (int) $servicioConsultado)
                ->whereDate('periodo', $periodo)
                ->first();
        }

        return view('secretaria.produccion', [
            'usuario' => $this->usuarioSesion(),
            'anios_disponibles' => $aniosDisponibles,
            'anio_seleccionado' => $anioSeleccionado,
            'mes_seleccionado' => $mesSeleccionado,
            'servicios_mes' => $serviciosMes,
            'total_registros_mes' => $totalRegistrosMes,
            'servicio_detalle' => $servicioDetalle,
            'servicio_detalle_tiene_manual' => $idServicioDetalle !== null && in_array($idServicioDetalle, $manualesMes, true),
            'detalle_produccion' => $detalleProduccion,
            'editando_diario' => $editandoDiario,
            'materiales_produccion' => MaterialProduccion::query()->where('activo', true)->orderBy('nombre')->get(),
            'servicios_produccion' => $serviciosCatalogo,
            'registros_produccion' => RegistroProduccion::query()
                ->with(['material', 'servicio'])
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString()
                ->fragment('produccion-registros'),
            'editando' => $editando,
            'periodo_form' => $periodo ? Carbon::parse($periodo)->format('Y-m') : sprintf('%04d-%02d', $anioSeleccionado, $mesSeleccionado),
        ]);
    }

    public function guardarProduccion(Request $request): RedirectResponse
    {
        // El nuevo formulario diario envía material_id. El formulario mensual
        // existente sigue funcionando mientras se actualizan las vistas.
        if ($request->filled('registro_diario_id')) {
            return $this->actualizarProduccionDiaria($request);
        }
        if ($request->has('material_id')) {
            return $this->guardarProduccionDiaria($request);
        }

        $datos = $request->validate([
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'periodo' => ['required', 'date_format:Y-m'],
            'consumo' => ['required', 'integer', 'min:0'],
            'litros' => ['required', 'numeric', 'min:0'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ], [
            'servicio_id.required' => 'Elige el servicio clínico.',
            'servicio_id.exists' => 'Ese servicio no está en el listado.',
            'periodo.required' => 'Indica el mes del consumo.',
            'periodo.date_format' => 'El período debe ser un mes válido.',
            'consumo.required' => 'Indica el consumo del servicio.',
            'consumo.integer' => 'El consumo debe ser un número entero.',
            'consumo.min' => 'El consumo no puede ser negativo.',
            'litros.required' => 'Indica los litros del período.',
            'litros.numeric' => 'Los litros deben ser un número.',
            'litros.min' => 'Los litros no pueden ser negativos.',
        ]);

        $periodo = $this->periodoDesdeRequest($datos['periodo']);
        if ($periodo === null) {
            return back()->withInput()->withErrors([
                'periodo' => 'El período debe ser un mes válido.',
            ]);
        }

        $servicioId = (int) $datos['servicio_id'];
        $nombreServicio = Servicio::query()->findOrFail($servicioId)->nombre;
        $existia = ConsumoServicio::query()
            ->where('servicio_id', $servicioId)
            ->whereDate('periodo', $periodo)
            ->exists();

        ConsumoServicio::query()->updateOrCreate(
            [
                'servicio_id' => $servicioId,
                'periodo' => $periodo,
            ],
            [
                'consumo' => $datos['consumo'],
                'litros' => $datos['litros'],
                'observacion' => $datos['observacion'] ?? null,
                'user_id' => Auth::id(),
            ]
        );

        $accion = $existia ? 'actualizado' : 'registrado';

        return redirect()
            ->route('secretaria.produccion', [
                'seccion' => 'mensual',
                'anio' => Carbon::parse($periodo)->year,
                'mes' => Carbon::parse($periodo)->month,
                'servicio_id' => $servicioId,
                'periodo' => $datos['periodo'],
            ])
            ->with('ok', 'Consumo de '.$nombreServicio.' '.$accion.' para '.ConsumoServicio::etiquetaPeriodo($periodo).'.');
    }

    /**
     * Corrige una fila diaria existente; los totales mensuales del servicio
     * se recalculan automáticamente en la siguiente consulta.
     * No modifica los registros mensuales manuales ni crea duplicados.
     */
    private function actualizarProduccionDiaria(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'registro_diario_id' => ['required', 'integer', 'exists:registros_produccion,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            // Los Excel antiguos pueden tener factor 0 porque está pendiente.
            // Permitir mantenerlo al editar, sin inventar una equivalencia.
            'factor_litros' => ['required', 'numeric', 'gte:0', 'decimal:0,2', 'max:99999999.99'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ], [
            'cantidad.required' => 'Ingresa la cantidad del registro.',
            'cantidad.min' => 'La cantidad debe ser mayor que cero.',
            'factor_litros.required' => 'Ingresa el factor de litros o 0 si sigue pendiente.',
            'factor_litros.decimal' => 'El factor admite hasta dos decimales.',
        ]);

        $registro = RegistroProduccion::query()->findOrFail($datos['registro_diario_id']);
        $cantidadAnterior = (int) $registro->cantidad;
        $factorAnterior = (float) $registro->factor_litros;

        DB::transaction(function () use ($registro, $datos, $cantidadAnterior, $factorAnterior) {
            $registro->update([
                'cantidad' => (int) $datos['cantidad'],
                'factor_litros' => $datos['factor_litros'],
                'observacion' => $datos['observacion'] ?? null,
            ]);

            // Registrar quién hizo la corrección, sin reescribir el usuario
            // responsable ni la fecha original de la producción importada.
            RegistroAuditoria::query()->create([
                'user_id' => Auth::id(),
                'accion' => 'Corrigió producción diaria',
                'tipo' => 'produccion',
                'objeto' => 'Registro #'.$registro->id,
                'detalle' => 'Cantidad '.$cantidadAnterior.' a '.$registro->cantidad.'; factor '.number_format($factorAnterior, 2, '.', '').' a '.number_format((float) $registro->factor_litros, 2, '.', '').'.',
            ]);
        });

        return redirect()->route('secretaria.produccion', [
            'seccion' => 'mensual',
            'anio' => $registro->fecha->year,
            'mes' => $registro->fecha->month,
            'detalle_servicio' => $registro->servicio_id,
        ])->with('ok', 'Registro diario #'.$registro->id.' actualizado. Se recalcularon los totales mensuales.');
    }

    private function guardarProduccionDiaria(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d'],
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'material_id' => ['required', 'integer', 'exists:materiales_produccion,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'factor_litros' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:99999999.99'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ], [
            'fecha.required' => 'Indica la fecha de producción.',
            'servicio_id.required' => 'Selecciona un servicio clínico.',
            'material_id.required' => 'Selecciona un material.',
            'cantidad.required' => 'Indica la cantidad producida.',
            'cantidad.min' => 'La cantidad debe ser mayor que cero.',
            'factor_litros.required' => 'Indica el factor de litros del material.',
            'factor_litros.gt' => 'El factor de litros debe ser mayor que cero.',
            'factor_litros.decimal' => 'El factor admite hasta dos decimales.',
        ]);

        $material = MaterialProduccion::query()->findOrFail($datos['material_id']);
        if (! $material->activo) {
            return back()->withInput()->withErrors([
                'material_id' => 'El material seleccionado no está activo.',
            ]);
        }

        RegistroProduccion::query()->create([
            'fecha' => $datos['fecha'],
            'servicio_id' => $datos['servicio_id'],
            'material_id' => $material->id,
            'cantidad' => $datos['cantidad'],
            'factor_litros' => $datos['factor_litros'],
            'user_id' => Auth::id(),
            'observacion' => $datos['observacion'] ?? null,
        ]);

        return redirect()->route('secretaria.produccion')
            ->with('ok', 'Producción diaria registrada correctamente.');
    }

    public function insumos(): View
    {
        $insumos = Insumo::query()->with('salaFisica')->orderBy('nombre')->get();
        $movimientos = MovimientoInsumo::query()
            ->with(['insumo.salaFisica', 'autor'])
            ->latest()
            ->limit(12)
            ->get();

        return view('secretaria.insumos', [
            'usuario' => $this->usuarioSesion(),
            'insumos' => $insumos,
            'movimientos' => $movimientos,
            'bajo_minimo' => $insumos->filter(fn (Insumo $insumo) => $insumo->estadoStock() !== 'ok')->count(),
        ]);
    }

    public function guardarMovimiento(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'insumo_id' => ['required', 'integer', 'exists:elementos_inventario,id'],
            'tipo' => ['required', 'in:'.MovimientoInsumo::ENTRADA.','.MovimientoInsumo::SALIDA],
            'cantidad' => ['required', 'integer', 'min:1'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ], [
            'insumo_id.required' => 'Elige un insumo.',
            'insumo_id.exists' => 'Ese insumo no está en el inventario.',
            'tipo.required' => 'Indica si es entrada o salida.',
            'tipo.in' => 'El movimiento debe ser entrada o salida.',
            'cantidad.required' => 'Indica la cantidad.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
        ]);

        $insumo = Insumo::query()->findOrFail($datos['insumo_id']);

        if ($datos['tipo'] === MovimientoInsumo::SALIDA && $insumo->stock < $datos['cantidad']) {
            return back()
                ->withInput()
                ->withErrors([
                    'cantidad' => 'No hay stock suficiente. Disponible: '.$insumo->stock.'.',
                ]);
        }

        DB::transaction(function () use ($datos, $insumo) {
            $delta = $datos['tipo'] === MovimientoInsumo::ENTRADA
                ? $datos['cantidad']
                : -$datos['cantidad'];

            $insumo->stock += $delta;
            $insumo->save();

            MovimientoInsumo::query()->create([
                'elemento_inventario_id' => $insumo->id,
                'tipo' => $datos['tipo'],
                'cantidad' => $datos['cantidad'],
                'observacion' => $datos['observacion'] ?? null,
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('secretaria.insumos')
            ->with('ok', 'Movimiento registrado. El stock de '.$insumo->nombre.' quedó actualizado.');
    }

    /**
     * Reportes equivalentes a Administradora; se reutilizan los mismos datos
     * para Producción, Trazabilidad y Entregas.
     */
    public function reportes(Request $request): View
    {
        return view('secretaria.reportes', array_merge(
            ReporteAdmin::vista($request),
            ['usuario' => $this->usuarioSesion()]
        ));
    }

    /** Excel idéntico al de Administradora; autorización controlada por la ruta de Secretaría. */
    public function exportarReportesExcel(Request $request): StreamedResponse
    {
        return app(AdminController::class)->exportarReportesExcel($request);
    }

    /** PDF idéntico al de Administradora, incluida la identificación del usuario autenticado. */
    public function exportarReportesPdf(Request $request): Response
    {
        return app(AdminController::class)->exportarReportesPdf($request);
    }

    public function exportarReportes(Request $request): StreamedResponse
    {
        $periodo = $this->periodoDesdeRequest($request->query('periodo'))
            ?? now()->startOfMonth()->toDateString();
        $filas = $this->filasReporte($periodo);
        $mes = Carbon::parse($periodo)->format('Y-m');
        $nombre = 'produccion-'.$mes.'.csv';

        return response()->streamDownload(function () use ($filas, $periodo) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Servicio', 'Periodo', 'Consumo', 'Litros', 'Observacion']);

            foreach ($filas as $fila) {
                fputcsv($salida, [
                    $fila->servicio,
                    ConsumoServicio::etiquetaPeriodo($periodo),
                    $fila->consumo,
                    number_format((float) $fila->litros, 2, '.', ''),
                    $fila->observacion ?? '',
                ]);
            }

            fclose($salida);
        }, $nombre, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{nombre: string, rol: string}
     */
    private function usuarioSesion(): array
    {
        $user = Auth::user();

        return [
            'nombre' => $user->name,
            'rol' => 'Secretaria',
        ];
    }

    private function periodoDesdeRequest(mixed $valor): ?string
    {
        if (! is_string($valor) || ! preg_match('/^\d{4}-\d{2}$/', $valor)) {
            return null;
        }

        [$anio, $mes] = array_map('intval', explode('-', $valor));
        if ($mes < 1 || $mes > 12) {
            return null;
        }

        return Carbon::create($anio, $mes, 1)->toDateString();
    }

    /**
     * @return \Illuminate\Support\Collection<int, ConsumoServicio>
     */
    private function filasProduccionDiaria(string $periodo)
    {
        $inicio = Carbon::parse($periodo)->startOfMonth()->toDateString();
        $fin = Carbon::parse($periodo)->endOfMonth()->toDateString();

        return RegistroProduccion::query()
            ->with(['material', 'servicio'])
            ->whereBetween('fecha', [$inicio, $fin])
            ->orderBy('fecha')
            ->orderBy('servicio_id')
            ->get();
    }

    private function filasReporte(string $periodo)
    {
        return ConsumoServicio::query()
            ->with('servicioClinico')
            ->whereDate('periodo', $periodo)
            ->orderBy('servicio_id')
            ->get();
    }
}
