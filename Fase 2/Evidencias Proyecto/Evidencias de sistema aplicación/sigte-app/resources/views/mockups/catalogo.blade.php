@extends('layouts.app')

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
                        placeholder="Buscar por código, nombre o servicio…"
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
            Filtrar por servicio
        </p>
        <div class="ops-filters" aria-label="Filtrar por servicio">
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
                    Prueba con otro código, nombre o servicio.
                    @if ($hay_filtros)
                        <a href="{{ route('mockups.catalogo') }}">Ver todo el catálogo</a>
                    @endif
                </p>
            </div>
        @else
            <p class="ops-result-meta">
                {{ count($items) }} {{ count($items) === 1 ? 'caja' : 'cajas' }}
                @if ($servicio !== '')
                    en {{ $servicio }}
                @else
                    en el catálogo
                @endif
            </p>
            <div class="cat-grid">
                @foreach ($items as $item)
                    <a class="cat-card" href="{{ route('mockups.ficha', $item['codigo']) }}">
                        <div class="cat-photo {{ $item['guia'] ? '' : 'is-empty' }}">
                            <span>{{ $item['guia'] ? 'Con guía visual' : 'Sin guía visual' }}</span>
                        </div>
                        <div class="cat-body">
                            <strong>{{ $item['codigo'] }}</strong>
                            <h2>{{ $item['nombre'] }}</h2>
                            <p>
                                <span class="cat-servicio">{{ $item['servicio'] }}</span>
                                {{ $item['tipo'] }} · {{ $item['piezas'] }} {{ $item['piezas'] === 1 ? 'pieza' : 'piezas' }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </main>
</div>
@endsection
