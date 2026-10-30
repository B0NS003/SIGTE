@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}?v={{ @filemtime(public_path('css/catalogo.css')) }}">
@endpush

@section('title', 'SIGTE — Ficha')

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
                <p class="eyebrow">Consulta · catálogo</p>
                <h1>{{ $item['nombre'] ?? $codigo }}</h1>
                @if ($item)
                    <p class="main-sub">{{ $item['codigo'] }}@if (!empty($item['codigo_referencia'])) · {{ $item['codigo_referencia'] }}@endif @if (!empty($item['numero'])) · Nº {{ $item['numero'] }}@endif · {{ $item['servicio'] }} · {{ $item['piezas'] }} {{ $item['piezas'] === 1 ? 'pieza' : 'piezas' }}</p>
                @endif
            </div>
            @if ($item)
<div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.catalogo') }}">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14.5 6.5 9 12l5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Volver al catálogo
                </a>
            </div>
            @endif
        </div>

        @if (! $item)
            <section class="ficha-error-contenedor">
                <div class="ficha-error-card" role="status">
                    <div class="ficha-error-ilustracion" aria-hidden="true">
                        <div class="ficha-error-circulo">
                            <svg viewBox="0 0 100 100" fill="none">
                                <path d="M17 30C17 25 21 21 26 21H43L52 30H74C79 30 83 34 83 39V72C83 77 79 81 74 81H26C21 81 17 77 17 72V30Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                                <rect x="27" y="43" width="46" height="29" rx="5" stroke="currentColor" stroke-width="4"/>
                                <circle cx="40" cy="53" r="4" fill="currentColor"/>
                                <path d="M31 67L43 57L51 64L60 54L70 67" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="ficha-error-alerta">!</span>
                    </div>
                    <h2>No fue posible cargar la ficha</h2>
                    <p class="ficha-error-descripcion">No hay una caja «{{ $codigo }}» en el catálogo.</p>
                    <div class="ficha-error-aviso">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" fill="#3B82F6"/>
                            <path d="M12 10.5V16M12 7.5V7.6" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <p>Verifica que el código esté correcto o selecciona una ficha desde el catálogo.</p>
                    </div>
                    <div class="ficha-error-acciones">
                        <a href="{{ route('mockups.catalogo') }}" class="ficha-error-boton">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Volver al catálogo
                        </a>
                    </div>
                </div>
            </section>
        @else
            <div class="ficha-layout ficha-operador-resumen">
                <section class="ficha-guia ficha-operador-guia {{ $item['guia'] ? '' : 'is-empty' }}" aria-label="Guía visual">
                    <div class="ficha-operador-guia-imagen">
                        <span class="ficha-operador-guia-etiqueta">
                            @if ($item['guia'])
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                Guía visual
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 6.1A10.5 10.5 0 0 1 12 6c6.5 0 10 6 10 6a17 17 0 0 1-3.1 3.7M6.5 6.6C3.6 8.5 2 12 2 12s3.5 6 10 6c1.5 0 2.9-.3 4.1-.8M10 10a3 3 0 0 0 4 4"/></svg>
                                Sin guía visual
                            @endif
                        </span>
                        <svg viewBox="0 0 48 48" width="42" height="42" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="7" y="9" width="34" height="30" rx="5"/><circle cx="18" cy="19" r="3"/><path d="m10 34 11-10 7 6 5-5 8 8"/></svg>
                    </div>
                    <div class="ficha-operador-guia-texto">
                        <span class="ficha-kicker">{{ $item['guia'] ? 'Guía visual' : 'Sin guía visual' }}</span>
                        <strong>{{ $item['guia'] ? 'Referencia del conjunto' : 'Sin imagen del conjunto' }}</strong>
                        <p>{{ $item['guia'] ? 'Consulta los instrumentos que debe contener.' : 'Puedes guiarte por la lista de instrumentos y marcar cada uno mientras armas el conjunto.' }}</p>
                    </div>
                </section>

                <section class="panel ficha-datos ficha-operador-datos">
                    <h2>Datos generales</h2>
                    <dl class="ficha-facts">
                        <div class="ficha-dato-con-icono"><span class="ficha-dato-icono" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="5" width="2" height="14"/><rect x="8" y="5" width="1" height="14"/><rect x="12" y="5" width="3" height="14"/><rect x="18" y="5" width="2" height="14"/></svg></span><span class="ficha-dato-texto"><dt>Código</dt><dd>{{ $item['codigo'] }}</dd></span></div>
                        <div class="ficha-dato-con-icono"><span class="ficha-dato-icono" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="3" width="12" height="18" rx="1"/><path d="M10 21v-5h4v5M9 7h1m4 0h1M9 11h1m4 0h1"/></svg></span><span class="ficha-dato-texto"><dt>Servicio</dt><dd>{{ $item['servicio'] }}</dd></span></div>
                        <div class="ficha-dato-con-icono"><span class="ficha-dato-icono" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5M12 12v9"/></svg></span><span class="ficha-dato-texto"><dt>Instrumentos</dt><dd>{{ count($item['instrumentos']) }} tipos</dd></span></div>
                        <div class="ficha-dato-con-icono"><span class="ficha-dato-icono" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 9 5-9 5-9-5 9-5ZM3 7v10l9 5 9-5V7M12 12v10"/></svg></span><span class="ficha-dato-texto"><dt>Debe llevar</dt><dd>{{ $item['piezas'] }} {{ $item['piezas'] === 1 ? 'pieza' : 'piezas' }}</dd></span></div>
                    </dl>
                </section>
            </div>

            <section class="panel ficha-lista ficha-operador-lista" id="ficha-checklist" data-ficha-codigo="{{ $item['codigo'] }}">
                <div class="ficha-operador-barra">
                    <div class="ficha-operador-titulo">
                        <h2>Instrumentos</h2>
                        <span class="ficha-operador-contador" id="ficha-progreso-texto" aria-live="polite">0 de {{ count($item['instrumentos']) }}</span>
                    </div>
                    <div class="ficha-operador-progreso" aria-label="Progreso de revisión">
                        <div class="ficha-operador-progreso-pista"><div id="ficha-progreso-barra"></div></div>
                        <span id="ficha-progreso-porcentaje">0%</span>
                    </div>
                    <div class="ficha-operador-acciones">
                        <button type="button" id="ficha-reiniciar" class="btn btn-ghost">↻ Reiniciar</button>
                        <button type="button" id="ficha-marcar-todos" class="btn btn-primary">Marcar todos</button>
                    </div>
                </div>
                <p class="ficha-operador-ayuda">Marca cada instrumento cuando hayas comprobado su cantidad. Esta lista es una ayuda visual y no modifica el inventario.</p>
                <div class="ficha-operador-cabecera" aria-hidden="true"><span></span><span>#</span><span>Instrumento</span><span>Cantidad</span></div>
                <ul class="ficha-items ficha-operador-items">
                    @foreach ($item['instrumentos'] as $instrumento)
                        <li>
                            <label class="ficha-operador-fila">
                                <input class="ficha-operador-check" type="checkbox" data-indice="{{ $loop->index }}" aria-label="Marcar {{ $instrumento['nombre'] }} como revisado">
                                <span class="ficha-operador-numero">{{ $loop->iteration }}</span>
                                <span class="ficha-operador-instrumento">
                                    <span class="ficha-thumb is-empty" aria-hidden="true">—</span>
                                    <span class="ficha-nombre">{{ $instrumento['nombre'] }}
                                        @if (!empty($instrumento['codigo']))<small>{{ $instrumento['codigo'] }}</small>@endif
                                    </span>
                                </span>
                                <span class="ficha-cant">× {{ $instrumento['cantidad'] }}</span>
                            </label>
                        </li>
                    @endforeach
                </ul>
                <p id="ficha-completada" class="ficha-operador-completada" hidden>Todos los instrumentos están marcados. Cuando termines, usa «Reiniciar» para comenzar una nueva revisión.</p>
            </section>
        @endif
    </main>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const lista = document.getElementById('ficha-checklist');
    if (!lista) return;
    const checks = Array.from(lista.querySelectorAll('.ficha-operador-check'));
    const texto = document.getElementById('ficha-progreso-texto');
    const barra = document.getElementById('ficha-progreso-barra');
    const porcentaje = document.getElementById('ficha-progreso-porcentaje');
    const completo = document.getElementById('ficha-completada');
    const botonTodos = document.getElementById('ficha-marcar-todos');
    const botonReset = document.getElementById('ficha-reiniciar');
    const clave = 'sigte-revision-ficha:' + lista.dataset.fichaCodigo;
    try {
        const guardados = JSON.parse(sessionStorage.getItem(clave) || '[]');
        if (Array.isArray(guardados)) checks.forEach((check, i) => { check.checked = guardados.includes(i); });
    } catch (_) { /* La revisión sigue funcionando aunque el navegador no permita almacenamiento. */ }
    function actualizar() {
        const marcados = checks.filter(c => c.checked).length;
        const total = checks.length;
        const pct = total ? Math.round(marcados / total * 100) : 0;
        texto.textContent = marcados + ' de ' + total;
        porcentaje.textContent = pct + '%';
        barra.style.width = pct + '%';
        checks.forEach(c => c.closest('li').classList.toggle('is-revisado', c.checked));
        completo.hidden = !total || marcados !== total;
        botonTodos.disabled = !total || marcados === total;
        try { sessionStorage.setItem(clave, JSON.stringify(checks.flatMap((c, i) => c.checked ? [i] : []))); } catch (_) {}
    }
    checks.forEach(c => c.addEventListener('change', actualizar));
    botonTodos.addEventListener('click', () => { checks.forEach(c => { c.checked = true; }); actualizar(); });
    botonReset.addEventListener('click', () => { checks.forEach(c => { c.checked = false; }); actualizar(); });
    actualizar();
})();
</script>
@endpush
