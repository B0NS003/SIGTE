@extends('layouts.app')

@section('title', 'SIGTE — Anotar la actividad')

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
                <p class="eyebrow">Trazabilidad · dentro de la etapa</p>
                <h1>Anotar la actividad</h1>
                <p class="main-sub">La caja sigue en su etapa. Se ve cuánto lleva el ciclo y cada nota se suma en esa caja.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Volver al flujo</a>
            </div>
        </div>

        @if ($codigo_no_encontrado)
            <p class="auth-error" role="alert">Esa caja no está en el flujo. Elige una de la lista.</p>
        @endif

        <form class="recv-form panel entrega-panel" action="{{ route('mockups.actividad') }}" method="get" onsubmit="return false">
            @if ($cajas === [])
                <p class="recv-help">No hay cajas en el flujo. Cuando una caja entre a recepción, se puede anotar aquí.</p>
            @elseif ($caja)
                <div class="entrega-cuerpo">
                    <section>
                        <h2>{{ $caja['servicio'] }} · {{ $caja['id'] }}</h2>
                        <p class="adv-movimiento">Está en <strong>{{ $etapa_actual }}</strong>. {{ $caja['ubicacion'] }}.</p>

                        @if ($ciclo)
                            <div class="act-ciclo">
                                <div class="entrega-datos">
                                    <div class="entrega-dato">
                                        <span>Lleva</span>
                                        <strong>{{ $caja['tiempo'] }}</strong>
                                    </div>
                                    <div class="entrega-dato">
                                        <span>Falta</span>
                                        <strong>{{ $ciclo['falta'] }}</strong>
                                    </div>
                                </div>
                                <div class="act-barra" role="progressbar" aria-valuenow="{{ $ciclo['porcentaje'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Avance del ciclo">
                                    <span style="width: {{ $ciclo['porcentaje'] }}%"></span>
                                </div>
                                <p class="recv-help">{{ $ciclo['ayuda'] }}</p>
                            </div>
                        @endif

                        <div class="track-pipe adv-pipe" aria-label="Etapa actual: {{ $etapa_actual }}">
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

                        <label class="field-label" for="caja">Cambiar caja</label>
                        <label class="field field-select">
                            <select id="caja" onchange="window.location='{{ url('/operador/actividad') }}?caja='+encodeURIComponent(this.value)">
                                @foreach (collect($cajas)->sortBy('servicio') as $c)
                                    <option value="{{ $c['id'] }}" @selected($c['id'] === $caja['id'])>
                                        {{ $c['servicio'] }} · {{ $c['id'] }} · {{ $fases[$c['fase_idx']] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </section>

                    <section>
                        <h2>Qué se está haciendo</h2>
                        <label class="field-label" for="actividad">Actividad de {{ $etapa_actual }}</label>
                        <label class="field field-select">
                            <select id="actividad" name="actividad">
                                <option value="">Elige la actividad</option>
                                @foreach ($actividades as $actividad)
                                    <option value="{{ $actividad }}">{{ $actividad }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="entrega-datos">
                            <div class="entrega-dato entrega-dato-ancho">
                                <span>Quién anota</span>
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

                        <details class="entrega-obs">
                            <summary>Agregar un dato</summary>
                            <label class="field-label" for="complemento">Cuánto falta, equipo u otro antecedente</label>
                            <textarea id="complemento" class="recv-textarea" name="complemento" rows="2" placeholder="Faltan 20 min, autoclave 2…"></textarea>
                        </details>

                        <p class="auth-error act-aviso" id="act-aviso" role="alert" hidden>Elige la actividad de esta etapa.</p>
                        <button class="btn btn-primary" id="anotar" type="button" style="width:auto; min-width:12rem;">Anotar en la caja</button>
                    </section>
                </div>

                <section class="act-suma">
                    <h2>En esta caja</h2>
                    <ol class="act-lista" id="anotaciones">
                        <li>
                            <strong>Entró a {{ $etapa_actual }}</strong>
                            <span>{{ $caja['fecha'] }} · {{ $caja['hora'] }}</span>
                        </li>
                    </ol>
                </section>
            @endif
        </form>
    </main>
</div>
@if ($caja)
<script>
    document.getElementById('anotar').addEventListener('click', function () {
        var actividad = document.getElementById('actividad');
        var aviso = document.getElementById('act-aviso');
        if (!actividad.value) {
            aviso.hidden = false;
            actividad.focus();
            return;
        }
        aviso.hidden = true;
        var extra = document.getElementById('complemento').value.trim();
        var li = document.createElement('li');
        var titulo = document.createElement('strong');
        var meta = document.createElement('span');
        titulo.textContent = extra ? actividad.value + ' · ' + extra : actividad.value;
        meta.textContent = @json($fecha.' · '.$hora.' · '.$usuario['nombre']);
        li.append(titulo, meta);
        document.getElementById('anotaciones').prepend(li);
        actividad.value = '';
        document.getElementById('complemento').value = '';
    });
</script>
@endif
@endsection
