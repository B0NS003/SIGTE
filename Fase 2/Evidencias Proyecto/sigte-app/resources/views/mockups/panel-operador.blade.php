@extends('layouts.app')

@section('title', 'SIGTE — Operadora')

@section('content')
@php
    $salaDe = function (int $i): string {
        if ($i <= 1) return 'lavado';
        if ($i <= 3) return 'armado';
        return 'esteril';
    };
    $salaLabel = [
        'lavado' => 'Sala lavado',
        'armado' => 'Sala armado',
        'esteril' => 'Material estéril',
    ];
@endphp
<div class="shell shell-ops">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Operación</div>
            </div>
        </div>
        <div class="nav-label">Mi trabajo</div>
        <nav class="nav">
            <a class="active" href="{{ route('mockups.panel', 'operador') }}">Flujo de cajas</a>
            <a href="{{ route('mockups.recepcion') }}">Nueva recepción</a>
            <a href="{{ route('mockups.avanzar') }}">Avanzar etapa</a>
            <a href="{{ route('mockups.entrega') }}">Registrar entrega</a>
        </nav>
        <div class="nav-label">Consulta</div>
        <nav class="nav">
            <a href="{{ route('mockups.catalogo') }}">Catálogo</a>
            <a href="{{ route('mockups.inventario') }}">Inventario</a>
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
                <p class="eyebrow">Turno · tres salas · trazabilidad</p>
                <h1>Flujo de cajas</h1>
                <p class="main-sub">Unidireccional por salas (cada una con su “libro”): lavado → armado → material estéril. Las etiquetas de color marcan la sala; el tracker sigue en naranja.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-dark" href="{{ route('mockups.recepcion') }}">+ Nueva recepción</a>
            </div>
        </div>

        <div class="sala-legend">
            <div class="sala-chip sala-lavado"><span></span>Sala lavado <small>Recepción · Lavado</small></div>
            <div class="sala-chip sala-armado"><span></span>Sala armado <small>Preparación · Esterilización</small></div>
            <div class="sala-chip sala-esteril"><span></span>Material estéril <small>Almacén · Entrega</small></div>
        </div>

        <div class="ops-lane-legend">
            @foreach ($fases as $i => $fase)
                <div class="ops-legend-item">
                    <span class="ops-dot">{{ $i + 1 }}</span>
                    {{ $fase }}
                </div>
            @endforeach
        </div>

        <div class="ops-board">
            @foreach ($cajas as $caja)
                @php $sala = $salaDe($caja['fase_idx']); @endphp
                <article class="track-card {{ $caja['urgente'] ? 'is-urgent' : '' }}">
                    <header class="track-head">
                        <div>
                            <h2>{{ $caja['id'] }}</h2>
                            <p>{{ $caja['servicio'] }} · {{ $caja['hora'] }} · {{ $caja['operadora'] }}</p>
                        </div>
                        <div class="track-head-badges">
                            <span class="badge badge-sala badge-{{ $sala }}">{{ $salaLabel[$sala] }}</span>
                            <span class="badge">{{ $fases[$caja['fase_idx']] }}</span>
                        </div>
                    </header>

                    <div class="track-pipe" aria-label="Progreso del ciclo">
                        @foreach ($fases as $i => $fase)
                            @php
                                $done = $i < $caja['fase_idx'];
                                $current = $i === $caja['fase_idx'];
                            @endphp
                            <div class="track-step {{ $done ? 'done' : '' }} {{ $current ? 'current' : '' }}">
                                <div class="track-node"></div>
                                <div class="track-label">{{ $fase }}</div>
                            </div>
                            @if (!$loop->last)
                                <div class="track-line {{ $i < $caja['fase_idx'] ? 'done' : '' }}"></div>
                            @endif
                        @endforeach
                    </div>

                    <footer class="track-foot">
                        <span>{{ $caja['estado'] }}</span>
                        <div class="track-actions">
                            <button class="btn btn-ghost" type="button" disabled title="Detalle después">Modificar</button>
                            <a class="btn btn-dark" href="{{ route('mockups.avanzar', ['caja' => $caja['id']]) }}">Aceptar avance</a>
                        </div>
                    </footer>
                </article>
            @endforeach
        </div>
    </main>
</div>
@endsection
