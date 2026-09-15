@extends('layouts.app')

@section('title', 'SIGTE — Administradora')

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
            <a class="active" href="{{ route('mockups.panel', 'administradora') }}">Resumen</a>
            <a href="{{ route('mockups.catalogo') }}">Catálogo</a>
            <a href="{{ route('mockups.inventario') }}">Inventario</a>
            <a href="{{ route('mockups.usuarios') }}">Usuarios</a>
            <a href="{{ route('mockups.reportes') }}">Reportes</a>
            <a href="{{ route('mockups.custodia') }}">Custodia / auditoría</a>
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
                <p class="eyebrow">Hospital San José de Melipilla</p>
                <h1>Resumen de gestión</h1>
                <p class="main-sub">No opera el ciclo caja a caja: configura la base (catálogo, stock, usuarios) y supervisa alertas, custodia y reportes.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Ver vista operadora</a>
                <a class="btn btn-dark" href="{{ route('mockups.catalogo') }}">Mantener catálogo</a>
            </div>
        </div>

        <section class="alert-strip" aria-label="Alertas">
            <div class="alert-strip-title">Requieren atención</div>
            <div class="alert-strip-list">
                @foreach ($alertas as $a)
                    <article class="alert-item alert-{{ $a['nivel'] }}">
                        <strong>{{ $a['titulo'] }}</strong>
                        <span>{{ $a['detalle'] }}</span>
                    </article>
                @endforeach
            </div>
        </section>

        <p class="section-title">Indicadores del día</p>
        <div class="kpi-row">
            @foreach ($kpis as $kpi)
                <div class="kpi {{ $kpi['tone'] }}">
                    <div class="l">{{ $kpi['label'] }}</div>
                    <div class="n">{{ $kpi['value'] }}</div>
                    <div class="h">{{ $kpi['hint'] }}</div>
                </div>
            @endforeach
        </div>

        <p class="section-title">Herramientas de gestión</p>
        <div class="action-row admin-tools">
            <a class="action-card" href="{{ route('mockups.catalogo') }}">
                <strong>Catálogo</strong>
                <span>Fichas maestras: qué es cada set/caja y su contenido.</span>
            </a>
            <a class="action-card" href="{{ route('mockups.inventario') }}">
                <strong>Inventario</strong>
                <span>Stock, mínimos y alertas de reposición.</span>
            </a>
            <a class="action-card" href="{{ route('mockups.usuarios') }}">
                <strong>Usuarios</strong>
                <span>Crear / desactivar cuentas y asignar rol (solo admin).</span>
            </a>
            <a class="action-card" href="{{ route('mockups.reportes') }}">
                <strong>Reportes</strong>
                <span>Producción del período, entregas, auditoría.</span>
            </a>
        </div>

        <div class="grid-2">
            <section class="panel list">
                <div class="panel-head">
                    <h2>Carga del ciclo (solo lectura)</h2>
                    <a class="panel-link" href="{{ route('mockups.panel', 'operador') }}">Abrir flujo operadora</a>
                </div>
                <div class="phases phases-compact">
                    @foreach ($fases as $i => $fase)
                        <div class="phase">
                            <div class="phase-step">{{ $i + 1 }}</div>
                            <div class="n">{{ collect($cajas)->where('fase_idx', $i)->count() }}</div>
                            <div class="l">{{ $fase }}</div>
                        </div>
                        @if (!$loop->last)
                            <div class="phase-sep" aria-hidden="true">&#8250;</div>
                        @endif
                    @endforeach
                </div>
                <table>
                    <thead>
                        <tr><th>Caja</th><th>Fase</th><th>Servicio</th><th>Hora</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($cajas as $c)
                            <tr>
                                <td><strong>{{ $c['id'] }}</strong></td>
                                <td><span class="badge">{{ $fases[$c['fase_idx']] }}</span></td>
                                <td>{{ $c['servicio'] }}</td>
                                <td>{{ $c['hora'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Cómo se reparte el trabajo</h2></div>
                <ul class="why-list">
                    <li><strong>Administradora</strong> — dueña de la información: catálogo, stock mínimo, usuarios, reportes y auditoría.</li>
                    <li><strong>Enfermera de turno</strong> — supervisa el día (alertas, inventario, entregas). Sin menú Usuarios.</li>
                    <li><strong>Operadora</strong> — ejecuta el ciclo: recepción → etapas → entrega. Consulta catálogo/inventario.</li>
                </ul>
                <div class="panel-note">Supervisión = solo mirar el flujo. Recepción y entrega viven en la vista operadora; admin entra ahí si cubre un turno.</div>
            </section>
        </div>
    </main>
</div>
@endsection
