@extends('layouts.app')

@section('title', 'SIGTE - Panel')

@section('content')
<div class="shell">
    <aside class="sidebar">
        <div class="logo">SIGTE</div>
        <div class="nav-label">Operacion</div>
        <nav class="nav">
            <a class="active" href="{{ route('panel.resumen') }}">Resumen</a>
            <a href="#">Recepcion</a>
            <a href="#">Lavado</a>
            <a href="#">Preparacion</a>
            <a href="#">Esterilizacion</a>
            <a href="#">Almacen</a>
            <a href="#">Entrega</a>
        </nav>
        <div class="nav-label">Gestion</div>
        <nav class="nav">
            <a href="#">Inventario</a>
            <a href="#">Reportes</a>
            @if(auth()->user()->isJefatura())
                <a href="#">Usuarios</a>
            @endif
        </nav>
        <div class="sidebar-foot">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background:none;border:none;color:#a8a29e;cursor:pointer;padding:0;font-size:0.9rem;">Cerrar sesion</button>
            </form>
        </div>
    </aside>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Hospital San Jose · Melipilla</p>
                <h1>Resumen operativo</h1>
            </div>
            <div class="user-chip">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->role === 'jefatura' ? 'Jefatura esterilizacion' : 'Tecnico esterilizacion' }}</small>
                </div>
            </div>
        </div>

        <div class="filters">
            <select><option>Hoy</option><option>Esta semana</option><option>Este mes</option></select>
            <select><option>Todas las areas</option><option>Pabellon</option><option>Urgencia</option></select>
            <select><option>Todos los metodos</option><option>Autoclave</option><option>Peroxido</option></select>
            <button class="btn btn-dark" type="button">Aplicar</button>
        </div>

        <p class="section-title">Prioridad operativa</p>
        <div class="kpi-row">
            <div class="kpi"><div class="l">Cajas en proceso</div><div class="n">{{ $kpis['en_proceso'] }}</div></div>
            <div class="kpi ok"><div class="l">Listas para entrega</div><div class="n">{{ $kpis['listas'] }}</div></div>
            <div class="kpi"><div class="l">Entregadas hoy</div><div class="n">{{ $kpis['entregadas'] }}</div></div>
            <div class="kpi warn"><div class="l">Retrasos / alertas</div><div class="n">{{ $kpis['alertas'] }}</div></div>
        </div>

        <p class="section-title">Flujo (6 fases)</p>
        <div class="phases">
            @foreach ($fases as $fase)
                <div class="phase">
                    <div class="n">{{ $fase['n'] }}</div>
                    <div class="l">{{ $fase['l'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid-2">
            <section class="panel">
                <h2>Ciclos por dia (demo)</h2>
                <div class="bars">
                    @foreach ($barras as $barra)
                        <div class="bar {{ $barra['fill'] ? 'fill' : '' }}" style="height:{{ $barra['h'] }}%"><span>{{ $barra['d'] }}</span></div>
                    @endforeach
                </div>
            </section>
            <section class="panel list">
                <h2>Ultimos movimientos</h2>
                <table>
                    <thead>
                        <tr><th>Caja / set</th><th>Fase</th><th>Area</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($movimientos as $m)
                            <tr>
                                <td>{{ $m['caja'] }}</td>
                                <td><span class="badge">{{ $m['fase'] }}</span></td>
                                <td>{{ $m['area'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</div>
@endsection