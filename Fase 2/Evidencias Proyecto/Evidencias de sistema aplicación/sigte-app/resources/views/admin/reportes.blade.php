@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) }}">
@endpush

@section('title', 'SIGTE — Reportes')

@section('content')
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Administración</div>
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
                <p class="eyebrow">Gestión</p>
                <h1>Reportes</h1>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'administradora') }}">Volver al resumen</a>
                @unless ($falla)
                    <a class="btn btn-dark" href="{{ route('mockups.reportes.exportar', $filtros) }}">Exportar Excel</a>
                @endunless
            </div>
        </div>

        @if ($falla)
            <div class="ops-empty">
                <strong>No se pudo armar el reporte</strong>
                <p>La consulta no está disponible en este momento.</p>
                <p><a href="{{ route('mockups.reportes') }}">Intentar de nuevo</a></p>
            </div>
        @else
            <form class="panel rep-armar" method="get" action="{{ route('mockups.reportes') }}">
                <div class="panel-head">
                    <h2>Período del reporte</h2>
                </div>
                <div class="rep-filtros">
                    <label class="field">
                        <span>Desde</span>
                        <input type="date" name="desde" value="{{ $filtros['desde'] }}" required>
                    </label>
                    <label class="field">
                        <span>Hasta</span>
                        <input type="date" name="hasta" value="{{ $filtros['hasta'] }}" required>
                    </label>
                    <label class="field field-select">
                        <span>Tipo de acción</span>
                        <select name="tipo">
                            <option value="">Todos</option>
                            @foreach ($tipos_auditoria as $clave => $etiqueta)
                                <option value="{{ $clave }}" @selected($filtros['tipo'] === $clave)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="users-filtros-acciones">
                        <button class="btn btn-dark" type="submit">Consultar</button>
                    </div>
                </div>
            </form>

            <article class="panel list rep-hoja" aria-label="Vista previa del reporte">
                <header class="rep-hoja-top">
                    <div>
                        <p>Hospital San José de Melipilla</p>
                        <h2>Actividad de la central</h2>
                        <p>
                            Del {{ \Carbon\Carbon::parse($filtros['desde'])->format('d/m/Y') }}
                            al {{ \Carbon\Carbon::parse($filtros['hasta'])->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="rep-formatos">
                        <a class="btn rep-excel" href="{{ route('mockups.reportes.exportar', $filtros) }}">Excel</a>
                    </div>
                </header>

                <dl class="rep-firma">
                    <div>
                        <dt>Fecha</dt>
                        <dd>{{ $fecha }}</dd>
                    </div>
                    <div>
                        <dt>Hora</dt>
                        <dd>{{ $hora }}</dd>
                    </div>
                    <div>
                        <dt>Generó</dt>
                        <dd>{{ $usuario['nombre'] }} · {{ $usuario['rol'] }}</dd>
                    </div>
                </dl>

                @if ($movimientos->isEmpty() && $entregas->isEmpty())
                    <div class="ops-empty">
                        <strong>Sin datos en ese período</strong>
                        <p>No hay movimientos ni entregas entre esas fechas. Prueba ampliar el rango.</p>
                    </div>
                @else
                    <p class="rep-total">Movimientos en el período <strong>{{ $movimientos->count() }}</strong></p>

                    <section>
                        <h3>Por tipo de acción</h3>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Tipo</th><th>Cantidad</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($por_tipo as $tipo => $cantidad)
                                        <tr>
                                            <td>{{ $tipos_auditoria[$tipo] ?? $tipo }}</td>
                                            <td>{{ $cantidad }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2">Sin acciones registradas</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section>
                        <h3>Cajas en flujo ahora</h3>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Etapa</th><th>Cajas</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($en_flujo as $fila)
                                        <tr>
                                            <td>{{ $fila['etapa'] }}</td>
                                            <td>{{ $fila['cantidad'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section>
                        <h3>Entregas del período</h3>
                        @if ($entregas->isEmpty())
                            <p class="recv-help">No hubo entregas registradas en ese rango.</p>
                        @else
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>Caja</th><th>Servicio</th><th>Cuándo</th><th>Responsable</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($entregas as $entrega)
                                            <tr>
                                                <td><strong>{{ $entrega['codigo'] }}</strong></td>
                                                <td>{{ $entrega['servicio'] }}</td>
                                                <td>{{ $entrega['cuando'] }}</td>
                                                <td>{{ $entrega['responsable'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </section>

                    <section>
                        <h3>Detalle de movimientos</h3>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Fecha y hora</th>
                                        <th>Usuario</th>
                                        <th>Acción</th>
                                        <th>Objeto</th>
                                        <th>Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movimientos as $fila)
                                        <tr>
                                            <td>
                                                <strong>{{ $fila['hora'] }}</strong>
                                                <small>{{ $fila['fecha'] }}</small>
                                            </td>
                                            <td><strong>{{ $fila['actor'] }}</strong></td>
                                            <td>{{ $fila['accion'] }}</td>
                                            <td>{{ $fila['objeto'] }}</td>
                                            <td class="muted-cell">{{ $fila['detalle'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </article>
        @endif
    </main>
</div>
@endsection
