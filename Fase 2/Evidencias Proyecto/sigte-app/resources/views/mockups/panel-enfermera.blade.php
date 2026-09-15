@extends('layouts.app')

@section('title', 'SIGTE — Enfermera de turno')

@section('content')
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Turno</div>
            </div>
        </div>
        <div class="nav-label">Operación</div>
        <nav class="nav">
            <a class="active" href="{{ route('mockups.panel', 'enfermera') }}">Resumen turno</a>
            <a href="{{ route('mockups.panel', 'operador') }}">Flujo de cajas</a>
            <a href="{{ route('mockups.recepcion') }}">Recepción</a>
            <a href="{{ route('mockups.entrega') }}">Entrega</a>
        </nav>
        <div class="nav-label">Gestión de turno</div>
        <nav class="nav">
            <a href="{{ route('mockups.inventario') }}">Inventario</a>
            <a href="{{ route('mockups.alertas') }}">Alertas <span class="nav-badge">3</span></a>
            <a href="{{ route('mockups.cierre_turno') }}">Cierre / traspaso</a>
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
                <p class="eyebrow">Enfermera de turno</p>
                <h1>Supervisión del turno</h1>
                <p class="main-sub">Opera y supervisa el día. Al terminar, cierra el turno dejando responsable, pendientes y alertas abiertas para el relevo.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Ir al flujo</a>
                <a class="btn btn-dark" href="{{ route('mockups.cierre_turno') }}">Cerrar turno</a>
            </div>
        </div>

        <section class="turno-banner panel">
            <div>
                <span class="adv-k">A cargo ahora</span>
                <strong>{{ $usuario['nombre'] }}</strong>
                <small>15 sep 2026 · Mañana 08:00–16:00 · Operadoras: Y. Maureira, C. Muñoz</small>
            </div>
            <a class="btn btn-ghost" href="{{ route('mockups.cierre_turno') }}">Traspasar / cerrar</a>
        </section>

        <section class="alert-strip">
            <div class="alert-strip-title">Atención del turno</div>
            <div class="alert-strip-list">
                @foreach ($alertas as $a)
                    <article class="alert-item alert-{{ $a['nivel'] }}">
                        <strong>{{ $a['titulo'] }}</strong>
                        <span>{{ $a['detalle'] }}</span>
                    </article>
                @endforeach
            </div>
        </section>

        <p class="section-title">Carga por fase</p>
        <div class="phases">
            @foreach ($fases as $i => $fase)
                <div class="phase">
                    <div class="phase-step">{{ $i + 1 }}</div>
                    <div class="n">{{ collect($cajas)->where('fase_idx', $i)->count() }}</div>
                    <div class="l">{{ $fase }}</div>
                </div>
                @if (!$loop->last)
                    <div class="phase-sep">&#8250;</div>
                @endif
            @endforeach
        </div>

        <div class="panel-note">Sin menú Usuarios ni reportes de período: eso es administradora. El “reporte” de enfermera es el cierre / traspaso de turno.</div>
    </main>
</div>
@endsection
