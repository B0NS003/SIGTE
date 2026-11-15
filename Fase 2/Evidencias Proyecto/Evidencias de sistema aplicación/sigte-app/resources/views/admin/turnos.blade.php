@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modulos/admin/comun.css') }}?v={{ @filemtime(public_path('css/modulos/admin/comun.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modulos/admin/turnos.css') }}?v={{ @filemtime(public_path('css/modulos/admin/turnos.css')) }}">
@endpush

@section('title', 'SIGTE — Turnos')

@section('content')
@php
    $columnas = ['' => 'Sin asignar'] + collect(\App\Models\Personal::TURNOS)->map(fn ($t) => $t['etiqueta'])->all();
    $esEnfermeras = $grupo === \App\Models\Rol::ENFERMERA;
    $etiquetaGrupo = $esEnfermeras ? 'Enfermeras de turno' : 'Operadoras';
@endphp
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

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Organización del puesto</p>
                <h1>Turnos</h1>
                <p class="main-sub" id="turno-main-sub">
                    @if ($esEnfermeras)
                        Asigna enfermeras de turno a Mañana, Tarde o Noche. En el cierre, el relevo prioriza a quienes quedan en el bloque siguiente.
                    @else
                        Asigna operadoras al turno del día. En el puesto aparecen primero quienes están de turno ahora.
                    @endif
                </p>
            </div>
            <div class="main-actions turno-main-actions">
                <a class="btn btn-exportar-pdf" id="turno-export-pdf" href="{{ route('mockups.turnos.pdf', ['grupo' => $grupo]) }}" data-descargar-reporte="pdf" title="Exportar PDF">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M9 13h6M9 17h6"/></svg>
                    <span>Exportar PDF</span>
                </a>
                <a class="btn btn-exportar-excel" id="turno-export-excel" href="{{ route('mockups.turnos.excel', ['grupo' => $grupo]) }}" data-descargar-reporte="excel" title="Exportar Excel">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M10 17l4-6M14 17l-4-6"/></svg>
                    <span>Exportar Excel</span>
                </a>
            </div>
        </div>

        <section class="panel nomina-panel turno-panel">
            <div class="panel-head">
                <div>
                    <h2>Nómina del puesto</h2>
                    <p class="turno-flujo" id="turno-flujo">
                        Arrastra un nombre a <strong>Mañana</strong>, <strong>Tarde</strong> o <strong>Noche</strong>. Mostrando {{ mb_strtolower($etiquetaGrupo) }}.
                    </p>
                </div>
            </div>

            <nav class="inv-pestanas turno-grupo-chips" role="tablist" aria-label="Grupo de turnos">
                <a
                    class="inv-pestana {{ ! $esEnfermeras ? 'is-active' : '' }}"
                    href="{{ route('mockups.turnos', ['grupo' => \App\Models\Rol::OPERADOR]) }}"
                    role="tab"
                    aria-selected="{{ ! $esEnfermeras ? 'true' : 'false' }}"
                >Operadoras</a>
                <a
                    class="inv-pestana {{ $esEnfermeras ? 'is-active' : '' }}"
                    href="{{ route('mockups.turnos', ['grupo' => \App\Models\Rol::ENFERMERA]) }}"
                    role="tab"
                    aria-selected="{{ $esEnfermeras ? 'true' : 'false' }}"
                >Enfermeras de turno</a>
            </nav>

            <div
                class="turno-tablero"
                data-url="{{ route('mockups.turnos.asignar') }}"
                data-grupo="{{ $grupo }}"
            >
                @foreach ($columnas as $clave => $etiqueta)
                    @php
                        $personasColumna = $nomina->where('turno', $clave === '' ? null : $clave)->sortBy('nombre');
                        $tono = match ($clave) {
                            'manana' => 'manana',
                            'tarde' => 'tarde',
                            'noche' => 'noche',
                            default => 'libre',
                        };
                    @endphp
                    <section
                        class="turno-columna tono-{{ $tono }} {{ $clave === $turnoVigente ? 'is-ahora' : '' }}"
                        data-turno="{{ $clave }}"
                    >
                        <header>
                            <div class="turno-columna-titulo">
                                <h3>{{ $etiqueta }}</h3>
                                @if ($clave !== '' && isset(\App\Models\Personal::TURNOS[$clave]))
                                    <span>{{ \App\Models\Personal::TURNOS[$clave]['horario'] }}</span>
                                @else
                                    <span>Pendientes de asignar</span>
                                @endif
                            </div>
                            <div class="turno-columna-meta">
                                <span class="turno-icono" aria-hidden="true">
                                    @if ($tono === 'manana')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                                    @elseif ($tono === 'tarde')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 18a5 5 0 0 0-10 0"/><path d="M12 2v4M4.2 10.2l2.1 2.1M17.7 12.3l2.1-2.1M2 18h20"/><circle cx="12" cy="12" r="3"/></svg>
                                    @elseif ($tono === 'noche')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 14.5A8.5 8.5 0 0 1 9.5 3 7 7 0 1 0 21 14.5Z"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    @endif
                                </span>
                                @if ($clave === $turnoVigente)
                                    <em>Ahora</em>
                                @endif
                            </div>
                        </header>
                        @if ($clave === '')
                            <label class="turno-busca">
                                <span class="sr-only">Buscar en sin asignar</span>
                                <input type="search" placeholder="Buscar nombre…" autocomplete="off">
                            </label>
                        @endif
                        <ul class="turno-lista">
                            @foreach ($personasColumna as $persona)
                                @php
                                    $partes = preg_split('/\s+/u', trim($persona->nombre)) ?: [];
                                    $iniciales = mb_strtoupper(mb_substr($partes[0] ?? '?', 0, 1).(isset($partes[1]) ? mb_substr($partes[1], 0, 1) : ''));
                                @endphp
                                <li class="turno-tarjeta" draggable="true" data-id="{{ $persona->id }}">
                                    <span class="turno-avatar" aria-hidden="true">{{ $iniciales }}</span>
                                    <strong>{{ $persona->nombre }}</strong>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>

            <div class="turno-acciones">
                <p class="turno-estado" hidden role="status"></p>
                <div class="turno-acciones-botones">
                    <button class="btn btn-dark turno-btn-guardar" type="button" id="turno-guardar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        <span>Guardar cambios</span>
                    </button>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/modulos/admin/turnos.js') }}?v={{ @filemtime(public_path('js/modulos/admin/turnos.js')) }}"></script>
@endpush
