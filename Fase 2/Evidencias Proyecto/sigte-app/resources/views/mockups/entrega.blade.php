@extends('layouts.app')

@section('title', 'SIGTE — Registrar entrega')

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
            <a href="{{ route('mockups.avanzar') }}">Avanzar etapa</a>
            <a class="active" href="{{ route('mockups.entrega') }}">Registrar entrega</a>
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
                <p class="eyebrow">Almacén estéril · cierre del ciclo</p>
                <h1>Registrar entrega</h1>
                <p class="main-sub">Entrega el material listo al servicio con custodia: quién entrega (Central) y quién retira. Cierra el ciclo en etapa <strong>Entrega</strong>.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Volver al flujo</a>
            </div>
        </div>

        <div class="recv-layout">
            <form class="recv-form panel" action="{{ route('mockups.panel', 'operador') }}" method="get">
                <div class="panel-head">
                    <h2>Custodia de salida</h2>
                    <span class="badge">Paso 6 de 6</span>
                </div>

                <label class="field-label" for="caja">Caja / set lista en almacén</label>
                <label class="field field-select">
                    <select id="caja" name="caja" onchange="window.location='{{ url('/operador/entrega') }}?caja='+this.value">
                        @forelse ($listas as $c)
                            <option value="{{ $c['id'] }}" {{ ($caja && $c['id'] === $caja['id']) ? 'selected' : '' }}>
                                {{ $c['id'] }} · {{ $c['servicio'] }}{{ $c['urgente'] ? ' · urgente' : '' }}
                            </option>
                        @empty
                            <option value="">No hay cajas listas</option>
                        @endforelse
                    </select>
                </label>

                @if ($caja)
                <div class="adv-summary">
                    <div>
                        <span class="adv-k">Desde</span>
                        <strong>Almacén</strong>
                        <small>{{ $caja['estado'] }}</small>
                    </div>
                    <div class="adv-arrow" aria-hidden="true">→</div>
                    <div>
                        <span class="adv-k">Hacia</span>
                        <strong>Entrega</strong>
                        <small>{{ $caja['servicio'] }} · {{ $caja['id'] }}</small>
                    </div>
                </div>
                @endif

                <label class="field-label" for="servicio">Servicio destino</label>
                <label class="field field-select">
                    <select id="servicio" name="servicio">
                        @foreach ($servicios as $s)
                            <option {{ ($caja && $s === $caja['servicio']) ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field-label" for="entrega_central">Quién entrega (Central)</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 19c1.5-3 4-4.5 7-4.5S17.5 16 19 19"/></svg>
                    </span>
                    <input id="entrega_central" name="entrega_central" type="text" value="{{ $usuario['nombre'] }}" readonly>
                </label>

                <label class="field-label" for="retira">Quién retira (servicio)</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 19c1.5-3 4-4.5 7-4.5S17.5 16 19 19"/></svg>
                    </span>
                    <input id="retira" name="retira" type="text" placeholder="Nombre de quien retira el material" value="Enf. Daniela Soto">
                </label>

                <label class="field-label" for="hora">Hora de entrega</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">⏱</span>
                    <input id="hora" name="hora" type="text" value="14:35">
                </label>

                <label class="field-label" for="obs">Observación (opcional)</label>
                <textarea id="obs" class="recv-textarea" name="obs" rows="2" placeholder="Ej: set completo, empaque íntegro, indicador OK…"></textarea>

                <div class="recv-checks">
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Empaque íntegro / indicador de esterilidad OK</span>
                    </label>
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Custodia firmada (quien entrega y quien retira)</span>
                    </label>
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Material sale de almacén estéril</span>
                    </label>
                </div>

                <div class="recv-actions">
                    <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Cancelar</a>
                    <button class="btn btn-primary" type="submit" style="width:auto; min-width:12rem;">Confirmar entrega</button>
                </div>
            </form>

            <aside class="recv-side">
                <section class="panel">
                    <div class="panel-head"><h2>Así queda en el flujo</h2></div>
                    <div class="mini-track">
                        <div class="mini-step"><span>1</span>Recepción</div>
                        <div class="mini-step"><span>2</span>Lavado</div>
                        <div class="mini-step"><span>3</span>Preparación</div>
                        <div class="mini-step"><span>4</span>Esterilización</div>
                        <div class="mini-step"><span>5</span>Almacén</div>
                        <div class="mini-step current"><span>6</span>Entrega</div>
                    </div>
                    <p class="recv-help">Solo aparecen cajas en <strong>Almacén</strong>. Al confirmar, salen del tablero activo y quedan como entregadas con registro de custodia.</p>
                </section>

                <section class="panel">
                    <div class="panel-head"><h2>Por qué estos campos</h2></div>
                    <ul class="why-list">
                        <li><strong>Quién entrega / retira</strong> — cierra la cadena de custodia (igual que en recepción, pero a la inversa).</li>
                        <li><strong>Servicio destino</strong> — a dónde va el material listo.</li>
                        <li><strong>Indicador / empaque</strong> — no se entrega si el control visual falla (en el sistema real se bloquearía).</li>
                        <li><strong>Hora</strong> — trazabilidad de salida del almacén estéril.</li>
                    </ul>
                    <div class="panel-note">Mockup: confirmar vuelve al flujo de cajas (sin guardar en base de datos).</div>
                </section>
            </aside>
        </div>
    </main>
</div>
@endsection
