@extends('layouts.app')

@section('title', 'SIGTE — Ficha')

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
                <p class="eyebrow">Consulta · catálogo</p>
                <h1>{{ $item['nombre'] ?? $codigo }}</h1>
                @if ($item)
                    <p class="main-sub">{{ $item['codigo'] }} · {{ $item['tipo'] }} · {{ $item['servicio'] }}</p>
                @endif
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.catalogo') }}">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14.5 6.5 9 12l5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Volver al catálogo
                </a>
            </div>
        </div>

        @if (! $item)
            <div class="ops-empty">
                <strong>No fue posible cargar la ficha</strong>
                <p>No hay una caja «{{ $codigo }}» en el catálogo.</p>
                <p><a href="{{ route('mockups.catalogo') }}">Intentar de nuevo</a></p>
            </div>
        @else
            <div class="ficha-layout">
                <section class="ficha-guia {{ $item['guia'] ? '' : 'is-empty' }}" aria-label="Guía visual">
                    @if ($item['guia'])
                        <span class="ficha-kicker">Guía visual</span>
                        <strong>Cómo se reconoce el set</strong>
                        <p>Referencia de los instrumentos que tienen foto.</p>
                        <div class="ficha-mosaico">
                            @foreach ($item['instrumentos'] as $instrumento)
                                @if ($instrumento['imagen'])
                                    <figure>
                                        <span class="ficha-thumb" aria-hidden="true">{{ mb_strtoupper(mb_substr($instrumento['nombre'], 0, 1)) }}</span>
                                        <figcaption>{{ $instrumento['nombre'] }}</figcaption>
                                    </figure>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <span class="ficha-kicker">Sin guía visual</span>
                        <strong>Esta caja no tiene imagen del set</strong>
                        <p>Se puede armar igual con la lista de abajo. Las fotos de cada pieza aparecen solo si están registradas.</p>
                    @endif
                </section>

                <section class="panel ficha-datos">
                    <h2>Datos generales</h2>
                    <dl class="ficha-facts">
                        <div>
                            <dt>Código</dt>
                            <dd>{{ $item['codigo'] }}</dd>
                        </div>
                        <div>
                            <dt>Tipo</dt>
                            <dd>{{ $item['tipo'] }}</dd>
                        </div>
                        <div>
                            <dt>Servicio</dt>
                            <dd>{{ $item['servicio'] }}</dd>
                        </div>
                        <div>
                            <dt>Debe llevar</dt>
                            <dd>{{ $item['piezas'] }} {{ $item['piezas'] === 1 ? 'pieza' : 'piezas' }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            <section class="panel ficha-lista">
                <div class="panel-head">
                    <h2>Qué debe contener</h2>
                    <span class="badge">{{ count($item['instrumentos']) }} {{ count($item['instrumentos']) === 1 ? 'ítem' : 'ítems' }}</span>
                </div>
                <ul class="ficha-items">
                    @foreach ($item['instrumentos'] as $instrumento)
                        <li>
                            <span class="ficha-thumb {{ $instrumento['imagen'] ? '' : 'is-empty' }}" aria-hidden="true">
                                {{ $instrumento['imagen'] ? mb_strtoupper(mb_substr($instrumento['nombre'], 0, 1)) : '—' }}
                            </span>
                            <span class="ficha-nombre">
                                {{ $instrumento['nombre'] }}
                                @unless ($instrumento['imagen'])
                                    <small>Sin foto</small>
                                @endunless
                            </span>
                            <span class="ficha-cant">× {{ $instrumento['cantidad'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </main>
</div>
@endsection
