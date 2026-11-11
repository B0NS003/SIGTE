@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modulos/reportes/comun.css') }}?v={{ @filemtime(public_path('css/modulos/reportes/comun.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modulos/reportes/secretaria.css') }}?v={{ @filemtime(public_path('css/modulos/reportes/secretaria.css')) }}">
@endpush
@section('title', 'SIGTE — Reportes de secretaría')
@section('content')
<div class="shell">
    @include('secretaria.partials.sidebar')
    <main class="main reportes-secretaria">
        <div class="main-top"><div><p class="eyebrow">Consolidación estadística</p><h1>Reporte mensual</h1><p class="main-sub">Producción diaria de materiales y litros equivalentes por servicio clínico.</p></div><div class="main-actions"><a class="btn btn-ghost" href="{{ route('mockups.panel', 'secretaria') }}">Volver al inicio</a><a class="btn btn-ghost" href="{{ route('secretaria.produccion') }}">Registrar producción</a></div></div>
        <form class="panel sp-card sp-filtro" method="GET" action="{{ route('secretaria.reportes') }}"><div class="sp-campo"><label for="periodo">Período de consulta</label><input type="month" id="periodo" name="periodo" value="{{ $periodo }}" required></div><button class="btn btn-primary" type="submit">Ver mes</button><span class="reportes-chip">{{ $periodo_etiqueta }}</span></form>
        <div class="sp-encabezado sp-titulo-reporte"><div><h2>Producción diaria consolidada</h2><p>Se calcula únicamente con los registros diarios de materiales del mes seleccionado.</p></div></div>
        <div class="reportes-resumen sp-resumen"><div class="reportes-kpi"><span>Registros diarios</span><strong>{{ $registros_diarios->count() }}</strong><small>Movimientos de producción</small></div><div class="reportes-kpi"><span>Unidades procesadas</span><strong>{{ number_format($total_unidades_diarias, 0, ',', '.') }}</strong><small>Total de materiales</small></div><div class="reportes-kpi"><span>Litros equivalentes</span><strong>{{ number_format((float)$total_litros_diarios, 2, ',', '.') }}</strong><small>Total del mes (L)</small></div></div>
        @php
            $porServicio = $registros_diarios->groupBy('servicio_id')->map(function ($grupo) {
                return (object)[
                    'servicio' => $grupo->first()->servicio?->nombre ?? 'Sin servicio',
                    'registros' => $grupo->count(),
                    'cantidad' => $grupo->sum('cantidad'),
                    'litros' => $grupo->sum(fn($fila) => $fila->litros),
                ];
            })->sortBy('servicio');
        @endphp
        <section class="panel sp-card sp-seccion"><div class="sp-encabezado"><div><h2>Resumen por servicio clínico</h2><p>Totales agrupados por servicio.</p></div><span class="reportes-chip">{{ $periodo_etiqueta }}</span></div><div class="reportes-tabla"><table><thead><tr><th>Servicio</th><th>Registros</th><th>Unidades</th><th>Litros equivalentes</th></tr></thead><tbody>@forelse($porServicio as $fila)<tr><td><strong>{{ $fila->servicio }}</strong></td><td>{{ $fila->registros }}</td><td>{{ number_format($fila->cantidad, 0, ',', '.') }}</td><td>{{ number_format((float)$fila->litros, 2, ',', '.') }}</td></tr>@empty<tr><td colspan="4" class="reportes-vacio">No hay producción diaria registrada en este mes.</td></tr>@endforelse
            @if($porServicio->isNotEmpty())<tr class="sp-total"><td><strong>Total</strong></td><td>{{ $registros_diarios->count() }}</td><td>{{ number_format($total_unidades_diarias, 0, ',', '.') }}</td><td>{{ number_format((float)$total_litros_diarios, 2, ',', '.') }}</td></tr>@endif</tbody></table></div></section>
        <section class="panel sp-card sp-seccion"><div class="sp-encabezado"><div><h2>Detalle diario por material</h2><p>Desglose de los registros que componen el resumen.</p></div></div><div class="reportes-tabla"><table><thead><tr><th>Fecha</th><th>Servicio</th><th>Material</th><th>Categoría</th><th>Cantidad</th><th>Factor L/u</th><th>Litros</th></tr></thead><tbody>@forelse($registros_diarios as $fila)<tr><td>{{ $fila->fecha?->format('d/m/Y') }}</td><td>{{ $fila->servicio?->nombre ?? '—' }}</td><td>{{ $fila->material?->nombre ?? '—' }}</td><td>{{ ucfirst($fila->material?->categoria ?? '—') }}</td><td>{{ $fila->cantidad }}</td><td>{{ number_format((float)$fila->factor_litros, 2, ',', '.') }}</td><td><strong>{{ number_format((float)$fila->litros, 2, ',', '.') }}</strong></td></tr>@empty<tr><td colspan="7" class="reportes-vacio">No hay registros diarios en este período.</td></tr>@endforelse</tbody></table></div></section>
        <details class="panel sp-card sp-seccion sp-historico"><summary>Reporte mensual anterior (histórico)</summary><p class="sp-ayuda">Los consumos mensuales antiguos se mantienen separados de la producción diaria para evitar duplicar cantidades y litros. La exportación CSV existente corresponde solamente a este histórico.</p><div class="reportes-resumen sp-resumen"><div class="reportes-kpi"><span>Servicios</span><strong>{{ $filas->count() }}</strong></div><div class="reportes-kpi"><span>Consumo mensual</span><strong>{{ number_format($total_consumo, 0, ',', '.') }}</strong></div><div class="reportes-kpi"><span>Litros mensuales</span><strong>{{ number_format((float)$total_litros, 2, ',', '.') }}</strong></div></div><div class="reportes-tabla"><table><thead><tr><th>Servicio</th><th>Consumo</th><th>Litros</th><th>Observación</th></tr></thead><tbody>@forelse($filas as $fila)<tr><td>{{ $fila->servicio }}</td><td>{{ $fila->consumo }}</td><td>{{ number_format((float)$fila->litros, 2, ',', '.') }}</td><td>{{ $fila->observacion ?: '—' }}</td></tr>@empty<tr><td colspan="4" class="reportes-vacio">Sin consumos mensuales históricos para este período.</td></tr>@endforelse</tbody></table></div><div class="sp-acciones"><a class="btn btn-ghost" href="{{ route('secretaria.reportes.exportar', ['periodo' => $periodo]) }}">Exportar histórico CSV</a></div></details>
    </main>
</div>
@endsection
