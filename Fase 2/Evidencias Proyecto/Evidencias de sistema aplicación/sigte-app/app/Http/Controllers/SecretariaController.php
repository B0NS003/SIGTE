<?php

namespace App\Http\Controllers;

use App\Models\ConsumoServicio;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
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
                ->where('servicio', $servicio)
                ->whereDate('periodo', $periodo)
                ->first();
        }

        $consumos = ConsumoServicio::query()
            ->orderByDesc('periodo')
            ->orderBy('servicio')
            ->get();

        return view('mockups.secretaria.produccion', [
            'usuario' => $this->usuarioSesion(),
            'servicios' => ConsumoServicio::servicios(),
            'consumos' => $consumos,
            'editando' => $editando,
            'periodo_form' => $periodo ? Carbon::parse($periodo)->format('Y-m') : now()->format('Y-m'),
        ]);
    }

    public function guardarProduccion(Request $request): RedirectResponse
    {
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

        $existia = ConsumoServicio::query()
            ->where('servicio', $datos['servicio'])
            ->whereDate('periodo', $periodo)
            ->exists();

        ConsumoServicio::query()->updateOrCreate(
            [
                'servicio' => $datos['servicio'],
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

    public function insumos(): View
    {
        $insumos = Insumo::query()->orderBy('nombre')->get();
        $movimientos = MovimientoInsumo::query()
            ->with(['insumo', 'autor'])
            ->latest()
            ->limit(12)
            ->get();

        return view('mockups.secretaria.insumos', [
            'usuario' => $this->usuarioSesion(),
            'insumos' => $insumos,
            'movimientos' => $movimientos,
            'bajo_minimo' => $insumos->filter(fn (Insumo $insumo) => $insumo->estadoStock() !== 'ok')->count(),
        ]);
    }

    public function guardarMovimiento(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'insumo_id' => ['required', 'integer', 'exists:insumos,id'],
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
                'insumo_id' => $insumo->id,
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

        return view('mockups.secretaria.reportes', [
            'usuario' => $this->usuarioSesion(),
            'periodo' => Carbon::parse($periodo)->format('Y-m'),
            'periodo_etiqueta' => ConsumoServicio::etiquetaPeriodo($periodo),
            'filas' => $filas,
            'total_consumo' => $filas->sum('consumo'),
            'total_litros' => $filas->sum('litros'),
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
    private function filasReporte(string $periodo)
    {
        return ConsumoServicio::query()
            ->whereDate('periodo', $periodo)
            ->orderBy('servicio')
            ->get();
    }
}
