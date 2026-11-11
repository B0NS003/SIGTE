@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modulos/reportes/comun.css') }}?v={{ @filemtime(public_path('css/modulos/reportes/comun.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modulos/reportes/admin.css') }}?v={{ @filemtime(public_path('css/modulos/reportes/admin.css')) }}">
@endpush

@section('title', 'SIGTE — Administradora')

@section('content')
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-sigte.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Administración</div>
            </div>
        </div>

                @include('partials.menu-rol')
        @include('partials.sidebar-foot')
    </aside>
    <!-- Contenido principal de la página -->
    <main class="main reportes-pagina">
        <div class="main-top">
            <div>
                <p class="eyebrow">Hospital San José de Melipilla</p>
                <h1>Resumen de gestión</h1>
                <p class="main-sub">Producción, trazabilidad y entregas del período.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Ver vista operadora</a>
                <a class="btn btn-dark" href="{{ route('mockups.catalogo.admin') }}">Mantener catálogo</a>
            </div>
        </div>

        @include('admin.partials.reportes', $reporte)
    </main>
</div>
@endsection
