<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ConsultaDeCajas;
use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\Caja;
use App\Models\ElementoInventario;
use App\Models\Entrega;
use App\Models\Personal;
use App\Models\RegistroAuditoria;
use App\Models\Rol;
use App\Models\User;
use App\Support\Auditoria;
use App\Support\CadenaCustodia;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AdminController extends Controller
{
    use ConsultaDeCajas;
    use ResuelveUsuario;

    public function usuarios(Request $request): View
    {
        $filtros = $this->filtrosCuentas($request);
        $consulta = User::query()->with('rol')->orderBy('id');
        if ($filtros['estado'] === 'activo') {
            $consulta->where('activo', true);
        }
        if ($filtros['estado'] === 'inactivo') {
            $consulta->where('activo', false);
        }
        if ($filtros['rol'] !== null) {
            $consulta->where('rol_id', $filtros['rol']);
        }
        if ($filtros['q'] !== '') {
            $buscar = $filtros['q'];
            $consulta->where(function ($q) use ($buscar) {
                $q->where('name', 'ilike', '%'.$buscar.'%')
                    ->orWhere('email', 'ilike', '%'.$buscar.'%');
            });
        }

        $usuarios = $consulta->paginate(8)->withQueryString();
        $editar = $request->filled('editar')
            ? User::query()->find((int) $request->query('editar'))
            : null;

        return view('admin.usuarios', [
            'usuario' => $this->usuarioSesion(),
            'usuarios' => $usuarios,
            'roles' => Rol::query()->orderBy('nombre')->get(),
            'editar' => $editar,
            'filtros' => $filtros,
            'resumen' => [
                'total' => User::query()->count(),
                'activos' => User::query()->where('activo', true)->count(),
                'inactivos' => User::query()->where('activo', false)->count(),
                'admin' => User::query()->whereHas('rol', fn ($q) => $q->where('nombre', Rol::ADMINISTRADORA))->where('activo', true)->count(),
            ],
        ]);
    }

    public function turnos(): View
    {
        return view('admin.turnos', [
            'usuario' => $this->usuarioSesion(),
            'nomina' => Personal::query()->orderBy('grupo')->orderBy('nombre')->get(),
            'turnoVigente' => Personal::turnoVigente(),
        ]);
    }

    public function asignarTurno(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'personal_id' => ['required', 'integer', 'exists:personal,id'],
            'turno' => ['nullable', Rule::in(array_keys(Personal::TURNOS))],
        ]);

        $persona = Personal::query()->findOrFail($datos['personal_id']);
        $antes = $persona->turno;
        $persona->turno = $datos['turno'] ?? null;
        $persona->save();

        if ($antes !== $persona->turno) {
            $etiqueta = $persona->turno === null
                ? 'Sin asignar'
                : (Personal::TURNOS[$persona->turno]['etiqueta'] ?? $persona->turno);
            Auditoria::registrar(
                $persona->turno === null ? 'Quitó turno' : 'Asignó turno',
                'turnos',
                $persona->nombre,
                'Turno '.$etiqueta,
            );
        }

        return response()->json(['ok' => true, 'turno' => $persona->turno]);
    }

    public function guardarUsuario(Request $request): RedirectResponse
    {
        $datos = $this->validarCuenta($request);

        $rol = Rol::query()->findOrFail($datos['rol_id']);
        $nuevo = User::query()->create([
            'name' => $datos['nombre'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'rol_id' => $rol->id,
            'activo' => true,
        ]);

        Auditoria::registrar('Creó usuario', 'usuarios', $nuevo->name, 'Rol '.$rol->etiqueta());

        return $this->volverACuentas($request)
            ->with('ok', 'Cuenta creada. '.$datos['nombre'].' quedó como '.$rol->etiqueta().'.');
    }

    public function actualizarUsuario(Request $request, User $user): RedirectResponse
    {
        $datos = $this->validarCuenta($request, $user);
        $rol = Rol::query()->findOrFail($datos['rol_id']);

        if ($user->id === Auth::id() && $rol->nombre !== Rol::ADMINISTRADORA) {
            return back()->withInput()->withErrors([
                'rol_id' => 'No puedes quitarte el rol de administradora.',
            ]);
        }

        if ($user->isAdministradora() && $rol->nombre !== Rol::ADMINISTRADORA && ! $this->hayOtraAdministradora($user)) {
            return back()->withInput()->withErrors([
                'rol_id' => 'Debe quedar al menos una administradora activa.',
            ]);
        }

        $user->name = $datos['nombre'];
        $user->email = $datos['email'];
        $user->rol_id = $rol->id;
        if (! empty($datos['password'])) {
            $user->password = $datos['password'];
        }
        $user->save();

        Auditoria::registrar('Actualizó cuenta', 'usuarios', $user->name, 'Rol '.$rol->etiqueta());

        return $this->volverACuentas($request)
            ->with('ok', 'Datos actualizados. '.$user->name.' quedó como '.$rol->etiqueta().'.');
    }

    public function cambiarEstadoUsuario(Request $request, User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors([
                'estado' => 'No puedes desactivar tu propia cuenta.',
            ]);
        }

        if ($user->activo && $user->isAdministradora() && ! $this->hayOtraAdministradora($user)) {
            return back()->withErrors([
                'estado' => 'Debe quedar al menos una administradora activa.',
            ]);
        }

        $user->activo = ! $user->activo;
        $user->save();

        Auditoria::registrar(
            $user->activo ? 'Activó usuario' : 'Desactivó usuario',
            'usuarios',
            $user->name,
            $user->activo ? 'Cuenta activa' : 'Cuenta inactiva',
        );

        $mensaje = $user->activo
            ? $user->name.' puede volver a entrar.'
            : $user->name.' quedó inactiva. No puede entrar. El historial se mantiene.';

        return $this->volverACuentas($request)->with('ok', $mensaje);
    }

    /**
     * @return array{estado: ?string, rol: ?int, q: string}
     */
    private function filtrosCuentas(Request $request): array
    {
        $estado = $request->input('estado');
        $rol = $request->input('rol');

        return [
            'estado' => in_array($estado, ['activo', 'inactivo'], true) ? $estado : null,
            'rol' => $rol !== null && $rol !== '' ? (int) $rol : null,
            'q' => trim((string) $request->input('q', '')),
        ];
    }

    private function volverACuentas(Request $request): RedirectResponse
    {
        $filtros = $this->filtrosCuentas($request);
        $pagina = (int) $request->input('page');

        return redirect()->route('mockups.usuarios', array_filter([
            'estado' => $filtros['estado'],
            'rol' => $filtros['rol'],
            'q' => $filtros['q'] !== '' ? $filtros['q'] : null,
            'page' => $pagina > 1 ? $pagina : null,
        ]));
    }

    private function validarCuenta(Request $request, ?User $user = null): array
    {
        if ($user !== null && ! $request->filled('password')) {
            $request->merge(['password' => null]);
        }

        return $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'rol_id' => ['required', 'integer', 'exists:roles,id'],
            'password' => [$user === null ? 'required' : 'nullable', 'string', 'min:8', 'max:80'],
        ], [
            'nombre.required' => 'Escribe el nombre completo.',
            'email.required' => 'Escribe el correo institucional.',
            'email.email' => 'El correo institucional no es válido.',
            'email.unique' => 'Ese correo ya tiene una cuenta.',
            'rol_id.required' => 'Elige el rol.',
            'rol_id.exists' => 'Elige un rol de la lista.',
            'password.required' => 'Escribe una contraseña temporal.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);
    }

    private function hayOtraAdministradora(User $user): bool
    {
        return User::query()
            ->where('activo', true)
            ->where('id', '!=', $user->id)
            ->whereHas('rol', fn ($consulta) => $consulta->where('nombre', Rol::ADMINISTRADORA))
            ->exists();
    }
    public function reportes(Request $request): View
    {
        $filtros = $this->filtrosReporte($request);
        $falla = $request->boolean('falla');
        $datos = [
            'movimientos' => collect(),
            'por_tipo' => collect(),
            'entregas' => collect(),
            'en_flujo' => collect(),
        ];

        if (! $falla) {
            try {
                $datos = $this->datosReporte($filtros);
            } catch (Throwable $e) {
                report($e);
                $falla = true;
            }
        }

        $momento = now()->timezone('America/Santiago');

        return view('admin.reportes', [
            'usuario' => $this->usuarioSesion(),
            'falla' => $falla,
            'filtros' => $filtros,
            'fecha' => $momento->format('d/m/Y'),
            'hora' => $momento->format('H:i'),
            'movimientos' => $datos['movimientos'],
            'por_tipo' => $datos['por_tipo'],
            'entregas' => $datos['entregas'],
            'en_flujo' => $datos['en_flujo'],
            'tipos_auditoria' => RegistroAuditoria::TIPOS,
        ]);
    }

    public function exportarReportes(Request $request): StreamedResponse
    {
        $filtros = $this->filtrosReporte($request);
        $datos = $this->datosReporte($filtros);
        $nombre = 'sigte-reporte-'.$filtros['desde'].'_'.$filtros['hasta'].'.csv';

        return response()->streamDownload(function () use ($datos, $filtros) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Reporte SIGTE', 'Desde '.$filtros['desde'], 'Hasta '.$filtros['hasta']]);
            fputcsv($salida, []);
            fputcsv($salida, ['Fecha', 'Hora', 'Usuario', 'Accion', 'Tipo', 'Objeto', 'Detalle']);
            foreach ($datos['movimientos'] as $fila) {
                fputcsv($salida, [
                    $fila['fecha'],
                    $fila['hora'],
                    $fila['actor'],
                    $fila['accion'],
                    $fila['tipo_etiqueta'],
                    $fila['objeto'],
                    $fila['detalle'],
                ]);
            }
            fputcsv($salida, []);
            fputcsv($salida, ['Entregas en el periodo']);
            fputcsv($salida, ['Codigo', 'Servicio', 'Fecha entrega', 'Responsable']);
            foreach ($datos['entregas'] as $entrega) {
                fputcsv($salida, [
                    $entrega['codigo'],
                    $entrega['servicio'],
                    $entrega['cuando'],
                    $entrega['responsable'],
                ]);
            }
            fclose($salida);
        }, $nombre, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{desde: string, hasta: string, tipo: string}
     */
    private function filtrosReporte(Request $request): array
    {
        $hoy = now()->timezone('America/Santiago')->toDateString();
        $desde = (string) $request->input('desde', now()->timezone('America/Santiago')->subDays(13)->toDateString());
        $hasta = (string) $request->input('hasta', $hoy);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
            $desde = now()->timezone('America/Santiago')->subDays(13)->toDateString();
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
        ];
    }

    /**
     * @param  array{desde: string, hasta: string, tipo: string}  $filtros
     * @return array{movimientos: Collection<int, array<string, string>>, por_tipo: Collection<string, int>, entregas: Collection<int, array<string, string>>, en_flujo: Collection<int, array<string, string|int>>}
     */
    private function datosReporte(array $filtros): array
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
        ];
    }
    public function historialEntregas(Request $request): View
    {
        $falla = $request->boolean('falla');
        $filtros = $this->filtrosHistorialEntregas($request);
        $entregas = new LengthAwarePaginator([], 0, 8, 1, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
        $servicios = collect();
        $resumen = [];

        if (! $falla) {
            try {
                $consulta = Entrega::query()
                    ->with(['quienEntrega.rol', 'items'])
                    ->latest('entregado_at')
                    ->latest('id');
                $this->aplicarFiltrosHistorialEntregas($consulta, $filtros);
                $entregas = $consulta
                    ->paginate(8)
                    ->withQueryString()
                    ->through(fn (Entrega $entrega) => $entrega->paraConsulta());

                $servicios = Entrega::query()
                    ->select('servicio')
                    ->selectRaw('count(*) as total')
                    ->selectRaw('max(entregado_at) as ultima')
                    ->groupBy('servicio')
                    ->orderBy('servicio')
                    ->get()
                    ->map(function ($fila) {
                        $ultima = $fila->ultima
                            ? Carbon::parse($fila->ultima)->timezone('America/Santiago')
                            : null;

                        return [
                            'servicio' => $fila->servicio,
                            'total' => (int) $fila->total,
                            'ultima_fecha' => $ultima?->format('d/m/Y'),
                            'ultima_hora' => $ultima?->format('H:i'),
                        ];
                    });

                $resumen = [
                    'total' => Entrega::query()->count(),
                    'hoy' => Entrega::query()
                        ->where('entregado_at', '>=', now('America/Santiago')->startOfDay()->timezone('UTC'))
                        ->count(),
                ];
            } catch (Throwable $e) {
                report($e);
                $falla = true;
            }
        }

        return view('admin.historial', [
            'usuario' => $this->usuarioSesion(),
            'entregas' => $entregas,
            'servicios' => $servicios,
            'filtros' => $filtros,
            'resumen' => $resumen,
            'falla' => $falla,
        ]);
    }

    /**
     * @return array{q: string, cuando: string, servicio: string}
     */
    private function filtrosHistorialEntregas(Request $request): array
    {
        $cuando = (string) $request->input('cuando', 'todas');
        if (! in_array($cuando, ['todas', 'hoy', 'ayer', 'semana'], true)) {
            $cuando = 'todas';
        }

        return [
            'q' => trim((string) $request->input('q', '')),
            'cuando' => $cuando,
            'servicio' => trim((string) $request->input('servicio', '')),
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Entrega>  $consulta
     * @param  array{q: string, cuando: string, servicio: string}  $filtros
     */
    private function aplicarFiltrosHistorialEntregas($consulta, array $filtros): void
    {
        $inicio = now()->timezone('America/Santiago')->startOfDay();
        if ($filtros['cuando'] === 'hoy') {
            $consulta->where('entregado_at', '>=', $inicio->copy()->timezone('UTC'));
        } elseif ($filtros['cuando'] === 'ayer') {
            $consulta->whereBetween('entregado_at', [
                $inicio->copy()->subDay()->timezone('UTC'),
                $inicio->copy()->timezone('UTC')->subSecond(),
            ]);
        } elseif ($filtros['cuando'] === 'semana') {
            $consulta->where('entregado_at', '>=', $inicio->copy()->subDays(6)->timezone('UTC'));
        }

        if ($filtros['servicio'] !== '') {
            $consulta->where('servicio', $filtros['servicio']);
        }

        if ($filtros['q'] !== '') {
            $buscar = $filtros['q'];
            $consulta->where(function ($q) use ($buscar) {
                $q->where('codigo', 'ilike', '%'.$buscar.'%')
                    ->orWhere('servicio', 'ilike', '%'.$buscar.'%')
                    ->orWhere('recibe', 'ilike', '%'.$buscar.'%')
                    ->orWhere('material', 'ilike', '%'.$buscar.'%')
                    ->orWhereHas('quienEntrega', fn ($actor) => $actor->where('name', 'ilike', '%'.$buscar.'%'))
                    ->orWhereHas('items', fn ($item) => $item
                        ->where('codigo', 'ilike', '%'.$buscar.'%')
                        ->orWhere('nombre', 'ilike', '%'.$buscar.'%'));
            });
        }
    }
    public function custodia(Request $request): View
    {
        $falla = $request->boolean('falla');
        $filtros = $this->filtrosAuditoria($request);
        $actores = collect();
        $cadenas = new LengthAwarePaginator([], 0, 3, 1, [
            'path' => $request->url(),
            'pageName' => 'pc',
        ]);
        $auditoria = new LengthAwarePaginator([], 0, 10, 1, [
            'path' => $request->url(),
            'pageName' => 'pa',
        ]);

        if (! $falla) {
            try {
                if ($filtros['modo'] === 'caja') {
                    if ($filtros['caja'] !== '') {
                        $todas = CadenaCustodia::buscar($filtros['caja']);
                        $porPagina = 3;
                        $total = count($todas);
                        $ultima = max(1, (int) ceil($total / $porPagina));
                        $pagina = min(max(1, (int) $request->query('pc', 1)), $ultima);
                        $cadenas = new LengthAwarePaginator(
                            array_slice($todas, ($pagina - 1) * $porPagina, $porPagina),
                            $total,
                            $porPagina,
                            $pagina,
                            [
                                'path' => $request->url(),
                                'query' => $request->query(),
                                'pageName' => 'pc',
                            ]
                        );
                    }
                } else {
                    $consulta = RegistroAuditoria::query()->with('actor')->latest('created_at')->latest('id');
                    $this->aplicarFiltrosAuditoria($consulta, $filtros);
                    $auditoria = $consulta
                        ->paginate(10, ['*'], 'pa')
                        ->withQueryString()
                        ->through(fn (RegistroAuditoria $registro) => $registro->paraConsulta());
                }

                $actores = User::query()
                    ->whereIn('id', RegistroAuditoria::query()->select('user_id')->whereNotNull('user_id'))
                    ->orderBy('name')
                    ->get(['id', 'name']);
            } catch (Throwable $e) {
                report($e);
                $falla = true;
            }
        }

        $usuarioElegido = $filtros['usuario'] !== null
            ? User::query()->find($filtros['usuario'])
            : null;

        return view('admin.custodia', [
            'usuario' => $this->usuarioSesion(),
            'falla' => $falla,
            'filtros' => $filtros,
            'actores' => $actores,
            'usuario_elegido' => $usuarioElegido,
            'tipos_auditoria' => RegistroAuditoria::TIPOS,
            'cadenas' => $cadenas,
            'auditoria' => $auditoria,
        ]);
    }

    /**
     * @return array{modo: string, q: string, usuario: ?int, periodo: string, tipo: string, caja: string}
     */
    private function filtrosAuditoria(Request $request): array
    {
        $modo = (string) $request->input('modo', 'bitacora');
        if (! in_array($modo, ['bitacora', 'caja'], true)) {
            $modo = 'bitacora';
        }
        $periodo = (string) $request->input('periodo', 'mes');
        if (! in_array($periodo, ['hoy', 'semana', 'mes'], true)) {
            $periodo = 'mes';
        }
        $tipo = (string) $request->input('tipo', '');
        if ($tipo !== '' && ! array_key_exists($tipo, RegistroAuditoria::TIPOS)) {
            $tipo = '';
        }
        $usuario = $request->input('usuario');
        $caja = mb_strtoupper(trim((string) $request->input('caja', '')));
        // Si llegan con un código de caja desde la bitácora, abrir modo cadena.
        if ($caja !== '' && ! $request->filled('modo')) {
            $modo = 'caja';
        }

        return [
            'modo' => $modo,
            'q' => trim((string) $request->input('q', '')),
            'usuario' => $usuario !== null && $usuario !== '' ? (int) $usuario : null,
            'periodo' => $periodo,
            'tipo' => $tipo,
            'caja' => $caja,
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\RegistroAuditoria>  $consulta
     * @param  array{modo: string, q: string, usuario: ?int, periodo: string, tipo: string, caja: string}  $filtros
     */
    private function aplicarFiltrosAuditoria($consulta, array $filtros): void
    {
        $inicio = now()->timezone('America/Santiago')->startOfDay();
        if ($filtros['periodo'] === 'hoy') {
            $consulta->where('created_at', '>=', $inicio->copy()->timezone('UTC'));
        } elseif ($filtros['periodo'] === 'semana') {
            $consulta->where('created_at', '>=', $inicio->copy()->subDays(6)->timezone('UTC'));
        } else {
            $consulta->where('created_at', '>=', $inicio->copy()->startOfMonth()->timezone('UTC'));
        }

        if ($filtros['usuario'] !== null) {
            $consulta->where('user_id', $filtros['usuario']);
        }

        if ($filtros['tipo'] !== '') {
            $consulta->where('tipo', $filtros['tipo']);
        }

        if ($filtros['q'] !== '') {
            $buscar = $filtros['q'];
            $consulta->where(function ($q) use ($buscar) {
                $q->where('accion', 'ilike', '%'.$buscar.'%')
                    ->orWhere('objeto', 'ilike', '%'.$buscar.'%')
                    ->orWhere('detalle', 'ilike', '%'.$buscar.'%')
                    ->orWhereHas('actor', fn ($actor) => $actor->where('name', 'ilike', '%'.$buscar.'%'));
            });
        }
    }
    private function alertasDeInventario(): array
    {
        return ElementoInventario::query()
            ->with('salaFisica')
            ->orderBy('codigo')
            ->get()
            ->filter(fn (ElementoInventario $elemento) => $elemento->tieneAlertaStock())
            ->map(function (ElementoInventario $elemento) {
                $critico = $elemento->estadoStock() === 'critico';

                return [
                    'id' => 'STK-'.$elemento->codigo,
                    'nivel' => $critico ? 'alta' : 'baja',
                    'titulo' => $critico
                        ? $elemento->codigo.' stock crítico'
                        : $elemento->nombre.' bajo stock mínimo',
                    'detalle' => 'Stock '.$elemento->stock.' · mínimo '.$elemento->minimo.' · '.$elemento->ubicacion,
                    'tipo' => 'Inventario',
                    'caja' => $elemento->codigo,
                    'sala' => $elemento->codigoSala(),
                    'hora' => 'Ahora',
                    'estado' => 'abierta',
                ];
            })
            ->values()
            ->all();
    }

    public function alertas(): View
    {
        $lista = [
            [
                'id' => 'AL-014',
                'nivel' => 'alta',
                'titulo' => 'SET-042 lleva 4h en esterilizacion',
                'detalle' => 'Pabellon · Autoclave 2 · iniciado 10:12',
                'tipo' => 'Retraso',
                'caja' => 'SET-042',
                'hora' => '14:12',
                'estado' => 'abierta',
            ],
            [
                'id' => 'AL-013',
                'nivel' => 'media',
                'titulo' => 'CAJA-118 sin custodia al salir de lavado',
                'detalle' => 'Urgencia · avance registrado sin receptor',
                'tipo' => 'Custodia',
                'caja' => 'CAJA-118',
                'hora' => '14:05',
                'estado' => 'abierta',
            ],
            [
                'id' => 'AL-012',
                'nivel' => 'media',
                'titulo' => 'Faltante en SET-007 (1 pinza)',
                'detalle' => 'Detectado en preparacion · Maternidad',
                'tipo' => 'Faltante',
                'caja' => 'SET-007',
                'hora' => '11:22',
                'estado' => 'abierta',
            ],
            ...$this->alertasDeInventario(),
            [
                'id' => 'AL-008',
                'nivel' => 'baja',
                'titulo' => 'SET-019 demora en almacén >12h',
                'detalle' => 'Curaciones · lista sin retiro',
                'tipo' => 'Retraso',
                'caja' => 'SET-019',
                'hora' => 'Ayer',
                'estado' => 'resuelta',
            ],
        ];
        return view('admin.alertas', [
            'usuario' => $this->usuarioSesion(),
            'alertas' => $lista,
            'resumen' => [
                'abiertas' => collect($lista)->where('estado', 'abierta')->count(),
                'alta' => collect($lista)->where('nivel', 'alta')->where('estado', '!=', 'resuelta')->count(),
                'inventario' => collect($lista)->where('tipo', 'Inventario')->where('estado', '!=', 'resuelta')->count(),
                'resueltas' => collect($lista)->where('estado', 'resuelta')->count(),
            ],
        ]);
    }
}
