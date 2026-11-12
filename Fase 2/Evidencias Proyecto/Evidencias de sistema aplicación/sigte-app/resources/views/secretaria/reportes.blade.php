@if ($modo_pdf ?? false)
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>SIGTE — Reporte de {{ ucfirst($tab) }}</title>
    <style>
        @page {
            margin: 28px 34px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #17263a;
            font-size: 9px;
        }

        h1 {
            font-size: 17px;
            color: #143b5c;
            margin: 0 0 6px;
        }

        h2 {
            font-size: 12px;
            color: #143b5c;
            margin: 17px 0 6px;
        }

        p {
            margin: 3px 0;
        }

        .meta {
            color: #576579;
            margin-bottom: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 12px;
        }

        th,
        td {
            border: 1px solid #cfdae7;
            padding: 5px 7px;
            vertical-align: top;
        }

        th {
            background: #e6edf5;
            text-align: left;
        }

        tr {
            page-break-inside: avoid;
        }

        .numero {
            text-align: right;
            white-space: nowrap;
        }

        .resumen {
            width: 58%;
        }

        .nota {
            font-size: 8px;
            color: #66788b;
        }

        .salto {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <h1>SIGTE — Reporte de {{ ucfirst($tab) }}</h1>
    <p class="meta">Hospital San José de Melipilla · Período consultado: {{ $filtros['desde'] }} al {{ $filtros['hasta'] }}</p>
    <p class="meta"><strong>Generado por:</strong> {{ auth()->user()?->name ?? 'Usuario no identificado' }} · <strong>Fecha y hora:</strong> {{ $fecha_generacion }} (Chile)</p>
    <h2>Resumen</h2>
    <table class="resumen">
        <thead>
            <tr>
                <th>Indicador</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resumen as $fila)
            <tr>
                <td>{{ $fila[0] }}</td>
                <td class="numero">{{ is_float($fila[1]) ? number_format($fila[1], 2, ',', '.') : number_format($fila[1], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if ($tab === 'produccion')
    <h2>Distribución de cajas en el flujo (estado actual)</h2>
    <p class="nota">Las cifras del flujo son actuales y no dependen de la fecha elegida. El filtro de servicio, si se seleccionó, sí aplica.</p>
    <table>
        <thead>
            <tr>
                <th>Etapa</th>
                <th>Cajas actuales</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['en_flujo'] as $fila)
            <tr>
                <td>{{ $fila['etapa'] }}</td>
                <td class="numero">{{ $fila['cantidad'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2">No hay cajas actualmente en flujo.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <h2>Producción mensual por servicio</h2>
    <table>
        <thead>
            <tr>
                <th>Mes</th>
                <th>Servicio</th>
                <th>Unidades</th>
                <th>Litros</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['produccion']['detalle'] as $fila)
            <tr>
                <td>{{ $fila->mes }}</td>
                <td>{{ $fila->servicio }}</td>
                <td class="numero">{{ number_format($fila->cantidad, 0, ',', '.') }}</td>
                <td class="numero">{{ number_format($fila->litros, 2, ',', '.') }} L</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No hay producción registrada en el período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @elseif ($tab === 'trazabilidad')
    <h2>Movimientos por tipo</h2>
    <table class="resumen">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['por_tipo'] as $tipo => $cantidad)
            <tr>
                <td>{{ \App\Models\RegistroAuditoria::TIPOS[$tipo] ?? $tipo }}</td>
                <td class="numero">{{ $cantidad }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2">No hay movimientos en el período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <h2>Historial de movimientos</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Tipo</th>
                <th>Objeto</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['movimientos'] as $fila)
            <tr>
                <td>{{ $fila['fecha'] }}</td>
                <td>{{ $fila['hora'] }}</td>
                <td>{{ $fila['actor'] }}</td>
                <td>{{ $fila['accion'] }}</td>
                <td>{{ $fila['tipo_etiqueta'] }}</td>
                <td>{{ $fila['objeto'] }}</td>
                <td>{{ $fila['detalle'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7">No hay movimientos de auditoría.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @else
    <h2>Cajas entregadas por servicio de destino</h2>
    <table class="resumen">
        <thead>
            <tr>
                <th>Servicio</th>
                <th>Cajas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['entregas_por_servicio'] as $nombre => $cantidad)
            <tr>
                <td>{{ $nombre }}</td>
                <td class="numero">{{ $cantidad }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2">No hay cajas entregadas en el período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <p class="nota">Una operación puede incluir varias cajas o solamente material. Por eso las operaciones y las cajas entregadas se cuentan por separado.</p>
    <h2>Detalle de cajas entregadas</h2>
    <table>
        <thead>
            <tr>
                <th>Caja</th>
                <th>Operación</th>
                <th>Servicio destino</th>
                <th>Fecha</th>
                <th>Responsable</th>
                <th>Recibe</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['entregas'] as $fila)
            <tr>
                <td>{{ $fila['codigo'] }}</td>
                <td>{{ $fila['operacion'] }}</td>
                <td>{{ $fila['servicio'] }}</td>
                <td>{{ $fila['cuando'] }}</td>
                <td>{{ $fila['responsable'] }}</td>
                <td>{{ $fila['recibe'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6">No hay cajas entregadas en el período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif
</body>

</html>

@else
@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/secretaria.css') }}?v={{ @filemtime(public_path('css/secretaria.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}?v={{ @filemtime(public_path('css/reportes.css')) }}">
@endpush

@section('title', 'SIGTE — Reportes')

@section('content')
<div class="shell">
    @include('secretaria.partials.sidebar')

    <main class="main reportes-pagina reportes-secretaria">
        <div class="main-top">
            <div>
                <p class="eyebrow">Consolidación estadística</p>
                <h1>Reportes</h1>
                <p class="main-sub">Producción, trazabilidad y entregas. Consulta los registros por período y genera reportes en Excel o PDF.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'secretaria') }}">Volver al inicio</a>
                <a class="btn btn-dark" href="{{ route('secretaria.produccion') }}">Registrar producción</a>
            </div>
        </div>

<nav class="reportes-tabs" aria-label="Tipos de reportes" role="tablist">

    <button class="reportes-tab activo" type="button" role="tab"
        id="tab-produccion" aria-selected="true"
        aria-controls="panel-produccion" data-tab="produccion">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="M3 21V9l6 4V9l6 4V5l6 4v12H3Z" />
            <path d="M7 17h2M12 17h2M17 17h2" />
        </svg>
        <span>Producción</span>
    </button>

    <button class="reportes-tab" type="button" role="tab"
        id="tab-trazabilidad" aria-selected="false"
        aria-controls="panel-trazabilidad" data-tab="trazabilidad">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <circle cx="6" cy="18" r="3" />
            <circle cx="18" cy="6" r="3" />
            <path d="M6 15V9a3 3 0 0 1 3-3h6" />
            <path d="M18 9v6a3 3 0 0 1-3 3h-6" />
        </svg>
        <span>Trazabilidad</span>
    </button>

    <button class="reportes-tab" type="button" role="tab"
        id="tab-entregas" aria-selected="false"
        aria-controls="panel-entregas" data-tab="entregas">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="m12 3 9 5v8l-9 5-9-5V8l9-5Z" />
            <path d="m3 8 9 5 9-5" />
            <path d="M12 13v8" />
            <path d="m9 5 9 5" />
        </svg>
        <span>Entregas</span>
    </button>

</nav>

<form id="reportes-filtros-form" class="panel reportes-filtros" method="GET" action="{{ route('secretaria.reportes') }}">
    <input type="hidden" name="tab" id="reportes-tab-seleccionada" value="{{ $tab_activo }}">

    <!-- DESCRIPCIÓN DE PRODUCCIÓN -->
    <div class="reportes-section-head reportes-descripcion-produccion"
        data-tab-descripcion="produccion">
        <div>
            <p>Consulta la producción registrada en el período seleccionado y revisa el procesamiento de cajas y los resultados por servicio.</p>
        </div>
    </div>

    <!-- DESCRIPCIÓN DE TRAZABILIDAD -->
    <div class="reportes-section-head reportes-descripcion-trazabilidad"
        data-tab-descripcion="trazabilidad">
        <div>
            <p>Consulta las acciones realizadas en el sistema durante el período seleccionado y filtra los registros según el tipo de movimiento.</p>
        </div>
    </div>

    <!-- DESCRIPCIÓN DE ENTREGAS -->
    <div class="reportes-section-head reportes-descripcion-entregas"
        data-tab-descripcion="entregas">
        <div>
            <p>Consulta las cajas de instrumental quirúrgico entregadas a los servicios durante el período seleccionado.</p>
        </div>
    </div>

    <!-- CAMPOS DE FILTRO -->
    <div class="reportes-campos reportes-campos-produccion">

        <label class="reportes-campo">
            Desde
            <input type="date" name="desde" value="{{ $filtros['desde'] }}" required>
        </label>

        <label class="reportes-campo">
            Hasta
            <input type="date" name="hasta" value="{{ $filtros['hasta'] }}" required>
        </label>

        <label class="reportes-campo reportes-filtro-tipo">
            Tipo de movimiento
            <select name="tipo">
                <option value="">Todos los tipos</option>
                @foreach ($tipos_auditoria as $clave => $nombre)
                <option value="{{ $clave }}" @selected($filtros['tipo']===$clave)>
                    {{ $nombre }}
                </option>
                @endforeach
            </select>
        </label>

        <label class="reportes-campo reportes-filtro-servicio">
            Servicio
            <select name="servicio">
                <option value="">Todos los servicios</option>
                @foreach ($servicios_produccion as $servicio)
                <option value="{{ $servicio->id }}" @selected(($filtros['servicio'] ?? null)===(int) $servicio->id)>
                    {{ $servicio->nombre }}
                </option>
                @endforeach
            </select>
        </label>

        <button class="btn btn-dark reportes-consultar" type="submit">
            Consultar registros
        </button>
    </div>

    <!-- AVISO INFORMATIVO DE PRODUCCIÓN -->
    <div class="reportes-ayuda reportes-aviso-produccion"
        data-tab-descripcion="produccion">
        <span class="reportes-aviso-produccion-icono" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="M12 11v5M12 8h.01"></path>
            </svg>
        </span>
        <p>La información se obtiene de los registros diarios de producción y del seguimiento de las cajas procesadas durante el período consultado.</p>
    </div>

    <!-- AVISO INFORMATIVO DE TRAZABILIDAD -->
    <div class="reportes-ayuda reportes-aviso-procesamiento"
        data-tab-descripcion="trazabilidad">
        <span class="reportes-aviso-produccion-icono" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="M12 11v5M12 8h.01"></path>
            </svg>
        </span>
        <p>Los resultados muestran las acciones registradas en el sistema, junto con su fecha, usuario, tipo y detalle.</p>
    </div>

    <!-- AVISO INFORMATIVO DE ENTREGAS -->
    <div class="reportes-ayuda reportes-aviso-entregas"
        data-tab-descripcion="entregas">
        <span class="reportes-aviso-produccion-icono" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="M12 11v5M12 8h.01"></path>
            </svg>
        </span>
        <p>El reporte considera las cajas entregadas, sus servicios de destino y las operaciones registradas durante el período consultado.</p>
    </div>
</form>

@if ($falla)
<div class="panel reportes-estado"><strong>No se pudo consultar la información.</strong>
    <p>Revisa la conexión a la base de datos e inténtalo nuevamente.</p>
</div>
@else
@php
$totalCajasFlujo = collect($en_flujo)->sum('cantidad');
$coloresFlujo = ['#3998ed', '#78b9f5', '#f5c44b', '#f48aa4', '#a98add', '#9edb91'];
$segmentosFlujo = [];
$inicioFlujo = 0;
foreach ($en_flujo as $indiceFlujo => $filaFlujo) {
$porcentajeFlujo = $totalCajasFlujo > 0 ? (float) $filaFlujo['cantidad'] / $totalCajasFlujo * 100 : 0;
$colorFlujo = $coloresFlujo[$indiceFlujo % count($coloresFlujo)];
$segmentosFlujo[] = $colorFlujo . ' ' . $inicioFlujo . '% ' . ($inicioFlujo + $porcentajeFlujo) . '%';
$inicioFlujo += $porcentajeFlujo;
}
$graficoFlujo = count($segmentosFlujo) ? 'conic-gradient(' . implode(', ', $segmentosFlujo) . ')' : 'conic-gradient(#e2e8f0 0% 100%)';
@endphp
<section class="reportes-panel activo" id="panel-produccion" role="tabpanel" aria-labelledby="tab-produccion">
    <div class="reportes-section-head reportes-titulo-modulo">
        <div>
            <h2>
                <svg class="reportes-icono" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M3 3v18h18" />
                    <path d="M18 17V9M13 17V5M8 17v-3" />
                </svg>
                Reporte de producción
            </h2>
            <p>Consulta la producción mensual y el procesamiento de cajas en secciones independientes.</p>
        </div>
    </div>

    <article class="panel reportes-bloque reportes-bloque-moderno">

        <!-- PROCESAMIENTO ACTUAL DE CAJAS -->
        <div class="reportes-banda reportes-banda-procesamiento">
            <div>
                <h3>
                    <span class="reportes-banda-icono">
                        <svg class="reportes-icono" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                            <path d="m3.3 7 8.7 5 8.7-5M12 22V12" />
                        </svg>
                    </span>
                    <span>1. Procesamiento de cajas</span>
                </h3>
            </div>
        </div>

        <div class="reportes-produccion-grid">
            <div class="reportes-produccion-principal">
                <div class="reportes-resumen reportes-resumen-compacto">
                    <div class="reportes-kpi reportes-kpi-azul"><svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                            <path d="m3.3 7 8.7 5 8.7-5M12 22V12" />
                        </svg>
                        <div class="reportes-kpi-texto"><span>Cajas actualmente en flujo</span><strong>{{ $totalCajasFlujo }}</strong></div>
                    </div>
                    <div class="reportes-kpi reportes-kpi-verde"><svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2" />
                            <path d="M16 3v4M8 3v4M3 10h18" />
                            <path d="m9 16 2 2 4-4" />
                        </svg>
                        <div class="reportes-kpi-texto"><span>Cajas recibidas en el período</span><strong>{{ $recibidas_periodo }}</strong><small>Según el rango de fechas seleccionado</small></div>
                    </div>
                    <div class="reportes-kpi reportes-kpi-violeta"><svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m12 2 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 17l9 5 9-5" />
                        </svg>
                        <div class="reportes-kpi-texto"><span>Etapas con cajas</span><strong>{{ count($en_flujo) }}</strong></div>
                    </div>
                </div>
                <h4 class="reportes-titulo-detalle">Cajas por etapa (flujo actual)</h4>
                <div class="reportes-etapas" aria-label="Etapas del procesamiento">
                    @foreach (['Recepción', 'Lavado', 'Preparación', 'Esterilización', 'Almacén', 'Entrega'] as $indiceEtapa => $nombreEtapa)
                    @php
                    $filaEtapa = collect($en_flujo)->first(fn ($item) => mb_strtolower(trim($item['etapa'])) === mb_strtolower($nombreEtapa));
                    @endphp
                    <div class="reportes-etapa reportes-etapa-{{ $indiceEtapa }}">
                        @if ($indiceEtapa === 0)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                            <path d="m3.3 7 8.7 5 8.7-5M12 22V12" />
                        </svg>@endif
                        @if ($indiceEtapa === 1)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 22a8 8 0 0 0 8-8c0-4-8-12-8-12S4 10 4 14a8 8 0 0 0 8 8Z" />
                        </svg>@endif
                        @if ($indiceEtapa === 2)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="6" cy="6" r="3" />
                            <circle cx="6" cy="18" r="3" />
                            <path d="m8.12 8.12 12.02 12.02M14.8 9.2 20.14 3.86M8.12 15.88 12 12" />
                        </svg>@endif
                        @if ($indiceEtapa === 3)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M8 3c-4 4 4 5 0 9s-2 5 0 9M14 3c-4 4 4 5 0 9s-2 5 0 9M20 3c-4 4 4 5 0 9s-2 5 0 9" />
                        </svg>@endif
                        @if ($indiceEtapa === 4)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="1" />
                            <path d="M3 12h18M8 5v7M16 12v9M7 16h5" />
                        </svg>@endif
                        @if ($indiceEtapa === 5)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 5h11v12H3zM14 9h4l3 4v4h-7z" />
                            <circle cx="7.5" cy="18" r="2" />
                            <circle cx="17.5" cy="18" r="2" />
                        </svg>@endif
                        <div class="reportes-etapa-texto"><span>{{ $nombreEtapa }}</span><strong>{{ $filaEtapa['cantidad'] ?? 0 }}</strong></div>
                    </div>
                    @if ($indiceEtapa < 5)<span class="reportes-etapa-flecha" aria-hidden="true">›</span>@endif
                        @endforeach
                </div>
                <div class="reportes-tabla reportes-tabla-compacta">
                    <table>
                        <thead>
                            <tr>
                                <th>Etapa actual</th>
                                <th>Cantidad de cajas</th>
                                <th>% del total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($en_flujo as $indice => $fila)
                            <tr>
                                <td><span class="reportes-punto" style="background: {{ $coloresFlujo[$indice % count($coloresFlujo)] }}"></span>{{ $fila['etapa'] }}</td>
                                <td>{{ $fila['cantidad'] }}</td>
                                <td>{{ $totalCajasFlujo ? number_format($fila['cantidad'] / $totalCajasFlujo * 100, 1, ',', '.') : 0 }} %</td>
                            </tr>
                            @empty <tr>
                                <td colspan="3" class="reportes-vacio">No hay cajas actualmente en flujo.</td>
                            </tr> @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="reportes-distribucion">
                <h4 class="reportes-titulo-detalle">Distribución de cajas en el flujo</h4>
                <div class="reportes-grafico-area">
                    <div class="reportes-dona" style="background: {{ $graficoFlujo }}" role="img" aria-label="Distribución de {{ $totalCajasFlujo }} cajas entre {{ count($en_flujo) }} etapas">
                        <div class="reportes-dona-centro"><strong>{{ $totalCajasFlujo }}</strong><span>cajas</span></div>
                    </div>
                    <div class="reportes-leyenda">
                        @forelse ($en_flujo as $indice => $fila)
                        <div><span class="reportes-punto" style="background: {{ $coloresFlujo[$indice % count($coloresFlujo)] }}"></span><span>{{ $fila['etapa'] }}</span><strong>{{ $fila['cantidad'] }}</strong></div>
                        @empty <p class="reportes-nota">Sin datos disponibles.</p> @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="reportes-ayuda reportes-aviso-procesamiento">
            <span class="reportes-aviso-produccion-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 11v5M12 8h.01"></path>
                </svg>
            </span>
            <p>Las cantidades reflejan la ubicación actual de las cajas en el flujo de procesamiento; no corresponden a movimientos históricos del período seleccionado.</p>
        </div>
    </article>

    @php
    $historicoEtapas = collect($cajas_por_etapa ?? []);
    $totalHistoricoEtapas = $historicoEtapas->sum('cantidad');
    @endphp

    <article class="panel reportes-bloque reportes-bloque-moderno">
        <div class="reportes-banda reportes-banda-procesamiento">
            <div>
                <h3>
                    <span class="reportes-banda-icono">
                        <svg class="reportes-icono" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M3 12a9 9 0 1 0 2.64-6.36L3 8" />
                            <path d="M3 3v5h5" />
                            <path d="M12 7v5l3 2" />
                        </svg>
                    </span>
                    <span>2. Procesamiento histórico por etapa</span>
                </h3>
            </div>
        </div>

        <div class="reportes-resumen reportes-resumen-compacto">
            <div class="reportes-kpi reportes-kpi-azul">
                <div class="reportes-kpi-texto">
                    <span>Ingresos registrados</span>
                    <strong>{{ number_format($totalHistoricoEtapas, 0, ',', '.') }}</strong>
                    <small>Suma de ingresos contabilizados en las distintas etapas.</small>
                </div>
            </div>
        </div>

        <div class="reportes-produccion-card reportes-produccion-detalle">
            <h4 class="reportes-titulo-detalle">Detalle de ingresos por etapa</h4>

            <div class="reportes-tabla reportes-tabla-compacta">
                <table>
                    <thead>
                        <tr>
                            <th>Etapa</th>
                            <th>Cajas ingresadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($historicoEtapas as $fila)
                        <tr>
                            <td>{{ $fila['etapa'] }}</td>
                            <td>{{ number_format($fila['cantidad'], 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="reportes-vacio">
                                No hay registros históricos para este período.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="reportes-ayuda reportes-aviso-procesamiento">
            <span class="reportes-aviso-produccion-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 11v5M12 8h.01"></path>
                </svg>
            </span>
            <p>
                Cada caja se contabiliza una vez por etapa, según los registros históricos del período seleccionado.
            </p>
        </div>
    </article>

    <article class="panel reportes-bloque reportes-bloque-moderno">
        <div class="reportes-banda">
            <h3><span class="reportes-banda-icono"><svg class="reportes-icono " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 3h6M10 3v7l-6 9a2 2 0 0 0 2 3h12a2 2 0 0 0 2-3l-6-9V3M8 16h8" />
                    </svg></span> 3. Producción mensual por servicio</h3>
        </div>
        <div class="reportes-resumen reportes-resumen-compacto reportes-produccion-kpis">
            <div class="reportes-kpi reportes-kpi-azul">
                <div class="reportes-kpi-texto"><span>Servicios con registros</span><strong>{{ number_format($produccion['servicios'], 0, ',', '.') }}</strong></div>
            </div>
            <div class="reportes-kpi reportes-kpi-verde">
                <div class="reportes-kpi-texto"><span>Unidades procesadas</span><strong>{{ number_format($produccion['cantidad'], 0, ',', '.') }}</strong></div>
            </div>
            <div class="reportes-kpi reportes-kpi-violeta">
                <div class="reportes-kpi-texto"><span>Litros totales</span><strong>{{ number_format($produccion['litros'], 2, ',', '.') }} L</strong></div>
            </div>
            <div class="reportes-kpi reportes-kpi-naranja">
                <div class="reportes-kpi-texto"><span>Materiales distintos</span><strong>{{ number_format($produccion['materiales'], 0, ',', '.') }}</strong></div>
            </div>
        </div>
        <div class="reportes-produccion-analitica">
            <div class="reportes-produccion-card">
                <h4 class="reportes-titulo-detalle">Litros por servicio (período)</h4>
                @php $maxLitrosServicio = max(1, (float) $produccion['por_servicio']->max('litros')); @endphp
                @forelse ($produccion['por_servicio'] as $servicio)
                <div class="reportes-produccion-barra-fila">
                    <span title="{{ $servicio->nombre }}">{{ $servicio->nombre }}</span>
                    <div class="reportes-produccion-barra-fondo">
                        <div style="width: {{ round(((float) $servicio->litros / $maxLitrosServicio) * 100, 2) }}%"></div>
                    </div>
                    <strong>{{ number_format($servicio->litros, 2, ',', '.') }} L</strong>
                </div>
                @empty
                <p class="reportes-vacio">No hay registros de producción para este período.</p>
                @endforelse
            </div>
            <div class="reportes-produccion-card">
                <h4 class="reportes-titulo-detalle">Evolución mensual de litros</h4>
                @php $maxLitrosMes = max(1, (float) $produccion['por_mes']->max('litros')); @endphp
                @if ($produccion['por_mes']->isNotEmpty())
                <div class="reportes-produccion-meses">
                    @foreach ($produccion['por_mes'] as $mes)
                    <div class="reportes-produccion-mes" title="{{ $mes->mes }}: {{ number_format($mes->litros, 2, ',', '.') }} litros">
                        <strong>{{ number_format($mes->litros, 0, ',', '.') }} L</strong>
                        <div class="reportes-produccion-mes-columna"><span style="height: {{ max(2, round(((float) $mes->litros / $maxLitrosMes) * 100, 2)) }}%"></span></div>
                        <small>{{ $mes->mes }}</small>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="reportes-vacio">No hay registros para graficar en el período seleccionado.</p>
                @endif
            </div>
        </div>
        <div class="reportes-produccion-card reportes-produccion-detalle">
            <h4 class="reportes-titulo-detalle">Detalle mensual por servicio</h4>
            <div class="reportes-tabla reportes-tabla-compacta">
                <table>
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th>Servicio</th>
                            <th>Unidades</th>
                            <th>Litros</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($produccion['detalle'] as $fila)
                        <tr>
                            <td>{{ $fila->mes }}</td>
                            <td>{{ $fila->servicio }}</td>
                            <td>{{ number_format($fila->cantidad, 0, ',', '.') }}</td>
                            <td>{{ number_format($fila->litros, 2, ',', '.') }} L</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="reportes-vacio">No hay registros de producción para las fechas y el servicio seleccionados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="reportes-ayuda reportes-aviso-procesamiento">
            <span class="reportes-aviso-produccion-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 11v5M12 8h.01"></path>
                </svg>
            </span>
            <p>La producción se agrupa por mes y servicio, según los consumos y litros registrados durante el período seleccionado.</p>
        </div>
    </article>
</section>

<section class="reportes-panel" id="panel-trazabilidad" role="tabpanel" aria-labelledby="tab-trazabilidad" hidden>
    @php
    // Los resúmenes son totales SQL del período; la tabla se pagina aparte.
    $trazaTotal = (int) $total_movimientos;
    $trazaTipos = collect($por_tipo)->mapWithKeys(fn ($cantidad, $tipo) => [($tipos_auditoria[$tipo] ?? $tipo) => (int) $cantidad]);
    $trazaPaleta = ['#4e74d9', '#78b9f5', '#f5c44b', '#f48aa4', '#a98add', '#9edb91', '#f3a66e'];
    $trazaSegmentos = [];
    $trazaAcumulado = 0;
    $trazaIndiceColor = 0;
    foreach ($trazaTipos as $grupoCantidad) {
    $trazaParte = $trazaTotal ? $grupoCantidad / $trazaTotal * 100 : 0;
    $trazaColor = $trazaPaleta[$trazaIndiceColor % count($trazaPaleta)];
    $trazaSegmentos[] = $trazaColor . ' ' . $trazaAcumulado . '% ' . ($trazaAcumulado + $trazaParte) . '%';
    $trazaAcumulado += $trazaParte;
    $trazaIndiceColor++;
    }
    $trazaDona = $trazaTotal ? 'conic-gradient(' . implode(', ', $trazaSegmentos) . ')' : '#e2e8f0';
    $trazaPorDia = collect($movimientos_por_dia);
    $trazaMaxDia = max(1, (int) $trazaPorDia->max());
    @endphp
    <div class="reportes-section-head reportes-traza-encabezado">
        <div class="reportes-traza-titulo">
            <svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="4" r="3" />
                <circle cx="5" cy="19" r="3" />
                <circle cx="19" cy="19" r="3" />
                <path d="M10.5 6.7 6.5 16M13.5 6.7l4 9.3M8 19h8" />
            </svg>
            <div>
                <h2>Reporte de trazabilidad</h2>
                <p>Acciones de auditoría registradas dentro del período seleccionado.</p>
            </div>
        </div>
    </div>

    <article class="panel reportes-traza-panel">
        <div class="reportes-traza-grid">
            <div class="reportes-traza-izquierda">
                <div class="reportes-resumen reportes-traza-kpis">
                    <div class="reportes-kpi reportes-kpi-azul">
                        <svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="5" y="4" width="14" height="18" rx="2" />
                            <rect x="9" y="2" width="6" height="4" rx="1" />
                            <path d="M9 11h6M9 15h6M9 19h4" />
                        </svg>
                        <div class="reportes-kpi-texto"><span>Registros del período</span><strong>{{ $trazaTotal }}</strong></div>
                    </div>
                    <div class="reportes-kpi reportes-kpi-violeta">
                        <svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m12 2 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 17l9 5 9-5" />
                        </svg>
                        <div class="reportes-kpi-texto"><span>Tipos encontrados</span><strong>{{ $trazaTipos->count() }}</strong></div>
                    </div>
                    <div class="reportes-kpi reportes-kpi-naranja">
                        <svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 12a9 9 0 0 1 15-6l3 3M21 3v6h-6M21 12a9 9 0 0 1-15 6l-3-3M3 21v-6h6" />
                        </svg>
                        <div class="reportes-kpi-texto"><span>Retrocesos estructurados</span><strong>{{ $retrocesos_periodo }}</strong><small>Registrados durante el período</small></div>
                    </div>
                </div>
                <div class="reportes-traza-serie">
                    <h3>Registros por fecha</h3>
                    @if ($trazaPorDia->isNotEmpty())
                    <div class="reportes-traza-barras" role="img" aria-label="Cantidad de registros de auditoría por fecha">
                        @foreach ($trazaPorDia as $dia => $cantidad)
                        <div class="reportes-traza-barra-col" title="{{ $dia }}: {{ $cantidad }} registros">
                            <strong>{{ $cantidad }}</strong>
                            <div class="reportes-traza-barra" style="height: {{ max(5, round($cantidad / $trazaMaxDia * 100)) }}%"></div>
                            <span>{{ $dia }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="reportes-traza-sin-datos">No hay registros en el período seleccionado.</div>
                    @endif
                </div>
            </div>
            <div class="reportes-traza-derecha">
                <h3>Movimientos por tipo</h3>
                <div class="reportes-traza-distribucion">
                    <div class="reportes-dona reportes-traza-dona" style="background: {{ $trazaDona }}" role="img" aria-label="{{ $trazaTotal }} registros distribuidos por tipo">
                        <div class="reportes-dona-centro"><strong>{{ $trazaTotal }}</strong><span>registros</span></div>
                    </div>
                    <div class="reportes-traza-leyenda">
                        @forelse ($trazaTipos as $tipo => $grupo)
                        <div><span class="reportes-punto" style="background: {{ $trazaPaleta[$loop->index % count($trazaPaleta)] }}"></span><span>{{ $tipo }}</span><strong>{{ $trazaTotal ? number_format($grupo / $trazaTotal * 100, 1, ',', '.') : 0 }} %</strong></div>
                        @empty
                        <p class="reportes-nota">Sin movimientos registrados.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </article>

    <article class="panel reportes-bloque reportes-traza-detalle">
        <div class="reportes-traza-detalle-cabecera">
            <svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M9 6h12M9 12h12M9 18h12" />
                <circle cx="4" cy="6" r="1" />
                <circle cx="4" cy="12" r="1" />
                <circle cx="4" cy="18" r="1" />
            </svg>
            <div>
                <h3>Detalle de movimientos</h3>
                <p class="reportes-nota">Historial paginado. Los indicadores incluyen todos los registros del período.</p>
            </div>
        </div>
        <div class="reportes-tabla reportes-traza-tabla">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Tipo</th>
                        <th>Objeto</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movimientos as $fila)
                    <tr>
                        <td>{{ $fila['fecha'] }}</td>
                        <td>{{ $fila['hora'] }}</td>
                        <td><span class="reportes-traza-usuario"><span class="reportes-traza-avatar">{{ mb_strtoupper(mb_substr((string) ($fila['actor'] ?? '?'), 0, 1)) }}</span>{{ $fila['actor'] }}</span></td>
                        <td><span class="reportes-traza-accion">{{ $fila['accion'] }}</span></td>
                        <td>{{ $fila['tipo_etiqueta'] }}</td>
                        <td>{{ $fila['objeto'] }}</td>
                        <td>{{ $fila['detalle'] }}</td>
                    </tr>
                    @empty <tr>
                        <td colspan="7" class="reportes-vacio">No hay movimientos de auditoría para estos filtros.</td>
                    </tr> @endforelse
                </tbody>
            </table>
        </div>
        <div class="reportes-paginacion">
            <span>Mostrando {{ $movimientos->firstItem() ?? 0 }}–{{ $movimientos->lastItem() ?? 0 }} de {{ $total_movimientos }} registros</span>
            <nav aria-label="Paginación de movimientos">
                @if ($movimientos->previousPageUrl())<a class="btn btn-ghost" href="{{ $movimientos->previousPageUrl() }}">Anterior</a>@endif
                <span>Página {{ $movimientos->currentPage() }} de {{ $movimientos->lastPage() }}</span>
                @if ($movimientos->nextPageUrl())<a class="btn btn-ghost" href="{{ $movimientos->nextPageUrl() }}">Siguiente</a>@endif
            </nav>
        </div>
    </article>
</section>

<section class="reportes-panel reportes-entregas" id="panel-entregas" role="tabpanel" aria-labelledby="tab-entregas" hidden>
    @php
    $entregasListado = $entregas;
    $entregasCantidad = (int) $total_cajas_entregadas;
    $entregasServicios = collect($entregas_por_servicio)->sortDesc();
    $entregasServiciosValidos = $entregasServicios->keys()->filter(fn ($nombre) => trim((string) $nombre) !== '')->count();
    $entregasColores = ['#398ff3', '#f5bc40', '#f48cac', '#a877ec', '#36b8a5', '#f18a51'];
    $entregasPorFecha = collect($entregas_por_fecha);
    $entregasDiaMaximo = $entregasPorFecha->max() ?? 0;
    $entregasFechaMaxima = $entregasPorFecha->filter(fn ($valor) => $valor === $entregasDiaMaximo)->keys()->first();
    $entregasDiasConActividad = $entregasPorFecha->count();
    $entregasPromedio = $entregasDiasConActividad ? $entregasCantidad / $entregasDiasConActividad : 0;
    $entregasInicio = \Carbon\Carbon::parse($filtros['desde'])->startOfDay();
    $entregasFin = \Carbon\Carbon::parse($filtros['hasta'])->startOfDay();
    $entregasTotalDias = (int) $entregasInicio->diffInDays($entregasFin) + 1;
    $entregasMostrarCalendario = $entregasTotalDias >= 1 && $entregasTotalDias <= 42;
        @endphp

        <div class="reportes-section-head reportes-entregas-encabezado">
        <div class="reportes-entregas-titulo">
            <svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 5h11v12H3zM14 9h4l3 4v4h-7z" />
                <circle cx="7.5" cy="18" r="2" />
                <circle cx="17.5" cy="18" r="2" />
            </svg>
            <div>
                <h2>Reporte de entregas</h2>
                <p>Resumen de cajas entregadas y distribución por servicio durante el período.</p>
            </div>
        </div>
        </div>

        <div class="reportes-entregas-kpis">
            <div class="reportes-entregas-kpi reportes-entregas-kpi-principal">
                <img class="reportes-entregas-bandeja" src="{{ asset('images/Reportes/reportes_entrega_decoracion_1.png') }}" alt="Caja metálica de esterilización para instrumental quirúrgico">
                <div class="reportes-entregas-kpi-contenido"><span>Cajas entregadas</span><strong>{{ $entregasCantidad }}</strong><small>Total del período</small></div>
            </div>
            <div class="reportes-entregas-kpi reportes-entregas-kpi-operaciones">
                <svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="5" y="4" width="14" height="18" rx="2" />
                    <rect x="9" y="2" width="6" height="4" rx="1" />
                    <path d="m9 14 2 2 4-4" />
                </svg>
                <div><span>Operaciones de entrega</span><strong>{{ $operaciones_entrega }}</strong><small>Incluye entregas solo de material</small></div>
            </div>
            <div class="reportes-entregas-kpi reportes-entregas-kpi-servicios">
                <svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="9" cy="8" r="3" />
                    <path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6M17 14a5 5 0 0 1 4 5v1" />
                </svg>
                <div><span>Servicios identificados</span><strong>{{ $entregasServiciosValidos }}</strong><small>Destinos con cajas entregadas</small></div>
            </div>
        </div>

        <div class="reportes-entregas-analitica">
            <article class="panel reportes-entregas-card">
                <h3><svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                        <rect x="3" y="9" width="7" height="12" />
                        <rect x="14" y="3" width="7" height="18" />
                        <path d="M5 13h3M16 7h3" />
                    </svg> Entregas por servicio</h3>
                @forelse ($entregasServicios as $nombreServicio => $cantidadServicio)
                @php $porcentajeServicio = $entregasCantidad ? $cantidadServicio / $entregasCantidad * 100 : 0; $colorServicio = $entregasColores[$loop->index % count($entregasColores)]; @endphp
                <div class="reportes-entregas-servicio-fila">
                    <span class="reportes-entregas-servicio-nombre"><i style="background: {{ $colorServicio }}"></i>{{ $nombreServicio }}</span>
                    <span class="reportes-entregas-servicio-cantidad">{{ $cantidadServicio }} {{ $cantidadServicio === 1 ? 'caja' : 'cajas' }}</span>
                    <div class="reportes-entregas-progreso"><span style="width: {{ $porcentajeServicio }}%; background: {{ $colorServicio }}"></span></div>
                    <strong>{{ number_format($porcentajeServicio, 0) }} %</strong>
                </div>
                @empty
                <div class="reportes-entregas-vacio">No hay entregas por servicio en el período seleccionado.</div>
                @endforelse
            </article>

            <article class="panel reportes-entregas-card">
                <h3><svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2" />
                        <path d="M7 3v4M17 3v4M3 10h18" />
                    </svg> Entregas por día (período)</h3>
                @if ($entregasMostrarCalendario)
                <div class="reportes-entregas-calendario" aria-label="Actividad diaria de entregas">
                    @for ($diaIndice = 0; $diaIndice < $entregasTotalDias; $diaIndice++)
                        @php $diaCalendario=$entregasInicio->copy()->addDays($diaIndice); $diaClave = $diaCalendario->format('Y-m-d'); $cantidadDia = $entregasPorFecha->get($diaClave, 0); $intensidadDia = $entregasDiaMaximo ? $cantidadDia / $entregasDiaMaximo : 0; @endphp
                        <div class="reportes-entregas-dia" title="{{ $diaCalendario->format('d/m/Y') }}: {{ $cantidadDia }} cajas" style="--entregas-intensidad: {{ $intensidadDia }}" aria-label="{{ $diaCalendario->format('d/m/Y') }}: {{ $cantidadDia }} cajas"><span>{{ $diaCalendario->format('d') }}</span></div>
                        @endfor
                </div>
                <p class="reportes-entregas-leyenda-calendario">Menor actividad <span></span><span></span><span></span><span></span> Mayor actividad</p>
                @else
                <div class="reportes-entregas-vacio">Para períodos de más de 42 días, consulta el detalle de entregas.</div>
                @endif
                <div class="reportes-entregas-datos-dia">
                    <div><span>Día con más entregas</span><strong>{{ $entregasFechaMaxima ? \Carbon\Carbon::parse($entregasFechaMaxima)->format('d/m/Y') : '—' }}</strong><small>{{ $entregasDiaMaximo }} cajas</small></div>
                    <div><span>Promedio por día con actividad</span><strong>{{ $entregasDiasConActividad ? number_format($entregasPromedio, 1, ',', '.') : '—' }}</strong><small>Sobre todos los días con actividad</small></div>
                </div>
            </article>
        </div>

        <div class="reportes-entregas-inferior">
            <article class="panel reportes-entregas-card reportes-entregas-ultimas">
                <div class="reportes-entregas-card-cabecera">
                    <h3><svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 2" />
                        </svg> Últimas entregas registradas</h3><span class="reportes-chip">{{ $entregasListado->count() }} mostradas</span>
                </div>
                <div class="reportes-tabla reportes-entregas-tabla">
                    <table>
                        <thead>
                            <tr>
                                <th>Código de caja</th>
                                <th>Operación</th>
                                <th>Servicio destino</th>
                                <th>Fecha de entrega</th>
                                <th>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($entregasListado as $fila)
                            <tr>
                                <td><span class="reportes-entregas-codigo">{{ $fila['codigo'] }}</span></td>
                                <td>{{ $fila['operacion'] }}</td>
                                <td>{{ $fila['servicio'] }}</td>
                                <td>{{ $fila['cuando'] }}</td>
                                <td><span class="reportes-traza-usuario"><span class="reportes-traza-avatar">{{ mb_strtoupper(mb_substr((string) ($fila['responsable'] ?? '?'), 0, 1)) }}</span>{{ $fila['responsable'] }}</span></td>
                            </tr>
                            @empty <tr>
                                <td colspan="5" class="reportes-vacio">No hay cajas entregadas para este período.</td>
                            </tr> @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="reportes-nota">Listado paginado. Los totales incluyen todas las cajas de las operaciones registradas en el período.</p>
                <div class="reportes-paginacion">
                    <span>Mostrando {{ $entregasListado->firstItem() ?? 0 }}–{{ $entregasListado->lastItem() ?? 0 }} de {{ $entregasCantidad }} cajas</span>
                    <nav aria-label="Paginación de entregas">
                        @if ($entregasListado->previousPageUrl())<a class="btn btn-ghost" href="{{ $entregasListado->previousPageUrl() }}">Anterior</a>@endif
                        <span>Página {{ $entregasListado->currentPage() }} de {{ $entregasListado->lastPage() }}</span>
                        @if ($entregasListado->nextPageUrl())<a class="btn btn-ghost" href="{{ $entregasListado->nextPageUrl() }}">Siguiente</a>@endif
                    </nav>
                </div>
            </article>
            <article class="panel reportes-entregas-card reportes-entregas-estado">
                <div class="reportes-entregas-estado-interior">
                    <img class="reportes-entregas-bandeja-estado" src="{{ asset('images/Reportes/reportes_entrega_decoracion_2.png') }}" alt="Bandeja metálica de esterilización con instrumental quirúrgico">
                    <div>
                        <h3>Estado del período</h3>
                        <p>{{ $entregasCantidad > 0 ? 'Se encontraron cajas de instrumental quirúrgico registradas como entregadas en el período seleccionado.' : 'No se encontraron cajas registradas como entregadas para el período seleccionado.' }}</p><small>Las cajas y sus servicios de destino se obtienen de Entrega y EntregaCaja.</small>
                    </div>
                </div>
            </article>
        </div>
</section>

<footer class="panel reportes-pie">
    <div><strong>Hospital San José de Melipilla</strong>
        <p>Generado para consulta por {{ $usuario['nombre'] }} · {{ $usuario['rol'] }} — {{ $fecha }} a las {{ $hora }}</p>
        <p>Período seleccionado: {{ $filtros['desde'] }} al {{ $filtros['hasta'] }}</p>
    </div>
    <div class="reportes-exportar">
        <a class="btn" href="{{ route('secretaria.reportes.excel', array_merge($filtros, ['tab' => $tab_activo])) }}" data-descargar-reporte="excel">Exportar Excel</a>
        <a class="btn" href="{{ route('secretaria.reportes.pdf', array_merge($filtros, ['tab' => $tab_activo])) }}" data-descargar-reporte="pdf">Exportar PDF</a>
    </div>
</footer>

{{-- Aviso visible: muestra progreso, confirmación de archivo recibido y errores de exportación. --}}
<div id="reportes-aviso-descarga" class="reportes-aviso-descarga" role="status" aria-live="polite" aria-atomic="true" hidden>
    <span id="reportes-aviso-icono" class="reportes-aviso-icono" aria-hidden="true"></span>
    <div class="reportes-aviso-textos">
        <strong id="reportes-aviso-titulo"></strong>
        <span id="reportes-aviso-mensaje"></span>
    </div>
    <button type="button" id="reportes-aviso-cerrar" class="reportes-aviso-cerrar" aria-label="Cerrar notificación">×</button>
</div>
<style>
    .reportes-aviso-descarga[hidden] {
        display: none !important;
    }

    .reportes-aviso-descarga {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 9999;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        width: min(430px, calc(100vw - 32px));
        box-sizing: border-box;
        padding: 15px 16px;
        background: #fff;
        color: #14304a;
        border: 1px solid #ccd9e7;
        border-left: 5px solid #377dc3;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .16);
    }

    .reportes-aviso-descarga[data-estado="exito"] {
        border-left-color: #198754;
    }

    .reportes-aviso-descarga[data-estado="error"] {
        border-left-color: #c83b3b;
    }

    .reportes-aviso-icono {
        flex-shrink: 0;
        font-size: 20px;
        font-weight: bold;
        line-height: 1.2;
    }

    .reportes-aviso-textos {
        display: flex;
        flex-direction: column;
        gap: 5px;
        flex: 1;
        min-width: 0;
    }

    .reportes-aviso-textos strong {
        font-size: 14px;
    }

    .reportes-aviso-textos span {
        font-size: 13px;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .reportes-aviso-cerrar {
        background: transparent;
        border: 0;
        color: inherit;
        cursor: pointer;
        font-size: 22px;
        line-height: 1;
    }

    .reportes-exportar a[aria-disabled="true"] {
        opacity: .65;
        cursor: progress;
    }

    @media (max-width: 640px) {
        .reportes-aviso-descarga {
            right: 16px;
            bottom: 16px;
        }
    }
</style>
@endif

@push('scripts')
<script src="{{ asset('js/reportes.js') }}?v={{ @filemtime(public_path('js/reportes.js')) }}" defer></script>
@endpush

    </main>
</div>
@endsection
@endif
