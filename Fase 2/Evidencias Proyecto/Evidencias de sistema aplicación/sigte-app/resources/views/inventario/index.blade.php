@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/inventario.css') }}?v={{ @filemtime(public_path('css/inventario.css')) }}">
<link rel="stylesheet" href="{{ asset('css/inventario/modal-reposicion.css') }}?v={{ filemtime(public_path('css/inventario/modal-reposicion.css')) }}">
<link rel="stylesheet" href="{{ asset('css/inventario/tarjeta-prioridad.css') }}?v={{ @filemtime(public_path('css/inventario/tarjeta-prioridad.css')) }}">
@endpush

@push('scripts')
<script src="{{ asset('js/inventario.js') }}?v={{ @filemtime(public_path('js/inventario.js')) }}"></script>

@if ($sala === 'todos')
<script src="{{ asset('js/inventario/tarjeta-prioridad.js') }}?v={{ @filemtime(public_path('js/inventario/tarjeta-prioridad.js')) }}"></script>
@endif

<script src="{{ asset('js/inventario/modal-reposicion.js') }}?v={{ filemtime(public_path('js/inventario/modal-reposicion.js')) }}" defer></script>
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

    <main class="main inv-vista sala-{{ $sala }}">
        <div class="main-top">
            <div>
                @if ($esAdministradora)
                <h1>Inventario</h1>
                <p class="main-sub">
                    Supervisa el stock disponible, identifica elementos bajo mínimo y revisa el estado de las salas.
                </p>
                @else
                <p class="eyebrow">Consulta · tres libros</p>
                <h1>Inventario</h1>
                <p class="main-sub">
                    Elige un libro, revisa el stock y, si hace falta, registra la reposición debajo.
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

                <!-- CAMPANITA DE NOTIFICACIÓN EN LA TARJETA PRINCIPAL -->
                <a class="inv-switch-item sala-todos {{ $sala === 'todos' ? 'is-active' : '' }}"
                    href="{{ route('mockups.inventario', ['sala' => 'todos']) }}">

                    <span class="inv-tarjeta-encabezado">
                        <strong>Los tres</strong>

                        @if ($esAdministradora && count($atencion) > 0)
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

                @foreach ($salas as $key => $label)
                @php $libroNav = $conteoSalas[$key]; @endphp
                <a class="inv-switch-item sala-{{ $key }} {{ $sala === $key ? 'is-active' : '' }}"
                    href="{{ route('mockups.inventario', ['sala' => $key]) }}">
                    <strong>{{ $label }}</strong>
                    <em>{{ $libroNav['tipos'] ?? 0 }} {{ ($libroNav['tipos'] ?? 0) === 1 ? 'elemento' : 'elementos' }} · {{ $libroNav['stock'] ?? 0 }} u.</em>
                    <span class="{{ ($libroNav['bajo'] ?? 0) > 0 ? 'is-alert' : '' }}">
                        {{ ($libroNav['bajo'] ?? 0) > 0 ? ($libroNav['bajo'].' bajo mínimo') : 'Stock en orden' }}
                    </span>
                </a>
                @endforeach
            </div>
        </section>

        @if ($sala === 'todos')
        @include('inventario.partials.tarjeta-prioridad')
        @else

        <section class="panel list inv-book sala-{{ $sala }}">
            <div class="panel-head">
                <div>
                    <h2 class="inv-titulo">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6.5 5.5h11A1.5 1.5 0 0 1 19 7v12.5H5V7A1.5 1.5 0 0 1 6.5 5.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
                            <path d="M9 9.5h6M9 13h6M9 16.5h4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                        </svg>
                        {{ $salas[$sala] }}
                    </h2>
                </div>
                <ul class="inv-resumen">
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
                </ul>
            </div>

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

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Ubicación</th>
                            <th>Stock</th>
                            <th>Mínimo</th>
                            <th>En proceso</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody id="cuerpo-inventario">
                        @forelse ($items as $item)
                        <tr
                            class="stock-{{ $item['estado'] }}"
                            data-stock="{{ $item['stock'] }}"
                            data-en-proceso="{{ $item['en_proceso'] }}"
                            data-estado="{{ $item['estado'] }}">
                            <td><strong>{{ $item['codigo'] }}</strong></td>
                            <td>{{ $item['nombre'] }}</td>
                            <td>{{ $item['ubicacion'] }}</td>
                            <td>{{ $item['stock'] }}</td>
                            <td>{{ $item['minimo'] }}</td>
                            <td>{{ $item['en_proceso'] }}</td>
                            <td>
                                @if ($item['estado'] === 'ok')
                                <span class="pill pill-ok">
                                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    OK
                                </span>
                                @elseif ($item['estado'] === 'bajo')
                                <span class="pill pill-warn">Bajo mínimo</span>
                                @else
                                <span class="pill pill-danger">Crítico</span>
                                @endif
                            </td>

                            <!-- BOTÓN DE EDITAR ELEMENTOS -->
                            <td class="inv-accion">
                                @if ($puedeReponer)
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
                                @endif
                            </td>
                        </tr>
                        @endforeach

                        <tr id="inv-sin-resultados" hidden>
                            <td colspan="8" class="text-center">
                                No se encontraron elementos que coincidan con tu búsqueda.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div class="inv-paginacion" id="inv-paginacion">

                <div class="inv-paginacion-info">
                    <label for="inv-items-pagina">Elementos por página</label>

                    <select id="inv-items-pagina">
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
        @endif

    </main>
</div>

@include('inventario.partials.modal-reposicion')

@endsection
