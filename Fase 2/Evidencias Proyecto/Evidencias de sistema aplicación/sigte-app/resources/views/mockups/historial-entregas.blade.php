@extends('layouts.app')

@section('title', 'SIGTE — Historial de entregas')

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
                <p class="eyebrow">Gestión · solo administradora</p>
                <h1>Historial de entregas</h1>
                <p class="main-sub">Cada salida queda con las cajas, el servicio y las dos personas: quien entrega y quien recibe.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'administradora') }}">Volver al resumen</a>
            </div>
        </div>

        @if ($falla)
            <div class="ops-empty">
                <strong>No se pudo cargar el historial</strong>
                <p>El listado no está disponible en este momento.</p>
                <p><a href="{{ route('mockups.historial_entregas') }}">Intentar de nuevo</a></p>
            </div>
        @else
            @php
                $ordenSectores = ['Pabellón', 'Dental', 'UCI', 'Urgencia', 'Maternidad', 'Curaciones'];
                $slugSector = [
                    'Pabellón' => 'pabellon',
                    'Dental' => 'dental',
                    'UCI' => 'uci',
                    'Urgencia' => 'urgencia',
                    'Maternidad' => 'maternidad',
                    'Curaciones' => 'curaciones',
                ];
                $grupos = collect($entregas)->groupBy('servicio')->sortBy(function ($grupo, $servicio) use ($ordenSectores) {
                    $indice = array_search($servicio, $ordenSectores, true);

                    return $indice === false ? 99 : $indice;
                });
            @endphp
            <div class="hist-sectores" id="sectores">
                @foreach ($grupos as $servicio => $grupo)
                    @php $ultima = $grupo->first(); @endphp
                    <button class="hist-sector sec-{{ $slugSector[$servicio] ?? 'otro' }}" type="button" data-servicio="{{ $servicio }}">
                        <strong>{{ $servicio }}</strong>
                        <span class="n">{{ $grupo->count() }}</span>
                        <small>{{ $grupo->count() === 1 ? 'entrega' : 'entregas' }}</small>
                        <small>Última {{ $ultima['hora'] }} · {{ $ultima['fecha'] }}</small>
                    </button>
                @endforeach
            </div>

            <form class="query-toolbar panel" id="filtro-entregas" action="{{ route('mockups.historial_entregas') }}" method="get" onsubmit="return false">
                <label class="field" style="margin:0; flex:1;">
                    <span class="icon" aria-hidden="true">⌕</span>
                    <input id="q" type="search" placeholder="Código, persona o número de entrega" autocomplete="off">
                </label>
                <label class="field field-select" style="margin:0; min-width:11rem;">
                    <select id="cuando">
                        <option value="todas">Todas</option>
                        <option value="hoy">Hoy</option>
                        <option value="ayer">Ayer</option>
                        <option value="semana">Esta semana</option>
                    </select>
                </label>
            </form>

            <div class="mant-head">
                <div>
                    <h2 id="lista-titulo">Últimas entregas</h2>
                    <p id="entregas-meta">Las {{ min(3, count($entregas)) }} más recientes</p>
                </div>
                <button class="btn btn-ghost" id="ver-ultimas" type="button" hidden>Ver últimas</button>
            </div>

            <div class="ops-empty" id="entregas-vacio" hidden>
                <strong>No hay entregas con ese criterio</strong>
                <p>Probá con otro código, servicio o persona.</p>
            </div>

            <div class="hist-lista" id="entregas-lista">
                @foreach ($entregas as $entrega)
                    @php
                        $texto = mb_strtolower(implode(' ', [
                            $entrega['id'],
                            $entrega['servicio'],
                            $entrega['entrega'],
                            $entrega['recibe'],
                            collect($entrega['cajas'])->pluck('codigo')->implode(' '),
                            collect($entrega['cajas'])->pluck('nombre')->implode(' '),
                            implode(' ', $entrega['materiales']),
                        ]));
                    @endphp
                    <article class="hist-card sec-{{ $slugSector[$entrega['servicio']] ?? 'otro' }}" data-servicio="{{ $entrega['servicio'] }}" data-cuando="{{ $entrega['cuando'] }}" data-texto="{{ $texto }}">
                        <div class="hist-cuando">
                            <strong>{{ $entrega['hora'] }}</strong>
                            <span>{{ $entrega['fecha'] }}</span>
                            <small>{{ $entrega['id'] }}</small>
                        </div>
                        <div class="hist-cuerpo">
                            <div class="hist-top">
                                <h2>{{ $entrega['servicio'] }}</h2>
                                <span class="badge">{{ count($entrega['cajas']) }} {{ count($entrega['cajas']) === 1 ? 'caja' : 'cajas' }}</span>
                            </div>
                            <div class="hist-cajas">
                                @foreach ($entrega['cajas'] as $caja)
                                    <span class="hist-chip">{{ $caja['codigo'] }} <small>{{ $caja['nombre'] }}</small></span>
                                @endforeach
                                @forelse ($entrega['materiales'] as $material)
                                    <span class="hist-chip is-material">{{ $material }}</span>
                                @empty
                                    <span class="hist-chip is-material">Sin material extra</span>
                                @endforelse
                            </div>
                            <div class="entrega-datos">
                                <div class="entrega-dato">
                                    <span>Quién entrega</span>
                                    <strong>{{ $entrega['entrega'] }}</strong>
                                    <small>{{ $entrega['entrega_rol'] }}</small>
                                </div>
                                <div class="entrega-dato">
                                    <span>Quién recibe</span>
                                    <strong>{{ $entrega['recibe'] }}</strong>
                                    <small>{{ $entrega['recibe_rol'] }}</small>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </main>
