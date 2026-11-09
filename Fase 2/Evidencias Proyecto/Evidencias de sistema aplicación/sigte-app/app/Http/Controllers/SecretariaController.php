<?php

namespace App\Http\Controllers;

use App\Models\ConsumoServicio;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use App\Models\MaterialProduccion;
use App\Models\RegistroProduccion;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecretariaController extends Controller
{
    public function produccion(Request $request): View
    {
        $periodo = $this->periodoDesdeRequest($request->query('periodo'));
        $servicio = (string) $request->query('servicio', '');
        $editando = null;

        if ($servicio !== '' && in_array($servicio, ConsumoServicio::servicios(), true) && $periodo !== null) {
            $editando = ConsumoServicio::query()
                ->where('servicio_id', Servicio::idPorNombre($servicio))
                ->whereDate('periodo', $periodo)
                ->first();
        }

        $consumos = ConsumoServicio::query()
            ->with('servicioClinico')
            ->orderByDesc('periodo')
            ->orderBy('servicio_id')
            ->get();

        return view('secretaria.produccion', [
            'usuario' => $this->usuarioSesion(),
            'servicios' => ConsumoServicio::servicios(),
            'consumos' => $consumos,
            'materiales_produccion' => MaterialProduccion::query()->where('activo', true)->orderBy('nombre')->get(),
            'servicios_produccion' => Servicio::query()->get(),
            'registros_produccion' => RegistroProduccion::query()->with(['material', 'servicio'])->orderByDesc('fecha')->orderByDesc('id')->limit(100)->get(),
            'editando' => $editando,
            'periodo_form' => $periodo ? Carbon::parse($periodo)->format('Y-m') : now()->format('Y-m'),
        ]);
    }

    public function guardarProduccion(Request $request): RedirectResponse
    {
        // El nuevo formulario diario envía material_id. El formulario mensual
        // existente sigue funcionando mientras se actualizan las vistas.
        if ($request->has('material_id')) {
            return $this->guardarProduccionDiaria($request);
        }

        $datos = $request->validate([
            'servicio' => ['required', 'string', 'in:'.implode(',', ConsumoServicio::servicios())],
            'periodo' => ['required', 'date_format:Y-m'],
            'consumo' => ['required', 'integer', 'min:0'],
            'litros' => ['required', 'numeric', 'min:0'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ], [
            'servicio.required' => 'Elige el servicio clínico.',
            'servicio.in' => 'Ese servicio no está en el listado.',
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

        $servicioId = Servicio::idPorNombre($datos['servicio']);
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
                'servicio' => $datos['servicio'],
                'periodo' => $datos['periodo'],
            ])
            ->with('ok', 'Consumo de '.$datos['servicio'].' '.$accion.' para '.ConsumoServicio::etiquetaPeriodo($periodo).'.');
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

    public function reportes(Request $request): View
    {
        $periodo = $this->periodoDesdeRequest($request->query('periodo'))
            ?? now()->startOfMonth()->toDateString();
        $filas = $this->filasReporte($periodo);
        $diarios = $this->filasProduccionDiaria($periodo);

        return view('secretaria.reportes', [
            'usuario' => $this->usuarioSesion(),
            'periodo' => Carbon::parse($periodo)->format('Y-m'),
            'periodo_etiqueta' => ConsumoServicio::etiquetaPeriodo($periodo),
            'filas' => $filas,
            'total_consumo' => $filas->sum('consumo'),
            'total_litros' => $filas->sum('litros'),
            'registros_diarios' => $diarios,
            'total_unidades_diarias' => $diarios->sum('cantidad'),
            'total_litros_diarios' => $diarios->sum(fn ($fila) => $fila->litros),
        ]);
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
