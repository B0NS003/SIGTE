@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}?v={{ @filemtime(public_path('css/catalogo.css')) }}">
@endpush

@section('title', 'SIGTE — Catálogo')

@section('content')
@php
    $urlCatalogo = function (?string $servicioFiltro = null) use ($busqueda) {
        $params = [];
        if ($busqueda !== '') {
            $params['q'] = $busqueda;
        }
        if ($servicioFiltro !== null && $servicioFiltro !== '') {
            $params['servicio'] = $servicioFiltro;
        }

        return route('mockups.catalogo', $params);
    };
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

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Consulta · fichas maestras</p>
                <h1>Catálogo</h1>
                <p class="main-sub">Elige una caja para ver qué debe llevar. El stock está en Inventario.</p>
            </div>
        </div>

        <form class="ops-consulta" method="get" action="{{ route('mockups.catalogo') }}" role="search">
            <div class="ops-search">
                <label class="sr-only" for="busqueda-catalogo">Buscar en el catálogo</label>
                <div class="ops-search-field">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.25" fill="none" stroke="currentColor" stroke-width="1.75"/>
                        <path d="M16 16.5 20 20.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                    <input
                        id="busqueda-catalogo"
                        type="search"
                        name="q"
                        value="{{ $busqueda }}"
                        placeholder="Buscar por código, nombre o número…"
                        autocomplete="off"
                    >
                </div>
                @if ($servicio !== '')
                    <input type="hidden" name="servicio" value="{{ $servicio }}">
                @endif
                <button class="btn btn-dark" type="submit">Buscar</button>
                @if ($hay_filtros)
                    <a class="btn btn-ghost" href="{{ route('mockups.catalogo') }}">Limpiar</a>
                @endif
            </div>
        </form>

        <p class="ops-filter-label">
            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4.5 6.5h15l-5.6 6.6V18l-3.8 1.8v-6.7L4.5 6.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
            </svg>
            Filtrar por área
        </p>
        <div class="ops-filters" aria-label="Filtrar por área">
            <a
                class="ops-filter {{ $servicio === '' ? 'is-active' : '' }}"
                href="{{ $urlCatalogo(null) }}"
            >
                Todos <em>{{ $total_servicios }}</em>
            </a>
            @foreach ($servicios as $opcion)
                <a
                    class="ops-filter {{ $servicio === $opcion ? 'is-active' : '' }}"
                    href="{{ $urlCatalogo($opcion) }}"
                >
                    {{ $opcion }} <em>{{ $conteo_servicios[$opcion] ?? 0 }}</em>
                </a>
            @endforeach
        </div>

        @if (count($items) === 0)
            <div class="ops-empty" role="status">
                <strong>No hay cajas coincidentes</strong>
                <p>
                    Prueba con otro código, nombre o área.
                    @if ($hay_filtros)
                        <a href="{{ route('mockups.catalogo') }}">Ver todo el catálogo</a>
                    @endif
                </p>
            </div>
        @else
            <p class="ops-result-meta">
                {{ count($items) }} {{ count($items) === 1 ? 'ficha' : 'fichas' }}
                @if ($servicio !== '')
                    en {{ $servicio }}
                @else
                    en el catálogo
                @endif
            </p>
            @foreach (collect($items)->groupBy('servicio') as $area => $cajas)
                <section class="cat-area">
                    <div class="cat-area-head">
                        <h2>{{ $area }}</h2>
                        <span>{{ $cajas->count() }} {{ $cajas->count() === 1 ? 'ficha' : 'fichas' }}</span>
                    </div>
                    <div class="cat-grid">
                        @foreach ($cajas as $item)
                            <a class="cat-card sigte-consulta-card" href="{{ route('mockups.ficha', $item['codigo']) }}">
                                <div class="cat-photo {{ $item['guia'] ? '' : 'is-empty' }}">
                                    <img
                                        class="cat-photo-placeholder"
                                        onerror="this.style.display='none'"
                                        src="{{ asset('images/catalogo/sin-imagen.png') }}"
                                        alt="Imagen de la caja no disponible"
                                        loading="lazy"
                                    >
                                    <span class="sigte-card-guide"><svg class="sigte-card-icon sigte-card-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.7 5.1C11.1 5 11.5 5 12 5c5.5 0 9 7 9 7a14 14 0 0 1-3.2 4.2"/><path d="M6.2 6.2C3.9 8.1 3 12 3 12s3.5 7 9 7a9.7 9.7 0 0 0 3.4-.6"/><path d="M10.1 10.1a2.7 2.7 0 0 0 3.8 3.8"/></svg><svg class="sigte-card-icon sigte-card-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.4-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.4 6.5-9.5 6.5S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.8"/></svg><span> {{ $item['guia'] ? 'Con guía visual' : 'Sin guía visual' }}</span></span>
                                </div>
                                <div class="cat-body">
                                    <strong class="sigte-consulta-codigo">{{ $item['codigo'] }}@if (!empty($item['codigo_referencia'])) · {{ $item['codigo_referencia'] }}@endif</strong>
                                    <h2>{{ $item['nombre'] }}</h2>
                                    <span class="cat-servicio"><svg class="sigte-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="1.5"/><path d="M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1M10 21v-3h4v3"/></svg><span>{{ $item['servicio'] }}</span></span>
                                    <p class="sigte-consulta-piezas"><svg class="sigte-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2.8 8.5 4.6v9.2L12 21.2l-8.5-4.6V7.4L12 2.8Z"/><path d="m3.5 7.4 8.5 4.8 8.5-4.8M12 12.2v9"/></svg><span>@if (!empty($item['numero']))Nº {{ $item['numero'] }} · @endif{{ $item['piezas'] }} {{ $item['piezas'] === 1 ? 'pieza' : 'piezas' }}</span></p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @endif
    </main>
</div>
@endsection
