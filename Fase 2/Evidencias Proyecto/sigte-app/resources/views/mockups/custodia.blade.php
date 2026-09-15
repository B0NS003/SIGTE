@extends('layouts.app')

@section('title', 'SIGTE — Custodia / auditoría')

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

        <div class="nav-label">Gestión</div>
        <nav class="nav">
            <a href="{{ route('mockups.panel', 'administradora') }}">Resumen</a>
            <a href="{{ route('mockups.catalogo') }}">Catálogo</a>
            <a href="{{ route('mockups.inventario') }}">Inventario</a>
            <a href="{{ route('mockups.usuarios') }}">Usuarios</a>
            <a href="{{ route('mockups.reportes') }}">Reportes</a>
            <a class="active" href="{{ route('mockups.custodia') }}">Custodia / auditoría</a>
            <a href="{{ route('mockups.alertas') }}">Alertas <span class="nav-badge">3</span></a>
        </nav>

        <div class="nav-label">Supervisión</div>
        <nav class="nav">
            <a href="{{ route('mockups.panel', 'operador') }}">Ver flujo de cajas</a>
        </nav>

        <div class="sidebar-foot">
            <div class="side-user">
                <div class="avatar">{{ strtoupper(substr($usuario['nombre'], 0, 1)) }}</div>
                <div>
                    <strong>{{ $usuario['nombre'] }}</strong>
                    <small>{{ $usuario['rol'] }}</small>
                </div>
            </div>
            <a class="side-logout" href="{{ route('mockups.login') }}">Cambiar rol</a>
        </div>
    </aside>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Gestión · trazabilidad de responsabilidad</p>
                <h1>Custodia / auditoría</h1>
                <p class="main-sub">Dos lecturas: la <strong>cadena de custodia</strong> de cada caja (quién entregó / recibió) y la <strong>bitácora</strong> de quién cambió qué en el sistema.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'administradora') }}">Volver al resumen</a>
                <button class="btn btn-dark" type="button">Exportar bitácora</button>
            </div>
        </div>

        <div class="query-toolbar panel">
            <label class="field" style="margin:0; flex:1;">
                <span class="icon" aria-hidden="true">⌕</span>
                <input type="search" placeholder="Buscar caja, usuario o acción…" value="SET-007">
            </label>
            <label class="field field-select" style="margin:0; min-width:11rem;">
                <select>
                    <option selected>Hoy</option>
                    <option>Últimos 7 días</option>
                    <option>Este mes</option>
                </select>
            </label>
            <label class="field field-select" style="margin:0; min-width:11rem;">
                <select>
                    <option selected>Todo</option>
                    <option>Solo custodia</option>
                    <option>Solo auditoría</option>
                    <option>Solo alertas</option>
                </select>
            </label>
        </div>

        <div class="grid-2">
            <section class="panel">
                <div class="panel-head">
                    <h2>Cadenas de custodia</h2>
                    <span class="badge">Por caja</span>
                </div>

                @foreach ($cadenas as $cadena)
                    <article class="custody-card {{ $cadena['estado'] === 'Incidencia' ? 'is-alert' : '' }}">
                        <header class="custody-head">
                            <div>
                                <strong>{{ $cadena['caja'] }}</strong>
                                <span>{{ $cadena['servicio'] }}</span>
                            </div>
                            @if ($cadena['estado'] === 'Incidencia')
                                <span class="pill pill-danger">{{ $cadena['estado'] }}</span>
                            @else
                                <span class="pill pill-ok">{{ $cadena['estado'] }}</span>
                            @endif
                        </header>
                        <ol class="custody-timeline">
                            @foreach ($cadena['eventos'] as $ev)
                                <li class="{{ $ev['tipo'] === 'Alerta' ? 'is-alert' : '' }}">
                                    <div class="ct-time">{{ $ev['hora'] }}</div>
                                    <div class="ct-body">
                                        <strong>{{ $ev['tipo'] }}</strong>
                                        <span>{{ $ev['nota'] }}</span>
                                        <small>
                                            @if ($ev['a'] !== '—')
                                                {{ $ev['de'] }} → {{ $ev['a'] }}
                                            @else
                                                {{ $ev['de'] }}
                                            @endif
                                        </small>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </article>
                @endforeach
            </section>

            <section class="panel list">
                <div class="panel-head">
                    <h2>Bitácora de auditoría</h2>
                    <span class="badge">Quién · qué · cuándo</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Actor</th>
                                <th>Acción</th>
                                <th>Objeto</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($auditoria as $row)
                                <tr>
                                    <td>{{ $row['hora'] }}</td>
                                    <td><strong>{{ $row['actor'] }}</strong></td>
                                    <td>{{ $row['accion'] }}</td>
                                    <td>{{ $row['objeto'] }}</td>
                                    <td class="muted-cell">{{ $row['detalle'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="panel-note">Custodia = responsabilidad del material. Auditoría = cambios en el sistema (etapas, stock, usuarios). Mockup sin BD.</div>
            </section>
        </div>

        <section class="panel" style="margin-top:1rem;">
            <div class="panel-head"><h2>Por qué van juntas</h2></div>
            <ul class="why-list" style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem 1.5rem;">
                <li><strong>Recepción / entrega</strong> — firma de quién entrega y quién recibe (cadena).</li>
                <li><strong>Avance de etapa</strong> — queda en bitácora quién movió la caja.</li>
                <li><strong>Incidencias</strong> — alertas sin custodia aparecen en la cadena (ej. CAJA-118).</li>
                <li><strong>Admin</strong> — también audita altas de usuario y cambios de stock mínimo.</li>
            </ul>
        </section>
    </main>
</div>
@endsection
