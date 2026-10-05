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
                <p class="eyebrow">Consulta · tres libros</p>
                <h1>Inventario</h1>
                <p class="main-sub">Cada sala tiene su libro. El conjunto muestra cómo están las tres.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.catalogo') }}">Ver catálogo</a>
            </div>
        </div>

        <div class="sala-tabs">
            <a class="sala-tab sala-todos {{ $sala === 'todos' ? 'active' : '' }}"
               href="{{ route('mockups.inventario', ['sala' => 'todos']) }}">Los tres</a>
            @foreach ($salas as $key => $label)
                <a class="sala-tab sala-{{ $key }} {{ $sala === $key ? 'active' : '' }}"
                   href="{{ route('mockups.inventario', ['sala' => $key]) }}">{{ $label }}</a>
            @endforeach
        </div>

        @if ($sala === 'todos')
            <div class="inv-libros">
                @foreach ($libros as $libro)
                    <a class="inv-libro sala-{{ $libro['clave'] }}" href="{{ route('mockups.inventario', ['sala' => $libro['clave']]) }}">
                        <span class="badge badge-sala badge-{{ $libro['clave'] }}">{{ $libro['nombre'] }}</span>
                        <strong>{{ $libro['stock'] }}</strong>
                        <em>unidades en sala</em>
                        <ul>
                            <li>{{ $libro['tipos'] }} {{ $libro['tipos'] === 1 ? 'elemento' : 'elementos' }}</li>
                            <li>{{ $libro['en_proceso'] }} en proceso</li>
                            <li class="{{ $libro['bajo'] > 0 ? 'is-alert' : '' }}">{{ $libro['bajo'] }} bajo mínimo</li>
                        </ul>
                    </a>
                @endforeach
            </div>

            <section class="panel">
                <div class="panel-head">
                    <h2>Para mirar ahora</h2>
                    <span class="badge">{{ count($atencion) }}</span>
                </div>
                @if ($atencion === [])
                    <p class="recv-help">Ningún elemento está bajo el mínimo.</p>
                @else
                    <ul class="inv-atencion">
                        @foreach ($atencion as $item)
                            <li>
                                <span class="badge badge-sala badge-{{ $item['sala'] }}">{{ $salas[$item['sala']] }}</span>
                                <div>
                                    <strong>{{ $item['nombre'] }}</strong>
                                    <span>{{ $item['ubicacion'] }} · hay {{ $item['stock'] }} · mínimo {{ $item['minimo'] }}</span>
                                </div>
                                @if ($item['estado'] === 'critico')
                                    <span class="pill pill-danger">Crítico</span>
                                @else
                                    <span class="pill pill-warn">Bajo mínimo</span>
                                @endif
                                @if ($puedeReponer)
                                    <a class="panel-link" href="{{ route('mockups.inventario', ['sala' => $item['sala'], 'elemento' => $item['codigo']]) }}">Reponer</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @else

        <div class="inv-libro inv-sala-hero sala-{{ $sala }}">
            <div>
                <span class="badge badge-sala badge-{{ $sala }}">{{ $salas[$sala] }}</span>
                <strong>{{ $resumen['en_almacen'] }}</strong>
                <em>unidades en sala</em>
            </div>
            <ul>
                <li>{{ $resumen['tipos'] }} {{ $resumen['tipos'] === 1 ? 'elemento' : 'elementos' }}</li>
                <li>{{ $resumen['en_proceso'] }} en proceso</li>
                <li class="{{ $resumen['bajo_minimo'] > 0 ? 'is-alert' : '' }}">{{ $resumen['bajo_minimo'] }} bajo mínimo</li>
            </ul>
        </div>

        <section class="panel list inv-book sala-{{ $sala }}">
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
        </section>
        @if ($puedeReponer)
        <form class="recv-form panel entrega-panel inv-mov sala-{{ $sala }}" action="{{ route('mockups.inventario') }}" method="get" onsubmit="return false">
            <div class="entrega-cuerpo">
                <section>
                    <h2>Registrar reposición</h2>
                    <label class="field-label" for="elemento">Elemento de {{ $salas[$sala] }}</label>
                    <label class="field field-select">
                        <select id="elemento" name="elemento">
                            <option value="">Elige el elemento</option>
                            @foreach ($items as $item)
                                <option value="{{ $item['codigo'] }}" data-stock="{{ $item['stock'] }}" data-minimo="{{ $item['minimo'] }}" data-nombre="{{ $item['nombre'] }}" @selected($elemento === $item['codigo'])>
                                    {{ $item['nombre'] }} · hay {{ $item['stock'] }}@if ($item['estado'] !== 'ok') · bajo mínimo @endif
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="field-label" for="cantidad">Cantidad que entra</label>
                    <label class="field">
                        <input id="cantidad" name="cantidad" type="number" min="1" step="1" placeholder="Cuántas unidades se reponen" value="">
                    </label>
                    <p class="recv-help" id="inv-queda" hidden></p>
                </section>

                <section>
                    <h2>Quién y cuándo</h2>
                    <div class="entrega-datos">
                        <div class="entrega-dato entrega-dato-ancho">
                            <span>Quién repone</span>
                            <strong>{{ $usuario['nombre'] }}</strong>
                        </div>
                        <div class="entrega-dato">
                            <span>Fecha</span>
                            <strong>{{ $fecha }}</strong>
                        </div>
                        <div class="entrega-dato">
                            <span>Hora</span>
                            <strong>{{ $hora }}</strong>
                        </div>
                    </div>
                    <p class="auth-error act-aviso" id="inv-aviso" role="alert" hidden></p>
                    <button class="btn btn-primary" id="inv-anotar" type="button" style="width:auto; min-width:12rem;">Registrar reposición</button>
                </section>
            </div>

            <section class="act-suma">
                <h2>Reposiciones de esta sala</h2>
                <p class="recv-help" id="inv-vacio">Todavía no hay reposiciones en esta pantalla.</p>
                <ol class="act-lista" id="inv-lista"></ol>
            </section>
        </form>
        @endif
        @endif
    </main>
