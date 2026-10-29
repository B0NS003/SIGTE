@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) }}">
@endpush

@section('title', 'SIGTE — Historial de entregas')

@section('content')
@php
    $ordenSectores = ['Pabellon', 'Pabellón', 'Dental', 'UCI', 'Urgencia', 'Maternidad', 'Curaciones', 'Otro servicio'];
    $slugSector = function (string $servicio): string {
        $clave = mb_strtolower(strtr($servicio, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
        ]));
        $clave = preg_replace('/[^a-z0-9]+/', '', $clave) ?: 'otro';

        return match (true) {
            str_contains($clave, 'pabell') => 'pabellon',
            str_contains($clave, 'dental') => 'dental',
            str_contains($clave, 'uci') => 'uci',
            str_contains($clave, 'urgencia') => 'urgencia',
            str_contains($clave, 'matern') => 'maternidad',
            str_contains($clave, 'curacion') => 'curaciones',
            default => 'otro',
        };
    };
    $etiquetaCuando = fn (string $cuando): ?string => match ($cuando) {
        'hoy' => 'Hoy',
        'ayer' => 'Ayer',
        default => null,
    };
    $panelVolver = ($usuario['rol'] ?? '') === 'Enfermera de turno'
        ? route('mockups.panel', 'enfermera')
        : route('mockups.panel', 'administradora');
