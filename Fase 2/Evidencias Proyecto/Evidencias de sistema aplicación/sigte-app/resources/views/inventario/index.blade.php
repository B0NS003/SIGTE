@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/inventario.css') }}?v={{ @filemtime(public_path('css/inventario.css')) }}">
@endpush

@section('title', 'SIGTE — Inventario')

@section('content')
@php
    $conteoSalas = collect($libros)->keyBy('clave');
    $totalElementos = collect($libros)->sum('tipos');
@endphp
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

    <main class="main inv-vista sala-{{ $sala }}">
        <div class="main-top">
            <div>
                <p class="eyebrow">Consulta · tres libros</p>
                <h1>Inventario</h1>
                <p class="main-sub">Elige un libro, revisa el stock y, si hace falta, registra la reposición debajo.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.catalogo') }}">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6.5 5.5h11A1.5 1.5 0 0 1 19 7v12.5H5V7A1.5 1.5 0 0 1 6.5 5.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                        <path d="M9 9.5h6M9 13h6M9 16.5h4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                    Ver catálogo
                </a>
            </div>
        </div>

        <section class="inv-elige" aria-label="Elegir libro">
            <p>1 · Elige el libro</p>
            <div class="inv-switch">
                <a class="inv-switch-item sala-todos {{ $sala === 'todos' ? 'is-active' : '' }}"
                   href="{{ route('mockups.inventario', ['sala' => 'todos']) }}">
                    <strong>Los tres</strong>
                    <em>{{ $totalElementos }} elementos</em>
                    <span>Vista general</span>
                </a>
                @foreach ($salas as $key => $label)
                    @php $libroNav = $conteoSalas[$key]; @endphp
                    <a class="inv-switch-item sala-{{ $key }} {{ $sala === $key ? 'is-active' : '' }}"
                       href="{{ route('mockups.inventario', ['sala' => $key]) }}">
                        <strong>{{ $label }}</strong>
                        <em>{{ $libroNav['tipos'] ?? 0 }} {{ ($libroNav['tipos'] ?? 0) === 1 ? 'elemento' : 'elementos' }} · {{ $libroNav['stock'] ?? 0 }} u.</em>
                        <span class="{{ ($libroNav['bajo'] ?? 0) > 0 ? 'is-alert' : '' }}">
                            {{ ($libroNav['bajo'] ?? 0) > 0 ? ($libroNav['bajo'].' bajo mínimo') : 'Stock en orden' }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>

        @if ($sala === 'todos')
            <section class="panel inv-mira">
                <div class="panel-head">
                    <div>
                        <p class="inv-paso">2 · Lo que hay que mirar</p>
                        <h2 class="inv-titulo">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 4.5 20.5 19.5h-17L12 4.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                            <path d="M12 10v4.2" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            <path d="M12 17.2h.01" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"/>
                        </svg>
                        Para mirar ahora
                        </h2>
                    </div>
                    <span class="badge badge-urgent">{{ count($atencion) }}</span>
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

        <section class="panel list inv-book sala-{{ $sala }}">
            <div class="panel-head">
                <div>
                    <p class="inv-paso">2 · Revisa el stock</p>
                    <h2 class="inv-titulo">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6.5 5.5h11A1.5 1.5 0 0 1 19 7v12.5H5V7A1.5 1.5 0 0 1 6.5 5.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                            <path d="M9 9.5h6M9 13h6M9 16.5h4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                        </svg>
                        {{ $salas[$sala] }}
                    </h2>
                </div>
                <ul class="inv-resumen">
                    <li><strong>{{ $resumen['en_almacen'] }}</strong> unidades</li>
                    <li><strong>{{ $resumen['en_proceso'] }}</strong> en proceso</li>
                    <li class="{{ $resumen['bajo_minimo'] > 0 ? 'is-alert' : '' }}"><strong>{{ $resumen['bajo_minimo'] }}</strong> bajo mínimo</li>
                </ul>
            </div>

            <form method="GET" action="{{ route('mockups.inventario') }}" class="inv-busqueda" role="search">
                <input type="hidden" name="sala" value="{{ $sala }}">
                <label class="field" for="busqueda-inventario">
                    <span class="sr-only">Buscar por código, nombre o ubicación</span>
                    <input type="search" id="busqueda-inventario" name="q" value="{{ $busqueda ?? '' }}" placeholder="Buscar por código, nombre o ubicación">
                </label>
                <button type="submit" class="btn btn-dark">Buscar</button>
                @if (($busqueda ?? '') !== '')
                    <a class="btn btn-ghost" href="{{ route('mockups.inventario', ['sala' => $sala]) }}">Limpiar</a>
                @endif
            </form>

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
                        @forelse ($items as $item)
                            <tr class="stock-{{ $item['estado'] }}">
                                <td><strong>{{ $item['codigo'] }}</strong></td>
                                <td>{{ $item['nombre'] }}</td>
                                <td>{{ $item['ubicacion'] }}</td>
                                <td>{{ $item['stock'] }}</td>
                                <td>{{ $item['minimo'] }}</td>
                                <td>{{ $item['en_proceso'] }}</td>
                                <td>
                                    @if ($item['estado'] === 'ok')
                                        <span class="pill pill-ok">
                                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            OK
                                        </span>
                                    @elseif ($item['estado'] === 'bajo')
                                        <span class="pill pill-warn">Bajo mínimo</span>
                                    @else
                                        <span class="pill pill-danger">Crítico</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No se encontraron elementos que coincidan con tu búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @if ($puedeReponer)
        <form class="panel inv-reposicion sala-{{ $sala }}" action="{{ route('mockups.inventario.reponer') }}" method="post">
            @csrf
            <input type="hidden" name="sala" value="{{ $sala }}">
            @if (session('ok'))
                <p class="adv-ok" role="status">{{ session('ok') }}</p>
            @endif
            @error('reposicion')
                <p class="auth-error" role="alert">{{ $message }}</p>
            @enderror
            <div class="panel-head">
                <div>
                    <p class="inv-paso">3 · Registra la entrada</p>
                    <h2 class="inv-titulo">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                        </svg>
                        Reposición
                    </h2>
                </div>
                <p class="inv-quien">{{ $usuario['nombre'] }} · {{ $fecha }} · {{ $hora }}</p>
            </div>
            <div class="inv-reposicion-fila">
                <div>
                    <label class="field-label" for="elemento">Elemento</label>
                    <label class="field field-select @error('elemento') entrega-campo-invalido @enderror">
                        <select id="elemento" name="elemento" required>
                            <option value="">Elige el elemento</option>
                            @foreach ($elementos as $item)
                                <option value="{{ $item['codigo'] }}" data-stock="{{ $item['stock'] }}" data-minimo="{{ $item['minimo'] }}" data-nombre="{{ $item['nombre'] }}" @selected(old('elemento', $elemento) === $item['codigo'])>
                                    {{ $item['nombre'] }} · hay {{ $item['stock'] }}@if ($item['estado'] !== 'ok') · bajo mínimo @endif
                                </option>
                            @endforeach
                        </select>
                    </label>
                    @error('elemento')
                        <p class="entrega-error-campo" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="field-label" for="cantidad">Cantidad que entra</label>
                    <label class="field @error('cantidad') entrega-campo-invalido @enderror">
                        <input id="cantidad" name="cantidad" type="number" min="1" max="9999" step="1" required placeholder="Unidades" value="{{ old('cantidad') }}">
                    </label>
                    @error('cantidad')
                        <p class="entrega-error-campo" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <button class="btn btn-primary" id="inv-anotar" type="submit">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Registrar
                </button>
            </div>
            <p class="recv-help" id="inv-queda" hidden></p>

            <section class="inv-historial">
                <h2>Últimas reposiciones de esta sala</h2>
                @if ($reposiciones->isEmpty())
                    <p class="recv-help">Todavía no hay reposiciones en esta sala.</p>
                @else
                    <ol class="act-lista">
                        @foreach ($reposiciones as $reposicion)
                            <li>
                                <strong>Reposición · {{ $reposicion->cantidad }} · {{ $reposicion->elemento->nombre }}</strong>
                                <span>{{ $reposicion->created_at->timezone('America/Santiago')->format('d/m/Y · H:i') }} · {{ $reposicion->autor?->name ?? 'Sin usuario' }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
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

</script>
@endif
@endsection