</div>
@if ($sala !== 'todos' && $puedeReponer)
<script>
    var elemento = document.getElementById('elemento');
    var cantidad = document.getElementById('cantidad');
    var queda = document.getElementById('inv-queda');

    function vistaReposicion() {
        var opcion = elemento.selectedOptions[0];
        var unidades = Number(cantidad.value);
        if (!elemento.value || !unidades || unidades < 1) {
            queda.hidden = true;
            return;
        }
        var stock = Number(opcion.dataset.stock);
        var minimo = Number(opcion.dataset.minimo);
        var total = stock + unidades;
        queda.hidden = false;
        queda.textContent = total >= minimo
            ? 'Quedaría en ' + total + '. La alerta de stock se apaga.'
            : 'Quedaría en ' + total + '. Sigue bajo el mínimo (' + minimo + ').';
    }

    elemento.addEventListener('change', vistaReposicion);
    cantidad.addEventListener('input', vistaReposicion);
    vistaReposicion();

    document.getElementById('inv-anotar').addEventListener('click', function () {
        var aviso = document.getElementById('inv-aviso');
        var opcion = elemento.selectedOptions[0];
        var unidades = Number(cantidad.value);
        if (!elemento.value || !Number.isInteger(unidades) || unidades < 1) {
            aviso.textContent = 'Elige el elemento e indica una cantidad mayor que cero.';
            aviso.hidden = false;
            return;
        }
        aviso.hidden = true;
        var li = document.createElement('li');
        var titulo = document.createElement('strong');
        var meta = document.createElement('span');
        titulo.textContent = 'Reposición · ' + unidades + ' · ' + opcion.dataset.nombre;
        meta.textContent = @json($fecha.' · '.$hora.' · '.$usuario['nombre']);
        li.append(titulo, meta);
        document.getElementById('inv-lista').prepend(li);
        document.getElementById('inv-vacio').hidden = true;
        cantidad.value = '';
        queda.hidden = true;
    });
</script>
@endif
@endsection
