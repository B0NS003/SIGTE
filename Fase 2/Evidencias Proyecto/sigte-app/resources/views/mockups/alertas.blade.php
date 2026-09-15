@extends('layouts.app')

@section('title', 'SIGTE — Alertas')

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
            <a href="{{ route('mockups.custodia') }}">Custodia / auditoría</a>
            <a class="active" href="{{ route('mockups.alertas') }}">Alertas <span class="nav-badge">{{ $resumen['abiertas'] }}</span></a>
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
                <p class="eyebrow">Gestión · atención priorizada</p>
                <h1>Alertas</h1>
                <p class="main-sub">Todo lo que sale del flujo normal: retrasos, custodia incompleta, faltantes y stock bajo. Desde aquí se prioriza y se marca como atendida.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'administradora') }}">Volver al resumen</a>
            </div>
        </div>

        <div class="kpi-row">
            <div class="kpi warn">
                <div class="l">Abiertas</div>
                <div class="n">{{ $resumen['abiertas'] }}</div>
                <div class="h">Pendientes de acción</div>
            </div>
            <div class="kpi warn">
                <div class="l">Prioridad alta</div>
                <div class="n">{{ $resumen['alta'] }}</div>
                <div class="h">Requieren atención ya</div>
            </div>
            <div class="kpi">
                <div class="l">Inventario</div>
                <div class="n">{{ $resumen['inventario'] }}</div>
                <div class="h">Bajo mínimo / crítico</div>
            </div>
            <div class="kpi ok">
                <div class="l">Resueltas</div>
                <div class="n">{{ $resumen['resueltas'] }}</div>
                <div class="h">En el período visible</div>
            </div>
        </div>

        <div class="query-toolbar panel">
            <label class="field field-select" style="margin:0; min-width:10rem;">
                <select>
                    <option selected>Todas</option>
                    <option>Abiertas</option>
                    <option>En revisión</option>
                    <option>Resueltas</option>
                </select>
            </label>
            <label class="field field-select" style="margin:0; min-width:10rem;">
                <select>
                    <option selected>Cualquier nivel</option>
                    <option>Alta</option>
                    <option>Media</option>
                    <option>Baja</option>
                </select>
            </label>
            <label class="field field-select" style="margin:0; min-width:10rem;">
                <select>
                    <option selected>Todos los tipos</option>
                    <option>Retraso</option>
                    <option>Custodia</option>
                    <option>Faltante</option>
                    <option>Inventario</option>
                </select>
            </label>
            <label class="field" style="margin:0; flex:1;">
                <span class="icon" aria-hidden="true">⌕</span>
                <input type="search" placeholder="Buscar por caja o texto…">
            </label>
        </div>

        <section class="panel list">
            <div class="panel-head">
                <h2>Bandeja de alertas</h2>
                <span class="badge">{{ count($alertas) }}</span>
            </div>

            <div class="alerts-board">
                @foreach ($alertas as $a)
                    <article class="alert-row nivel-{{ $a['nivel'] }} estado-{{ $a['estado'] }}">
                        <div class="alert-row-main">
                            <div class="alert-row-top">
                                <span class="pill {{ $a['nivel'] === 'alta' ? 'pill-danger' : ($a['nivel'] === 'media' ? 'pill-warn' : 'pill-ok') }}">
                                    {{ ucfirst($a['nivel']) }}
                                </span>
                                <span class="alert-type">{{ $a['tipo'] }}</span>
                                <span class="alert-id">{{ $a['id'] }}</span>
                                <span class="alert-time">{{ $a['hora'] }}</span>
                            </div>
                            <strong>{{ $a['titulo'] }}</strong>
                            <p>{{ $a['detalle'] }}</p>
                            <div class="alert-meta">
                                <span>Ref: <strong>{{ $a['caja'] }}</strong></span>
                                @if ($a['estado'] === 'abierta')
                                    <span class="pill pill-warn">Abierta</span>
                                @elseif ($a['estado'] === 'en_revision')
                                    <span class="pill">En revisión</span>
                                @else
                                    <span class="pill pill-ok">Resuelta</span>
                                @endif
                            </div>
                        </div>
                        <div class="alert-row-actions">
                            @if ($a['tipo'] === 'Inventario')
                                <a class="btn btn-ghost" href="{{ route('mockups.inventario') }}">Ver inventario</a>
                            @elseif ($a['tipo'] === 'Custodia')
                                <a class="btn btn-ghost" href="{{ route('mockups.custodia') }}">Ver custodia</a>
                            @else
                                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Ver flujo</a>
                            @endif
                            @if ($a['estado'] !== 'resuelta')
                                <button class="btn btn-dark" type="button">Marcar atendida</button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="panel-note">Mockup: el badge del menú usa alertas abiertas. En el sistema real, marcar atendida queda en la bitácora de auditoría.</div>
        </section>
    </main>
</div>
@endsection
