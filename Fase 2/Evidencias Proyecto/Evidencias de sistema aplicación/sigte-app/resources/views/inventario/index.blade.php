@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/inventario.css') }}?v={{ @filemtime(public_path('css/inventario.css')) }}">
<link rel="stylesheet" href="{{ asset('css/inventario/tarjeta-prioridad.css') }}?v={{ @filemtime(public_path('css/inventario/tarjeta-prioridad.css')) }}">
<link rel="stylesheet" href="{{ asset('css/inventario/registro-movimiento.css') }}?v={{ @filemtime(public_path('css/inventario/registro-movimiento.css')) }}">
<link rel="stylesheet" href="{{ asset('css/inventario/configurar-minimo.css') }}?v={{ @filemtime(public_path('css/inventario/configurar-minimo.css')) }}">
<link rel="stylesheet" href="{{ asset('css/inventario/modal-reposicion.css') }}?v={{ @filemtime(public_path('css/inventario/modal-reposicion.css')) }}">
@endpush

@push('scripts')
<script src="{{ asset('js/inventario.js') }}?v={{ @filemtime(public_path('js/inventario.js')) }}"></script>

@if ($sala === 'todos')
<script src="{{ asset('js/inventario/tarjeta-prioridad.js') }}?v={{ @filemtime(public_path('js/inventario/tarjeta-prioridad.js')) }}"></script>
@endif

<script src="{{ asset('js/inventario/modal-utils.js') }}?v={{ @filemtime(public_path('js/inventario/modal-utils.js')) }}"></script>
<script src="{{ asset('js/inventario/modal-reposicion.js') }}?v={{ @filemtime(public_path('js/inventario/modal-reposicion.js')) }}" defer></script>
<script src="{{ asset('js/inventario/registro-movimiento.js') }}?v={{ @filemtime(public_path('js/inventario/registro-movimiento.js')) }}" defer></script>
<script src="{{ asset('js/inventario/configurar-minimo.js') }}?v={{ @filemtime(public_path('js/inventario/configurar-minimo.js')) }}" defer></script>
@endpush

@section('title', 'SIGTE — Inventario')

