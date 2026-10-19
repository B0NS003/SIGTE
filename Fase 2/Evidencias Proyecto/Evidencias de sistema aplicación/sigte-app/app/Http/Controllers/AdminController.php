<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ConsultaDeCajas;
use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\Caja;
use App\Models\ElementoInventario;
use App\Models\Personal;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
        $persona->turno = $datos['turno'] ?? null;
        $persona->save();

        return response()->json(['ok' => true, 'turno' => $persona->turno]);
    }

    public function guardarUsuario(Request $request): RedirectResponse
    {
        $datos = $this->validarCuenta($request);

        $rol = Rol::query()->findOrFail($datos['rol_id']);
        User::query()->create([
            'name' => $datos['nombre'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'rol_id' => $rol->id,
            'activo' => true,
        ]);

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
        $cajas = $this->cajas();
        $fases = $this->fases();
        $momento = now()->timezone('America/Santiago');
        $filas = array_map(fn (array $caja) => [
            'id' => $caja['id'],
            'servicio' => $caja['servicio'],
            'indice' => $caja['fase_idx'],
            'etapa' => $fases[$caja['fase_idx']] ?? '',
        ], $cajas);
        return view('admin.reportes', [
            'usuario' => $this->usuarioSesion(),
            'falla' => $request->boolean('falla'),
            'fecha' => $momento->format('d/m/Y'),
            'hora' => $momento->format('H:i'),
            'fases' => $fases,
            'filas' => $filas,
        ]);
    }
    public function historialEntregas(Request $request): View
    {
        $entregas = [
            [
                'id' => 'ENT-241',
                'fecha' => '06/10/2026',
                'hora' => '11:40',
                'cuando' => 'hoy',
                'servicio' => 'Pabellón',
                'entrega' => 'Yamilet Maureira',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Daniela Soto',
                'recibe_rol' => 'Enfermera de pabellón',
                'cajas' => [
                    ['codigo' => 'SET-033', 'nombre' => 'Set de pabellón'],
                    ['codigo' => 'CAJA-091', 'nombre' => 'Caja instrumental'],
                ],
                'materiales' => ['Paquete de ropa'],
            ],
            [
                'id' => 'ENT-240',
                'fecha' => '06/10/2026',
                'hora' => '10:20',
                'cuando' => 'hoy',
                'servicio' => 'Dental',
                'entrega' => 'Carla Muñoz',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Daniela Soto',
                'recibe_rol' => 'Enfermera de dental',
                'cajas' => [
                    ['codigo' => 'SET-DEN-04', 'nombre' => 'Set dental'],
                ],
                'materiales' => ['Instrumental suelto'],
            ],
            [
                'id' => 'ENT-238',
                'fecha' => '06/10/2026',
                'hora' => '09:15',
                'cuando' => 'hoy',
                'servicio' => 'UCI',
                'entrega' => 'Carla Muñoz',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Alejandra Riquelme',
                'recibe_rol' => 'Enfermera de turno',
                'cajas' => [
                    ['codigo' => 'SET-055', 'nombre' => 'Set de UCI'],
                ],
                'materiales' => ['Material de curación'],
            ],
            [
                'id' => 'ENT-230',
                'fecha' => '05/10/2026',
                'hora' => '18:02',
                'cuando' => 'ayer',
                'servicio' => 'Urgencia',
                'entrega' => 'Yamilet Maureira',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Patricia Vega',
                'recibe_rol' => 'Enfermera de urgencia',
                'cajas' => [
                    ['codigo' => 'CAJA-118', 'nombre' => 'Caja de urgencia'],
                ],
                'materiales' => [],
            ],
            [
                'id' => 'ENT-226',
                'fecha' => '05/10/2026',
                'hora' => '16:20',
                'cuando' => 'ayer',
                'servicio' => 'Maternidad',
                'entrega' => 'Carla Muñoz',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Natalia Sánchez',
                'recibe_rol' => 'Jefatura de maternidad',
                'cajas' => [
                    ['codigo' => 'SET-007', 'nombre' => 'Set de maternidad'],
                ],
                'materiales' => ['Instrumental suelto'],
            ],
            [
                'id' => 'ENT-214',
                'fecha' => '04/10/2026',
                'hora' => '12:05',
                'cuando' => 'anterior',
                'servicio' => 'Curaciones',
                'entrega' => 'Yamilet Maureira',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Daniela Soto',
                'recibe_rol' => 'Enfermera de curaciones',
                'cajas' => [
                    ['codigo' => 'CAJA-200', 'nombre' => 'Caja de curación'],
                ],
                'materiales' => ['Material de curación'],
            ],
        ];
        return view('admin.historial', [
            'usuario' => $this->usuarioSesion(),
            'entregas' => $entregas,
            'servicios' => collect($entregas)->pluck('servicio')->unique()->values()->all(),
            'falla' => $request->boolean('falla'),
        ]);
    }
    public function custodia(Request $request): View
    {
        return view('admin.custodia', [
            'usuario' => $this->usuarioSesion(),
            'falla' => $request->boolean('falla'),
            'cadenas' => [
                [
                    'caja' => 'SET-007',
                    'servicio' => 'Maternidad',
                    'estado' => 'En almacén',
                    'alerta' => false,
                    'cuando' => 'hoy',
                    'eventos' => [
                        ['hora' => '08:10', 'tipo' => 'Recepción', 'de' => 'Enf. Carla Muñoz', 'a' => 'Y. Maureira', 'nota' => 'Área sucia'],
                        ['hora' => '09:05', 'tipo' => 'Avance', 'de' => 'Y. Maureira', 'a' => '—', 'nota' => 'Recepción → Lavado'],
                        ['hora' => '10:20', 'tipo' => 'Avance', 'de' => 'A. Riquelme', 'a' => '—', 'nota' => 'Lavado → Preparación'],
                        ['hora' => '11:40', 'tipo' => 'Avance', 'de' => 'Y. Maureira', 'a' => '—', 'nota' => 'Preparación → Esterilización'],
                        ['hora' => '13:41', 'tipo' => 'Avance', 'de' => 'Y. Maureira', 'a' => '—', 'nota' => 'Esterilización → Almacén'],
                    ],
                ],
                [
                    'caja' => 'CAJA-118',
                    'servicio' => 'Urgencia',
                    'estado' => 'Incidencia',
                    'alerta' => true,
                    'cuando' => 'hoy',
                    'eventos' => [
                        ['hora' => '12:40', 'tipo' => 'Recepción', 'de' => 'Enf. Luis Perez', 'a' => 'A. Riquelme', 'nota' => 'Ingreso urgencia'],
                        ['hora' => '13:58', 'tipo' => 'Avance', 'de' => 'A. Riquelme', 'a' => '—', 'nota' => 'Recepción → Lavado'],
                        ['hora' => '14:05', 'tipo' => 'Alerta', 'de' => 'Sistema', 'a' => '—', 'nota' => 'Sin custodia al salir de lavado'],
                    ],
                ],
            ],
            'auditoria' => [
                ['fecha' => '06/10', 'hora' => '14:12', 'cuando' => 'hoy', 'actor' => 'Y. Maureira', 'accion' => 'Avanzó etapa', 'tipo' => 'etapa', 'objeto' => 'SET-042', 'detalle' => 'Preparación → Esterilización'],
                ['fecha' => '06/10', 'hora' => '13:58', 'cuando' => 'hoy', 'actor' => 'A. Riquelme', 'accion' => 'Avanzó etapa', 'tipo' => 'etapa', 'objeto' => 'CAJA-118', 'detalle' => 'Recepción → Lavado'],
                ['fecha' => '06/10', 'hora' => '13:41', 'cuando' => 'hoy', 'actor' => 'Y. Maureira', 'accion' => 'Avanzó etapa', 'tipo' => 'etapa', 'objeto' => 'SET-007', 'detalle' => 'Esterilización → Almacén'],
                ['fecha' => '06/10', 'hora' => '12:10', 'cuando' => 'hoy', 'actor' => 'N. Sánchez', 'accion' => 'Actualizó stock mínimo', 'tipo' => 'inventario', 'objeto' => 'SET-CES-02', 'detalle' => 'Mínimo 2 → 3'],
                ['fecha' => '06/10', 'hora' => '11:55', 'cuando' => 'hoy', 'actor' => 'N. Sánchez', 'accion' => 'Creó usuario', 'tipo' => 'usuarios', 'objeto' => 'C. Muñoz', 'detalle' => 'Rol Operadora'],
                ['fecha' => '06/10', 'hora' => '11:20', 'cuando' => 'hoy', 'actor' => 'Y. Maureira', 'accion' => 'Registró recepción', 'tipo' => 'etapa', 'objeto' => 'CAJA-091', 'detalle' => 'Desde Pabellón'],
                ['fecha' => '05/10', 'hora' => '10:45', 'cuando' => 'ayer', 'actor' => 'A. Riquelme', 'accion' => 'Registró entrega', 'tipo' => 'entrega', 'objeto' => 'SET-019', 'detalle' => 'Retira Enf. D. Soto · UCI'],
                ['fecha' => '01/10', 'hora' => '09:30', 'cuando' => 'mes', 'actor' => 'N. Sánchez', 'accion' => 'Desactivó usuario', 'tipo' => 'usuarios', 'objeto' => 'P. Vega', 'detalle' => 'Cuenta inactiva'],
            ],
        ]);
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
