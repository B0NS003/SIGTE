@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/flujo.css') }}?v={{ @filemtime(public_path('css/flujo.css')) }}">
@endpush

@section('title', 'SIGTE — Entrega')

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
                <p class="eyebrow">Almacén estéril · salida</p>
                <h1>Entrega</h1>
                <p class="main-sub">Marca las cajas que salen y anota quién las retira en el servicio. Tú quedas como quien entrega.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14.5 6.5 9 12l5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Volver al flujo
                </a>
            </div>
        </div>

        @include('flujo.partials.puesto')

        <form class="recv-form panel entrega-panel" action="{{ route('mockups.entrega.registrar') }}" method="POST">
            @csrf
            <div class="entrega-cuerpo">
                <section>
                    <h2 class="entrega-titulo">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 13.2V18a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 20 18v-4.8" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
                            <path d="M4 13.2 6.3 7.2A1.6 1.6 0 0 1 7.8 6.2h8.4a1.6 1.6 0 0 1 1.5 1l2.3 6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
                            <path d="M4 13.2h4.1a2 2 0 0 0 1.9 1.3h4a2 2 0 0 0 1.9-1.3H20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
                        </svg>
                        Qué sale
                    </h2>
                    <p class="field-label"> Cajas listas para entrega <span class="entrega-obligatorio" aria-label="Obligatorio">*</span></p>
                    @if ($listas === [])
                    <p class="recv-help">No hay listas para entregar.</p>
                    @else
                    <div class="entrega-opciones @error('cajas') entrega-cajas-error @enderror">
                        @foreach (collect($listas)->sortBy('servicio') as $c)
                        <label class="check">
                            <input type="checkbox" name="cajas[]" value="{{ $c['id'] }}" @checked(in_array($c['id'], old('cajas', $caja ? [$caja['id']] : [])))>
                            <span>{{ $c['servicio'] }} · {{ $c['id'] }}@if ($c['urgente']) · requiere atención @endif</span>
                        </label>
                        @endforeach
                    </div>
                    @endif

                    @error('cajas')
                    <p class="entrega-error-campo" role="alert">{{ $message }}</p>
                    @enderror

                    @error('cajas.*')
                    <p class="entrega-error-campo" role="alert">{{ $message }}</p>
                    @enderror

                    <label class="field-label" for="material">Material que no es caja</label>
                    <label class="field field-select">
                        <select id="material" name="material" onchange="document.getElementById('otro-wrap').hidden = this.value !== 'otro'">
                            <option value="">Sin material extra</option>
                            @foreach ($materiales as $material)
                            <option value="{{ $material }}" @selected(old('material') === $material)>{{ $material }}</option>
                            @endforeach
                            <option value="otro" @selected(old('material') === 'otro')>Otro</option>
                        </select>
                    </label>

                    @error('material')
                    <p class="entrega-error-campo" role="alert">{{ $message }}</p>
                    @enderror

                    <div id="otro-wrap" @if(old('material') !== 'otro') hidden @endif>
                        <label class="field-label" for="otro">Cuál</label>
                        <label class="field">
                            <input id="otro" name="otro" type="text" placeholder="Nombre del material" value="{{ old('otro') }}">
                        </label>
                    </div>

                    @error('otro')
                    <p class="entrega-error-campo" role="alert">{{ $message }}</p>
                    @enderror
                </section>

                <section>
                    <h2 class="entrega-titulo">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.2" fill="none" stroke="currentColor" stroke-width="1.75" />
                            <path d="M5.5 18.5c1.4-2.8 3.7-4.2 6.5-4.2s5.1 1.4 6.5 4.2" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                        </svg>
                        Quién y cuándo
                    </h2>
                    <div class="entrega-datos">
                        <div class="entrega-dato entrega-dato-ancho">
                            <span>Quién entrega</span>
                            <strong>{{ $puesto?->name ?? 'Elige la cuenta del puesto' }}</strong>
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

                    <label class="field-label" id="entrega-servicio-etiqueta">
                        Servicio que lo recibe
                        <span class="entrega-obligatorio" aria-label="Obligatorio">*</span>
                    </label>

                    <div class="entrega-servicio-control">
                        {{-- Un único control visible: se evita duplicar el select del navegador y el menú personalizado. --}}
                        <input type="hidden" id="servicio" name="servicio"
                               value="{{ old('servicio', $caja['servicio'] ?? '') }}">

                        <button type="button"
                                id="entrega-servicio-toggle"
                                class="entrega-servicio-toggle @error('servicio') entrega-campo-invalido @enderror"
                                aria-labelledby="entrega-servicio-etiqueta entrega-servicio-valor"
                                aria-haspopup="listbox"
                                aria-expanded="false"
                                aria-controls="entrega-servicio-lista"
                                aria-required="true">
                            <svg class="sigte-icon entrega-servicio-casa" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4.5 19.5v-9.2L12 4.8l7.5 5.5v9.2"
                                      fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
                                <path d="M9.5 19.5v-5h5v5"
                                      fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
                            </svg>
                            <span id="entrega-servicio-valor">Elige el servicio</span>
                            <svg class="entrega-servicio-flecha" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m6 9 6 6 6-6" fill="none"
                                      stroke="currentColor" stroke-width="1.8"
                                      stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <div id="entrega-servicio-lista" class="entrega-servicio-lista" hidden>
                            <input id="entrega-servicio-buscar" type="search"
                                   placeholder="Buscar servicio..." aria-label="Buscar servicio"
                                   autocomplete="off">
                            <div class="entrega-servicio-opciones" role="listbox" aria-label="Servicios disponibles">
                                @foreach ($servicios as $s)
                                    <button type="button" role="option"
                                            class="entrega-servicio-opcion"
                                            data-value="{{ $s }}" aria-selected="false">{{ $s }}</button>
                                @endforeach
                            </div>
                            <p id="entrega-servicio-vacio" hidden>No se encontraron servicios.</p>
                        </div>
                        <p id="entrega-servicio-error-js" class="entrega-error-campo"
                           role="alert" hidden>Selecciona el servicio que recibe el material.</p>
                    </div>

                    @error('servicio')
                    <p class="entrega-error-campo" role="alert">{{ $message }}</p>
                    @enderror

                    <label class="field-label" for="retira">Quién retira en el servicio <span class="entrega-obligatorio" aria-label="Obligatorio">*</span></label>
                    <label class="field @error('retira') entrega-campo-invalido @enderror">
                        <span class="icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="8" r="3.5" />
                                <path d="M5 19c1.5-3 4-4.5 7-4.5S17.5 16 19 19" />
                            </svg>
                        </span>
                        <input id="retira" name="retira" type="text" placeholder="Nombre de quien retira" value="{{ old('retira') }}">
                    </label>

                    @error('retira')
                    <p class="entrega-error-campo" role="alert">{{ $message }}</p>
                    @enderror
                </section>
            </div>

            <details class="entrega-obs">
                <summary>
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6.5 5.5h11A1.5 1.5 0 0 1 19 7v12.2l-3.2-2H6.5A1.5 1.5 0 0 1 5 15.7V7A1.5 1.5 0 0 1 6.5 5.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
                        <path d="M9 10h6M9 13.2h4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                    </svg>
                    Agregar observación
                </summary>
                <textarea id="obs" class="recv-textarea" name="obs" rows="2" placeholder="Empaque, indicador, faltante…">{{ old('obs') }}</textarea>
            </details>

            @error('obs')
            <p class="entrega-error-campo" role="alert">{{ $message }}</p>
            @enderror

            <div class="recv-actions entrega-acciones">
                <div class="entrega-estado" aria-live="polite">
                    @if ($errors->any())
                        <div class="entrega-mensaje entrega-mensaje-error" role="alert">
                            <span class="entrega-mensaje-icono" aria-hidden="true">!</span>
                            <div><strong>No se pudo validar la entrega</strong><p>Revisa los campos señalados en rojo.</p></div>
                        </div>
                    @elseif (session('ok'))
                        <div class="entrega-mensaje entrega-mensaje-exito" role="status">
                            <span class="entrega-mensaje-icono" aria-hidden="true">✓</span>
                            <div><strong>Datos validados correctamente</strong><p>{{ session('ok') }}</p></div>
                        </div>
                    @endif
                </div>
                <button class="btn btn-primary" type="submit" style="width:auto; min-width:12rem;">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Registrar entrega
                </button>
            </div>
        </form>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const campo = document.getElementById('servicio');
    const toggle = document.getElementById('entrega-servicio-toggle');
    const lista = document.getElementById('entrega-servicio-lista');
    const buscador = document.getElementById('entrega-servicio-buscar');
    const valor = document.getElementById('entrega-servicio-valor');
    const vacio = document.getElementById('entrega-servicio-vacio');
    const error = document.getElementById('entrega-servicio-error-js');
    if (!campo || !toggle || !lista || !buscador || !valor || !vacio || !error) return;

    const opciones = Array.from(lista.querySelectorAll('.entrega-servicio-opcion'));
    const normalizar = texto => texto.normalize('NFD').replace(/[\\u0300-\\u036f]/g, '').toLocaleLowerCase('es').trim();

    function sincronizar() {
        const seleccionada = opciones.find(op => op.dataset.value === campo.value);
        valor.textContent = seleccionada ? seleccionada.textContent.trim() : 'Elige el servicio';
        toggle.classList.toggle('sin-seleccion', !seleccionada);
        opciones.forEach(op => op.setAttribute('aria-selected', String(op === seleccionada)));
        if (seleccionada) {
            error.hidden = true;
            toggle.classList.remove('entrega-campo-invalido');
            toggle.removeAttribute('aria-invalid');
        }
    }

    function filtrar() {
        const texto = normalizar(buscador.value);
        let disponibles = 0;
        opciones.forEach(op => {
            op.hidden = !normalizar(op.textContent).includes(texto);
            if (!op.hidden) disponibles++;
        });
        vacio.hidden = disponibles > 0;
    }

    function cerrar() {
        lista.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
    }

    function abrir() {
        lista.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        buscador.value = '';
        filtrar();
        buscador.focus();
    }

    toggle.addEventListener('click', function () {
        lista.hidden ? abrir() : cerrar();
    });
    buscador.addEventListener('input', filtrar);

    opciones.forEach(op => {
        op.addEventListener('click', function () {
            campo.value = op.dataset.value;
            campo.dispatchEvent(new Event('change', { bubbles: true }));
            sincronizar();
            cerrar();
            toggle.focus();
        });
        op.addEventListener('keydown', function (evento) {
            if (evento.key !== 'ArrowDown' && evento.key !== 'ArrowUp') return;
            evento.preventDefault();
            const visibles = opciones.filter(x => !x.hidden);
            const indice = visibles.indexOf(op);
            visibles[(indice + (evento.key === 'ArrowDown' ? 1 : -1) + visibles.length) % visibles.length]?.focus();
        });
    });

    buscador.addEventListener('keydown', function (evento) {
        if (evento.key === 'ArrowDown' || evento.key === 'Enter') {
            const primera = opciones.find(x => !x.hidden);
            if (primera) {
                evento.preventDefault();
                primera.focus();
            }
        }
    });

    document.addEventListener('click', function (evento) {
        if (!toggle.contains(evento.target) && !lista.contains(evento.target)) cerrar();
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && !lista.hidden) {
            cerrar();
            toggle.focus();
        }
    });

    campo.closest('form')?.addEventListener('submit', function (evento) {
        if (campo.value) return;
        evento.preventDefault();
        error.hidden = false;
        toggle.classList.add('entrega-campo-invalido');
        toggle.setAttribute('aria-invalid', 'true');
        toggle.focus();
    });

    sincronizar();
});
</script>

@endsection
