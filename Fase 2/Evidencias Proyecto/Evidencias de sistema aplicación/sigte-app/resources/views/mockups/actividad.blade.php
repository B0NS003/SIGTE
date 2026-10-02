@extends('layouts.app')

@section('title', 'SIGTE — Actividad en la etapa')

@section('content')
<div class="shell shell-ops">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Operación</div>
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
                <p class="eyebrow">Trazabilidad · actividad dentro de la etapa</p>
                <h1>Actividad en la etapa</h1>
                <p class="main-sub">La caja no cambia de etapa. Primero se elige cuál es y el sistema muestra en qué etapa del flujo ya está.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Volver al flujo</a>
            </div>
        </div>

        <div class="recv-layout">
            <section class="recv-form panel">
                <div class="panel-head">
                    <h2>Seleccionar caja</h2>
                    @if ($etapa_actual)
                        <span class="badge">Etapa actual: {{ $etapa_actual }}</span>
                    @endif
                </div>

                @if ($cajas === [])
                    <p class="recv-help">No hay cajas en el flujo. Cuando una caja entre a recepción, se podrá elegir aquí.</p>
                @else
                    <label class="field-label" for="caja">Caja / set en proceso</label>
                    <label class="field field-select">
                        <select id="caja" name="caja" onchange="window.location='{{ url('/operador/actividad') }}?caja='+encodeURIComponent(this.value)">
                            @if ($codigo_no_encontrado)
                                <option value="" selected>Elige una caja del flujo</option>
                            @endif
                            @foreach ($cajas as $c)
                                <option value="{{ $c['id'] }}" {{ $caja && $c['id'] === $caja['id'] ? 'selected' : '' }}>
                                    {{ $c['id'] }} · {{ $fases[$c['fase_idx']] }} · {{ $c['servicio'] }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    @if ($codigo_no_encontrado)
                        <div class="panel-note">Esa caja no está en el flujo. Elige una de la lista.</div>
                    @endif

                    @if ($caja)
                        <div class="adv-summary act-etapa">
                            <div>
                                <span class="adv-k">Etapa actual</span>
                                <strong>{{ $etapa_actual }}</strong>
                                <small>{{ $caja['estado'] }}</small>
                            </div>
                        </div>

                        <div class="track-pipe adv-pipe" aria-label="Etapa actual en el flujo">
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

                        <p class="recv-help">La lista de actividades válidas para {{ $etapa_actual }} se elige en el siguiente paso. Todavía no se guarda nada.</p>
                    @endif
                @endif
            </section>

            <aside class="recv-side">
                <section class="panel">
                    <div class="panel-head"><h2>Detalle</h2></div>
                    @if ($caja)
                        <ul class="why-list">
                            <li><strong>{{ $caja['id'] }}</strong> — {{ $caja['servicio'] }}</li>
                            <li><strong>Etapa actual:</strong> {{ $etapa_actual }}</li>
                            <li><strong>Ubicación:</strong> {{ $caja['ubicacion'] }}</li>
                            <li><strong>Responsable:</strong> {{ $caja['operadora'] }}</li>
                            <li><strong>Tiempo en la etapa:</strong> {{ $caja['tiempo'] }}</li>
                        </ul>
                    @else
                        <p class="recv-help">Al elegir una caja aparece su etapa, servicio y responsable.</p>
                    @endif
                </section>
            </aside>
        </div>
    </main>
</div>
@endsection
