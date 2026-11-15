@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modulos/reportes/comun.css') }}?v={{ @filemtime(public_path('css/modulos/reportes/comun.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modulos/reportes/admin.css') }}?v={{ @filemtime(public_path('css/modulos/reportes/admin.css')) }}">
@endpush

@section('title', 'SIGTE — Reportes de secretaría')

@section('content')
<div class="shell">
    @include('secretaria.partials.sidebar')

    <main class="main reportes-pagina reportes-secretaria">
        <div class="main-top">
            <div>
                <p class="eyebrow">Consolidación estadística</p>
                <h1>Reportes</h1>
                <p class="main-sub">Producción diaria por servicio y período. Consulta cifras y genera Excel o PDF.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'secretaria') }}">Volver al inicio</a>
                <a class="btn btn-dark" href="{{ route('secretaria.produccion') }}">Registrar producción</a>
            </div>
        </div>

        @include('admin.partials.reportes')
    </main>
</div>
@endsection
