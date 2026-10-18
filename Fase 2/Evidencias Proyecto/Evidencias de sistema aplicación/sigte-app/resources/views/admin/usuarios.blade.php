@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) }}">
@endpush

@section('title', 'SIGTE — Usuarios')

<!-- Sección de contenido de la página de usuarios -->
@section('content')
<!-- Contenedor principal de la página -->
<div class="shell">
    <!-- Barra lateral de navegación -->
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Administración</div>
            </div>
        </div>

                @include('partials.menu-rol')
        
        <div class="sidebar-foot">
            <div class="side-user">
                <div class="avatar">{{ strtoupper(substr($usuario['nombre'], 0, 1)) }}</div>
                <div>
                    <strong>{{ $usuario['nombre'] }}</strong>
                    <small>{{ $usuario['rol'] }}</small>
                </div>
            </div>
            @include('partials.logout')
        </div>
    </aside>

    <!-- Contenido principal de la página -->
    <main class="main">
        <!-- Encabezado de la página -->
        <div class="main-top">
            <div>
                <p class="eyebrow">Gestión de cuentas</p>
                <h1>Usuarios</h1>
                <p class="main-sub">Crea las cuentas del servicio, asigna el rol y desactiva el acceso cuando ya no corresponda.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'administradora') }}">Volver al resumen</a>
            </div>
        </div>

        <div class="kpi-row users-kpis">
            <div class="kpi">
                <div class="l">Total</div>
                <div class="n">{{ $resumen['total'] }}</div>
                <div class="h">Cuentas registradas</div>
            </div>
            <div class="kpi ok">
                <div class="l">Activos</div>
                <div class="n">{{ $resumen['activos'] }}</div>
                <div class="h">Pueden iniciar sesión</div>
            </div>
            <div class="kpi">
                <div class="l">Inactivos</div>
                <div class="n">{{ $resumen['inactivos'] }}</div>
                <div class="h">Desactivados</div>
            </div>
            <div class="kpi">
                <div class="l">Administradoras</div>
                <div class="n">{{ $resumen['admin'] }}</div>
                <div class="h">Con gestión de usuarios</div>
            </div>
        </div>
        <!-- Grilla de dos columnas para la lista de usuarios -->
        <div class="users-page">
            <section class="panel list">
                <!-- Encabezado de la lista de usuarios -->
                <div class="panel-head">
                    <h2>Cuentas</h2>
                    <span class="badge">{{ $usuarios->total() }}</span>
                </div>
                <form class="users-filtros" method="get" action="{{ route('mockups.usuarios') }}">
                    <label class="field">
                        <span>Nombre o correo</span>
                        <input type="search" name="q" value="{{ $filtros['q'] }}" placeholder="Buscar cuenta">
                    </label>
                    <label class="field">
                        <span>Estado</span>
                        <select name="estado">
                            <option value="">Todos</option>
                            <option value="activo" @selected($filtros['estado'] === 'activo')>Activo</option>
                            <option value="inactivo" @selected($filtros['estado'] === 'inactivo')>Inactivo</option>
                        </select>
                    </label>
                    <label class="field">
                        <span>Rol</span>
                        <select name="rol">
                            <option value="">Todos</option>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}" @selected($filtros['rol'] === $rol->id)>{{ $rol->etiqueta() }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="users-filtros-acciones">
                        <button class="btn btn-dark" type="submit">Filtrar</button>
                        @if ($filtros['q'] !== '' || $filtros['estado'] || $filtros['rol'])
                            <a class="btn btn-ghost" href="{{ route('mockups.usuarios') }}">Limpiar</a>
                        @endif
                    </div>
                </form>
                <!-- Tabla de usuarios -->
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Creada</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Iteración sobre los usuarios -->
                            @forelse ($usuarios as $cuenta)
                                <tr class="{{ $cuenta->activo ? '' : 'user-inactive' }}">
                                    <td class="user-id">{{ $cuenta->id }}</td>
                                    <td>
                                        <div class="user-quien">
                                            <span class="user-icono {{ $cuenta->rol?->claseVisual() }}" aria-hidden="true">
                                                @switch($cuenta->rol?->nombre)
                                                    @case('administradora')
                                                        <svg viewBox="0 0 24 24"><path d="M12 4.2 18.2 6.6v4.4c0 3.5-2.4 6-6.2 7.2-3.8-1.2-6.2-3.7-6.2-7.2V6.6L12 4.2z"/><path d="M9.4 12.1 11.2 13.9 14.8 10.2"/></svg>
                                                        @break
                                                    @case('enfermera')
                                                        <svg viewBox="0 0 24 24"><path d="M12 5.2v13.6M7.2 9.2h9.6"/><circle cx="12" cy="12" r="7.2"/></svg>
                                                        @break
                                                    @case('secretaria')
                                                        <svg viewBox="0 0 24 24"><path d="M7 4.8h7.2L18.2 9v10.2H7z"/><path d="M14.2 4.8V9H18.2M9.2 13h5.6M9.2 16.2h4"/></svg>
                                                        @break
                                                    @default
                                                        <svg viewBox="0 0 24 24"><circle cx="12" cy="9" r="2.7"/><path d="M6.2 18.2c1.2-2.6 3.2-3.8 5.8-3.8s4.6 1.2 5.8 3.8"/></svg>
                                                @endswitch
                                            </span>
                                            <div>
                                                <strong>{{ $cuenta->name }}</strong>
                                                <div class="muted-cell">{{ $cuenta->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge rol-pill {{ $cuenta->rol?->claseVisual() }}">{{ $cuenta->rol?->etiqueta() ?? 'Sin rol' }}</span></td>
                                    <td>
                                        <span class="pill {{ $cuenta->activo ? 'pill-ok' : 'pill-warn' }}">{{ $cuenta->activo ? 'Activo' : 'Inactivo' }}</span>
                                    </td>
                                    <td>{{ $cuenta->created_at?->timezone('America/Santiago')->format('d/m/Y') }}</td>
                                    <td class="user-actions">
                                        <a class="btn btn-ghost" href="{{ route('mockups.usuarios', array_filter(['editar' => $cuenta->id, 'q' => $filtros['q'] !== '' ? $filtros['q'] : null, 'estado' => $filtros['estado'], 'rol' => $filtros['rol'], 'page' => $usuarios->currentPage() > 1 ? $usuarios->currentPage() : null])) }}">Editar</a>
                                        <form method="post" action="{{ route('mockups.usuarios.estado', $cuenta) }}">
                                            @csrf
                                            @if ($filtros['q'] !== '')
                                                <input type="hidden" name="q" value="{{ $filtros['q'] }}">
                                            @endif
                                            @if ($filtros['estado'])
                                                <input type="hidden" name="estado" value="{{ $filtros['estado'] }}">
                                            @endif
                                            @if ($filtros['rol'])
                                                <input type="hidden" name="rol" value="{{ $filtros['rol'] }}">
                                            @endif
                                            @if ($usuarios->currentPage() > 1)
                                                <input type="hidden" name="page" value="{{ $usuarios->currentPage() }}">
                                            @endif
                                            <button class="btn {{ $cuenta->activo ? 'btn-ghost' : 'btn-dark' }}" type="submit">{{ $cuenta->activo ? 'Desactivar' : 'Reactivar' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">Ninguna cuenta coincide con ese filtro.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($usuarios->hasPages())
                    <nav class="users-paginas" aria-label="Páginas de cuentas">
                        @if ($usuarios->onFirstPage())
                            <span class="users-pagina is-off">Anterior</span>
                        @else
                            <a class="users-pagina" href="{{ $usuarios->previousPageUrl() }}">Anterior</a>
                        @endif
                        @foreach ($usuarios->getUrlRange(1, $usuarios->lastPage()) as $numero => $url)
                            @if ($numero === $usuarios->currentPage())
                                <span class="users-pagina is-on" aria-current="page">{{ $numero }}</span>
                            @else
                                <a class="users-pagina" href="{{ $url }}">{{ $numero }}</a>
                            @endif
                        @endforeach
                        @if ($usuarios->hasMorePages())
                            <a class="users-pagina" href="{{ $usuarios->nextPageUrl() }}">Siguiente</a>
                        @else
                            <span class="users-pagina is-off">Siguiente</span>
                        @endif
                        <span class="users-rango">{{ $usuarios->firstItem() }}–{{ $usuarios->lastItem() }} de {{ $usuarios->total() }}</span>
                    </nav>
                @endif
            </section>

            <form class="panel cuenta-alta" method="post" action="{{ $editar ? route('mockups.usuarios.actualizar', $editar) : route('mockups.usuarios.guardar') }}">
                @csrf
                @if ($filtros['q'] !== '')
                    <input type="hidden" name="q" value="{{ $filtros['q'] }}">
                @endif
                @if ($filtros['estado'])
                    <input type="hidden" name="estado" value="{{ $filtros['estado'] }}">
                @endif
                @if ($filtros['rol'])
                    <input type="hidden" name="rol" value="{{ $filtros['rol'] }}">
                @endif
                @if ($usuarios->currentPage() > 1)
                    <input type="hidden" name="page" value="{{ $usuarios->currentPage() }}">
                @endif
                <div class="panel-head">
                    <h2>{{ $editar ? 'Editar cuenta' : 'Nueva cuenta' }}</h2>
                    <span class="badge">{{ $editar ? 'Cambio' : 'Alta' }}</span>
                </div>
                @if (session('ok'))
                    <p class="adv-ok" role="status">{{ session('ok') }}</p>
                @endif
                @error('estado')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror
                <div class="cuenta-grid">
                    <div>
                        <label class="field-label" for="nombre">Nombre completo</label>
                        <label class="field @error('nombre') is-invalido @enderror">
                            <input id="nombre" name="nombre" type="text" required maxlength="120" placeholder="Nombre y apellido" value="{{ old('nombre', $editar->name ?? '') }}">
                        </label>
                        @error('nombre')
                            <p class="sigte-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="field-label" for="email">Correo institucional</label>
                        <label class="field @error('email') is-invalido @enderror">
                            <input id="email" name="email" type="email" required maxlength="255" placeholder="nombre@hsjmelipilla.cl" value="{{ old('email', $editar->email ?? '') }}">
                        </label>
                        @error('email')
                            <p class="sigte-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="field-label" for="rol_id">Rol</label>
                        <label class="field field-select @error('rol_id') is-invalido @enderror">
                            <select id="rol_id" name="rol_id" required>
                                <option value="">Elige el rol</option>
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id }}" @selected((string) old('rol_id', $editar->rol_id ?? '') === (string) $rol->id)>{{ $rol->etiqueta() }}</option>
                                @endforeach
                            </select>
                        </label>
                        @error('rol_id')
                            <p class="sigte-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="field-label" for="password">{{ $editar ? 'Nueva contraseña' : 'Contraseña temporal' }}</label>
                        <label class="field @error('password') is-invalido @enderror">
                            <input id="password" name="password" type="password" @unless($editar) required @endunless minlength="8" maxlength="80" placeholder="{{ $editar ? 'Déjala vacía para no cambiarla' : 'Mínimo 8 caracteres' }}" autocomplete="new-password">
                            <button class="btn-ver-clave" type="button" aria-label="Mostrar contraseña" aria-pressed="false">
                                <svg class="icono-ojo" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path class="parpado" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                    <path class="cerrado" d="M4 12.5h16"/>
                                    <circle class="pupila" cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </label>
                        @error('password')
                            <p class="sigte-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="cuenta-pie">
                    <div class="recv-actions">
                        @if ($editar)
                            <a class="btn btn-ghost" href="{{ route('mockups.usuarios', array_filter(['q' => $filtros['q'] !== '' ? $filtros['q'] : null, 'estado' => $filtros['estado'], 'rol' => $filtros['rol'], 'page' => $usuarios->currentPage() > 1 ? $usuarios->currentPage() : null])) }}">Cancelar</a>
                        @endif
                        <button class="btn btn-primary" type="submit">{{ $editar ? 'Guardar cambios' : 'Crear usuario' }}</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