</div>
@unless ($falla)
<script>
    var q = document.getElementById('q');
    var cuando = document.getElementById('cuando');
    var meta = document.getElementById('entregas-meta');
    var titulo = document.getElementById('lista-titulo');
    var vacio = document.getElementById('entregas-vacio');
    var verUltimas = document.getElementById('ver-ultimas');
    var cards = document.querySelectorAll('#entregas-lista .hist-card');
    var sectorActivo = '';

    function marcarSector() {
        document.querySelectorAll('.hist-sector').forEach(function (boton) {
            boton.classList.toggle('is-on', boton.dataset.servicio === sectorActivo);
        });
        verUltimas.hidden = sectorActivo === '';
        titulo.textContent = sectorActivo === '' ? 'Últimas entregas' : sectorActivo;
    }

    function aplicar() {
        var texto = q.value.trim().toLowerCase();
        var coinciden = [];
        cards.forEach(function (card) {
            var coincideTexto = texto === '' || card.dataset.texto.indexOf(texto) !== -1;
            var coincideSector = sectorActivo === '' || card.dataset.servicio === sectorActivo;
            var coincideCuando = cuando.value === 'todas'
                || (cuando.value === 'semana' && card.dataset.cuando !== 'anterior')
                || card.dataset.cuando === cuando.value;
            card.hidden = true;
            if (coincideTexto && coincideSector && coincideCuando) {
                coinciden.push(card);
            }
        });
        var mostrar = sectorActivo === '' ? coinciden.slice(0, 3) : coinciden;
        mostrar.forEach(function (card) {
            card.hidden = false;
        });
        vacio.hidden = mostrar.length !== 0;
        meta.hidden = mostrar.length === 0;
        if (sectorActivo === '') {
            meta.textContent = mostrar.length === 1 ? 'La más reciente' : 'Las ' + mostrar.length + ' más recientes';
        } else {
            meta.textContent = mostrar.length === 1 ? '1 entrega' : mostrar.length + ' entregas';
        }
    }

    document.getElementById('sectores').addEventListener('click', function (evento) {
        var boton = evento.target.closest('.hist-sector');
        if (!boton) {
            return;
        }
        sectorActivo = sectorActivo === boton.dataset.servicio ? '' : boton.dataset.servicio;
        marcarSector();
        aplicar();
    });

    verUltimas.addEventListener('click', function () {
        sectorActivo = '';
        marcarSector();
        aplicar();
    });

    q.addEventListener('input', aplicar);
    cuando.addEventListener('change', aplicar);
    aplicar();
</script>
@endunless
@endsection
