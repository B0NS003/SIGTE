<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\Caja;
use App\Models\ElementoInventario;
use App\Models\Reposicion;
use App\Models\MovimientoInsumo;
use App\Models\Rol;
use App\Models\User;
use App\Support\Auditoria;
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
            ->map(fn(ElementoInventario $elemento) => [
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

        $cajasPorPagina = (int) request()->query('cajas_per_page', 10);

        if (! in_array($cajasPorPagina, [5, 10, 20, 50], true)) {
            $cajasPorPagina = 10;
        }

        $cajasLavado = Caja::query()
            ->with([
                'servicioClinico',
                'ficha.items.instrumento',
            ])
            ->where('etapa', Caja::ETAPA_LAVADO)
            ->orderBy('codigo')
            ->paginate($cajasPorPagina, ['*'], 'cajas_page')
            ->appends(request()->except('cajas_page'));

        $cajasArmado = Caja::query()
            ->with([
                'servicioClinico',
                'ficha.items.instrumento',
            ])
            ->where('etapa', Caja::ETAPA_PREPARACION)
            ->orderBy('codigo')
            ->paginate($cajasPorPagina, ['*'], 'cajas_armado_page')
            ->appends(request()->except('cajas_armado_page'));

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

        /* JERARQUÍA PARA EL ELEMENTO A REPONER */
        $atencion = collect($items)
            ->whereIn('estado', ['bajo', 'critico'])
            ->sortBy(function ($item) {
                $prioridadEstado = [
                    'critico' => 0,
                    'bajo' => 1,
                ][$item['estado']] ?? 2;

                $stock = (float) $item['stock'];
                $minimo = (float) $item['minimo'];

                // Calcula cuánto stock hay en relación con el mínimo requerido.
                $proporcionStock = $minimo > 0
                    ? $stock / $minimo
                    : 1;

                return [$prioridadEstado, $proporcionStock];
            })
            ->values()
            ->all();

        $rol = Auth::user()->nombreRol();
        return view('inventario.index', [
            'usuario' => $this->usuarioSesion(),
            'rol' => $rol,
            'esAdministradora' => $rol === Rol::ADMINISTRADORA,
            'esEnfermera' => $rol === Rol::ENFERMERA,
            'esOperador' => $rol === Rol::OPERADOR,
            'puedeReponer' => in_array($rol, [Rol::ENFERMERA, Rol::ADMINISTRADORA], true),
            'elemento' => trim((string) request()->query('elemento', '')),
            'busqueda' => $busqueda,
            'sala' => $sala,
            'salas' => $salas,
            'libros' => $libros,
            'items' => $filtrados,
            'cajasLavado' => $cajasLavado,
            'cajasArmado' => $cajasArmado,
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
                ->whereHas('elemento.salaFisica', fn($consulta) => $consulta->where('codigo', $sala))
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
            ->whereHas('salaFisica', fn($consulta) => $consulta->where('codigo', $datos['sala']))
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
        Auditoria::registrar(
            'Registró reposición',
            'inventario',
            $elemento->codigo,
            '+' . $datos['cantidad'] . ' unidades · stock ' . $elemento->stock,
        );
        $mensaje = 'Reposición registrada. ' . $elemento->nombre . ' quedó en ' . $elemento->stock . ' unidades.';
        if ($teniaAlerta && ! $elemento->tieneAlertaStock()) {
            $mensaje .= ' La alerta de stock quedó desactivada.';
        } elseif ($teniaAlerta) {
            $mensaje .= ' Sigue bajo el mínimo (' . $elemento->minimo . ').';
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


    /* CONFIGURAR MÍNIMO DE UN ELEMENTO */
    public function configurarMinimo(Request $request): JsonResponse
    {
        $usuario = Auth::user();

        // LA ENFERMERA PUEDE CUMPLIR CON ESTA ACCIÓN
        if (! $usuario instanceof User || ! $usuario->isEnfermera()) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No tienes permiso para configurar mínimos.',
            ], 403);
        }

        $datos = $request->validate([
            'sala' => ['required', 'in:lavado,armado,esteril'],
            'codigo' => ['required', 'string'],
            'minimo' => ['required', 'integer', 'min:0', 'max:9999'],
        ], [
            'sala.required' => 'No se pudo identificar la sala.',
            'sala.in' => 'La configuración de mínimos no está habilitada para esta sala.',
            'codigo.required' => 'No se pudo identificar el elemento.',
            'minimo.required' => 'Ingresa un mínimo válido.',
            'minimo.integer' => 'El mínimo debe ser un número entero.',
            'minimo.min' => 'El mínimo no puede ser negativo.',
            'minimo.max' => 'El mínimo no puede superar las 9999 unidades.',
        ]);

        $elemento = ElementoInventario::query()
            ->whereHas('salaFisica', fn($consulta) => $consulta->where('codigo', $datos['sala']))
            ->where('codigo', $datos['codigo'])
            ->first();

        if ($elemento === null) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No se encontró el elemento seleccionado.',
            ], 422);
        }

        try {
            $resultado = DB::transaction(function () use ($elemento, $datos) {
                $bloqueado = ElementoInventario::query()
                    ->whereKey($elemento->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $minimoAnterior = (int) $bloqueado->minimo;
                $minimoNuevo = (int) $datos['minimo'];

                // SI NO HAY CAMBIOS, NO SE ACTUALIZA NI SE REGISTRA AUDITORÍA
                if ($minimoAnterior === $minimoNuevo) {
                    return [
                        'elemento' => $bloqueado,
                        'minimoAnterior' => $minimoAnterior,
                        'cambio' => false,
                    ];
                }

                $bloqueado->minimo = $minimoNuevo;
                $bloqueado->save();

                return [
                    'elemento' => $bloqueado,
                    'minimoAnterior' => $minimoAnterior,
                    'cambio' => true,
                ];
            });
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'mensaje' => 'No fue posible guardar el mínimo. Inténtalo nuevamente.',
            ], 500);
        }

        $elemento = $resultado['elemento'];

        // CONFIRMAR SIN REGISTRAR UNA MODIFICACIÓN INNECESARIA
        if (! $resultado['cambio']) {
            return response()->json([
                'ok' => true,
                'mensaje' => 'La configuración se mantiene en '
                    . $elemento->minimo . ' unidades.',
                'elemento' => [
                    'codigo' => $elemento->codigo,
                    'sala' => $elemento->codigoSala(),
                    'stock' => (int) $elemento->stock,
                    'minimo' => (int) $elemento->minimo,
                ],
            ]);
        }

        Auditoria::registrar(
            'Configuró mínimo',
            'inventario',
            $elemento->codigo,
            'Mínimo anterior: ' . $resultado['minimoAnterior']
                . ' · nuevo mínimo: ' . $elemento->minimo,
        );

        return response()->json([
            'ok' => true,
            'mensaje' => 'El mínimo de ' . $elemento->nombre . ' se actualizó correctamente.',
            'elemento' => [
                'codigo' => $elemento->codigo,
                'sala' => $elemento->codigoSala(),
                'stock' => (int) $elemento->stock,
                'minimo' => (int) $elemento->minimo,
            ],
        ]);
    }

    /* MODAL DEL ROL OPERADOR PARA EL REGISTRO DEL MOVIMIENTO */
    public function registrarMovimiento(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'sala' => ['required', 'in:lavado,armado'],
            'elemento' => ['required', 'string'],
            'tipo' => ['required', 'in:consumo,uso,devolucion'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:9999'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ], [
            'sala.required' => 'No se pudo identificar la sala.',
            'sala.in' => 'La sala seleccionada no permite registrar movimientos.',
            'elemento.required' => 'No se pudo identificar el insumo.',
            'tipo.required' => 'Selecciona un tipo de movimiento.',
            'tipo.in' => 'Selecciona un tipo de movimiento válido.',
            'cantidad.required' => 'Ingresa una cantidad válida.',
            'cantidad.integer' => 'Ingresa una cantidad válida.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'cantidad.max' => 'La cantidad no puede superar las 9999 unidades.',
            'observacion.max' => 'La observación no puede superar los 255 caracteres.',
        ]);

        $elemento = ElementoInventario::query()
            ->whereHas('salaFisica', fn($consulta) => $consulta->where('codigo', $datos['sala']))
            ->where('codigo', $datos['elemento'])
            ->first();

        if ($elemento === null) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No se encontró el insumo seleccionado.',
            ], 422);
        }

        try {
            $elemento = DB::transaction(function () use ($elemento, $datos) {
                $bloqueado = ElementoInventario::query()
                    ->whereKey($elemento->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $cantidad = (int) $datos['cantidad'];
                $tipo = $datos['tipo'];

                if (in_array($tipo, ['consumo', 'uso'], true)) {
                    if ($cantidad > $bloqueado->stock) {
                        throw new \DomainException(
                            'No hay suficientes unidades disponibles. Stock actual: ' . $bloqueado->stock . '.'
                        );
                    }

                    $bloqueado->stock -= $cantidad;

                    if ($tipo === 'uso') {
                        $bloqueado->en_proceso += $cantidad;
                    }
                }

                if ($tipo === 'devolucion') {
                    if ($cantidad > $bloqueado->en_proceso) {
                        throw new \DomainException(
                            'La cantidad supera las unidades en proceso. Actualmente hay ' . $bloqueado->en_proceso . '.'
                        );
                    }

                    $bloqueado->stock += $cantidad;
                    $bloqueado->en_proceso -= $cantidad;
                }

                $bloqueado->save();

                MovimientoInsumo::query()->create([
                    'elemento_inventario_id' => $bloqueado->id,
                    'tipo' => match ($tipo) {
                        'consumo' => MovimientoInsumo::CONSUMO,
                        'uso' => MovimientoInsumo::USO,
                        'devolucion' => MovimientoInsumo::DEVOLUCION,
                    },
                    'cantidad' => $cantidad,
                    'observacion' => $datos['observacion'] ?? null,
                    'user_id' => Auth::id(),
                ]);

                return $bloqueado;
            });
        } catch (\DomainException $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'mensaje' => 'No fue posible registrar el movimiento. El inventario no cambió.',
            ], 500);
        }

        $etiquetas = [
            'consumo' => 'Consumo',
            'uso' => 'Uso',
            'devolucion' => 'Devolución',
        ];

        Auditoria::registrar(
            'Registró ' . strtolower($etiquetas[$datos['tipo']]),
            'inventario',
            $elemento->codigo,
            'Movimiento: ' . $etiquetas[$datos['tipo']]
                . ' · ' . $datos['cantidad'] . ' unidades'
                . ' · stock ' . $elemento->stock
                . ' · en proceso ' . $elemento->en_proceso,
        );

        return response()->json([
            'ok' => true,
            'mensaje' => $etiquetas[$datos['tipo']] . ' registrado correctamente.',
            'elemento' => [
                'stock' => (int) $elemento->stock,
                'en_proceso' => (int) $elemento->en_proceso,
                'minimo' => (int) $elemento->minimo,
                'codigo' => $elemento->codigo,
                'sala' => $elemento->codigoSala(),
            ],
        ]);
    }

    public function historialElemento(string $sala, string $codigo)
    {

        $elemento = ElementoInventario::query()
            ->whereHas('salaFisica', fn($consulta) => $consulta->where('codigo', $sala))
            ->where('codigo', $codigo)
            ->firstOrFail();

        $reposiciones = Reposicion::query()
            ->with('autor')
            ->where('elemento_inventario_id', $elemento->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn(Reposicion $reposicion) => [
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

    /* HISTORIAL DEL MODAL DEL OPERADOR CON LOS TIPOS DE MOVIMIENTOS*/
    public function historialConsumos(string $sala, string $codigo): JsonResponse
    {
        $elemento = ElementoInventario::query()
            ->whereHas('salaFisica', fn($consulta) => $consulta->where('codigo', $sala))
            ->where('codigo', $codigo)
            ->firstOrFail();

        $movimientos = MovimientoInsumo::query()
            ->with('autor')
            ->where('elemento_inventario_id', $elemento->id)
            ->whereIn('tipo', [
                MovimientoInsumo::CONSUMO,
                MovimientoInsumo::USO,
                MovimientoInsumo::DEVOLUCION,
            ])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn(MovimientoInsumo $movimiento) => [
                'tipo' => $movimiento->etiquetaTipo(),
                'cantidad' => $movimiento->cantidad,
                'observacion' => $movimiento->observacion,
                'fecha' => $movimiento->created_at
                    ->timezone('America/Santiago')
                    ->format('d/m/Y · H:i'),
                'autor' => $movimiento->autor?->name ?? 'Sin usuario',
            ]);

        return response()->json([
            'elemento' => $elemento->nombre,
            'historial' => $movimientos,
        ]);
    }
}
