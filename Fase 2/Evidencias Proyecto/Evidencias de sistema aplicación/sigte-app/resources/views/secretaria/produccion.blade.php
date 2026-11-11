@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/secretaria.css') }}?v={{ @filemtime(public_path('css/secretaria.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modulos/reportes/comun.css') }}?v={{ @filemtime(public_path('css/modulos/reportes/comun.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modulos/reportes/secretaria.css') }}?v={{ @filemtime(public_path('css/modulos/reportes/secretaria.css')) }}">
@endpush
@section('title', 'SIGTE — Producción y litros')
@section('content')
<div class="shell">
    @include('secretaria.partials.sidebar')
    <main class="main reportes-secretaria">
        <div class="main-top">
            <div>
                <p class="eyebrow">Registro de producción</p>
                <h1>Producción y litros</h1>
                <p class="main-sub">Registra diariamente los materiales procesados por servicio clínico y su equivalencia en litros.</p>
            </div>
            <div class="main-actions"><a class="btn btn-ghost" href="{{ route('mockups.panel', 'secretaria') }}">Volver al inicio</a></div>
        </div>
        @if(session('ok'))<div class="sp-alerta sp-alerta-ok" role="status">{{ session('ok') }}</div>@endif
        @if($errors->any())
            <div class="sp-alerta sp-alerta-error" role="alert"><strong>Revisa los campos:</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <div class="sp-grid">
            <section class="panel sp-card">
                <div class="sp-encabezado"><div><h2>Nuevo registro diario</h2><p>Los campos marcados con * son obligatorios.</p></div><span class="reportes-chip">Por día y material</span></div>
                <form method="POST" action="{{ route('secretaria.produccion.guardar') }}" class="sp-form">
                    @csrf
                    <div class="sp-form-grid">
                        <div class="sp-campo"><label for="fecha">Fecha <span>*</span></label><input id="fecha" type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" required max="{{ now()->toDateString() }}">@error('fecha')<small class="sp-error">{{ $message }}</small>@enderror</div>
                        <div class="sp-campo"><label for="servicio_id">Servicio clínico <span>*</span></label><select id="servicio_id" name="servicio_id" required><option value="">Selecciona un servicio</option>@foreach($servicios_produccion as $servicio)<option value="{{ $servicio->id }}" @selected((string)old('servicio_id') === (string)$servicio->id)>{{ $servicio->nombre }}</option>@endforeach</select>@error('servicio_id')<small class="sp-error">{{ $message }}</small>@enderror</div>
                        <div class="sp-campo sp-ancho"><label for="material_id">Material <span>*</span></label><select id="material_id" name="material_id" required><option value="">Selecciona un material</option>@foreach($materiales_produccion->groupBy('categoria') as $categoria => $materiales)<optgroup label="{{ ucfirst($categoria) }}">@foreach($materiales as $material)<option value="{{ $material->id }}" @selected((string)old('material_id') === (string)$material->id)>{{ $material->nombre }}</option>@endforeach</optgroup>@endforeach</select>@error('material_id')<small class="sp-error">{{ $message }}</small>@enderror
                            @if($materiales_produccion->isEmpty())<small class="sp-aviso">Aún no hay materiales activos en el catálogo. Debes cargarlos antes de registrar producción.</small>@endif
                        </div>
                        <div class="sp-campo"><label for="cantidad">Cantidad (unidades) <span>*</span></label><input id="cantidad" type="number" name="cantidad" min="1" step="1" value="{{ old('cantidad') }}" placeholder="Ej.: 12" required>@error('cantidad')<small class="sp-error">{{ $message }}</small>@enderror</div>
                        <div class="sp-campo"><label for="factor_litros">Factor (L por unidad) <span>*</span></label><input id="factor_litros" type="number" name="factor_litros" min="0.01" max="99999999.99" step="0.01" value="{{ old('factor_litros') }}" placeholder="Según tabla validada" required>@error('factor_litros')<small class="sp-error">{{ $message }}</small>@enderror</div>
                        <div class="sp-campo sp-ancho"><label for="observacion">Observación (opcional)</label><textarea id="observacion" name="observacion" maxlength="255" rows="3" placeholder="Información adicional del registro">{{ old('observacion') }}</textarea></div>
                    </div>
                    <p class="sp-ayuda">Litros equivalentes = cantidad × factor de litros. Utiliza el factor autorizado por el servicio; no se asigna automáticamente.</p>
                    <div class="sp-acciones"><a class="btn btn-ghost" href="{{ route('secretaria.produccion') }}">Limpiar</a><button type="submit" class="btn btn-primary" @disabled($materiales_produccion->isEmpty())>Guardar producción</button></div>
                </form>
            </section>
            <aside class="sp-lateral">
                <section class="panel sp-card"><h2>Indicaciones</h2><p>Registra cada material producido con su fecha, servicio clínico y cantidad. Los litros se calculan con el factor ingresado.</p><p>Los registros diarios se pueden consultar en el reporte mensual.</p><a class="btn btn-ghost sp-enlace" href="{{ route('secretaria.reportes') }}">Ver reportes</a></section>
                <section class="panel sp-card"><h2>Últimos registros</h2><p class="sp-numero">{{ $registros_produccion->count() }}</p><p>Registros mostrados (máximo 100).</p></section>
            </aside>
        </div>
        <section class="panel sp-card sp-seccion"><div class="sp-encabezado"><div><h2>Producción diaria registrada</h2><p>Últimos 100 registros, ordenados por fecha.</p></div></div>
            <div class="reportes-tabla"><table><thead><tr><th>Fecha</th><th>Servicio</th><th>Material</th><th>Categoría</th><th>Cantidad</th><th>Factor L/u</th><th>Litros</th><th>Observación</th></tr></thead><tbody>
                @forelse($registros_produccion as $fila)<tr><td>{{ $fila->fecha?->format('d/m/Y') }}</td><td>{{ $fila->servicio?->nombre ?? '—' }}</td><td><strong>{{ $fila->material?->nombre ?? '—' }}</strong></td><td>{{ ucfirst($fila->material?->categoria ?? '—') }}</td><td>{{ number_format($fila->cantidad, 0, ',', '.') }}</td><td>{{ number_format((float)$fila->factor_litros, 2, ',', '.') }}</td><td><strong>{{ number_format((float)$fila->litros, 2, ',', '.') }}</strong></td><td>{{ $fila->observacion ?: '—' }}</td></tr>
                @empty<tr><td colspan="8" class="reportes-vacio">Todavía no hay registros de producción diaria.</td></tr>@endforelse
            </tbody></table></div>
        </section>
        <details class="panel sp-card sp-seccion sp-historico" @if(old('periodo') || $editando) open @endif>
            <summary>Registro mensual anterior (histórico)</summary>
            <p class="sp-ayuda">Esta sección conserva el registro agregado anterior. Los consumos mensuales y los registros diarios se guardan por separado y no deben sumarse entre sí.</p>
            <form method="POST" action="{{ route('secretaria.produccion.guardar') }}" class="sp-form sp-form-grid">
                @csrf
                <div class="sp-campo"><label for="servicio">Servicio clínico *</label><select id="servicio" name="servicio" required>@foreach($servicios as $servicio)<option value="{{ $servicio }}" @selected(old('servicio', $editando->servicio ?? '') === $servicio)>{{ $servicio }}</option>@endforeach</select></div>
                <div class="sp-campo"><label for="periodo">Mes *</label><input id="periodo" name="periodo" type="month" required value="{{ old('periodo', $periodo_form) }}"></div>
                <div class="sp-campo"><label for="consumo">Consumo (unidades) *</label><input id="consumo" name="consumo" type="number" min="0" step="1" required value="{{ old('consumo', $editando->consumo ?? '') }}"></div>
                <div class="sp-campo"><label for="litros">Litros *</label><input id="litros" name="litros" type="number" min="0" step="0.01" required value="{{ old('litros', $editando->litros ?? '') }}"></div>
                <div class="sp-campo sp-ancho"><label for="observacion_mensual">Observación</label><textarea id="observacion_mensual" name="observacion" rows="2" maxlength="255">{{ old('observacion', $editando->observacion ?? '') }}</textarea></div>
                <div class="sp-acciones sp-ancho"><button class="btn btn-primary" type="submit">Guardar consumo mensual</button></div>
            </form>
            <div class="reportes-tabla sp-tabla-historica"><table><thead><tr><th>Servicio</th><th>Período</th><th>Consumo</th><th>Litros</th><th>Acción</th></tr></thead><tbody>@forelse($consumos as $fila)<tr><td>{{ $fila->servicio }}</td><td>{{ \App\Models\ConsumoServicio::etiquetaPeriodo($fila->periodo) }}</td><td>{{ $fila->consumo }}</td><td>{{ number_format((float)$fila->litros, 2, ',', '.') }}</td><td><a href="{{ route('secretaria.produccion', ['servicio' => $fila->servicio, 'periodo' => $fila->periodo->format('Y-m')]) }}">Actualizar</a></td></tr>@empty<tr><td colspan="5" class="reportes-vacio">Sin consumos mensuales anteriores.</td></tr>@endforelse</tbody></table></div>
        </details>
    </main>
</div>
@endsection