@endphp
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">{{ ($usuario['rol'] ?? '') === 'Enfermera de turno' ? 'Turno' : 'Administración' }}</div>
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
                <p class="eyebrow">Gestión · entregas</p>
                <h1>Historial de entregas</h1>
                <p class="main-sub">Consulta las salidas registradas: cajas, material, servicio y quién entregó o recibió.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ $panelVolver }}">Volver al resumen</a>
            </div>
        </div>

        @if ($falla)
            <div class="ops-empty">
                <strong>No se pudo cargar el historial</strong>
                <p>El listado no está disponible en este momento. Las entregas no se modificaron.</p>
                <p><a href="{{ route('mockups.historial_entregas') }}">Intentar de nuevo</a></p>
            </div>
        @else
            @if ($servicios->isNotEmpty())
                <div class="hist-sectores" aria-label="Filtrar por servicio">
                    <a
                        class="hist-sector {{ $filtros['servicio'] === '' ? 'is-on' : '' }}"
                        href="{{ route('mockups.historial_entregas', array_filter(['q' => $filtros['q'] ?: null, 'cuando' => $filtros['cuando'] !== 'todas' ? $filtros['cuando'] : null])) }}"
                    >
                        <strong>Todas</strong>
                        <span class="n">{{ $resumen['total'] ?? 0 }}</span>
                        <small>Hoy {{ $resumen['hoy'] ?? 0 }}</small>
                    </a>
                    @foreach ($servicios->sortBy(function ($fila) use ($ordenSectores) {
                        $indice = array_search($fila['servicio'], $ordenSectores, true);
                        return $indice === false ? 99 : $indice;
                    }) as $fila)
                        <a
                            class="hist-sector sec-{{ $slugSector($fila['servicio']) }} {{ $filtros['servicio'] === $fila['servicio'] ? 'is-on' : '' }}"
                            href="{{ route('mockups.historial_entregas', array_filter([
                                'q' => $filtros['q'] ?: null,
                                'cuando' => $filtros['cuando'] !== 'todas' ? $filtros['cuando'] : null,
                                'servicio' => $fila['servicio'],
                            ])) }}"
                        >
                            <strong>{{ $fila['servicio'] }}</strong>
                            <span class="n">{{ $fila['total'] }}</span>
                            @if ($fila['ultima_hora'])
                                <small>{{ $fila['ultima_hora'] }} · {{ $fila['ultima_fecha'] }}</small>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif

            <form class="users-filtros hist-filtros" method="get" action="{{ route('mockups.historial_entregas') }}">
                <label class="field hist-buscar">
                    <span>Buscar</span>
                    <input type="search" name="q" value="{{ $filtros['q'] }}" placeholder="Código, persona, caja o material" autocomplete="off">
                </label>
                <label class="field hist-periodo">
                    <span>Período</span>
                    <select name="cuando">
                        <option value="todas" @selected($filtros['cuando'] === 'todas')>Todas</option>
                        <option value="hoy" @selected($filtros['cuando'] === 'hoy')>Hoy</option>
                        <option value="ayer" @selected($filtros['cuando'] === 'ayer')>Ayer</option>
                        <option value="semana" @selected($filtros['cuando'] === 'semana')>Últimos 7 días</option>
                    </select>
                </label>
                @if ($filtros['servicio'] !== '')
                    <input type="hidden" name="servicio" value="{{ $filtros['servicio'] }}">
                @endif
                <div class="users-filtros-acciones">
                    <button class="btn btn-dark" type="submit">Consultar</button>
                    @if ($filtros['q'] !== '' || $filtros['cuando'] !== 'todas' || $filtros['servicio'] !== '')
                        <a class="btn btn-ghost" href="{{ route('mockups.historial_entregas') }}">Limpiar</a>
                    @endif
                </div>
            </form>

            <div class="mant-head">
                <div>
                    <h2>
                        @if ($filtros['servicio'] !== '')
                            {{ $filtros['servicio'] }}
                        @else
                            Entregas registradas
                        @endif
                    </h2>
                    <p>
                        @if ($entregas->total() === 0)
                            Sin coincidencias con el criterio actual
                        @else
                            {{ $entregas->total() }} {{ $entregas->total() === 1 ? 'entrega' : 'entregas' }}
                            · pág. {{ $entregas->currentPage() }}/{{ max(1, $entregas->lastPage()) }}
                        @endif
                    </p>
                </div>
            </div>

            @if ($entregas->total() === 0)
                <div class="ops-empty">
                    <strong>No hay entregas con ese criterio</strong>
                    <p>Prueba con otro código, servicio, persona o período.</p>
                    @if ($filtros['q'] !== '' || $filtros['cuando'] !== 'todas' || $filtros['servicio'] !== '')
                        <p><a href="{{ route('mockups.historial_entregas') }}">Ver todas las entregas</a></p>
                    @endif
                </div>
            @else
                <div class="hist-lista">
                    @foreach ($entregas as $entrega)
                        <article class="hist-card sec-{{ $slugSector($entrega['servicio']) }}">
                            <div class="hist-cuando">
                                <strong>{{ $entrega['hora'] }}</strong>
                                <span>{{ $entrega['fecha'] }}</span>
                                @if ($relativo = $etiquetaCuando($entrega['cuando']))
                                    <em class="hist-relativo">{{ $relativo }}</em>
                                @endif
                                <small>{{ $entrega['id'] }}</small>
                            </div>
                            <div class="hist-cuerpo">
                                <div class="hist-top">
                                    <h2>{{ $entrega['servicio'] }}</h2>
                                    <span class="badge">{{ count($entrega['cajas']) }} {{ count($entrega['cajas']) === 1 ? 'caja' : 'cajas' }}</span>
                                </div>
                                <div class="hist-cajas">
                                    @forelse ($entrega['cajas'] as $caja)
                                        <span class="hist-chip">{{ $caja['codigo'] }} <small>{{ $caja['nombre'] }}</small></span>
                                    @empty
                                        <span class="hist-chip is-material">Sin cajas</span>
                                    @endforelse
                                    @foreach ($entrega['materiales'] as $material)
                                        <span class="hist-chip is-material">{{ $material }}</span>
                                    @endforeach
                                </div>
                                <div class="entrega-datos">
                                    <div class="entrega-dato">
                                        <span>Quién entrega</span>
                                        <strong>{{ $entrega['entrega'] }}</strong>
                                        <small>{{ $entrega['entrega_rol'] }}</small>
                                    </div>
                                    <div class="entrega-dato">
                                        <span>Quién recibe</span>
                                        <strong>{{ $entrega['recibe'] }}</strong>
                                        <small>{{ $entrega['recibe_rol'] }}</small>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                @include('admin.partials.paginas', ['paginador' => $entregas, 'etiqueta' => 'Páginas de entregas'])
            @endif
        @endif
    </main>
</div>
@endsection
