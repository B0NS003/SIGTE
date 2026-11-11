@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/secretaria.css') }}?v={{ @filemtime(public_path('css/secretaria.css')) }}">
@endpush

@section('title', 'SIGTE — Secretaría')

@section('content')
<div class="shell">
    {{-- Se mantiene exactamente la barra lateral compartida del proyecto. --}}
    @include('secretaria.partials.sidebar')

    <main class="main sp-dashboard">
        <header class="main-top spd-top">
            <div>
                <p class="eyebrow">Hospital San José de Melipilla</p>
                <h1>Panel de secretaría</h1>
                <p class="main-sub">Gestión de producción, insumos y reportes del servicio de esterilización.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-dark spd-cargar" href="{{ route('secretaria.produccion') }}">Cargar producción</a>
            </div>
        </header>

        <form class="spd-periodo" method="GET" action="{{ route('mockups.panel', 'secretaria') }}" aria-label="Seleccionar el mes de consulta">
            <label for="spd-periodo">Período de consulta</label>
            <input type="month" id="spd-periodo" name="periodo" value="{{ $resumen['periodo'] }}" required>
            <button type="submit">Ver período</button>
            <span class="spd-periodo-etiqueta">{{ $resumen['periodo_etiqueta'] }}</span>
        </form>

        <section class="spd-kpis" aria-label="Indicadores de producción y stock">
            <article class="spd-kpi spd-kpi-azul">
                <span class="spd-kpi-icono" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v13H5V5a2 2 0 0 1 2-2Z"/><path d="M14 3v5h5M9 13h6M12 10v6"/></svg>
                </span>
                <div>
                    <p>Servicios con producción</p>
                    <strong>{{ number_format($resumen['servicios'], 0, ',', '.') }}</strong>
                    <small>Registros diarios del mes</small>
                </div>
            </article>
            <article class="spd-kpi spd-kpi-verde">
                <span class="spd-kpi-icono" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C9 7 5 11 5 15a7 7 0 0 0 14 0c0-4-4-8-7-13Z"/></svg>
                </span>
                <div>
                    <p>Litros del mes</p>
                    <strong>{{ number_format((float) $resumen['litros'], 2, ',', '.') }} <span>L</span></strong>
                    <small>Producción diaria acumulada</small>
                </div>
            </article>
            <article class="spd-kpi spd-kpi-celeste">
                <span class="spd-kpi-icono" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 8 4.5v10L12 21l-8-4.5v-10L12 2Z"/><path d="m4 6.5 8 4.7 8-4.7M12 11.2V21"/></svg>
                </span>
                <div>
                    <p>Unidades del mes</p>
                    <strong>{{ number_format($resumen['consumo'], 0, ',', '.') }}</strong>
                    <small>{{ number_format($resumen['registros'], 0, ',', '.') }} registros diarios</small>
                </div>
            </article>
            <article class="spd-kpi spd-kpi-naranja">
                <span class="spd-kpi-icono" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 9 16a1 1 0 0 1-.9 1.5H3.9A1 1 0 0 1 3 19l9-16Z"/><path d="M12 9v5M12 17.5h.01"/></svg>
                </span>
                <div>
                    <p>Insumos a revisar</p>
                    <strong>{{ number_format($resumen['bajo_minimo'], 0, ',', '.') }}</strong>
                    <small>Bajo mínimo o críticos · stock actual</small>
                </div>
            </article>
        </section>

        <section class="spd-centro" aria-label="Herramientas y seguimiento">
            <article class="spd-panel spd-atajos">
                <div class="spd-cabecera">
                    <span class="spd-enc-icono" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m13 2-3 8h6l-6 12 2-9H6l7-11Z"/></svg>
                    </span>
                    <div><h2>Accesos rápidos</h2><p>Acciones principales de Secretaría</p></div>
                </div>
                <div class="spd-atajos-lista">
                    <a href="{{ route('secretaria.produccion') }}" class="spd-atajo">
                        <span class="spd-atajo-icono" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2C9 7 5 11 5 15a7 7 0 0 0 14 0c0-4-4-8-7-13Z"/></svg></span>
                        <span class="spd-atajo-texto"><strong>Producción y litros</strong><small>Ingresa materiales y consumos diarios</small></span>
                        <span class="spd-flecha" aria-hidden="true">›</span>
                    </a>
                    <a href="{{ route('secretaria.insumos') }}" class="spd-atajo">
                        <span class="spd-atajo-icono" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m12 2 8 4.5v10L12 21l-8-4.5v-10L12 2Z"/><path d="m4 6.5 8 4.7 8-4.7M12 11.2V21"/></svg></span>
                        <span class="spd-atajo-texto"><strong>Insumos</strong><small>Consulta stock y registra movimientos</small></span>
                        <span class="spd-flecha" aria-hidden="true">›</span>
                    </a>
                    <a href="{{ route('secretaria.reportes') }}" class="spd-atajo">
                        <span class="spd-atajo-icono" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M5 20V11M10 20V6M15 20v-9M20 20V3"/></svg></span>
                        <span class="spd-atajo-texto"><strong>Reportes</strong><small>Consulta el consolidado mensual</small></span>
                        <span class="spd-flecha" aria-hidden="true">›</span>
                    </a>
                </div>
            </article>

            <article class="spd-panel spd-alertas">
                <div class="spd-cabecera">
                    <span class="spd-enc-icono" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 8 4.5v10L12 21l-8-4.5v-10L12 2Z"/><path d="m4 6.5 8 4.7 8-4.7M12 11.2V21"/></svg>
                    </span>
                    <div><h2>Insumos que requieren atención</h2><p>Stock bajo mínimo o en estado crítico</p></div>
                </div>
                <div class="spd-alertas-lista">
                    @forelse($resumen['insumos_alerta'] as $insumo)
                        <div class="spd-insumo">
                            <div class="spd-insumo-nombre" title="{{ $insumo['codigo'] }} · {{ $insumo['nombre'] }}"><strong>{{ $insumo['nombre'] }}</strong><small>{{ $insumo['codigo'] }} · Mín. {{ $insumo['minimo'] }}</small></div>
                            <span class="spd-insumo-cantidad">{{ number_format($insumo['stock'], 0, ',', '.') }} un.</span>
                            <span class="spd-estado {{ $insumo['estado'] === 'critico' ? 'spd-estado-critico' : 'spd-estado-bajo' }}">{{ $insumo['estado'] === 'critico' ? 'Crítico' : 'Bajo' }}</span>
                        </div>
                    @empty
                        <div class="spd-vacio"><strong>Sin alertas de stock</strong><p>Los insumos registrados están dentro de sus mínimos establecidos.</p></div>
                    @endforelse
                </div>
                <a class="spd-enlace-pie" href="{{ route('secretaria.insumos') }}">Ver todos los insumos ({{ $resumen['bajo_minimo'] }}) <span aria-hidden="true">›</span></a>
            </article>

            <article class="spd-panel spd-actividad">
                <div class="spd-cabecera">
                    <span class="spd-enc-icono" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    </span>
                    <div><h2>Actividad reciente</h2><p>Últimos registros de producción e insumos</p></div>
                </div>
                <ol class="spd-timeline">
                    @forelse($resumen['actividad'] as $evento)
                        <li class="spd-evento">
                            <span class="spd-evento-marca {{ $evento['tipo'] === 'produccion' ? 'spd-marca-produccion' : 'spd-marca-insumo' }}" aria-hidden="true"></span>
                            <div class="spd-evento-contenido">
                                <div class="spd-evento-linea"><strong>{{ $evento['titulo'] }}</strong><time>{{ $evento['momento']?->format('d/m H:i') ?? '—' }}</time></div>
                                <p>{{ $evento['detalle'] }}</p>
                                <small>Por: {{ $evento['autor'] }}</small>
                            </div>
                        </li>
                    @empty
                        <li class="spd-vacio"><strong>Sin actividad registrada</strong><p>Los registros nuevos se mostrarán aquí.</p></li>
                    @endforelse
                </ol>
                <div class="spd-actividad-enlaces"><a href="{{ route('secretaria.produccion') }}">Ver producción <span aria-hidden="true">›</span></a><a href="{{ route('secretaria.insumos') }}">Ver insumos <span aria-hidden="true">›</span></a></div>
            </article>
        </section>

        <section class="spd-panel spd-resumen" aria-labelledby="spd-resumen-titulo">
            <div class="spd-cabecera">
                <span class="spd-enc-icono" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 20V8M9 20V13M14 20V4M19 20v-9"/></svg></span>
                <div><h2 id="spd-resumen-titulo">Resumen del mes</h2><p>Producción diaria registrada y evolución de litros equivalentes</p></div>
            </div>
            <div class="spd-resumen-interior">
                <div class="spd-resumen-total">
                    <span>Producción de {{ $resumen['periodo_etiqueta'] }}</span>
                    <strong>{{ number_format((float) $resumen['litros'], 2, ',', '.') }} <small>L</small></strong>
                    <p>{{ number_format($resumen['consumo'], 0, ',', '.') }} unidades · {{ $resumen['registros'] }} registros diarios</p>
                </div>
                <div class="spd-resumen-grafico">
                    <h3>Litros de los últimos seis meses hasta el período seleccionado</h3>
                    @if($resumen['hay_datos_grafico'])
                        <div class="spd-grafico" role="img" aria-label="Evolución de litros de producción diaria en los seis meses anteriores o iguales al seleccionado">
                            @foreach($resumen['meses'] as $mes)
                                <div class="spd-columna" title="{{ $mes['etiqueta'] }}: {{ number_format($mes['litros'], 2, ',', '.') }} litros">
                                    <span class="spd-valor">{{ number_format($mes['litros'], 0, ',', '.') }} L</span>
                                    <div class="spd-barra-pista"><span class="spd-barra" style="height: {{ $mes['litros'] > 0 ? max(3, $mes['porcentaje']) : 0 }}%"></span></div>
                                    <small>{{ $mes['etiqueta'] }}</small>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="spd-grafico-vacio">Aún no hay producción diaria registrada en estos seis meses.</div>
                    @endif
                </div>
            </div>
            <p class="spd-nota">Este panel utiliza los registros de producción diaria. Los consumos mensuales históricos se consultan por separado en Reportes, para no duplicar cifras.</p>
        </section>
    </main>
</div>
@endsection
