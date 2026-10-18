@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) }}">
@endpush

@section('title', 'SIGTE — Turnos')

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
                <p class="eyebrow">Organización del puesto</p>
                <h1>Turnos</h1>
                <p class="main-sub">Arma el turno del día arrastrando las cuentas de operadora. Los horarios todavía son un ejemplo.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.usuarios') }}">Volver a usuarios</a>
            </div>
        </div>

        <section class="panel nomina-panel">
            <div class="panel-head">
                <h2>Nómina del puesto</h2>
                <span class="badge">{{ $nomina->count() }}</span>
            </div>
            <p class="recv-help">Cada persona tiene su propia cuenta de operadora. La cuenta operador@sigte.local es la del computador del puesto. Judith Herrada Valladares es administradora.</p>
            <p class="turno-aviso">Prototipo para preguntar los turnos reales. Los horarios son un ejemplo: mañana 08:00 a 16:00, tarde 16:00 a 00:00 y noche 00:00 a 08:00. Arrastra un nombre a la columna del día. Según el reloj de Chile, ahora corresponde a <strong>{{ \App\Models\Personal::TURNOS[$turnoVigente]['etiqueta'] }}</strong>. En el puesto esos nombres quedan primero y el resto sigue en el buscador. El grupo bajo cada nombre es el bloque en que llegó la lista: todavía no es el turno ni indica quién es enfermera.</p>
            <div class="turno-tablero" data-url="{{ route('mockups.turnos.asignar') }}">
                @foreach (['' => 'Sin asignar'] + collect(\App\Models\Personal::TURNOS)->map(fn ($t) => $t['etiqueta'])->all() as $clave => $etiqueta)
                    <section class="turno-columna {{ $clave === $turnoVigente ? 'is-ahora' : '' }}" data-turno="{{ $clave }}">
                        <header>
                            <h3>{{ $etiqueta }}</h3>
                            @if ($clave !== '' && isset(\App\Models\Personal::TURNOS[$clave]))
                                <span>{{ \App\Models\Personal::TURNOS[$clave]['horario'] }}</span>
                            @endif
                            @if ($clave === $turnoVigente)
                                <em>Ahora</em>
                            @endif
                        </header>
                        @if ($clave === '')
                            <label class="turno-busca">
                                <span class="sr-only">Buscar en sin asignar</span>
                                <input type="search" placeholder="Escribe un nombre" autocomplete="off">
                            </label>
                        @endif
                        <ul class="turno-lista">
                            @foreach ($nomina->where('turno', $clave === '' ? null : $clave)->sortBy('nombre') as $persona)
                                <li class="turno-tarjeta" draggable="true" data-id="{{ $persona->id }}">
                                    <strong>{{ $persona->nombre }}</strong>
                                    <span>Grupo {{ $persona->grupo }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>
            <p class="turno-estado" hidden role="status"></p>
        </section>
    </main>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var tablero = document.querySelector('.turno-tablero');
    if (!tablero) return;
    var estado = document.querySelector('.turno-estado');
    var token = document.querySelector('meta[name="csrf-token"]').content;
    var arrastrada = null;

    function normalizar(texto) {
        return (texto || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    var busca = tablero.querySelector('.turno-busca input');
    if (busca) {
        var columnaLibre = busca.closest('.turno-columna');
        var avisoVacio = document.createElement('p');
        avisoVacio.className = 'turno-vacio';
        avisoVacio.textContent = 'Ningún nombre coincide.';
        avisoVacio.hidden = true;
        columnaLibre.querySelector('.turno-lista').before(avisoVacio);
        busca.addEventListener('input', function () {
            var consulta = normalizar(busca.value.trim());
            var visibles = 0;
            columnaLibre.querySelectorAll('.turno-tarjeta').forEach(function (tarjeta) {
                var coincide = consulta === '' || normalizar(tarjeta.textContent).indexOf(consulta) !== -1;
                tarjeta.hidden = !coincide;
                if (coincide) visibles += 1;
            });
            avisoVacio.hidden = visibles > 0;
        });
    }

    function avisar(texto) {
        estado.hidden = false;
        estado.textContent = texto;
    }

    tablero.querySelectorAll('.turno-tarjeta').forEach(function (tarjeta) {
        tarjeta.addEventListener('dragstart', function () {
            arrastrada = tarjeta;
            tarjeta.classList.add('is-arrastrando');
        });
        tarjeta.addEventListener('dragend', function () {
            tarjeta.classList.remove('is-arrastrando');
            arrastrada = null;
        });
    });

    tablero.querySelectorAll('.turno-columna').forEach(function (columna) {
        columna.addEventListener('dragover', function (evento) {
            evento.preventDefault();
            columna.classList.add('is-sobre');
        });
        columna.addEventListener('dragleave', function () {
            columna.classList.remove('is-sobre');
        });
        columna.addEventListener('drop', function (evento) {
            evento.preventDefault();
            columna.classList.remove('is-sobre');
            if (!arrastrada) return;
            var lista = columna.querySelector('.turno-lista');
            var turno = columna.dataset.turno || null;
            var anterior = arrastrada.parentElement;
            lista.appendChild(arrastrada);
            avisar('Guardando…');
            fetch(tablero.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ personal_id: arrastrada.dataset.id, turno: turno })
            }).then(function (respuesta) {
                if (!respuesta.ok) throw new Error();
                avisar('Turno guardado.');
                if (busca) busca.dispatchEvent(new Event('input'));
            }).catch(function () {
                anterior.appendChild(arrastrada);
                avisar('No se pudo guardar. Vuelve a soltar el nombre.');
            });
        });
    });
})();
</script>
@endpush
