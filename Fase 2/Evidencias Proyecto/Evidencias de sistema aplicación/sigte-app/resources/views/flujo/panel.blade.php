@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/flujo.css') }}?v={{ @filemtime(public_path('css/flujo.css')) }}">
@endpush

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
                <a class="btn btn-dark" href="{{ route('mockups.recepcion') }}">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 13.2V18a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 20 18v-4.8" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                        <path d="M4 13.2 6.3 7.2A1.6 1.6 0 0 1 7.8 6.2h8.4a1.6 1.6 0 0 1 1.5 1l2.3 6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                        <path d="M4 13.2h4.1a2 2 0 0 0 1.9 1.3h4a2 2 0 0 0 1.9-1.3H20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                    </svg>
                    Nueva recepción
                </a>
            </div>
        </div>

        @include('flujo.partials.puesto')

        <form class="ops-consulta" method="get" action="{{ route('mockups.panel', 'operador') }}" role="search">
            <div class="ops-search">
                <label class="sr-only" for="busqueda-caja">Buscar caja quirúrgica</label>
                <div class="ops-search-field">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.25" fill="none" stroke="currentColor" stroke-width="1.75"/>
                        <path d="M16 16.5 20 20.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                    <input
                        id="busqueda-caja"
                        type="search"
                        name="q"
                        value="{{ $busqueda }}"
                        placeholder="Buscar por código, servicio, ubicación o responsable…"
                        autocomplete="off"
                    >
                </div>
                @if ($etapa_filtro !== null)
                    <input type="hidden" name="etapa" value="{{ $etapa_filtro }}">
                @endif
                <button class="btn btn-dark" type="submit">Buscar</button>
                @if ($hay_filtros)
                    <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Limpiar</a>
                @endif
            </div>
        </form>

        <p class="ops-filter-label">
            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4.5 6.5h15l-5.6 6.6V18l-3.8 1.8v-6.7L4.5 6.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
            </svg>
            Filtrar por etapa
        </p>
        <div class="ops-filters" aria-label="Filtrar por etapa">
            <a
                class="ops-filter {{ $etapa_filtro === null ? 'is-active' : '' }}"
                href="{{ $urlConsulta(null, $busqueda) }}"
            >
                <span>Todas</span>
                <em>{{ $totalTodas }}</em>
            </a>

            @foreach ($fases as $i => $fase)
                <a
                    class="ops-filter {{ $etapa_filtro === $i ? 'is-active' : '' }}"
                    href="{{ $urlConsulta($i, $busqueda) }}"
                >
                    @switch($i)
                        @case(0)
                            {{-- Recepción: Package --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/>
                                <path d="M12 22V12"/>
                                <path d="m3.3 7 8.7 5 8.7-5"/>
                                <path d="m7.5 4.27 9 5.15"/>
                            </svg>
                            @break

                        @case(1)
                            {{-- Lavado --}}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                aria-hidden="true">
                                <path d="M12 3c-2.5 4-7 8.5-7 12a7 7 0 0 0 14 0c0-3.5-4.5-8-7-12z"/>
                                <path d="M9 16a3 3 0 0 0 3 3"/>
                            </svg>
                            @break

                        @case(2)
                            {{-- Preparación: Wrench --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a2.121 2.121 0 0 1-3-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z"/>
                            </svg>
                            @break

                        @case(3)
                            {{-- Esterilización --}}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M9 12l2 2 4-4"/>
                                <path d="M12 3v2m0 14v2M3 12h2m14 0h2"/>
                            </svg>
                            @break

                        @case(4)
                            {{-- Almacén: Archive --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect width="20" height="5" x="2" y="3" rx="1"/>
                                <path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/>
                                <path d="M10 12h4"/>
                            </svg>
                            @break

                        @case(5)
                            {{-- Entrega: Check --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 6 9 17l-5-5"/>
                            </svg>
                            @break
                    @endswitch

                    <span>{{ $fase }}</span>
                    <em>{{ $conteo_etapas[$i] ?? 0 }}</em>
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
                                @include('flujo.partials.tarjeta-caja', ['caja' => $caja, 'fases' => $fases])
                            @endforeach
                        </div>
                    </details>
                @endforeach
            @endif
        </div>
    </main>
</div>

@if (($usuario['rol'] ?? '') === 'Operadora')
    @include('flujo.partials.modales-flujo')
@endif
@include('flujo.partials.proceso-reloj')
@endsection
