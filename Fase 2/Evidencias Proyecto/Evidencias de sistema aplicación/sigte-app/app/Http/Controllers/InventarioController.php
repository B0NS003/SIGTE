<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\ElementoInventario;
use App\Models\Reposicion;
use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class InventarioController extends Controller
{
    use ResuelveUsuario;

    private function inventarioItems(): array
    {
        return ElementoInventario::query()
            ->with('salaFisica')
            ->orderBy('nombre')
            ->get()
            ->map(fn (ElementoInventario $elemento) => [
                'sala' => $elemento->codigoSala(),
                'codigo' => $elemento->codigo,
                'nombre' => $elemento->nombre,
                'ubicacion' => $elemento->ubicacion,
                'stock' => $elemento->stock,
                'minimo' => $elemento->minimo,
                'en_proceso' => $elemento->en_proceso,
                'estado' => $elemento->estadoStock(),
            ])
            ->all();
    }
    public function inventario(): View
    {
        $items = $this->inventarioItems();
        $salas = [
            'lavado' => 'Sala lavado',
            'armado' => 'Sala armado',
            'esteril' => 'Material estéril',
        ];
        $sala = request()->query('sala', 'todos');
        if ($sala !== 'todos' && ! isset($salas[$sala])) {
            $sala = 'todos';
        }
        $busqueda = trim((string) request()->query('q', ''));
        $libros = [];
        foreach ($salas as $clave => $nombre) {
            $delLibro = collect($items)->where('sala', $clave);
            $libros[] = [
                'clave' => $clave,
                'nombre' => $nombre,
                'tipos' => $delLibro->count(),
                'stock' => (int) $delLibro->sum('stock'),
                'bajo' => $delLibro->whereIn('estado', ['bajo', 'critico'])->count(),
                'en_proceso' => (int) $delLibro->sum('en_proceso'),
            ];
        }

        $itemsSala = $sala === 'todos'
            ? collect()
            : collect($items)
                ->where('sala', $sala)
                ->sortBy(fn(array $item) => [
                    'crítico' => 0,
                    'bajo' => 1,
                    'ok' => 2,
                ][$item['estado']] ?? 9)
                ->values();

        $filtrados = $itemsSala->all();

        $atencion = collect($items)->whereIn('estado', ['bajo', 'critico'])->values()->all();
        $rol = Auth::user()->nombreRol();
        return view('inventario.index', [
            'usuario' => $this->usuarioSesion(),
            'rol' => $rol,
            'esAdministradora' => $rol === Rol::ADMINISTRADORA,
            'puedeReponer' => in_array($rol, [Rol::ENFERMERA, Rol::ADMINISTRADORA], true),
            'elemento' => trim((string) request()->query('elemento', '')),
            'busqueda' => $busqueda,
            'sala' => $sala,
            'salas' => $salas,
            'libros' => $libros,
            'items' => $filtrados,
            'elementos' => $sala === 'todos'
                ? []
                : collect($items)->where('sala', $sala)->values()->all(),
            'atencion' => $atencion,
            'resumen' => [
                'tipos' => $itemsSala->count(),
                'bajo_minimo' => $itemsSala
                    ->whereIn('estado', ['bajo', 'critico'])->count(),
                'en_almacen' => $itemsSala->sum('stock'),
                'en_proceso' => $itemsSala->sum('en_proceso'),
            ],
            'fecha' => now()->timezone('America/Santiago')->format('d/m/Y'),
            'hora' => now()->timezone('America/Santiago')->format('H:i'),
            'reposiciones' => $sala === 'todos'
                ? collect()
                : Reposicion::query()
                    ->with(['elemento', 'autor'])
                    ->whereHas('elemento.salaFisica', fn ($consulta) => $consulta->where('codigo', $sala))
                    ->latest()
                    ->limit(8)
                    ->get(),
        ]);
    }

    public function registrarReposicion(Request $request): JsonResponse|RedirectResponse
    {
        $datos = $request->validate([
            'sala' => ['required', 'in:lavado,armado,esteril'],
            'elemento' => ['required', 'string'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:9999'],
        ], [
            'sala.required' => 'Elige la sala del elemento.',
            'sala.in' => 'Elige la sala del elemento.',
            'elemento.required' => 'Elige el elemento que se repone.',
            'cantidad.required' => 'Ingresa una cantidad válida.',
            'cantidad.integer' => 'Ingresa una cantidad válida.',
            'cantidad.min' => 'Ingresa una cantidad válida.',
            'cantidad.max' => 'Ingresa una cantidad válida.',
        ]);

        $elemento = ElementoInventario::query()
            ->whereHas('salaFisica', fn ($consulta) => $consulta->where('codigo', $datos['sala']))
            ->where('codigo', $datos['elemento'])
            ->first();

        if ($elemento === null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Elige el elemento que se repone.',
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['elemento' => 'Elige el elemento que se repone.']);
        }

        $teniaAlerta = $elemento->tieneAlertaStock();

        try {
            DB::transaction(function () use ($elemento, $datos) {
                $bloqueado = ElementoInventario::query()->whereKey($elemento->id)->lockForUpdate()->first();
                $bloqueado->stock += $datos['cantidad'];
                $bloqueado->save();

                Reposicion::query()->create([
                    'elemento_inventario_id' => $bloqueado->id,
                    'cantidad' => $datos['cantidad'],
                    'user_id' => Auth::id(),
                ]);
            });
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'No fue posible registrar la reposición. La cantidad disponible no cambió.',
                ], 500);
            }

            return back()
                ->withInput()
                ->withErrors([
                    'reposicion' => 'No fue posible registrar la reposición. La cantidad disponible no cambió.',
                ]);
        }

        $elemento->refresh();
        $mensaje = 'Reposición registrada. '.$elemento->nombre.' quedó en '.$elemento->stock.' unidades.';
        if ($teniaAlerta && ! $elemento->tieneAlertaStock()) {
            $mensaje .= ' La alerta de stock quedó desactivada.';
        } elseif ($teniaAlerta) {
            $mensaje .= ' Sigue bajo el mínimo ('.$elemento->minimo.').';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'mensaje' => $mensaje,
                'elemento' => [
                    'stock' => (int) $elemento->stock,
                    'minimo' => (int) $elemento->minimo,
                    'codigo' => $elemento->codigo,
                    'sala' => $elemento->codigoSala(),
                ],
            ]);
        }

        return redirect()
            ->route('mockups.inventario', [
                'sala' => $elemento->codigoSala(),
                'elemento' => $elemento->codigo,
            ])
            ->with('ok', $mensaje);
    }
    
    public function historialElemento(string $sala, string $codigo)
    {
        $elemento = ElementoInventario::query()
            ->where('sala', $sala)
            ->where('codigo', $codigo)
            ->firstOrFail();
            
        $reposiciones = Reposicion::query()
            ->with('autor')
            ->where('elemento_inventario_id', $elemento->id)
            ->latest()
            ->get()
            ->map(fn (Reposicion $reposicion) => [
                'cantidad' => $reposicion->cantidad,
                'fecha' => $reposicion->created_at
                    ->timezone('America/Santiago')
                    ->format('d/m/Y · H:i'),
                'autor' => $reposicion->autor?->name ?? 'Sin usuario',
            ]);
            
        return response()->json([
            'elemento' => $elemento->nombre,
            'historial' => $reposiciones,
        ]);
    }
}
