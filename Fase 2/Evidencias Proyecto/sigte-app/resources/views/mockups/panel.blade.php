@extends('layouts.app')

@section('title', 'SIGTE — Resumen')

@section('content')
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Esterilizacion</div>
            </div>
        </div>

        <div class="nav-label">Operacion</div>
        <nav class="nav">
            <a class="active" href="{{ route('mockups.panel') }}">Resumen</a>
            <a href="#">Recepcion</a>
            <a href="#">Lavado</a>
            <a href="#">Preparacion</a>
            <a href="#">Esterilizacion</a>
            <a href="#">Almacen</a>
            <a href="#">Entrega</a>
        </nav>

        <div class="nav-label">Gestion</div>
        <nav class="nav">
            <a href="#">Inventario</a>
            <a href="#">Custodia</a>
            <a href="#">Alertas <span class="nav-badge">3</span></a>
            <a href="#">Reportes</a>
            <a href="#">Usuarios</a>
        </nav>

        <div class="sidebar-foot">
            <div class="side-user">
                <div class="avatar">{{ strtoupper(substr($usuario['nombre'], 0, 1)) }}</div>
                <div>
                    <strong>{{ $usuario['nombre'] }}</strong>
                    <small>{{ $usuario['rol'] }}</small>
                </div>
            </div>
            <a class="side-logout" href="{{ route('mockups.login') }}">Cerrar sesion</a>
        </div>
    </aside>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Hospital San Jose de Melipilla</p>
                <h1>Resumen operativo</h1>
                <p class="main-sub">Vista de jefatura: donde hay carga, retrasos y movimientos recientes.</p>
            </div>
            <div class="main-actions">
                <button class="btn btn-ghost" type="button">Hoy</button>
                <button class="btn btn-dark" type="button">Nueva recepcion</button>
            </div>
        </div>

        <section class="alert-strip" aria-label="Alertas">
            <div class="alert-strip-title">Requieren atencion</div>
            <div class="alert-strip-list">
                @foreach ($alertas as $a)
                    <article class="alert-item alert-{{ $a['nivel'] }}">
                        <strong>{{ $a['titulo'] }}</strong>
                        <span>{{ $a['detalle'] }}</span>
                    </article>
                @endforeach
            </div>
        </section>

        <p class="section-title">Indicadores del dia</p>
        <div class="kpi-row">
            @foreach ($kpis as $kpi)
                <div class="kpi {{ $kpi['tone'] }}">
                    <div class="l">{{ $kpi['label'] }}</div>
                    <div class="n">{{ $kpi['value'] }}</div>
                    <div class="h">{{ $kpi['hint'] }}</div>
                </div>
            @endforeach
        </div>

        <p class="section-title">Flujo del ciclo (6 fases)</p>
        <div class="phases">
            @foreach ($fases as $i => $fase)
                <div class="phase">
                    <div class="phase-step">{{ $i + 1 }}</div>
                    <div class="n">{{ $fase['n'] }}</div>
                    <div class="l">{{ $fase['l'] }}</div>
                </div>
                @if (!$loop->last)
                    <div class="phase-sep" aria-hidden="true">&#8250;</div>
                @endif
            @endforeach
        </div>

        <p class="section-title">Acciones frecuentes</p>
        <div class="action-row">
            @foreach ($acciones as $acc)
                <button class="action-card" type="button">
                    <strong>{{ $acc['t'] }}</strong>
                    <span>{{ $acc['d'] }}</span>
                </button>
            @endforeach
        </div>

        <div class="grid-2">
            <section class="panel list">
                <div class="panel-head">
                    <h2>Ultimos movimientos</h2>
                    <span class="panel-link">Ver todos</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Caja / set</th>
                            <th>Fase</th>
                            <th>Area</th>
                            <th>Responsable</th>
                            <th>Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movimientos as $m)
                            <tr>
                                <td><strong>{{ $m['caja'] }}</strong></td>
                                <td><span class="badge">{{ $m['fase'] }}</span></td>
                                <td>{{ $m['area'] }}</td>
                                <td>{{ $m['quien'] }}</td>
                                <td>{{ $m['hora'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h2>Por que importa este panel</h2>
                </div>
                <ul class="why-list">
                    <li><strong>Ver cuellos de botella</strong> — si esterilizacion acumula, se actúa antes del atraso clinico.</li>
                    <li><strong>Custodia visible</strong> — quien tenia el set en cada paso.</li>
                    <li><strong>Alertas arriba</strong> — lo urgente no se esconde en tablas.</li>
                    <li><strong>Acciones a un clic</strong> — recepcion, cambio de estado e inventario.</li>
                </ul>
                <div class="panel-note">Mockup visual: los botones y filtros no ejecutan logica todavia.</div>
            </section>
        </div>
    </main>
</div>
@endsection