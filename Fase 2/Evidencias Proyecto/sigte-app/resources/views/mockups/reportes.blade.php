@extends('layouts.app')

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

        <div class="nav-label">Gestión</div>
        <nav class="nav">
            <a href="{{ route('mockups.panel', 'administradora') }}">Resumen</a>
            <a href="{{ route('mockups.catalogo') }}">Catálogo</a>
            <a href="{{ route('mockups.inventario') }}">Inventario</a>
            <a href="{{ route('mockups.usuarios') }}">Usuarios</a>
            <a class="active" href="{{ route('mockups.reportes') }}">Reportes</a>
            <a href="{{ route('mockups.custodia') }}">Custodia / auditoría</a>
            <a href="{{ route('mockups.alertas') }}">Alertas <span class="nav-badge">3</span></a>
        </nav>

        <div class="nav-label">Supervisión</div>
        <nav class="nav">
            <a href="{{ route('mockups.panel', 'operador') }}">Ver flujo de cajas</a>
        </nav>

        <div class="sidebar-foot">
            <div class="side-user">
                <div class="avatar">{{ strtoupper(substr($usuario['nombre'], 0, 1)) }}</div>
                <div>
                    <strong>{{ $usuario['nombre'] }}</strong>
                    <small>{{ $usuario['rol'] }}</small>
                </div>
            </div>
            <a class="side-logout" href="{{ route('mockups.login') }}">Cambiar rol</a>
        </div>
    </aside>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Gestión · producción y trazabilidad</p>
                <h1>Reportes</h1>
                <p class="main-sub">Resumen del período: volumen del ciclo, tiempos por etapa, carga por servicio e incidencias. Lectura de gestión, no operación del día.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'administradora') }}">Volver al resumen</a>
                <button class="btn btn-dark" type="button">Exportar CSV</button>
            </div>
        </div>

        <div class="query-toolbar panel">
            <label class="field field-select" style="margin:0; min-width:11rem;">
                <select>
                    <option>Esta semana</option>
                    <option selected>Últimos 7 días</option>
                    <option>Este mes</option>
                    <option>Personalizado…</option>
                </select>
            </label>
            <label class="field field-select" style="margin:0; min-width:11rem;">
                <select>
                    <option selected>Todos los servicios</option>
                    <option>Pabellon</option>
                    <option>Urgencia</option>
                    <option>Maternidad</option>
                    <option>UCI</option>
                    <option>Curaciones</option>
                </select>
            </label>
            <label class="field field-select" style="margin:0; min-width:11rem;">
                <select>
                    <option selected>Todos los tipos</option>
                    <option>Set quirurgico</option>
                    <option>Caja de curacion</option>
                    <option>Contenedor</option>
                </select>
            </label>
            <span class="report-period">Período: <strong>{{ $periodo }}</strong></span>
        </div>

        <div class="kpi-row">
            @foreach ($kpis as $kpi)
                <div class="kpi {{ $kpi['tone'] }}">
                    <div class="l">{{ $kpi['label'] }}</div>
                    <div class="n">{{ $kpi['value'] }}</div>
                    <div class="h">{{ $kpi['hint'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid-2">
            <section class="panel list">
                <div class="panel-head">
                    <h2>Por servicio</h2>
                    <span class="badge">Volumen</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Recepciones</th>
                                <th>Entregas</th>
                                <th>Incidencias</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($por_servicio as $row)
                                <tr>
                                    <td><strong>{{ $row['servicio'] }}</strong></td>
                                    <td>{{ $row['recepciones'] }}</td>
                                    <td>{{ $row['entregas'] }}</td>
                                    <td>
                                        @if ($row['incidencias'] > 0)
                                            <span class="pill pill-warn">{{ $row['incidencias'] }}</span>
                                        @else
                                            <span class="pill pill-ok">0</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel list">
                <div class="panel-head">
                    <h2>Tiempo promedio por etapa</h2>
                    <span class="badge">Horas</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Etapa</th>
                                <th>Promedio</th>
                                <th>Máximo</th>
                                <th>Carga visual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($por_fase as $row)
                                @php $pct = min(100, (int) round(($row['promedio_h'] / 8) * 100)); @endphp
                                <tr>
                                    <td><strong>{{ $row['fase'] }}</strong></td>
                                    <td>{{ number_format($row['promedio_h'], 1) }} h</td>
                                    <td>{{ number_format($row['max_h'], 1) }} h</td>
                                    <td>
                                        <div class="bar-track" aria-hidden="true">
                                            <div class="bar-fill" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="grid-2" style="margin-top:1rem;">
            <section class="panel list">
                <div class="panel-head">
                    <h2>Sets más usados</h2>
                    <span class="badge">Ciclos del período</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Ciclos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($top_sets as $s)
                                <tr>
                                    <td><strong>{{ $s['codigo'] }}</strong></td>
                                    <td>{{ $s['nombre'] }}</td>
                                    <td>{{ $s['ciclos'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Qué responde este reporte</h2></div>
                <ul class="why-list">
                    <li><strong>Volumen</strong> — cuánto entró / salió en el período.</li>
                    <li><strong>Cuellos de botella</strong> — etapas con mayor tiempo (ej. esterilización / almacén).</li>
                    <li><strong>Servicios</strong> — quién genera más carga e incidencias.</li>
                    <li><strong>Catálogo</strong> — qué sets circulan más (apoya inventario y stock mínimo).</li>
                </ul>
                <div class="panel-note">Mockup: filtros y exportar no calculan datos reales. Enfermera vería un “reporte de turno” más corto; admin ve el período completo.</div>
            </section>
        </div>
    </main>
</div>
@endsection
