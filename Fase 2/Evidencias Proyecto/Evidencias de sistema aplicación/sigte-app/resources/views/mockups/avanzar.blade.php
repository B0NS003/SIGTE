@extends('layouts.app')

@section('title', 'SIGTE — Pasar de etapa')

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
                <p class="eyebrow">Trazabilidad · cambio de etapa</p>
                <h1>Pasar de etapa</h1>
                <p class="main-sub">La caja avanza un solo paso. Al guardar quedan la fecha, la hora y quién lo hizo.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Volver al flujo</a>
            </div>
        </div>

        @if ($errors->any())
            <p class="auth-error" role="alert">{{ $errors->first() }}</p>
        @endif

        <div class="recv-layout">
            @if (session('ok'))
                <section class="panel adv-listo" role="status">
                    <p class="eyebrow">Listo</p>
                    <h2>{{ $caja['id'] }} está en {{ $fase_actual }}</h2>
                    <p class="adv-movimiento">Quedó el {{ $caja['fecha'] }} a las {{ $caja['hora'] }}, por {{ $caja['operadora'] }}.</p>
                    <div class="track-pipe adv-pipe" aria-label="Etapa actual: {{ $fase_actual }}">
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
                    <div class="recv-actions">
                        <a class="btn btn-primary" style="width:auto;min-width:12rem;" href="{{ route('mockups.actividad', ['caja' => $caja['id']]) }}">Anotar la actividad</a>
                        <a class="btn btn-ghost" href="{{ route('mockups.avanzar') }}">Elegir otra caja</a>
                    </div>
                </section>
            @else
                <form class="recv-form panel" action="{{ route('mockups.avanzar.guardar') }}" method="post">
                    @csrf
                    <input type="hidden" name="caja" value="{{ $caja['id'] }}">
                    @if ($etapa_destino)
                        <input type="hidden" name="etapa_destino" value="{{ $etapa_destino }}">
                    @endif

                    <div class="adv-hero">
                        <h2>{{ $caja['id'] }} · {{ $caja['servicio'] }}</h2>
                        @if ($caja['urgente'])
                            <span class="badge badge-urgent">Requiere atención</span>
                        @endif
                        @if ($fase_siguiente)
                            <p class="adv-movimiento">Está en <strong>{{ $fase_actual }}</strong>. Pasa a <strong>{{ $fase_siguiente }}</strong>.</p>
                        @else
                            <p class="adv-movimiento">Está en <strong>Entrega</strong>. El paso que sigue es registrarla.</p>
                        @endif
                    </div>

                    <div class="track-pipe adv-pipe" aria-label="Recorrido de la caja">
                        @foreach ($fases as $i => $fase)
                            @php
                                $done = $i < $caja['fase_idx'];
                                $current = $i === $caja['fase_idx'];
                                $next = $fase_siguiente && $i === $caja['fase_idx'] + 1;
                            @endphp
                            <div class="track-step {{ $done ? 'done' : '' }} {{ $current ? 'current' : '' }} {{ $next ? 'next' : '' }}">
                                <div class="track-node"></div>
                                <div class="track-label">{{ $fase }}</div>
                            </div>
                            @if (!$loop->last)
                                <div class="track-line {{ $i < $caja['fase_idx'] ? 'done' : '' }}"></div>
                            @endif
                        @endforeach
                    </div>

                    <div class="recv-actions">
                        @if ($fase_siguiente)
                            <button class="btn btn-primary" type="submit" style="width:auto;min-width:12rem;">Pasar a {{ $fase_siguiente }}</button>
                        @else
                            <a class="btn btn-primary" style="width:auto;min-width:12rem;" href="{{ route('mockups.entrega', ['caja' => $caja['id']]) }}">Registrar la entrega</a>
                        @endif
                    </div>

                    <label class="field-label" for="caja">Cambiar caja</label>
                    <label class="field field-select">
                        <select id="caja" onchange="window.location='{{ url('/operador/avanzar') }}?caja='+encodeURIComponent(this.value)">
                            @foreach (collect($cajas)->sortBy('servicio') as $c)
                                <option value="{{ $c['id'] }}" {{ $c['id'] === $caja['id'] ? 'selected' : '' }}>
                                    {{ $c['servicio'] }} · {{ $c['id'] }} · {{ $fases[$c['fase_idx']] }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </form>
            @endif

            <aside class="recv-side">
                <section class="panel">
                    <div class="panel-head"><h2>La caja</h2></div>
                    <ul class="why-list">
                        <li><strong>Servicio:</strong> {{ $caja['servicio'] }}</li>
                        <li><strong>Ubicación:</strong> {{ $caja['ubicacion'] }}</li>
                        <li><strong>Tiempo en esta etapa:</strong> {{ $caja['tiempo'] }}</li>
                    </ul>
                </section>
            </aside>
        </div>
    </main>
</div>
@endsection
