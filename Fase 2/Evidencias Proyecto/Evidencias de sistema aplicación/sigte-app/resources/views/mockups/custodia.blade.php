@extends('layouts.app')

@section('title', 'SIGTE — Custodia / auditoría')

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
                <p class="eyebrow">Gestión · trazabilidad de responsabilidad</p>
                <h1>Custodia / auditoría</h1>
                <p class="main-sub">Dos lecturas: la <strong>cadena de custodia</strong> de cada caja (quién entregó / recibió) y la <strong>bitácora</strong> de quién cambió qué en el sistema.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'administradora') }}">Volver al resumen</a>
                <button class="btn btn-dark" type="button">Exportar bitácora</button>
            </div>
        </div>

        @if ($falla)
            <div class="ops-empty">
                <strong>No se pudo consultar la auditoría</strong>
                <p>El registro no está disponible en este momento.</p>
                <p><a href="{{ route('mockups.custodia') }}">Intentar de nuevo</a></p>
            </div>
        @else
            <div class="query-toolbar panel">
                <label class="field" style="margin:0; flex:1;">
                    <span class="icon" aria-hidden="true">⌕</span>
                    <input id="aud-buscar" type="search" placeholder="Buscar caja, usuario o acción…" aria-label="Buscar caja, usuario o acción">
                </label>
                <label class="field field-select" style="margin:0; min-width:11rem;">
                    <select id="aud-cuando" aria-label="Período">
                        <option value="hoy" selected>Hoy</option>
                        <option value="semana">Últimos 7 días</option>
                        <option value="mes">Este mes</option>
                    </select>
                </label>
                <label class="field field-select" style="margin:0; min-width:11rem;">
                    <select id="aud-vista" aria-label="Qué ver">
                        <option value="todo" selected>Todo</option>
                        <option value="custodia">Solo custodia</option>
                        <option value="auditoria">Solo auditoría</option>
                        <option value="alertas">Solo alertas</option>
                    </select>
                </label>
            </div>

            <div class="grid-2" id="aud-paneles">
                <section class="panel" id="aud-cadenas">
                    <div class="panel-head">
                        <h2>Cadenas de custodia</h2>
                        <span class="badge">Por caja</span>
                    </div>
                    <div id="aud-cadenas-vacio" class="ops-empty" hidden>
                        <strong>Sin resultados</strong>
                        <p>Ninguna caja coincide con esa búsqueda.</p>
                    </div>
                    <div id="aud-cadenas-lista">
                        @foreach ($cadenas as $cadena)
                            <article class="custody-card {{ $cadena['alerta'] ? 'is-alert' : '' }}" data-alerta="{{ $cadena['alerta'] ? '1' : '0' }}" data-cuando="{{ $cadena['cuando'] }}" data-texto="{{ mb_strtolower($cadena['caja'].' '.$cadena['servicio'].' '.collect($cadena['eventos'])->pluck('de')->implode(' ').' '.collect($cadena['eventos'])->pluck('nota')->implode(' ')) }}">
                                <header class="custody-head">
                                    <div>
                                        <strong>{{ $cadena['caja'] }}</strong>
                                        <span>{{ $cadena['servicio'] }}</span>
                                    </div>
                                    @if ($cadena['alerta'])
                                        <span class="pill pill-danger">{{ $cadena['estado'] }}</span>
                                    @else
                                        <span class="pill pill-ok">{{ $cadena['estado'] }}</span>
                                    @endif
                                </header>
                                <ol class="custody-timeline">
                                    @foreach ($cadena['eventos'] as $ev)
                                        <li class="{{ $ev['tipo'] === 'Alerta' ? 'is-alert' : '' }}">
                                            <div class="ct-time">{{ $ev['hora'] }}</div>
                                            <div class="ct-body">
                                                <strong>{{ $ev['tipo'] }}</strong>
                                                <span>{{ $ev['nota'] }}</span>
                                                <small>
                                                    @if ($ev['a'] !== '—')
                                                        {{ $ev['de'] }} → {{ $ev['a'] }}
                                                    @else
                                                        {{ $ev['de'] }}
                                                    @endif
                                                </small>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="panel list" id="aud-bitacora">
                    <div class="panel-head">
                        <h2 id="aud-titulo">Bitácora de auditoría</h2>
                        <span class="badge">Quién · qué · cuándo</span>
                    </div>
                    <div id="aud-vacio" class="ops-empty" hidden>
                        <strong>Sin resultados</strong>
                        <p>Ninguna acción coincide con esa búsqueda o con esos filtros.</p>
                    </div>
                    <div class="table-wrap" id="aud-tabla">
                        <table>
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Actor</th>
                                    <th>Acción</th>
                                    <th>Objeto</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody id="aud-cuerpo"></tbody>
                        </table>
                    </div>
                </section>
            </div>
        @endif
    </main>