@section('content')
@php
$conteoSalas = collect($libros)->keyBy('clave');
$totalElementos = collect($libros)->sum('tipos');
@endphp
<div class="shell shell-ops">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-sigte.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Operación</div>
            </div>
        </div>
        @include('partials.menu-rol')

        @include('partials.sidebar-foot')
    </aside>

    <main class="main inv-vista sala-{{ $sala }}">
        <div class="main-top">
            <div>
                <!-- ROL ADMINISTRADORA -->
                @if ($esAdministradora)
                <h1>Inventario</h1>
                <p class="main-sub">
                    Supervisa el stock disponible, identifica elementos bajo mínimo y revisa el estado de las salas.
                </p>

                <!-- ROL ENFERMERA DE TURNO -->
                @elseif ($esEnfermera)
                <h1>Inventario</h1>
                <p class="main-sub">
                    Supervisa el stock de las salas, identifica elementos bajo mínimo y gestiona las reposiciones durante el turno.
                </p>

                <!-- ROL OPERADOR -->
                @else
                <h1>Inventario</h1>
                <p class="main-sub">
                    Consulta la disponibilidad y ubicación de los elementos para apoyar las tareas diarias de la Central.
                </p>
                @endif

            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.catalogo') }}">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6.5 5.5h11A1.5 1.5 0 0 1 19 7v12.5H5V7A1.5 1.5 0 0 1 6.5 5.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
                        <path d="M9 9.5h6M9 13h6M9 16.5h4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                    </svg>
                    Ver catálogo
                </a>
            </div>
        </div>

        <section class="inv-elige" aria-label="Elegir libro">
            <div class="inv-switch">

                @if (!$esOperador)

                <!-- CAMPANITA DE NOTIFICACIÓN EN LA TARJETA PRINCIPAL -->
                <a class="inv-switch-item sala-todos {{ $sala === 'todos' ? 'is-active' : '' }}"
                    href="{{ route('mockups.inventario', ['sala' => 'todos']) }}">

                    <span class="inv-tarjeta-encabezado">
                        <strong>Los tres</strong>

                        @if (($esAdministradora || $esEnfermera) && count($atencion) > 0)
                        <span class="inv-campana-alertas"
                            aria-label="{{ count($atencion) }} elementos requieren atención"
                            title="{{ count($atencion) }} elementos requieren atención">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M10 21h4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round" />
                            </svg>
                            <span class="inv-campana-contador">{{ count($atencion) }}</span>
                        </span>
                        @endif
                    </span>

                    <em>{{ $totalElementos }} elementos</em>
                    <span>Vista general</span>
                </a>
                @endif

                @foreach ($salas as $key => $label)
                @php $libroNav = $conteoSalas[$key]; @endphp
                <a class="inv-switch-item sala-{{ $key }} {{ $sala === $key ? 'is-active' : '' }}"
                    href="{{ route('mockups.inventario', ['sala' => $key]) }}">
                    <strong>{{ $label }}</strong>
                    @if ($key === 'esteril')
                    <em>{{ $libroNav['tipos'] ?? 0 }} fichas · {{ $libroNav['stock'] ?? 0 }} cajas</em>
                    <span>En almacén</span>
                    @else
                    <em>{{ $libroNav['tipos'] ?? 0 }} {{ ($libroNav['tipos'] ?? 0) === 1 ? 'elemento' : 'elementos' }} · {{ $libroNav['stock'] ?? 0 }} u.</em>
                    @if (!$esOperador)
                    <span class="{{ ($libroNav['bajo'] ?? 0) > 0 ? 'is-alert' : '' }}">
                        {{ ($libroNav['bajo'] ?? 0) > 0 ? ($libroNav['bajo'].' bajo mínimo') : 'Stock en orden' }}
                    </span>
                    @else
                    <span>Consulta de stock</span>
                    @endif
                    @endif
                </a>
                @endforeach
            </div>
        </section>

        @if ($sala === 'todos')
        @if ($esAdministradora || $esEnfermera)
        @include('inventario.partials.tarjeta-prioridad')
        @endif
        @else

        <div class="inv-vistas-contenedor" id="inv-vistas-contenedor">
            <section class="panel list inv-book sala-{{ $sala }}">
                <div class="panel-head">
                    <div>
                        <h2 class="inv-titulo">
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M6.5 5.5h11A1.5 1.5 0 0 1 19 7v12.5H5V7A1.5 1.5 0 0 1 6.5 5.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
                                <path d="M9 9.5h6M9 13h6M9 16.5h4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                            </svg>
                            {{ $sala === 'lavado' ? 'Insumos de lavado' : ($sala === 'armado' ? 'Insumos de armado' : $salas[$sala]) }}
                        </h2>

                        @if ($esEnfermera && $sala === 'lavado')
                        <p class="main-sub">
                            Supervisa el stock de los insumos de lavado y ajusta sus niveles mínimos para asegurar su disponibilidad.
                        </p>
                        @elseif ($esEnfermera && $sala === 'armado')
                        <p class="main-sub">
                            Supervisa el stock de los insumos de armado y ajusta sus niveles mínimos para mantener el abastecimiento de la sala.
                        </p>
                        @elseif ($esEnfermera && $sala === 'esteril')
                        <p class="main-sub">
                            Supervisa las cajas esterilizadas disponibles y verifica las existencias para asegurar su disponibilidad.
                        </p>
                        @elseif ($esAdministradora && in_array($sala, ['lavado', 'armado']))
                        <p class="main-sub">
                            Consulta los insumos disponibles para las tareas de {{ $sala }} y revisa sus niveles de stock y disponibilidad.
                        </p>
                        @elseif ($esOperador && $sala === 'lavado')
                        <p class="main-sub">
                            Consulta los insumos disponibles y registra sus movimientos.
                        </p>
                        @elseif ($esOperador && $sala === 'armado')
                        <p class="main-sub">
                            Consulta las existencias y registra los movimientos de los insumos.
                        </p>
                        @elseif ($esOperador && $sala === 'esteril')
                        <p class="main-sub">
                            Consulta las cajas esterilizadas disponibles y sus ubicaciones.
                        </p>
                        @endif
                    </div>
                    <ul class="inv-resumen">
                        @if ($sala === 'esteril')
                        <li>
                            <strong id="inv-total-stock">{{ $resumen['en_almacen'] }}</strong>
                            cajas disponibles
                        </li>
                        <li>
                            <strong>{{ $resumen['tipos'] }}</strong>
                            fichas
                        </li>
                        @else
                        <li>
                            <strong id="inv-total-stock">{{ $resumen['en_almacen'] }}</strong>
                            unidades
                        </li>
                        <li>
                            <strong id="inv-total-proceso">{{ $resumen['en_proceso'] }}</strong>
                            en proceso
                        </li>
                        <li id="inv-alerta-resumen" class="{{ $resumen['bajo_minimo'] > 0 ? 'is-alert' : '' }}">
                            <strong id="inv-total-bajo">{{ $resumen['bajo_minimo'] }}</strong>
                            bajo mínimo
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- BUSCADOR DEL INVENTARIO -->
                <div class="inv-barra-herramientas">

                    <form method="GET"
                        action="{{ route('mockups.inventario') }}"
                        class="inv-busqueda"
                        id="form-busqueda-inventario"
                        role="search">

                        <input type="hidden" name="sala" value="{{ $sala }}">

                        <!-- ICONO DE LUPA -->
                        <label class="field inv-campo-busqueda" for="busqueda-inventario">

                            <svg class="inv-icono-busqueda" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="11" cy="11" r="6.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8" />
                                <path d="M16 16l4.5 4.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round" />
                            </svg>

                            <span class="sr-only">Buscar por código, nombre o ubicación</span>

                            <input
                                type="search"
                                id="busqueda-inventario"
                                name="q"
                                value="{{ $busqueda ?? '' }}"
                                placeholder="Buscar por código, nombre o ubicación"
                                autocomplete="off">

                            <!-- X PARA LIMPIAR -->
                            <button
                                type="button"
                                class="inv-limpiar-busqueda"
                                id="inv-limpiar-busqueda"
                                aria-label="Limpiar búsqueda"
                                title="Limpiar búsqueda"
                                hidden>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M7 7l10 10M17 7L7 17"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round" />
                                </svg>
                            </button>

                        </label>

                    </form>

                </div>

                <div class="inv-insumos-grid" id="cuerpo-inventario">

                    @if ($sala === 'esteril')

                    <!-- LISTADO DE EXISTENCIAS -->
                    <!-- Encabezados de las columnas -->
                    <div class="inv-esteril-encabezado" aria-hidden="true">
                        <span>Material / Set</span>
                        <span>Stock disponible</span>
                        <span>Ubicación</span>
                    </div>

                    @forelse ($cajasEsteril as $ficha)

                    <!-- Fila correspondiente a una ficha -->
                    <article
                        class="inv-insumo-card inv-esteril-fila"
                        data-stock="{{ $ficha['stock'] }}">

                        <!-- Imagen e identificación del material -->
                        <div class="inv-esteril-material">
                            <div class="inv-esteril-imagen">
                                <img
                                    src="{{ route('fichas.imagen', $ficha['ficha_id']) }}"
                                    alt=""
                                    loading="lazy"
                                    onload="this.hidden=false; this.nextElementSibling.hidden=true"
                                    onerror="this.hidden=true; this.nextElementSibling.hidden=false">

                                <!-- Imagen alternativa cuando no existe fotografía -->
                                <div class="inv-esteril-sin-imagen">
                                    <svg viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M8 16 24 7l16 9v19l-16 9-16-9V16Z" />
                                        <path d="m8 16 16 9 16-9M24 25v19" />
                                        <path d="M19 13h10M24 10v6" />
                                    </svg>
                                    <span>Sin imagen</span>
                                </div>
                            </div>

                            <div class="inv-esteril-identificacion">
                                <span class="inv-insumo-codigo">
                                    {{ $ficha['codigo'] }}
                                </span>

                                <h3 class="inv-insumo-nombre">
                                    {{ $ficha['nombre'] }}
                                </h3>
                            </div>
                        </div>

                        <!-- Cantidad de cajas físicas disponibles -->
                        <div class="inv-esteril-stock">
                            <span class="inv-esteril-etiqueta">Cajas disponibles</span>
                            <strong class="inv-insumo-stock">
                                {{ $ficha['stock'] }}
                            </strong>
                        </div>

                        <!-- Ubicación de las cajas en bodega -->
                        <div class="inv-esteril-ubicacion">
                            <span class="inv-esteril-etiqueta">Ubicación</span>
                            <span>
                                {{ $ficha['ubicaciones']->isNotEmpty()
                        ? $ficha['ubicaciones']->join(', ')
                        : 'Sin ubicación' }}
                            </span>
                        </div>

                    </article>

                    @empty

                    <p class="inv-sin-resultados">
                        No hay fichas registradas en Material estéril.
                    </p>

                    @endforelse

                    @else

                    @if (in_array($sala, ['lavado', 'armado']))
                    <div class="inv-insumo-lista-encabezado">
                        <span aria-hidden="true"></span>
                        <span>Insumo</span>
                        <span>Stock</span>
                        <span>Mínimo</span>
                        <span>En proceso</span>
                        <span>Estado</span>
                        <span>Acciones</span>
                    </div>
                    @endif

                    @forelse ($items as $item)

                    <article
                        class="inv-insumo-card stock-{{ $item['estado'] }}"
                        data-stock="{{ $item['stock'] }}"
                        data-en-proceso="{{ $item['en_proceso'] }}"
                        data-estado="{{ $item['estado'] }}">

                        <!-- ESPACIO RESERVADO PARA LA IMAGEN DEL INSUMO -->
                        <div class="inv-insumo-imagen">
                            @if (!empty($item['imagen']))
                            <img
                                src="{{ asset($item['imagen']) }}"
                                alt="{{ $item['nombre'] }}"
                                loading="lazy"
                                onerror="this.style.display='none'; this.nextElementSibling.hidden = false;">
                            @endif

                            <div class="inv-insumo-imagen-placeholder" @if (!empty($item['imagen'])) hidden @endif aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5z"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linejoin="round" />
                                    <path d="m4.5 7.8 7.5 4.3 7.5-4.3M12 12.1V21"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>

                        <!-- IDENTIFICACIÓN DEL INSUMO -->
                        <div class="inv-insumo-contenido">

                            <div class="inv-insumo-identificacion">
                                <span class="inv-insumo-codigo">
                                    {{ $item['codigo'] }}
                                </span>

                                <h3 class="inv-insumo-nombre">
                                    {{ $item['nombre'] }}
                                </h3>

                                <p class="inv-insumo-ubicacion">
                                    <strong>Ubicación:</strong>
                                    {{ $item['ubicacion'] }}
                                </p>
                            </div>

                            <!-- INDICADORES DE STOCK -->
                            <div class="inv-insumo-indicadores">

                                <div class="inv-insumo-indicador">
                                    <strong class="inv-insumo-stock">
                                        {{ $item['stock'] }}
                                    </strong>
                                </div>

                                <div class="inv-insumo-indicador">
                                    <strong class="inv-insumo-minimo" data-minimo-celda>{{ $item['minimo'] }}</strong>
                                </div>

                                <div class="inv-insumo-indicador">
                                    <strong class="inv-insumo-proceso">{{ $item['en_proceso'] }}</strong>
                                </div>

                            </div>

                            <!-- ESTADO DEL INSUMO -->
                            <div class="inv-insumo-pie">

                                <div class="inv-insumo-estado">
                                    @if ($item['estado'] === 'ok')
                                    <span class="pill pill-ok">
                                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M5.5 12.5 10 17l8.5-9"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.75"
                                                stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        OK
                                    </span>
                                    @elseif ($item['estado'] === 'bajo')
                                    <span class="pill pill-warn">Bajo mínimo</span>
                                    @else
                                    <span class="pill pill-danger">Crítico</span>
                                    @endif
                                </div>

                                <!-- ACCIONES SEGÚN EL ROL -->
                                @if (($puedeReponer && in_array($sala, ['lavado', 'armado'])) || ($esOperador && in_array($sala, ['lavado', 'armado'])))

                                <div class="inv-insumo-acciones">

                                    <!-- ADMIN Y ENFERMERA: REPOSICIÓN -->
                                    @if ($puedeReponer && in_array($sala, ['lavado', 'armado']))
                                    <button
                                        type="button"
                                        class="inv-editar-stock"
                                        data-codigo="{{ $item['codigo'] }}"
                                        data-sala="{{ $item['sala'] }}"
                                        data-nombre="{{ $item['nombre'] }}"
                                        data-stock="{{ $item['stock'] }}"
                                        data-minimo="{{ $item['minimo'] }}"
                                        aria-label="Registrar reposición de {{ $item['nombre'] }}"
                                        title="Registrar reposición">
                                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M4.5 19.5h4L19 9a2.1 2.1 0 0 0-3-3L5.5 15.5v4z"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.75"
                                                stroke-linejoin="round" />
                                            <path d="m14.5 7.5 2 2"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.75"
                                                stroke-linecap="round" />
                                        </svg>
                                    </button>

                                    <!-- ENFERMERA: CONFIGURAR MÍNIMO -->
                                    @if ($esEnfermera && in_array($sala, ['lavado', 'armado', 'esteril']))
                                    <button
                                        type="button"
                                        class="inv-configurar-minimo"
                                        data-codigo="{{ $item['codigo'] }}"
                                        data-sala="{{ $item['sala'] }}"
                                        data-nombre="{{ $item['nombre'] }}"
                                        data-stock="{{ $item['stock'] }}"
                                        data-minimo="{{ $item['minimo'] }}"
                                        aria-label="Configurar mínimo de {{ $item['nombre'] }}"
                                        title="Configurar mínimo">
                                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M4 7h16M4 17h16M9 4v6m6 4v6"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.75"
                                                stroke-linecap="round" />
                                        </svg>
                                    </button>
                                    @endif

                                    <!-- OPERADOR: REGISTRAR MOVIMIENTO -->
                                    @elseif ($esOperador && in_array($sala, ['lavado', 'armado']))
                                    <button
                                        type="button"
                                        class="inv-abrir-movimiento"
                                        data-codigo="{{ $item['codigo'] }}"
                                        data-sala="{{ $item['sala'] }}"
                                        data-nombre="{{ $item['nombre'] }}"
                                        data-stock="{{ $item['stock'] }}"
                                        data-en-proceso="{{ $item['en_proceso'] }}"
                                        data-minimo="{{ $item['minimo'] }}"
                                        aria-label="Registrar movimiento de {{ $item['nombre'] }}"
                                        title="Registrar movimiento">
                                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M4.5 19.5h4L19 9a2.1 2.1 0 0 0-3-3L5.5 15.5v4z"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.75"
                                                stroke-linejoin="round" />
                                            <path d="m14.5 7.5 2 2"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.75"
                                                stroke-linecap="round" />
                                        </svg>
                                    </button>
                                    @endif

                                </div>
                                @endif

                            </div>
                        </div>
                    </article>

                    @empty
                    @endforelse

                    @endif
                </div>

                <!-- MENSAJE CUANDO NO HAY RESULTADOS -->
                <div id="inv-sin-resultados" class="inv-sin-resultados" hidden>
                    No se encontraron elementos que coincidan con tu búsqueda.
                </div>

                <!-- PAGINACIÓN -->
                <div class="inv-paginacion" id="inv-paginacion">

                    <div class="inv-paginacion-info">
                        <label for="inv-items-pagina">Elementos por página</label>

                        <select id="inv-items-pagina" data-sigte-lista="1">
                            <option value="5" selected>5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </select>
                    </div>

                    <div class="inv-paginacion-controles">

                        <span class="inv-rango" id="inv-rango">
                            0–0 de 0
                        </span>

                        <button
                            type="button"
                            class="inv-pagina-btn"
                            id="inv-pagina-anterior"
                            aria-label="Página anterior">
                            ‹
                        </button>

                        <div class="inv-paginas" id="inv-paginas"></div>

                        <button
                            type="button"
                            class="inv-pagina-btn"
                            id="inv-pagina-siguiente"
                            aria-label="Página siguiente">
                            ›
                        </button>

                    </div>

                </div>

            </section>

            @include('inventario.partials.configurar-minimo')
            @include('inventario.partials.registro-movimiento')

        </div>
        @endif

    </main>
</div>

@include('inventario.partials.modal-reposicion')

@endsection