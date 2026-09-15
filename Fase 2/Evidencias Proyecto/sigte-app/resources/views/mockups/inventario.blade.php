@extends('layouts.app')

@section('title', 'SIGTE — Inventario')

@section('content')
<div class="shell shell-ops">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Operación</div>
            </div>
        </div>
        <div class="nav-label">Mi trabajo</div>
        <nav class="nav">
            <a href="{{ route('mockups.panel', 'operador') }}">Flujo de cajas</a>
            <a href="{{ route('mockups.recepcion') }}">Nueva recepción</a>
            <a href="{{ route('mockups.avanzar') }}">Avanzar etapa</a>
            <a href="{{ route('mockups.entrega') }}">Registrar entrega</a>
        </nav>
        <div class="nav-label">Consulta</div>
        <nav class="nav">
            <a href="{{ route('mockups.catalogo') }}">Catálogo</a>
            <a class="active" href="{{ route('mockups.inventario') }}">Inventario</a>
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
                <p class="eyebrow">Consulta · tres libros / tres salas</p>
                <h1>Inventario</h1>
                <p class="main-sub">Como en terreno: cada sala anota su propio inventario. Lavado, armado y material estéril no comparten el mismo “libro”.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.catalogo') }}">Ver catálogo</a>
            </div>
        </div>

        <div class="sala-tabs">
            @foreach ($salas as $key => $label)
                <a class="sala-tab sala-{{ $key }} {{ $sala === $key ? 'active' : '' }}"
                   href="{{ route('mockups.inventario', ['sala' => $key]) }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="kpi-row">
            <div class="kpi">
                <div class="l">Ítems en esta sala</div>
                <div class="n">{{ $resumen['tipos'] }}</div>
                <div class="h">{{ $salas[$sala] }}</div>
            </div>
            <div class="kpi ok">
                <div class="l">Stock visible</div>
                <div class="n">{{ $resumen['en_almacen'] }}</div>
                <div class="h">Unidades</div>
            </div>
            <div class="kpi">
                <div class="l">En proceso</div>
                <div class="n">{{ $resumen['en_proceso'] }}</div>
                <div class="h">Ligadas al flujo</div>
            </div>
            <div class="kpi warn">
                <div class="l">Bajo mínimo</div>
                <div class="n">{{ $resumen['bajo_minimo'] }}</div>
                <div class="h">De esta sala</div>
            </div>
        </div>

        <section class="panel list">
            <div class="panel-head">
                <h2>Libro · {{ $salas[$sala] }}</h2>
                <span class="badge badge-sala badge-{{ $sala }}">{{ $salas[$sala] }}</span>
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
                            <th>En proceso</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr class="stock-{{ $item['estado'] }}">
                                <td><strong>{{ $item['codigo'] }}</strong></td>
                                <td>{{ $item['nombre'] }}</td>
                                <td>{{ $item['ubicacion'] }}</td>
                                <td>{{ $item['stock'] }}</td>
                                <td>{{ $item['minimo'] }}</td>
                                <td>{{ $item['en_proceso'] }}</td>
                                <td>
                                    @if ($item['estado'] === 'ok')
                                        <span class="pill pill-ok">OK</span>
                                    @elseif ($item['estado'] === 'bajo')
                                        <span class="pill pill-warn">Bajo mínimo</span>
                                    @else
                                        <span class="pill pill-danger">Crítico</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-note">Mockup: pestañas = los 3 libros de sala. La trazabilidad de cajas sigue en “Flujo”; aquí es stock por área.</div>
        </section>
    </main>
</div>
@endsection
