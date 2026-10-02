@extends('layouts.app')

@section('title', 'SIGTE — Insumos')

@section('content')
<div class="shell">
    @include('mockups.partials.sidebar-secretaria')

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Control de stock</p>
                <h1>Insumos</h1>
                <p class="main-sub">Consulta el stock actual y registra entradas o salidas. Cada movimiento queda guardado y ajusta la cantidad disponible.</p>
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

        <div class="kpi-row">
            <div class="kpi">
                <div class="l">Ítems</div>
                <div class="n">{{ $insumos->count() }}</div>
                <div class="h">En inventario</div>
            </div>
            <div class="kpi ok">
                <div class="l">Unidades</div>
                <div class="n">{{ $insumos->sum('stock') }}</div>
                <div class="h">Stock visible</div>
            </div>
            <div class="kpi {{ $bajo_minimo > 0 ? 'warn' : '' }}">
                <div class="l">Bajo mínimo</div>
                <div class="n">{{ $bajo_minimo }}</div>
                <div class="h">Requieren reposición</div>
            </div>
        </div>

        <div class="recv-layout">
            <section class="panel list">
                <div class="panel-head">
                    <h2>Stock</h2>
                    <span class="badge">Consulta</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Ubicación</th>
                                <th>Stock</th>
                                <th>Mínimo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($insumos as $item)
                                <tr>
                                    <td><strong>{{ $item->codigo }}</strong></td>
                                    <td>{{ $item->nombre }}</td>
                                    <td>{{ $item->ubicacion ?: '—' }}</td>
                                    <td>{{ $item->stock }}</td>
                                    <td>{{ $item->minimo }}</td>
                                    <td>
                                        @if ($item->estadoStock() === 'ok')
                                            <span class="pill pill-ok">OK</span>
                                        @elseif ($item->estadoStock() === 'bajo')
                                            <span class="pill pill-warn">Bajo mínimo</span>
                                        @else
                                            <span class="pill pill-danger">Crítico</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No hay insumos cargados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <form class="recv-form panel" method="post" action="{{ route('secretaria.insumos.movimiento') }}">
                @csrf
                <div class="panel-head">
                    <h2>Registrar movimiento</h2>
                    <span class="badge">Entrada o salida</span>
                </div>

                <label class="field-label" for="insumo_id">Insumo</label>
                <label class="field field-select">
                    <select id="insumo_id" name="insumo_id" required>
                        @foreach ($insumos as $item)
                            <option value="{{ $item->id }}" @selected((string) old('insumo_id') === (string) $item->id)>
                                {{ $item->codigo }} — {{ $item->nombre }} ({{ $item->stock }})
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="field-label" for="tipo">Tipo</label>
                <label class="field field-select">
                    <select id="tipo" name="tipo" required>
                        <option value="entrada" @selected(old('tipo') === 'entrada')>Entrada</option>
                        <option value="salida" @selected(old('tipo', 'salida') === 'salida')>Salida</option>
                    </select>
                </label>

                <label class="field-label" for="cantidad">Cantidad</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">#</span>
                    <input id="cantidad" name="cantidad" type="number" min="1" step="1" required value="{{ old('cantidad', 1) }}">
                </label>

                <label class="field-label" for="observacion">Observación (opcional)</label>
                <textarea id="observacion" class="recv-textarea" name="observacion" rows="3" maxlength="255" placeholder="Ej: reposición de bodega o consumo de la semana">{{ old('observacion') }}</textarea>

                <div class="recv-actions">
                    <button class="btn btn-primary" type="submit" style="width:auto; min-width:12rem;">Guardar movimiento</button>
                </div>
            </form>
        </div>

        <section class="panel list" style="margin-top:1rem;">
            <div class="panel-head">
                <h2>Últimos movimientos</h2>
                <span class="badge">{{ $movimientos->count() }}</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Insumo</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Observación</th>
                            <th>Quién</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movimientos as $mov)
                            <tr>
                                <td>{{ $mov->created_at?->format('d-m-Y H:i') }}</td>
                                <td><strong>{{ $mov->insumo?->codigo }}</strong> {{ $mov->insumo?->nombre }}</td>
                                <td>
                                    @if ($mov->tipo === 'entrada')
                                        <span class="pill pill-ok">Entrada</span>
                                    @else
                                        <span class="pill pill-warn">Salida</span>
                                    @endif
                                </td>
                                <td>{{ $mov->cantidad }}</td>
                                <td>{{ $mov->observacion ?: '—' }}</td>
                                <td>{{ $mov->autor?->name ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">Todavía no hay movimientos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@endsection
