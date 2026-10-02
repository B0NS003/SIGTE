@extends('layouts.app')

@section('title', 'SIGTE — Reportes de secretaría')

@section('content')
<div class="shell">
    @include('mockups.partials.sidebar-secretaria')

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Consolidación estadística</p>
                <h1>Reporte mensual</h1>
                <p class="main-sub">Producción y litros por servicio clínico, tomados de los consumos cargados. No incluye el flujo de cajas.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'secretaria') }}">Volver al inicio</a>
                <a class="btn btn-dark" href="{{ route('secretaria.reportes.exportar', ['periodo' => $periodo]) }}">Exportar CSV</a>
            </div>
        </div>

        <form class="query-toolbar panel" method="get" action="{{ route('secretaria.reportes') }}">
            <label class="field" style="margin:0; min-width:14rem;">
                <span class="icon" aria-hidden="true">📅</span>
                <input type="month" name="periodo" value="{{ $periodo }}" required aria-label="Período">
            </label>
            <button class="btn btn-primary" type="submit" style="width:auto;">Ver mes</button>
            <span class="report-period">Período: <strong>{{ $periodo_etiqueta }}</strong></span>
        </form>

        <div class="kpi-row">
            <div class="kpi">
                <div class="l">Servicios</div>
                <div class="n">{{ $filas->count() }}</div>
                <div class="h">Con datos en el mes</div>
            </div>
            <div class="kpi">
                <div class="l">Consumo</div>
                <div class="n">{{ $total_consumo }}</div>
                <div class="h">Unidades</div>
            </div>
            <div class="kpi ok">
                <div class="l">Litros</div>
                <div class="n">{{ number_format((float) $total_litros, 1, ',', '.') }}</div>
                <div class="h">Total del período</div>
            </div>
        </div>

        <section class="panel list">
            <div class="panel-head">
                <h2>Por servicio clínico</h2>
                <span class="badge">{{ $periodo_etiqueta }}</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Consumo</th>
                            <th>Litros</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($filas as $fila)
                            <tr>
                                <td><strong>{{ $fila->servicio }}</strong></td>
                                <td>{{ $fila->consumo }}</td>
                                <td>{{ number_format((float) $fila->litros, 2, ',', '.') }}</td>
                                <td>{{ $fila->observacion ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No hay consumos cargados para {{ $periodo_etiqueta }}.</td>
                            </tr>
                        @endforelse
                        @if ($filas->isNotEmpty())
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong>{{ $total_consumo }}</strong></td>
                                <td><strong>{{ number_format((float) $total_litros, 2, ',', '.') }}</strong></td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@endsection
