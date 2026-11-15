<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\Caja;
use App\Models\ElementoInventario;
use App\Models\Ficha;
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
                'imagen' => $elemento->imagen,
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

        // Material estéril: cajas del flujo en almacén/entrega (no insumos de bodega).
        $cajasEnAlmacen = Caja::query()
            ->whereIn('etapa', [
                Caja::ETAPA_ALMACEN,
                Caja::ETAPA_ENTREGA,
            ])
            ->whereNull('entregado_at')
            ->whereNotNull('ficha_id')
            ->orderBy('codigo')
            ->get()
            ->groupBy('ficha_id');

        $idsFicha = $cajasEnAlmacen->keys()->filter()->values();
        $cajasEsteril = $idsFicha->isEmpty()
            ? collect()
            : Ficha::query()
                ->select(['id', 'codigo', 'nombre', 'servicio_id', 'imagen_formato'])
                ->with('servicioClinico:id,nombre')
                ->whereIn('id', $idsFicha)
                ->orderBy('nombre')
                ->get()
                ->map(function (Ficha $ficha) use ($cajasEnAlmacen) {
                    $cajas = $cajasEnAlmacen->get($ficha->id, collect());

                    return [
                        'ficha_id' => $ficha->id,
                        'codigo' => $ficha->codigo,
                        'nombre' => $ficha->nombre,
                        'servicio' => $ficha->servicioClinico?->nombre ?? 'Sin servicio',
                        'imagen_formato' => $ficha->imagen_formato,
                        'stock' => $cajas->count(),
                        'ubicaciones' => $cajas->pluck('ubicacion')
                            ->filter()
                            ->unique()
                            ->values(),
                        'cajas' => $cajas->map(fn ($caja) => [
                            'codigo' => $caja->codigo,
                            'ubicacion' => $caja->ubicacion ?? 'Sin ubicación',
                            'ingreso' => $caja->etapa_desde,
                        ])->values(),
                    ];
                })
                ->values();

        $salas = [
            'lavado' => 'Sala lavado',
            'armado' => 'Sala armado',
            'esteril' => 'Material estéril',
        ];

        $rol = Auth::user()->nombreRol();

        $salaPredeterminada = $rol === Rol::OPERADOR ? 'lavado' : 'todos';

        $sala = request()->query('sala', $salaPredeterminada);

        if ($sala !== 'todos' && ! isset($salas[$sala])) {
            $sala = $salaPredeterminada;
        }
        $busqueda = trim((string) request()->query('q', ''));
        $libros = [];

        foreach ($salas as $clave => $nombre) {
            if ($clave === 'esteril') {
                $libros[] = [
                    'clave' => $clave,
                    'nombre' => $nombre,
                    'tipos' => $cajasEsteril->count(),
                    'stock' => (int) $cajasEsteril->sum('stock'),
                    'bajo' => 0,
                    'en_proceso' => 0,
                ];

                continue;
            }

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

        $itemsSala = in_array($sala, ['todos', 'esteril'], true)
            ? collect()
            : collect($items)
            ->where('sala', $sala)
            ->sortBy(fn(array $item) => [
                'critico' => 0,
                'bajo' => 1,
                'ok' => 2,
            ][$item['estado']] ?? 9)
            ->values();

        $filtrados = $itemsSala->all();

        /* JERARQUÍA PARA EL ELEMENTO A REPONER */
        $atencion = collect($items)
            ->whereIn('sala', ['lavado', 'armado'])
            ->whereIn('estado', ['bajo', 'critico'])
            ->sortBy(function ($item) {
                $prioridadEstado = [
                    'critico' => 0,
                    'bajo' => 1,
                ][$item['estado']] ?? 2;

                $stock = (float) $item['stock'];
                $minimo = (float) $item['minimo'];

                $proporcionStock = $minimo > 0
                    ? $stock / $minimo
                    : 1;

                return [$prioridadEstado, $proporcionStock];
            })
            ->values()
            ->all();

        $quien = User::quienRegistra();

        return view('inventario.index', [
            'usuario' => $this->usuarioSesion(),
            'quien_registra' => $quien?->name ?? $this->usuarioSesion()['nombre'],
            'sin_puesto' => Auth::user()?->esCuentaDelPuesto() && $quien === null,
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
            'cajasEsteril' => $cajasEsteril,
            'elementos' => $sala === 'todos'
                ? []
                : collect($items)->where('sala', $sala)->values()->all(),
            'atencion' => $atencion,
            'resumen' => [
                'tipos' => $sala === 'esteril'
                    ? $cajasEsteril->count()
                    : $itemsSala->count(),

                'bajo_minimo' => $sala === 'esteril'
                    ? 0
                    : $itemsSala->whereIn('estado', ['bajo', 'critico'])->count(),

                'en_almacen' => $sala === 'esteril'
                    ? (int) $cajasEsteril->sum('stock')
                    : (int) $itemsSala->sum('stock'),

                'en_proceso' => $sala === 'esteril'
                    ? 0
                    : (int) $itemsSala->sum('en_proceso'),
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
                // El trigger de PostgreSQL actualiza el stock al insertar el movimiento.
                ElementoInventario::query()->whereKey($elemento->id)->lockForUpdate()->firstOrFail();

                Reposicion::query()->create([
                    'elemento_inventario_id' => $elemento->id,
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
        $quien = User::quienRegistra();
        if (Auth::user()?->esCuentaDelPuesto() && $quien === null) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Elige qué cuenta de operadora está en el puesto antes de registrar.',
            ], 422);
        }

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

        $autorId = $quien?->id ?? Auth::id();

        try {
            $elemento = DB::transaction(function () use ($elemento, $datos, $autorId) {
                $bloqueado = ElementoInventario::query()
                    ->whereKey($elemento->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $cantidad = (int) $datos['cantidad'];
                $tipo = $datos['tipo'];

                // Validación previa (mensaje claro); el trigger aplica el cambio real.
                if (in_array($tipo, ['consumo', 'uso'], true) && $cantidad > $bloqueado->stock) {
                    throw new \DomainException(
                        'No hay suficientes unidades disponibles. Stock actual: ' . $bloqueado->stock . '.'
                    );
                }

                if ($tipo === 'devolucion' && $cantidad > $bloqueado->en_proceso) {
                    throw new \DomainException(
                        'La cantidad supera las unidades en proceso. Actualmente hay ' . $bloqueado->en_proceso . '.'
                    );
                }

                MovimientoInsumo::query()->create([
                    'elemento_inventario_id' => $bloqueado->id,
                    'tipo' => match ($tipo) {
                        'consumo' => MovimientoInsumo::CONSUMO,
                        'uso' => MovimientoInsumo::USO,
                        'devolucion' => MovimientoInsumo::DEVOLUCION,
                    },
                    'cantidad' => $cantidad,
                    'observacion' => $datos['observacion'] ?? null,
                    'user_id' => $autorId,
                ]);

                return $bloqueado->refresh();
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
            $quien,
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

    public function historialElemento(Request $request, string $sala, string $codigo): JsonResponse
    {
        $elemento = ElementoInventario::query()
            ->whereHas('salaFisica', fn ($consulta) => $consulta->where('codigo', $sala))
            ->where('codigo', $codigo)
            ->firstOrFail();

        $pagina = max(1, (int) $request->query('page', 1));
        $porPagina = 5;

        $consulta = Reposicion::query()
            ->with('autor')
            ->where('elemento_inventario_id', $elemento->id)
            ->latest('id');

        $total = (clone $consulta)->count();
        $ultima = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $ultima);

        $historial = $consulta
            ->forPage($pagina, $porPagina)
            ->get()
            ->map(fn (Reposicion $reposicion) => [
                'tipo' => 'Reposición',
                'cantidad' => $reposicion->cantidad,
                'fecha' => $reposicion->created_at
                    ->timezone('America/Santiago')
                    ->format('d/m/Y · H:i'),
                'autor' => $reposicion->autor?->name ?? 'Sin usuario',
            ]);

        return response()->json([
            'elemento' => $elemento->nombre,
            'historial' => $historial,
            'paginacion' => [
                'actual' => $pagina,
                'ultima' => $ultima,
                'total' => $total,
                'por_pagina' => $porPagina,
            ],
        ]);
    }

    /* HISTORIAL DEL MODAL DEL OPERADOR CON LOS TIPOS DE MOVIMIENTOS*/
    public function historialConsumos(Request $request, string $sala, string $codigo): JsonResponse
    {
        $elemento = ElementoInventario::query()
            ->whereHas('salaFisica', fn ($consulta) => $consulta->where('codigo', $sala))
            ->where('codigo', $codigo)
            ->firstOrFail();

        $pagina = max(1, (int) $request->query('page', 1));
        $porPagina = 5;

        $consulta = MovimientoInsumo::query()
            ->with('autor')
            ->where('elemento_inventario_id', $elemento->id)
            ->whereIn('tipo', [
                MovimientoInsumo::CONSUMO,
                MovimientoInsumo::USO,
                MovimientoInsumo::DEVOLUCION,
            ])
            ->latest('id');

        $total = (clone $consulta)->count();
        $ultima = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $ultima);

        $historial = $consulta
            ->forPage($pagina, $porPagina)
            ->get()
            ->map(fn (MovimientoInsumo $movimiento) => [
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
            'historial' => $historial,
            'paginacion' => [
                'actual' => $pagina,
                'ultima' => $ultima,
                'total' => $total,
                'por_pagina' => $porPagina,
            ],
        ]);
    }
}
