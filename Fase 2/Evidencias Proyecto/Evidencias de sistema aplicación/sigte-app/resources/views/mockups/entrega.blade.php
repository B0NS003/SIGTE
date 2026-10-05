@extends('layouts.app')

@section('title', 'SIGTE — Entrega')

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
                <p class="eyebrow">Almacén estéril · salida</p>
                <h1>Entrega</h1>
                <p class="main-sub">Marca las cajas que salen y anota quién las retira en el servicio. Tú quedas como quien entrega.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Volver al flujo</a>
            </div>
        </div>

        <form class="recv-form panel entrega-panel" action="{{ route('mockups.entrega') }}" method="get" onsubmit="return false">
            <div class="entrega-cuerpo">
                <section>
                    <h2>Qué sale</h2>
                    <p class="field-label">Cajas en almacén</p>
                    @if ($listas === [])
                        <p class="recv-help">No hay cajas en almacén.</p>
                    @else
                        <div class="entrega-opciones">
                            @foreach (collect($listas)->sortBy('servicio') as $c)
                                <label class="check">
                                    <input type="checkbox" name="cajas[]" value="{{ $c['id'] }}" @checked($caja && $c['id'] === $caja['id'])>
                                    <span>{{ $c['servicio'] }} · {{ $c['id'] }}@if ($c['urgente']) · requiere atención @endif</span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <label class="field-label" for="material">Material que no es caja</label>
                    <label class="field field-select">
                        <select id="material" name="material" onchange="document.getElementById('otro-wrap').hidden = this.value !== 'otro'">
                            <option value="">Sin material extra</option>
                            @foreach ($materiales as $material)
                                <option value="{{ $material }}">{{ $material }}</option>
                            @endforeach
                            <option value="otro">Otro</option>
                        </select>
                    </label>
                    <div id="otro-wrap" hidden>
                        <label class="field-label" for="otro">Cuál</label>
                        <label class="field">
                            <input id="otro" name="otro" type="text" placeholder="Nombre del material" value="">
                        </label>
                    </div>
                </section>

                <section>
                    <h2>Quién y cuándo</h2>
                    <div class="entrega-datos">
                        <div class="entrega-dato entrega-dato-ancho">
                            <span>Quién entrega</span>
                            <strong>{{ $usuario['nombre'] }}</strong>
                        </div>
                        <div class="entrega-dato">
                            <span>Fecha</span>
                            <strong>{{ $fecha }}</strong>
                        </div>
                        <div class="entrega-dato">
                            <span>Hora</span>
                            <strong>{{ $hora }}</strong>
                        </div>
                    </div>

                    <label class="field-label" for="servicio">Servicio que lo recibe</label>
                    <label class="field field-select">
                        <select id="servicio" name="servicio">
                            <option value="">Elige el servicio</option>
                            @foreach ($servicios as $s)
                                <option value="{{ $s }}" @selected($caja && $s === $caja['servicio'])>{{ $s }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="field-label" for="retira">Quién retira en el servicio</label>
                    <label class="field">
                        <span class="icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 19c1.5-3 4-4.5 7-4.5S17.5 16 19 19"/></svg>
                        </span>
                        <input id="retira" name="retira" type="text" placeholder="Nombre de quien retira" value="">
                    </label>
                </section>
            </div>

            <details class="entrega-obs">
                <summary>Agregar observación</summary>
                <textarea id="obs" class="recv-textarea" name="obs" rows="2" placeholder="Empaque, indicador, faltante…"></textarea>
            </details>

            <div class="recv-actions">
                <button class="btn btn-primary" type="submit" style="width:auto; min-width:12rem;">Registrar entrega</button>
            </div>
        </form>
    </main>
</div>
@endsection
