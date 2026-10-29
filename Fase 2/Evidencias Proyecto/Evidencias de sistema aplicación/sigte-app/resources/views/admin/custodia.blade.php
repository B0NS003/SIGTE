@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) }}">
@endpush

@section('title', 'SIGTE — Auditoría')

@section('content')
<div class="shell">
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

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Gestión</p>
                <h1>Auditoría</h1>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'administradora') }}">Volver al resumen</a>
            </div>
        </div>

        @if ($falla)
            <div class="ops-empty">
                <strong>No se pudo consultar la auditoría</strong>
                <p>El registro no está disponible en este momento. La bitácora no se modificó.</p>
                <p><a href="{{ route('mockups.custodia') }}">Intentar de nuevo</a></p>
            </div>
        @else
            <nav class="aud-modos" aria-label="Tipo de consulta">
                <a
                    class="aud-modo {{ $filtros['modo'] === 'bitacora' ? 'is-on' : '' }}"
                    href="{{ route('mockups.custodia', ['modo' => 'bitacora', 'periodo' => $filtros['periodo']]) }}"
                >Bitácora de acciones</a>
                <a
                    class="aud-modo {{ $filtros['modo'] === 'caja' ? 'is-on' : '' }}"
                    href="{{ route('mockups.custodia', ['modo' => 'caja']) }}"
                >Cadena de una caja</a>
            </nav>

            @if ($filtros['modo'] === 'bitacora')
                <section class="panel list aud-panel">
                    <div class="panel-head">
                        <h2>
                            @if ($usuario_elegido)
                                Historial de {{ $usuario_elegido->name }}
                            @else
                                Bitácora de acciones
                            @endif
                        </h2>
                        <span class="badge">{{ $auditoria->total() }}</span>
                    </div>

                    <form class="users-filtros aud-filtros" method="get" action="{{ route('mockups.custodia') }}">
                        <input type="hidden" name="modo" value="bitacora">
                        <label class="field">
                            <span>Buscar</span>
                            <input type="search" name="q" value="{{ $filtros['q'] }}" placeholder="Acción, objeto o detalle" autocomplete="off">
                        </label>
                        <label class="field">
                            <span>Usuario</span>
                            <select name="usuario">
                                <option value="">Todos</option>
                                @foreach ($actores as $actor)
                                    <option value="{{ $actor->id }}" @selected($filtros['usuario'] === $actor->id)>{{ $actor->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="field">
                            <span>Período</span>
                            <select name="periodo">
                                <option value="hoy" @selected($filtros['periodo'] === 'hoy')>Hoy</option>
                                <option value="semana" @selected($filtros['periodo'] === 'semana')>Últimos 7 días</option>
                                <option value="mes" @selected($filtros['periodo'] === 'mes')>Este mes</option>
                            </select>
                        </label>
                        <label class="field">
                            <span>Tipo</span>
                            <select name="tipo">
                                <option value="">Todos</option>
                                @foreach ($tipos_auditoria as $clave => $etiqueta)
                                    <option value="{{ $clave }}" @selected($filtros['tipo'] === $clave)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div class="users-filtros-acciones">
                            <button class="btn btn-dark" type="submit">Consultar</button>
                            @if ($filtros['q'] !== '' || $filtros['usuario'] || $filtros['periodo'] !== 'mes' || $filtros['tipo'] !== '')
                                <a class="btn btn-ghost" href="{{ route('mockups.custodia', ['modo' => 'bitacora']) }}">Limpiar</a>
                            @endif
                        </div>
                    </form>

                    @if ($auditoria->total() === 0)
                        <div class="ops-empty">
                            <strong>Sin resultados</strong>
                            <p>Ninguna acción coincide con esos filtros. Prueba otro período o limpia la búsqueda.</p>
                        </div>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Fecha y hora</th>
                                        <th>Usuario</th>
                                        <th>Acción</th>
                                        <th>Objeto</th>
                                        <th>Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($auditoria as $registro)
                                        <tr>
                                            <td>
                                                <strong>{{ $registro['hora'] }}</strong>
                                                <small>{{ $registro['fecha'] }}</small>
                                            </td>
                                            <td><strong>{{ $registro['actor'] }}</strong></td>
                                            <td>{{ $registro['accion'] }}</td>
                                            <td>
                                                @if (preg_match('/^(SET|CAJA)-/i', $registro['objeto']))
                                                    <a class="aud-caja-link" href="{{ route('mockups.custodia', ['modo' => 'caja', 'caja' => $registro['objeto']]) }}">{{ $registro['objeto'] }}</a>
                                                @else
                                                    {{ $registro['objeto'] }}
                                                @endif
                                            </td>
                                            <td class="muted-cell">{{ $registro['detalle'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.partials.paginas', ['paginador' => $auditoria, 'etiqueta' => 'Páginas de bitácora'])
                    @endif
                </section>
            @else
                <section class="panel aud-panel">
                    <div class="panel-head">
                        <h2>Cadena de una caja</h2>
                        @if ($filtros['caja'] !== '')
                            <span class="badge">{{ $cadenas->total() }}</span>
                        @endif
                    </div>

                    <form class="aud-busca-caja" method="get" action="{{ route('mockups.custodia') }}">
                        <input type="hidden" name="modo" value="caja">
                        <label class="field">
                            <span>Código de caja</span>
                            <input
                                type="search"
                                name="caja"
                                value="{{ $filtros['caja'] }}"
                                placeholder="Ej. SET-061 o CAJA-118"
                                autofocus
                                autocomplete="off"
                                required
                            >
                        </label>
                        <button class="btn btn-dark" type="submit">Ver cadena</button>
                    </form>

                    @if ($filtros['caja'] === '')
                        <div class="aud-espera">
                            <strong>Elige una caja</strong>
                            <p>Escribe el código para ver en qué etapa está, quién es responsable y el historial de avances.</p>
                        </div>
                    @elseif ($cadenas->total() === 0)
                        <div class="ops-empty">
                            <strong>No hay coincidencias</strong>
                            <p>Ninguna caja coincide con <strong>{{ $filtros['caja'] }}</strong>.</p>
                        </div>
                    @else
                        <div class="aud-cadenas">
                            @foreach ($cadenas as $cadena)
                                <article class="custody-card {{ $cadena['alerta'] ? 'is-alert' : '' }}">
                                    <header class="custody-head">
                                        <div>
                                            <strong>{{ $cadena['caja'] }}</strong>
                                            <span>{{ $cadena['servicio'] }} · {{ $cadena['ubicacion'] }}</span>
                                            <span class="custody-ahora">Ahora: {{ $cadena['estado'] }} · {{ $cadena['responsable'] }}</span>
                                        </div>
                                        @if ($cadena['alerta'])
                                            <span class="pill pill-danger">{{ $cadena['estado'] }} · urgente</span>
                                        @else
                                            <span class="pill pill-ok">{{ $cadena['estado'] }}</span>
                                        @endif
                                    </header>
                                    <ol class="custody-timeline">
                                        @foreach ($cadena['eventos'] as $ev)
                                            <li class="{{ $ev['tipo'] === 'Corrección' ? 'is-alert' : '' }}">
                                                <div class="ct-time">{{ $ev['hora'] }}</div>
                                                <div class="ct-body">
                                                    <strong>{{ $ev['tipo'] }}</strong>
                                                    <span>{{ $ev['nota'] }}</span>
                                                    <small>{{ $ev['de'] }}</small>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ol>
                                </article>
                            @endforeach
                        </div>
                        @include('admin.partials.paginas', ['paginador' => $cadenas, 'etiqueta' => 'Páginas de cadenas'])
                    @endif
                </section>
            @endif
        @endif
    </main>
</div>
@endsection
