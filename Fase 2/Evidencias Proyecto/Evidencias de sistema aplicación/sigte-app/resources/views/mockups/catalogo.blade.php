@extends('layouts.app')

@section('title', 'SIGTE — Catálogo')

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
                <p class="eyebrow">Consulta · fichas maestras</p>
                <h1>Catálogo</h1>
                <p class="main-sub">Elige una caja para ver qué debe llevar. El stock está en Inventario.</p>
            </div>
        </div>
        
        <form class="query-toolbar panel" method="get" action="{{ route('mockups.catalogo') }}">
            <label class="field" style="margin:0; flex:1;">
                <span class="icon" aria-hidden="true">⌕</span>
                <input type="search" name="q" value="{{ $busqueda }}" placeholder="Buscar por código, nombre o servicio…">
            </label>
            <label class="field field-select" style="margin:0; min-width:12rem;">
                <select name="servicio" onchange="this.form.submit()">
                    <option value="">Todos los servicios</option>
                    @foreach ($servicios as $opcion)
                        <option value="{{ $opcion }}" @selected($servicio === $opcion)>{{ $opcion }}</option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-dark" type="submit">Buscar</button>
        </form>
        
        @if (count($items) === 0)
            <div class="ops-empty">
                <strong>No hay cajas coincidentes</strong>
                <p>Prueba con otro código, nombre o servicio.</p>
            </div>
        @else
            <p class="ops-result-meta">{{ count($items) }} {{ count($items) === 1 ? 'caja' : 'cajas' }} en el catálogo</p>
            <div class="cat-grid">
                @foreach ($items as $item)
                    <a class="cat-card" href="{{ route('mockups.ficha', $item['codigo']) }}">
                        <div class="cat-photo {{ $item['guia'] ? '' : 'is-empty' }}">
                            <span>{{ $item['guia'] ? 'Con guía visual' : 'Sin guía visual' }}</span>
                        </div>
                        <div class="cat-body">
                            <strong>{{ $item['codigo'] }}</strong>
                            <h2>{{ $item['nombre'] }}</h2>
                            <p>{{ $item['servicio'] }} · {{ $item['tipo'] }} · {{ $item['piezas'] }} {{ $item['piezas'] === 1 ? 'pieza' : 'piezas' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </main>
</div>
@endsection
