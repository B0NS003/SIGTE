@extends('layouts.app')

@section('title', 'SIGTE — Cierre de turno')

@section('content')
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Turno</div>
            </div>
        </div>
        <div class="nav-label">Operación</div>
        <nav class="nav">
            <a href="{{ route('mockups.panel', 'enfermera') }}">Resumen turno</a>
            <a href="{{ route('mockups.panel', 'operador') }}">Flujo de cajas</a>
            <a href="{{ route('mockups.recepcion') }}">Recepción</a>
            <a href="{{ route('mockups.entrega') }}">Entrega</a>
        </nav>
        <div class="nav-label">Gestión de turno</div>
        <nav class="nav">
            <a href="{{ route('mockups.inventario') }}">Inventario</a>
            <a href="{{ route('mockups.alertas') }}">Alertas <span class="nav-badge">3</span></a>
            <a class="active" href="{{ route('mockups.cierre_turno') }}">Cierre / traspaso</a>
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
                <p class="eyebrow">Traspaso · responsabilidad del turno</p>
                <h1>Cierre de turno</h1>
                <p class="main-sub">Deja constancia de quién estuvo a cargo, qué quedó pendiente y qué alertas siguen abiertas. Si mañana pasa algo, se sabe a quién preguntar.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'enfermera') }}">Volver</a>
            </div>
        </div>

        <div class="grid-2">
            <form class="panel recv-form" action="{{ route('mockups.panel', 'enfermera') }}" method="get">
                <div class="panel-head">
                    <h2>Datos del cierre</h2>
                    <span class="badge">{{ $turno['fecha'] }}</span>
                </div>

                <div class="adv-summary">
                    <div>
                        <span class="adv-k">Entrega turno</span>
                        <strong>{{ $turno['responsable'] }}</strong>
                        <small>{{ $turno['bloque'] }}</small>
                    </div>
                    <div class="adv-arrow" aria-hidden="true">→</div>
                    <div>
                        <span class="adv-k">Recibe relevo</span>
                        <strong>{{ $turno['relevo'] }}</strong>
                        <small>Próximo bloque</small>
                    </div>
                </div>

                <label class="field-label" for="relevo">Quién recibe el turno</label>
                <label class="field field-select">
                    <select id="relevo" name="relevo">
                        <option selected>Patricia Vega</option>
                        <option>Alejandra Riquelme</option>
                        <option>Otra enfermera…</option>
                    </select>
                </label>

                <label class="field-label" for="nota">Pendientes / nota al relevo</label>
                <textarea id="nota" class="recv-textarea" name="nota" rows="4">@foreach ($pendientes as $p)• {{ $p }}
@endforeach</textarea>

                <div class="recv-checks">
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Revisé alertas abiertas con el equipo</span>
                    </label>
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Informé cajas críticas en proceso (ej. autoclave)</span>
                    </label>
                    <label class="check">
                        <input type="checkbox">
                        <span>Stock bajo comunicado a administradora</span>
                    </label>
                </div>

                <div class="recv-actions">
                    <a class="btn btn-ghost" href="{{ route('mockups.panel', 'enfermera') }}">Cancelar</a>
                    <button class="btn btn-primary" type="submit" style="width:auto;min-width:12rem;">Confirmar cierre de turno</button>
                </div>
            </form>

            <aside>
                <section class="panel" style="margin-bottom:1rem;">
                    <div class="panel-head"><h2>Resumen del turno</h2></div>
                    <div class="kpi-row" style="grid-template-columns:1fr 1fr; margin:0;">
                        @foreach ($resumen as $k)
                            <div class="kpi">
                                <div class="l">{{ $k['label'] }}</div>
                                <div class="n">{{ $k['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                    <p class="recv-help" style="margin-top:0.85rem;">Operadoras del bloque: <strong>{{ $turno['operadoras'] }}</strong></p>
                </section>

                <section class="panel list">
                    <div class="panel-head">
                        <h2>Historial reciente</h2>
                        <span class="badge">Quién estuvo a cargo</span>
                    </div>
                    <table>
                        <thead>
                            <tr><th>Fecha</th><th>Bloque</th><th>Responsable</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach ($historial as $h)
                                <tr>
                                    <td>{{ $h['fecha'] }}</td>
                                    <td>{{ $h['bloque'] }}</td>
                                    <td><strong>{{ $h['responsable'] }}</strong></td>
                                    <td><span class="pill pill-ok">{{ $h['estado'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="panel-note">Esto no reemplaza los reportes de administradora (período/producción). Es la bitácora de responsabilidad del turno.</div>
                </section>
            </aside>
        </div>
    </main>
</div>
@endsection
