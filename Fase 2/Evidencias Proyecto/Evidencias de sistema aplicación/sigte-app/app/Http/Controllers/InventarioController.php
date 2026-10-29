<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\ElementoInventario;
use App\Models\Reposicion;
use App\Models\Rol;
use App\Support\Auditoria;
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
            ->orderBy('nombre')
            ->get()
            ->map(fn (ElementoInventario $elemento) => [
                'sala' => $elemento->sala,
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
        $filtrados = $sala === 'todos'
            ? []
            : collect($items)
                ->where('sala', $sala)
                ->sortBy(fn (array $item) => ['critico' => 0, 'bajo' => 1, 'ok' => 2][$item['estado']] ?? 9)
                ->values()
                ->all();
        if ($sala !== 'todos' && $busqueda !== '') {
            $needle = mb_strtolower($busqueda);
            $filtrados = array_values(array_filter(
                $filtrados,
                function (array $item) use ($needle): bool {
                    $texto = mb_strtolower(
                        $item['codigo'].' '.$item['nombre'].' '.$item['ubicacion']
                    );
                    return str_contains($texto, $needle);
                }
            ));
        }
        $atencion = collect($items)->whereIn('estado', ['bajo', 'critico'])->values()->all();
        $rol = Auth::user()->nombreRol();
        return view('inventario.index', [
            'usuario' => $this->usuarioSesion(),
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
                'tipos' => count($filtrados),
                'bajo_minimo' => collect($filtrados)->whereIn('estado', ['bajo', 'critico'])->count(),
                'en_almacen' => collect($filtrados)->sum('stock'),
                'en_proceso' => collect($filtrados)->sum('en_proceso'),
            ],
            'fecha' => now()->timezone('America/Santiago')->format('d/m/Y'),
            'hora' => now()->timezone('America/Santiago')->format('H:i'),
            'reposiciones' => $sala === 'todos'
                ? collect()
                : Reposicion::query()
                    ->with(['elemento', 'autor'])
                    ->whereHas('elemento', fn ($consulta) => $consulta->where('sala', $sala))
                    ->latest()
                    ->limit(8)
                    ->get(),
        ]);
    }

    public function registrarReposicion(Request $request): RedirectResponse
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
            ->where('sala', $datos['sala'])
            ->where('codigo', $datos['elemento'])
            ->first();

        if ($elemento === null) {
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

            return back()
                ->withInput()
                ->withErrors([
                    'reposicion' => 'No fue posible registrar la reposición. La cantidad disponible no cambió.',
                ]);
        }

        $elemento->refresh();
        Auditoria::registrar(
            'Registró reposición',
            'inventario',
            $elemento->codigo,
            '+'.$datos['cantidad'].' unidades · stock '.$elemento->stock,
        );
        $mensaje = 'Reposición registrada. '.$elemento->nombre.' quedó en '.$elemento->stock.' unidades.';
        if ($teniaAlerta && ! $elemento->tieneAlertaStock()) {
            $mensaje .= ' La alerta de stock quedó desactivada.';
        } elseif ($teniaAlerta) {
            $mensaje .= ' Sigue bajo el mínimo ('.$elemento->minimo.').';
        }

        return redirect()
            ->route('mockups.inventario', [
                'sala' => $elemento->sala,
                'elemento' => $elemento->codigo,
            ])
            ->with('ok', $mensaje);
    }
}
