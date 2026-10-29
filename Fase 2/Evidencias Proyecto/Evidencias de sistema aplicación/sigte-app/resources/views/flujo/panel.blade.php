@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/flujo.css') }}?v={{ @filemtime(public_path('css/flujo.css')) }}-ops6">
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

        <section class="ops-toolbar" aria-label="Buscar y filtrar cajas">
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
                            placeholder="Código, servicio, ubicación o responsable…"
                            autocomplete="off"
                        >
                    </div>
                    @if ($etapa_filtro !== null)
                        <input type="hidden" name="etapa" value="{{ $etapa_filtro }}">
                    @endif
                    <button class="btn btn-dark ops-search-go" type="submit">Buscar</button>
                    @if ($hay_filtros)
                        <a class="ops-search-clear" href="{{ route('mockups.panel', 'operador') }}">Limpiar</a>
                    @endif
                </div>
            </form>

            <div class="ops-filters" aria-label="Etapa">
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
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/>
                                    <path d="M12 22V12"/>
                                    <path d="m3.3 7 8.7 5 8.7-5"/>
                                    <path d="m7.5 4.27 9 5.15"/>
                                </svg>
                                @break
                            @case(1)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 3c-2.5 4-7 8.5-7 12a7 7 0 0 0 14 0c0-3.5-4.5-8-7-12z"/>
                                    <path d="M9 16a3 3 0 0 0 3 3"/>
                                </svg>
                                @break
                            @case(2)
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a2.121 2.121 0 0 1-3-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z"/>
                                </svg>
                                @break
                            @case(3)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M9 12l2 2 4-4"/>
                                    <path d="M12 3v2m0 14v2M3 12h2m14 0h2"/>
                                </svg>
                                @break
                            @case(4)
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="20" height="5" x="2" y="3" rx="1"/>
                                    <path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/>
                                    <path d="M10 12h4"/>
                                </svg>
                                @break
                            @case(5)
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 6 9 17l-5-5"/>
                                </svg>
                                @break
                        @endswitch
                        <span>{{ $fase }}</span>
                        <em>{{ $conteo_etapas[$i] ?? 0 }}</em>
                    </a>
                @endforeach
            </div>

            @if ($hay_filtros)
                <p class="ops-result-meta">
                    {{ $total_consulta }} {{ $total_consulta === 1 ? 'caja encontrada' : 'cajas encontradas' }}
                    @if ($busqueda !== '')
                        para “{{ $busqueda }}”
                    @endif
                    @if ($etapa_filtro !== null)
                        en {{ $fases[$etapa_filtro] }}
                    @endif
                </p>
            @endif
        </section>

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
                @php
                    $gruposVisibles = array_values($cajas_por_etapa);
                @endphp
                @foreach ($gruposVisibles as $indiceGrupo => $grupo)
                    @php
                        $cantidadGrupo = count($grupo['cajas']);
                        $siguienteGrupo = $gruposVisibles[$indiceGrupo + 1] ?? null;
                        $claveEtapa = \App\Models\Caja::etapas()[$grupo['indice']] ?? 'recepcion';
                        $urgentesGrupo = count(array_filter(
                            $grupo['cajas'],
                            fn (array $caja) => ! empty($caja['urgente'])
                        ));
                        $listosGrupo = count(array_filter(
                            $grupo['cajas'],
                            fn (array $caja) => ! empty($caja['proceso_listo'])
                        ));
                    @endphp
                    <details class="ops-etapa ops-etapa--{{ $claveEtapa }}" open>
                        <summary class="ops-etapa-head">
                            <h2>{{ $grupo['nombre'] }}</h2>
                            <div class="ops-etapa-resumen" aria-label="Resumen de {{ $grupo['nombre'] }}">
                                <span class="ops-etapa-chip ops-etapa-chip-total">
                                    {{ $cantidadGrupo }} {{ $cantidadGrupo === 1 ? 'caja' : 'cajas' }}
                                </span>
                                <span
                                    class="ops-etapa-chip ops-etapa-chip-urgent {{ $urgentesGrupo === 0 ? 'is-empty' : '' }}"
                                    title="{{ $urgentesGrupo }} {{ $urgentesGrupo === 1 ? 'urgente' : 'urgentes' }}"
                                >
                                    <svg class="ops-etapa-bell {{ $urgentesGrupo > 0 ? 'badge-urgent-bell' : '' }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <em>{{ $urgentesGrupo }}</em>
                                    <span class="sr-only">urgentes</span>
                                </span>
                                <span
                                    class="ops-etapa-chip ops-etapa-chip-listo {{ $listosGrupo === 0 ? 'is-empty' : '' }}"
                                    title="{{ $listosGrupo }} {{ $listosGrupo === 1 ? 'lista' : 'listas' }} para pasar"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="12" r="8.25" fill="none" stroke="currentColor" stroke-width="1.75"/>
                                        <path d="M8.2 12.2 10.7 14.7 15.8 9.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <em>{{ $listosGrupo }}</em>
                                    <span class="sr-only">listas para pasar</span>
                                </span>
                            </div>
                        </summary>
                        <div
                            class="ops-etapa-panel"
                            data-etapa-nombre="{{ $grupo['nombre'] }}"
                        >
                            <div
                                class="ops-etapa-list {{ $cantidadGrupo > 2 ? 'is-kds' : '' }}"
                                tabindex="0"
                                aria-label="Cajas en {{ $grupo['nombre'] }}"
                            >
                                @foreach ($grupo['cajas'] as $caja)
                                    @include('flujo.partials.tarjeta-caja', ['caja' => $caja, 'fases' => $fases])
                                @endforeach
                            </div>
                            <footer class="ops-etapa-foot">
                                <p>
                                    Fin de {{ $grupo['nombre'] }}
                                    <strong>· {{ $cantidadGrupo }} {{ $cantidadGrupo === 1 ? 'caja' : 'cajas' }}</strong>
                                </p>
                                @if ($siguienteGrupo)
                                    <p class="ops-etapa-next">
                                        Sigue
                                        <span>{{ $siguienteGrupo['nombre'] }}</span>
                                        ({{ count($siguienteGrupo['cajas']) }})
                                    </p>
                                @else
                                    <p class="ops-etapa-next">Última etapa del flujo</p>
                                @endif
                            </footer>
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
