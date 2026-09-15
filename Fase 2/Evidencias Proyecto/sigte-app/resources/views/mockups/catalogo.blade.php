@extends('layouts.app')

@section('title', 'SIGTE — Catálogo')

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
            <a class="active" href="{{ route('mockups.catalogo') }}">Catálogo</a>
            <a href="{{ route('mockups.inventario') }}">Inventario</a>
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
                <p class="eyebrow">Consulta · fichas maestras</p>
                <h1>Catálogo</h1>
                <p class="main-sub">Base de qué es cada set/caja: código, tipo, piezas y contenido típico. No es stock (eso va en Inventario).</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.inventario') }}">Ver inventario</a>
            </div>
        </div>

        <div class="query-toolbar panel">
            <label class="field" style="margin:0; flex:1;">
                <span class="icon" aria-hidden="true">⌕</span>
                <input type="search" placeholder="Buscar por código, nombre o servicio…" value="">
            </label>
            <label class="field field-select" style="margin:0; min-width:12rem;">
                <select>
                    <option>Todos los tipos</option>
                    <option>Set quirurgico</option>
                    <option>Caja de curacion</option>
                    <option>Contenedor</option>
                    <option>Paquete grado medico</option>
                </select>
            </label>
        </div>

        <section class="panel list">
            <div class="panel-head">
                <h2>Fichas del catálogo</h2>
                <span class="badge">{{ count($items) }} ítems</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Piezas</th>
                            <th>Servicio típico</th>
                            <th>Contenido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td><strong>{{ $item['codigo'] }}</strong></td>
                                <td>{{ $item['nombre'] }}</td>
                                <td>{{ $item['tipo'] }}</td>
                                <td>{{ $item['piezas'] }}</td>
                                <td>{{ $item['servicio'] }}</td>
                                <td class="muted-cell">{{ $item['contenido'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-note">Mockup: esta lista es la “base” de datos de ejemplo. Después se convierte en tabla <code>catalogo_items</code>.</div>
        </section>
    </main>
</div>
@endsection
