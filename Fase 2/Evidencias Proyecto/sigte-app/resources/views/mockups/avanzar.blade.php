@extends('layouts.app')

@section('title', 'SIGTE — Avanzar etapa')

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
        <div class="nav-label">Mi trabajo</div>
        <nav class="nav">
            <a href="{{ route('mockups.panel', 'operador') }}">Flujo de cajas</a>
            <a href="{{ route('mockups.recepcion') }}">Nueva recepción</a>
            <a class="active" href="{{ route('mockups.avanzar') }}">Avanzar etapa</a>
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
                <p class="eyebrow">Trazabilidad · cambio de etapa</p>
                <h1>Avanzar etapa</h1>
                <p class="main-sub">Mueve la caja al siguiente paso del ciclo (solo hacia adelante). Queda registro de quién y cuándo — mockup visual.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Volver al flujo</a>
            </div>
        </div>

        <div class="recv-layout">
            <form class="recv-form panel" action="{{ route('mockups.panel', 'operador') }}" method="get">
                <div class="panel-head">
                    <h2>Seleccionar caja</h2>
                    <span class="badge">{{ $fase_actual }} → {{ $fase_siguiente ?? 'Fin' }}</span>
                </div>

                <label class="field-label" for="caja">Caja / set en proceso</label>
                <label class="field field-select">
                    <select id="caja" name="caja" onchange="window.location='{{ url('/operador/avanzar') }}?caja='+this.value">
                        @foreach ($cajas as $c)
                            <option value="{{ $c['id'] }}" {{ $c['id'] === $caja['id'] ? 'selected' : '' }}>
                                {{ $c['id'] }} · {{ $fases[$c['fase_idx']] }} · {{ $c['servicio'] }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div class="adv-summary">
                    <div>
                        <span class="adv-k">Ahora</span>
                        <strong>{{ $fase_actual }}</strong>
                        <small>{{ $caja['estado'] }}</small>
                    </div>
                    <div class="adv-arrow" aria-hidden="true">→</div>
                    <div>
                        <span class="adv-k">Siguiente</span>
                        @if ($fase_siguiente)
                            <strong>{{ $fase_siguiente }}</strong>
                            <small>Solo se permite esta transición</small>
                        @else
                            <strong>Ya en entrega</strong>
                            <small>Usar “Registrar entrega”</small>
                        @endif
                    </div>
                </div>

                <div class="track-pipe adv-pipe" aria-label="Progreso">
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

                <label class="field-label" for="nota">Nota del avance (opcional)</label>
                <textarea id="nota" class="recv-textarea" name="nota" rows="2" placeholder="Ej: ciclo de lavado OK, pasa a armado / control visual OK"></textarea>

                <div class="recv-checks">
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Confirmó que la etapa actual está completa</span>
                    </label>
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>No intenta retroceder el flujo (unidireccional)</span>
                    </label>
                </div>

                <div class="recv-actions">
                    <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Cancelar</a>
                    @if ($fase_siguiente)
                        <button class="btn btn-primary" type="submit" style="width:auto;min-width:12rem;">Confirmar avance a {{ $fase_siguiente }}</button>
                    @else
                        <a class="btn btn-primary" style="width:auto;min-width:12rem;" href="{{ route('mockups.entrega') }}">Ir a entrega</a>
                    @endif
                </div>
            </form>

            <aside class="recv-side">
                <section class="panel">
                    <div class="panel-head"><h2>Detalle</h2></div>
                    <ul class="why-list">
                        <li><strong>{{ $caja['id'] }}</strong> — {{ $caja['servicio'] }}</li>
                        <li><strong>Responsable actual:</strong> {{ $caja['operadora'] }}</li>
                        <li><strong>Última hora:</strong> {{ $caja['hora'] }}</li>
                    </ul>
                </section>
                <section class="panel">
                    <div class="panel-head"><h2>Regla del mockup</h2></div>
                    <p class="recv-help">Igual que en la entrevista: el material no vuelve atrás en el área limpia. Solo se avanza de etapa en etapa. Si el control de esterilización falla, en el sistema real se bloquearía el avance (aquí solo se muestra la idea).</p>
                    <div class="panel-note">Mockup: confirmar vuelve al flujo de cajas.</div>
                </section>
            </aside>
        </div>
    </main>
</div>
@endsection
