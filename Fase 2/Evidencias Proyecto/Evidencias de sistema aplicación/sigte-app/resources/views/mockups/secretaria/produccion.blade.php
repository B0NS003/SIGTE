@extends('layouts.app')

@section('title', 'SIGTE — Producción y litros')

@section('content')
<div class="shell">
    @include('mockups.partials.sidebar-secretaria')

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Datos de consumo</p>
                <h1>Producción y litros</h1>
                <p class="main-sub">Ingresa o actualiza el consumo de un servicio clínico para un mes. Si el servicio ya tiene datos en ese período, se reemplazan.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'secretaria') }}">Volver al inicio</a>
            </div>
        </div>

        @if (session('ok'))
            <p class="panel-note" role="status">{{ session('ok') }}</p>
        @endif
        @if ($errors->any())
            <p class="auth-error" role="alert">{{ $errors->first() }}</p>
        @endif

        <div class="recv-layout">
            <form class="recv-form panel" method="post" action="{{ route('secretaria.produccion.guardar') }}">
                @csrf
                <div class="panel-head">
                    <h2>{{ $editando ? 'Actualizar consumo' : 'Nuevo consumo' }}</h2>
                    <span class="badge">Por servicio y mes</span>
                </div>

                <label class="field-label" for="servicio">Servicio clínico</label>
                <label class="field field-select">
                    <select id="servicio" name="servicio" required>
                        @foreach ($servicios as $servicio)
                            <option value="{{ $servicio }}" @selected(old('servicio', $editando->servicio ?? '') === $servicio)>{{ $servicio }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field-label" for="periodo">Período</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">📅</span>
                    <input id="periodo" name="periodo" type="month" required value="{{ old('periodo', $periodo_form) }}">
                </label>

                <label class="field-label" for="consumo">Consumo (unidades del servicio)</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">#</span>
                    <input id="consumo" name="consumo" type="number" min="0" step="1" required value="{{ old('consumo', $editando->consumo ?? '') }}" placeholder="Ej: 24">
                </label>

                <label class="field-label" for="litros">Litros del período</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">L</span>
                    <input id="litros" name="litros" type="number" min="0" step="0.01" required value="{{ old('litros', $editando->litros ?? '') }}" placeholder="Ej: 180">
                </label>

                <label class="field-label" for="observacion">Observación (opcional)</label>
                <textarea id="observacion" class="recv-textarea" name="observacion" rows="3" maxlength="255" placeholder="Notas del cierre mensual">{{ old('observacion', $editando->observacion ?? '') }}</textarea>

                <div class="recv-actions">
                    <a class="btn btn-ghost" href="{{ route('secretaria.produccion') }}">Limpiar</a>
                    <button class="btn btn-primary" type="submit" style="width:auto; min-width:12rem;">Guardar consumo</button>
                </div>
            </form>

            <aside class="recv-side">
                <section class="panel">
                    <div class="panel-head"><h2>Cómo se usa</h2></div>
                    <ul class="why-list">
                        <li><strong>Un registro por mes</strong> — Pabellón en octubre es una fila. Volver a guardarlo lo actualiza.</li>
                        <li><strong>Litros</strong> — quedan junto al consumo para el reporte mensual.</li>
                        <li><strong>Servicios</strong> — Pabellón, Dental, Maternidad, Urgencia, UCI y Curaciones.</li>
                    </ul>
                </section>
            </aside>
        </div>

        <section class="panel list" style="margin-top:1rem;">
            <div class="panel-head">
                <h2>Consumos registrados</h2>
                <span class="badge">{{ $consumos->count() }}</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Período</th>
                            <th>Consumo</th>
                            <th>Litros</th>
                            <th>Observación</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($consumos as $fila)
                            <tr>
                                <td><strong>{{ $fila->servicio }}</strong></td>
                                <td>{{ \App\Models\ConsumoServicio::etiquetaPeriodo($fila->periodo) }}</td>
                                <td>{{ $fila->consumo }}</td>
                                <td>{{ number_format((float) $fila->litros, 2, ',', '.') }}</td>
                                <td>{{ $fila->observacion ?: '—' }}</td>
                                <td>
                                    <a href="{{ route('secretaria.produccion', ['servicio' => $fila->servicio, 'periodo' => $fila->periodo->format('Y-m')]) }}">Actualizar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">Todavía no hay consumos cargados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@endsection