</div>
@unless ($falla)
<script>
(function () {
    var registros = @json($auditoria);
    var buscar = document.getElementById('aud-buscar');
    var cuando = document.getElementById('aud-cuando');
    var vista = document.getElementById('aud-vista');
    var titulo = document.getElementById('aud-titulo');
    var cuerpo = document.getElementById('aud-cuerpo');
    var tabla = document.getElementById('aud-tabla');
    var vacio = document.getElementById('aud-vacio');
    var panelCadenas = document.getElementById('aud-cadenas');
    var listaCadenas = document.getElementById('aud-cadenas-lista');
    var vacioCadenas = document.getElementById('aud-cadenas-vacio');
    var tarjetas = document.querySelectorAll('#aud-cadenas-lista .custody-card');

    function enPeriodo(marca) {
        if (cuando.value === 'hoy') {
            return marca === 'hoy';
        }
        if (cuando.value === 'semana') {
            return marca === 'hoy' || marca === 'ayer';
        }
        return true;
    }

    function pintarCadenas(texto) {
        var modo = vista.value;
        var mostrarPanel = modo === 'todo' || modo === 'custodia' || modo === 'alertas';
        panelCadenas.hidden = !mostrarPanel;
        if (!mostrarPanel) {
            return;
        }
        var visibles = 0;
        tarjetas.forEach(function (tarjeta) {
            var alerta = tarjeta.getAttribute('data-alerta') === '1';
            var sirvePeriodo = enPeriodo(tarjeta.getAttribute('data-cuando'));
            var sirveTexto = !texto || tarjeta.getAttribute('data-texto').indexOf(texto) !== -1;
            var sirveModo = modo !== 'alertas' || alerta;
            var visible = sirvePeriodo && sirveTexto && sirveModo;
            tarjeta.hidden = !visible;
            if (visible) {
                visibles += 1;
            }
        });
        listaCadenas.hidden = visibles === 0;
        vacioCadenas.hidden = visibles !== 0;
    }

    function pintar() {
        var texto = buscar.value.trim().toLocaleLowerCase('es');
        var modo = vista.value;
        pintarCadenas(texto);

        var mostrarBitacora = modo === 'todo' || modo === 'auditoria';
        document.getElementById('aud-bitacora').hidden = !mostrarBitacora;
        if (!mostrarBitacora) {
            return;
        }

        var elegidos = registros.filter(function (registro) {
            if (!enPeriodo(registro.cuando)) {
                return false;
            }
            if (!texto) {
                return true;
            }
            var bolsa = (registro.actor + ' ' + registro.accion + ' ' + registro.objeto + ' ' + registro.detalle).toLocaleLowerCase('es');
            return bolsa.indexOf(texto) !== -1;
        });

        var actores = elegidos.map(function (registro) { return registro.actor; }).filter(function (actor, indice, lista) {
            return lista.indexOf(actor) === indice;
        });
        titulo.textContent = texto && actores.length === 1
            ? 'Historial de ' + actores[0]
            : 'Bitácora de auditoría';

        if (elegidos.length === 0) {
            tabla.hidden = true;
            vacio.hidden = false;
            return;
        }

        vacio.hidden = true;
        tabla.hidden = false;
        cuerpo.innerHTML = elegidos.map(function (registro) {
            return '<tr><td><strong>' + registro.hora + '</strong><small>' + registro.fecha + '</small></td><td><strong>' + registro.actor + '</strong></td><td>' + registro.accion + '</td><td>' + registro.objeto + '</td><td class="muted-cell">' + registro.detalle + '</td></tr>';
        }).join('');
    }

    buscar.addEventListener('input', pintar);
    cuando.addEventListener('change', pintar);
    vista.addEventListener('change', pintar);
    pintar();
})();
</script>
@endunless
@endsection
