@extends('layouts.app')

@section('title', 'SIGTE — Operadora')

@section('content')
@php
    $totalTodas = array_sum($conteo_etapas ?? []);
    $urlConsulta = function (?int $etapa = null, ?string $q = null) {
        $params = [];
        $texto = $q ?? ($busqueda ?? '');
        if ($texto !== '') {
            $params['q'] = $texto;
        }
        if ($etapa !== null) {
            $params['etapa'] = $etapa;
        }

        return route('mockups.panel', array_merge(['rol' => 'operador'], $params));
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
                <p class="eyebrow">Operación · consulta de cajas</p>
                <h1>Flujo de cajas</h1>
            </div>
            <div class="main-actions">
                <a class="btn btn-dark" href="{{ route('mockups.recepcion') }}">+ Nueva recepción</a>
            </div>
        </div>

        <form class="ops-consulta" method="get" action="{{ route('mockups.panel', 'operador') }}" role="search">
            <div class="ops-search">
                <label class="sr-only" for="busqueda-caja">Buscar caja quirúrgica</label>
                <input
                    id="busqueda-caja"
                    type="search"
                    name="q"
                    value="{{ $busqueda }}"
                    placeholder="Buscar por código, servicio, ubicación o responsable…"
                    autocomplete="off"
                >
                @if ($etapa_filtro !== null)
                    <input type="hidden" name="etapa" value="{{ $etapa_filtro }}">
                @endif
                <button class="btn btn-dark" type="submit">Buscar</button>
                @if ($hay_filtros)
                    <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="ops-filters" aria-label="Filtrar por etapa">
            <a
                class="ops-filter {{ $etapa_filtro === null ? 'is-active' : '' }}"
                href="{{ $urlConsulta(null, $busqueda) }}"
            >
                Todas <em>{{ $totalTodas }}</em>
            </a>
            @foreach ($fases as $i => $fase)
                <a
                    class="ops-filter {{ $etapa_filtro === $i ? 'is-active' : '' }}"
                    href="{{ $urlConsulta($i, $busqueda) }}"
                >
                    {{ $fase }} <em>{{ $conteo_etapas[$i] ?? 0 }}</em>
                </a>
            @endforeach
        </div>

        <p class="ops-result-meta">
            @if ($hay_filtros)
                {{ $total_consulta }} {{ $total_consulta === 1 ? 'caja encontrada' : 'cajas encontradas' }}
                @if ($busqueda !== '')
                    para “{{ $busqueda }}”
                @endif
                @if ($etapa_filtro !== null)
                    en {{ $fases[$etapa_filtro] }}
                @endif
            
            @endif
        </p>

        <div class="ops-board">
            @if ($total_consulta === 0)
                <div class="ops-empty" role="status">
                    @if ($hay_filtros)
                        <strong>No se encontraron cajas coincidentes</strong>
                        <p>
                            Prueba con otro código o quita el filtro de etapa.
                            @if ($busqueda !== '' || $etapa_filtro !== null)
                                <a href="{{ route('mockups.panel', 'operador') }}">Ver todas las cajas</a>
                            @endif
                        </p>
                    @else
                        <strong>No hay cajas en el flujo</strong>
                        <p>Cuando se registren recepciones, aparecerán aquí por etapa.</p>
                    @endif
                </div>
            @else
                @foreach ($cajas_por_etapa as $grupo)
                    <details class="ops-etapa" open>
                        <summary class="ops-etapa-head">
                            <h2>{{ $grupo['nombre'] }}</h2>
                            <span>{{ count($grupo['cajas']) }} {{ count($grupo['cajas']) === 1 ? 'caja' : 'cajas' }}</span>
                        </summary>
                        <div class="ops-etapa-list">
                            @foreach ($grupo['cajas'] as $caja)
                                @include('mockups.partials.tarjeta-caja', ['caja' => $caja, 'fases' => $fases])
                            @endforeach
                        </div>
                    </details>
                @endforeach
            @endif
        </div>
    </main>
</div>

@if (($usuario['rol'] ?? '') === 'Operadora')
    @include('mockups.partials.modales-flujo')
@endif
@include('mockups.partials.proceso-reloj')
@endsection
