@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modulos/catalogo/comun.css') }}?v={{ @filemtime(public_path('css/modulos/catalogo/comun.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modulos/catalogo/admin.css') }}?v={{ @filemtime(public_path('css/modulos/catalogo/admin.css')) }}">
@endpush

@section('title', 'SIGTE — Administrar catálogo')

@section('content')
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-sigte.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Administración</div>
            </div>
        </div>
        @include('partials.menu-rol')
        @include('partials.sidebar-foot')
    </aside>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Gestión · solo administradora</p>
                <h1>Administrar catálogo</h1>
                <p class="main-sub">Gestiona fichas quirúrgicas e instrumentos desde un solo lugar.</p>
            </div>
        </div>

        <nav class="admin-tabs" aria-label="Secciones de administración del catálogo" role="tablist">
            <button class="admin-tab is-active" type="button" role="tab" aria-selected="true" data-tab="catalogo">Catálogo</button>
            <button class="admin-tab" type="button" role="tab" aria-selected="false" data-tab="instrumentos">Instrumentos</button>
            <button class="admin-tab" type="button" role="tab" aria-selected="false" data-tab="nueva-ficha">+ Nueva ficha</button>
            <button class="admin-tab" type="button" role="tab" aria-selected="false" data-tab="nuevo-instrumento">+ Nuevo instrumento</button>
        </nav>
        <div class="admin-panel-tab" id="tab-catalogo" role="tabpanel">
            <div class="ops-search admin-search"><label class="ops-search-field" for="buscar-fichas"><span aria-hidden="true">⌕</span><input type="search" id="buscar-fichas" placeholder="Buscar por código, nombre, tipo o servicio…" autocomplete="off"></label></div>
            <div class="admin-filter-sections">
                <section class="admin-filter-section">
                    <button class="ops-filter-label admin-filter-toggle" type="button" aria-expanded="false" aria-controls="filtros-servicio" data-filter-toggle="servicio"><span class="admin-filter-chevron" aria-hidden="true">▸</span> Filtrar por servicio</button>
                    <div class="ops-filters" id="filtros-servicio" aria-label="Filtrar fichas por servicio" hidden></div>
                </section>
            </div>
        <section class="mant-vitrina">
            <div class="mant-head">
                <div>
                    <h2>Fichas</h2>
                    <p id="fichas-meta">{{ count($items) }} vigentes · {{ collect($items)->where('guia', false)->count() }} sin guía visual</p>
                </div>
                <span class="badge" id="fichas-cuenta">{{ count($items) }}</span>
            </div>
            <div class="cat-grid" id="fichas-mural">
                @foreach ($items as $item)
                    <article class="cat-card mant-card" data-estado="vigente" data-servicio="{{ $item['servicio_real'] ?? 'Sin servicio asignado' }}" data-guia="{{ $item['guia'] ? '1' : '0' }}">
                        <div class="cat-photo {{ $item['guia'] ? '' : 'is-empty' }}">
                            <span class="js-guia-label sigte-card-guide"><svg class="sigte-card-icon sigte-card-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.7 5.1C11.1 5 11.5 5 12 5c5.5 0 9 7 9 7a14 14 0 0 1-3.2 4.2"/><path d="M6.2 6.2C3.9 8.1 3 12 3 12s3.5 7 9 7a9.7 9.7 0 0 0 3.4-.6"/><path d="M10.1 10.1a2.7 2.7 0 0 0 3.8 3.8"/></svg><svg class="sigte-card-icon sigte-card-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.4-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.4 6.5-9.5 6.5S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.8"/></svg><span class="sigte-guide-text">{{ $item['guia'] ? 'Con guía visual' : 'Sin guía visual' }}</span></span>
                        </div>
                        <div class="cat-body">
                            <strong class="js-codigo">{{ $item['codigo'] }}</strong>
                            <h2 class="js-nombre">{{ $item['nombre'] }}</h2>
                            <span class="cat-servicio js-servicio"><svg class="sigte-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="1.5"/><path d="M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1M10 21v-3h4v3"/></svg><span class="sigte-servicio-texto">{{ $item['servicio'] }}</span></span>
                            <p class="js-detalle"><svg class="sigte-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2.8 8.5 4.6v9.2L12 21.2l-8.5-4.6V7.4L12 2.8Z"/><path d="m3.5 7.4 8.5 4.8 8.5-4.8M12 12.2v9"/></svg><span class="sigte-piezas-texto">{{ $item['piezas'] }} {{ $item['piezas'] === 1 ? 'pieza' : 'piezas' }}</span></p>
                            <div class="mant-acciones">
                                <button class="btn btn-ghost js-editar" type="button"><svg class="sigte-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 20 4.4-1 10.5-10.5-3.4-3.4L5 15.6 4 20Z"/><path d="m13.8 6.9 3.4 3.4M14.5 5.1l1.6-1.6a2.1 2.1 0 0 1 3 3l-1.6 1.6"/></svg><span>Editar</span></button>
                                <button class="btn btn-ghost js-retirar" type="button"><svg class="sigte-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 7h15M9 7V4h6v3m3.2 0-.8 13H6.6L5.8 7M10 10.5v6m4-6v6"/></svg><span>Retirar</span></button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
        </div>

        <section class="admin-panel-tab" id="tab-instrumentos" role="tabpanel" hidden>
            <div class="mant-head"><div><h2>Instrumentos registrados</h2><p>Consulta los instrumentos utilizados en las fichas.</p></div><span class="badge" id="instrumentos-cuenta">0</span></div>
            <div class="ops-search admin-search"><label class="ops-search-field" for="buscar-instrumentos"><span aria-hidden="true">⌕</span><input type="search" id="buscar-instrumentos" placeholder="Buscar instrumento por nombre o código…" autocomplete="off"></label></div>
            <section class="admin-instrument-section">
                <div class="admin-instrument-section-head">
                    <div><h3>Instrumentos</h3><p id="instrumentos-meta">Instrumentos registrados</p></div>
                    <div class="admin-instrument-view" role="group" aria-label="Tipo de visualización">
                        <button type="button" class="admin-view-btn is-active" data-instrument-view="cards" aria-pressed="true">▦ Vista de tarjetas</button>
                        <button type="button" class="admin-view-btn" data-instrument-view="list" aria-pressed="false">☷ Vista de lista</button>
                    </div>
                </div>
                <div class="admin-instrument-grid" id="instrumentos-mural"></div>
                <p class="admin-empty" id="instrumentos-vacio" hidden>No se encontraron instrumentos.</p>
            </section>
        </section>

        <div class="admin-panel-tab" id="tab-nueva-ficha" role="tabpanel" hidden>
        <div class="mant-head"><div><h2 id="ficha-encabezado">Crear nueva ficha</h2><p>Registra los datos generales y los instrumentos del conjunto.</p></div></div>
        <form class="mant-editor ficha-admin" id="ficha-form" action="{{ route('mockups.catalogo.fichas.guardar') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="ficha-layout ficha-layout-redisenada">
                <section class="panel ficha-preview-panel" aria-label="Vista previa de la ficha">
                    <h2>Vista previa de la ficha</h2>
                    <div class="ficha-preview-content">
                        <div class="ficha-preview-photo" id="preview-guia">
                            <span class="ficha-preview-badge" id="preview-kicker">Sin guía visual</span>
                            <img id="preview-foto" src="{{ asset('images/catalogo/sin_imagen.png') }}" data-placeholder="{{ asset('images/catalogo/sin_imagen.png') }}" alt="Vista previa del conjunto">
                        </div>
                        <div class="ficha-preview-info">
                            <span class="ficha-preview-code" id="preview-codigo">SET-XXX-00</span>
                            <strong id="preview-titulo">Nombre del conjunto</strong>
                            <span class="ficha-preview-type" id="preview-tipo">Tipo de conjunto</span>
                            <div class="ficha-preview-fact"><span aria-hidden="true">✚</span><span id="preview-servicio">Servicio</span></div>
                            <div class="ficha-preview-separator"></div>
                            <div class="ficha-preview-fact"><span aria-hidden="true">▣</span><span id="preview-cantidad">0 piezas</span></div>
                            <div class="ficha-preview-fact"><span aria-hidden="true">▧</span><span id="preview-guia-texto">Sin guía visual</span></div>
                        </div>
                    </div>
                    <p class="ficha-preview-hint" id="preview-texto">Si el conjunto no tiene foto, se mostrará una imagen de referencia.</p>
                    <div class="ficha-mosaico" id="preview-mosaico" hidden></div>
                </section>
                <section class="panel ficha-datos">
                    <div class="panel-head">
                        <h2 id="ficha-titulo">Datos generales</h2>
                        <span class="badge" id="ficha-modo">Alta</span>
                    </div>
                    <p class="adv-ok" id="ficha-ok" role="status" hidden></p>
                    <p class="auth-error" id="ficha-error" role="alert" hidden></p>
                    <div class="mant-datos">
                        <label for="codigo">Código <span class="ficha-required">*</span>
                            <input id="codigo" name="codigo" type="text" placeholder="SET-XXX-00" autocomplete="off">
                            <small class="ficha-input-hint">Ej.: SET-LAP-01</small>
                        </label>
                        <label for="nombre">Nombre del conjunto <span class="ficha-required">*</span>
                            <input id="nombre" name="nombre" type="text" placeholder="Nombre del conjunto" autocomplete="off">
                        </label>
                        <label for="tipo">Tipo de conjunto <span class="ficha-required">*</span>
                             <select id="tipo" name="tipo_conjunto_id" required data-sigte-lista="1"><option value="">Selecciona el tipo de conjunto</option></select>
                         </label>
                         <label for="servicio">Servicio <span class="ficha-required">*</span>
                             <select id="servicio" name="servicio_id" required data-sigte-lista="1"><option value="">Selecciona el servicio</option>
                                 @foreach ($serviciosFicha as $servicioFicha)
                                     <option value="{{ $servicioFicha->id }}">{{ $servicioFicha->nombre }}</option>
                                 @endforeach
                             </select>
                         </label>
                        <div class="ficha-upload-group">
                            <span class="ficha-upload-label">Fotografía del conjunto</span>
                            <label class="ficha-upload" for="foto">
                                <span class="ficha-upload-icon" aria-hidden="true">☁</span>
                                <strong id="foto-label">Seleccionar archivo</strong>
                                <small>JPG, PNG o WEBP (máx. 5 MB)</small>
                            </label>
                            <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="ficha-file-input">
                        </div>
                        <label class="ficha-guia-check" for="guia">
                            <input id="guia" type="checkbox">
                            <span><strong>Incluir guía visual</strong><small>Opcional. Puedes agregar imágenes de las piezas.</small></span>
                        </label>
                    </div>
                </section>
            </div>
            <section class="panel ficha-lista ficha-lista-redisenada">
                <div class="panel-head">
                    <h2>Instrumentos de la ficha</h2>
                    <button class="btn btn-ghost ficha-add-button" id="agregar-pieza" type="button">+ Agregar pieza</button>
                </div>
                <div class="ficha-table-head" aria-hidden="true"><span>#</span><span>Instrumento o material</span><span>Cantidad</span><span>Acción</span></div>
                <div id="piezas"></div>
                <div class="ficha-empty-state" id="piezas-vacio" hidden>
                    <span aria-hidden="true">▣</span>
                    <strong>Aún no se han agregado instrumentos.</strong>
                    <p>Presiona «Agregar pieza» para comenzar.</p>
                </div>
                <p class="ficha-ayuda">Selecciona los instrumentos registrados para el tipo de conjunto y especifica su cantidad.</p>
            </section>
            <div class="ficha-footer-actions">
                <button class="btn btn-ghost" id="ficha-cancelar" type="button">Cancelar</button>
                <button class="btn btn-primary" id="ficha-guardar" type="submit">Crear ficha</button>
            </div>
        </form>
        </div>

        <section class="admin-panel-tab" id="tab-nuevo-instrumento" role="tabpanel" hidden>
            <div class="instrumento-create-layout">
                <form class="panel admin-instrument-form" id="instrumento-form" enctype="multipart/form-data">
                    @csrf
                    <p id="instrumento-mensaje" role="status" aria-live="polite" hidden></p>
                    <div class="instrumento-form-content">
                        <h2 id="instrumento-form-titulo">Registrar nuevo instrumento</h2>
                        <p class="instrumento-form-sub">Completa la información del instrumento. Podrá utilizarse en varias fichas, sin duplicar su registro.</p>
                        <div class="instrumento-fields">
                            <label for="instrumento-nombre">Nombre del instrumento <span class="instrumento-required">*</span>
                                <input id="instrumento-nombre" name="nombre" type="text" placeholder="Ej. Pinza Kocher" autocomplete="off" required maxlength="200">
                            </label>
                            <label for="instrumento-codigo">Código del fabricante
                                <input id="instrumento-codigo" name="codigo_fabricante" type="text" placeholder="Ej. 0200-018-000" autocomplete="off">
                            </label>
                            <label for="instrumento-tamano">Tamaño
                                <input id="instrumento-tamano" name="tamano" type="text" placeholder="Ej. 14 cm, 20 mm, N°5, etc." autocomplete="off">
                            </label>
                            <label for="instrumento-tipo-conjunto">Tipo de conjunto <span class="instrumento-required">*</span>
                                <select id="instrumento-tipo-conjunto" name="tipo_conjunto_id" required>
                                    <option value="">Selecciona el tipo de conjunto</option>
                                    @forelse ($tiposConjuntoInstrumento as $tipoConjunto)
                                        <option value="{{ $tipoConjunto->id }}">{{ $tipoConjunto->nombre_conjunto }}</option>
                                    @empty
                                        <option value="" disabled>No hay tipos de conjunto registrados</option>
                                    @endforelse
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="instrumento-form-footer">
                        <button type="button" class="btn btn-ghost" id="instrumento-limpiar">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="instrumento-guardar">Guardar instrumento</button>
                    </div>
                </form>
                <aside class="panel instrumento-photo-panel" aria-label="Fotografía del instrumento">
                    <h3>Fotografía del instrumento</h3>
                    <label for="instrumento-foto" class="instrumento-dropzone" id="instrumento-dropzone">
                        <svg width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                        <strong>Arrastra una imagen aquí</strong>
                        <span>o selecciona un archivo</span>
                        <small>Formatos: JPG, PNG, WEBP · Máx. 5 MB</small>
                    </label>
                    <input id="instrumento-foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="instrumento-hidden-file">
                    <p class="instrumento-photo-error" id="instrumento-foto-error" role="alert" hidden></p>
                    <h4>Vista previa</h4>
                    <div class="instrumento-preview" id="instrumento-preview">
                        <img id="instrumento-preview-img" src="{{ asset('images/catalogo/sin_imagen.png') }}" data-placeholder="{{ asset('images/catalogo/sin_imagen.png') }}" alt="Vista previa del instrumento">
                    </div>
                    <div class="instrumento-photo-actions">
                        <button type="button" class="btn btn-ghost" id="instrumento-cambiar-foto">Cambiar imagen</button>
                        <button type="button" class="instrumento-delete-photo" id="instrumento-quitar-foto" title="Quitar imagen" aria-label="Quitar imagen">✕</button>
                    </div>
                </aside>
            </div>
        </section>
    </main>
</div>
@php
    $datosCatalogo = [
        'fichas' => collect($items)->keyBy('codigo'),
        'servicios' => $servicios,
        'instrumentos' => $instrumentosCatalogo,
        'imagenRespaldo' => asset('images/catalogo/sin_imagen.png'),
        'serviciosFicha' => $serviciosFicha,
        'tiposFicha' => $tiposConjuntoInstrumento,
        'rutas' => [
            'instrumentoGuardar' => route('mockups.catalogo.instrumentos.guardar'),
            'instrumentoActualizar' => route('mockups.catalogo.instrumentos.actualizar', ['instrumento' => '__ID__']),
            'fichaGuardar' => route('mockups.catalogo.fichas.guardar'),
            'fichaActualizar' => route('mockups.catalogo.fichas.actualizar', ['ficha' => '__ID__']),
            'admin' => route('mockups.catalogo.admin'),
        ],
    ];
@endphp
<script type="application/json" id="catalogo-admin-datos">@json($datosCatalogo)</script>
<script src="{{ asset('js/modulos/catalogo/admin.js') }}?v={{ @filemtime(public_path('js/modulos/catalogo/admin.js')) }}"></script>
@endsection
