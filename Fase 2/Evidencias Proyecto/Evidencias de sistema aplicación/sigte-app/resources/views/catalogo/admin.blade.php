@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}?v={{ @filemtime(public_path('css/catalogo.css')) }}">
@endpush

@section('title', 'SIGTE — Administrar catálogo')

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
                            <span class="js-guia-label">{{ $item['guia'] ? 'Con guía visual' : 'Sin guía visual' }}</span>
                        </div>
                        <div class="cat-body">
                            <strong class="js-codigo">{{ $item['codigo'] }}</strong>
                            <h2 class="js-nombre">{{ $item['nombre'] }}</h2>
                            <span class="cat-servicio js-servicio">{{ $item['servicio'] }}</span>
                            <p class="js-detalle">{{ $item['tipo'] }} · {{ $item['piezas'] }} {{ $item['piezas'] === 1 ? 'pieza' : 'piezas' }}</p>
                            <div class="mant-acciones">
                                <button class="btn btn-ghost js-editar" type="button">Editar</button>
                                <button class="btn btn-ghost js-retirar" type="button">Retirar</button>
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
<script>
    var fichas = @json(collect($items)->keyBy('codigo'));
    var tabActual = 'catalogo';
    function mostrarTab(id) {
        tabActual = id;
        document.querySelectorAll('.admin-tab').forEach(function (boton) {
            var activo = boton.dataset.tab === id;
            boton.classList.toggle('is-active', activo);
            boton.setAttribute('aria-selected', activo ? 'true' : 'false');
        });
        document.querySelectorAll('.admin-panel-tab').forEach(function (panel) {
            panel.hidden = panel.id !== 'tab-' + id;
        });
    }
    document.querySelectorAll('.admin-tab').forEach(function (boton) {
        boton.addEventListener('click', function () {
            if (boton.dataset.tab === 'nueva-ficha' && tabActual !== 'nueva-ficha') {
                limpiarAlta();
                document.getElementById('ficha-encabezado').textContent = 'Crear nueva ficha';
            }
            if (boton.dataset.tab === 'nuevo-instrumento' && tabActual !== 'nuevo-instrumento') {
                limpiarFormularioInstrumento();
            }
            mostrarTab(boton.dataset.tab);
        });
    });
    var servicioActivo = '';
    var serviciosDisponibles = @json($servicios);
    document.querySelectorAll('[data-filter-toggle]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var panel = document.getElementById('filtros-' + boton.dataset.filterToggle);
            panel.hidden = !panel.hidden;
            boton.setAttribute('aria-expanded', String(!panel.hidden));
            boton.querySelector('.admin-filter-chevron').textContent = panel.hidden ? '▸' : '▾';
        });
    });
    function tarjetasCatalogo() { return Array.from(document.querySelectorAll('#fichas-mural .mant-card')); }
    function agruparPorArea() {
        var mural = document.getElementById('fichas-mural');
        var cards = tarjetasCatalogo();
        var grupos = new Map();
        cards.forEach(function (card) {
            var area = card.dataset.servicio || 'Sin servicio asignado';
            if (!grupos.has(area)) grupos.set(area, []);
            grupos.get(area).push(card);
        });
        mural.replaceChildren();
        Array.from(grupos.keys()).sort(function(a,b) { return a.localeCompare(b,'es'); }).forEach(function(area) {
            var grupo = document.createElement('details');
            grupo.className = 'admin-area-group';
            grupo.dataset.area = area;
            grupo.open = true;
            var resumen = document.createElement('summary');
            resumen.className = 'admin-area-summary';
            var titulo = document.createElement('strong');
            titulo.textContent = area;
            var cuenta = document.createElement('span');
            cuenta.className = 'admin-area-count';
            resumen.append(titulo, cuenta);
            var grid = document.createElement('div');
            grid.className = 'cat-grid';
            grupos.get(area).forEach(function(card) { grid.appendChild(card); });
            grupo.append(resumen, grid);
            mural.appendChild(grupo);
        });
    }
    function filtrarFichas() {
        var q = document.getElementById('buscar-fichas').value.trim().toLocaleLowerCase();
        tarjetasCatalogo().forEach(function(card) {
            var coincide = (!servicioActivo || card.dataset.servicio === servicioActivo)
                && card.textContent.toLocaleLowerCase().includes(q);
            card.hidden = !coincide;
        });
        document.querySelectorAll('#fichas-mural .admin-area-group').forEach(function(grupo) {
            var visibles = Array.from(grupo.querySelectorAll('.mant-card')).filter(function(card) { return !card.hidden; }).length;
            grupo.hidden = visibles === 0;
            grupo.querySelector('.admin-area-count').textContent = visibles + (visibles === 1 ? ' ficha' : ' fichas');
        });
    }
    function construirFiltrosServicio() {
        var cards = tarjetasCatalogo().filter(function(card) { return card.dataset.estado !== 'retirada'; });
        function pintar(nombre, contenedorId, opciones, seleccionado, alSeleccionar, otraClave, otroValor) {
            var contenedor = document.getElementById(contenedorId);
            var conteos = new Map();
            cards.forEach(function(card) {
                if (otroValor && card.dataset[otraClave] !== otroValor) return;
                var valor = card.dataset[nombre] || ('Sin ' + nombre + ' asignada');
                conteos.set(valor, (conteos.get(valor) || 0) + 1);
            });
            contenedor.replaceChildren();
            function agregar(etiqueta, valor, cantidad) {
                var boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'ops-filter' + (seleccionado === valor ? ' is-active' : '');
                boton.setAttribute('aria-pressed', String(seleccionado === valor));
                boton.appendChild(document.createTextNode(etiqueta + ' '));
                var em = document.createElement('em');
                em.textContent = cantidad;
                boton.appendChild(em);
                boton.addEventListener('click', function() { alSeleccionar(valor); construirFiltrosServicio(); });
                contenedor.appendChild(boton);
            }
            agregar('Todos', '', Array.from(conteos.values()).reduce(function(a,b) { return a+b; },0));
            Array.from(new Set(opciones.concat(Array.from(conteos.keys())))).sort(function(a,b) { return a.localeCompare(b,'es'); }).forEach(function(valor) {
                agregar(valor, valor, conteos.get(valor) || 0);
            });
        }
        pintar('servicio','filtros-servicio',serviciosDisponibles,servicioActivo,function(v){servicioActivo=v;},null,null);
        filtrarFichas();
    }
    document.getElementById('buscar-fichas').addEventListener('input', filtrarFichas);
    // Datos reales de instrumentos, sin transferir fotografías BYTEA en el HTML.
    var instrumentosDemo = @json($instrumentosCatalogo);
    var imagenInstrumentoRespaldo = @json(asset('images/catalogo/sin_imagen.png'));
    var vistaInstrumentos = 'cards';
    var instrumentosRetirados = new Set();
    function pintarInstrumentos(filtro) {
        var mural = document.getElementById('instrumentos-mural');
        mural.replaceChildren();
        mural.classList.toggle('is-list', vistaInstrumentos === 'list');
        var q = (filtro || '').trim().toLocaleLowerCase();
        var filtrados = instrumentosDemo.filter(function (x) {
            return (x.nombre + ' ' + x.codigo).toLocaleLowerCase().includes(q);
        });
        filtrados.forEach(function (inst) {
            var clave = String(inst.id);
            var retirado = instrumentosRetirados.has(clave);
            var card = document.createElement('article');
            card.className = 'admin-instrument-card' + (retirado ? ' mant-retirada' : '');

            var foto = document.createElement('div');
            foto.className = 'admin-instrument-photo';
            var imagen = document.createElement('img');
            imagen.className = 'admin-instrument-img';
            imagen.src = inst.imagen_url || imagenInstrumentoRespaldo;
            imagen.alt = 'Fotografía de ' + inst.nombre;
            imagen.loading = 'lazy';
            imagen.decoding = 'async';
            imagen.addEventListener('error', function () {
                if (!imagen.src.endsWith('/sin_imagen.png')) {
                    imagen.src = imagenInstrumentoRespaldo;
                }
            });
            foto.appendChild(imagen);

            var contenido = document.createElement('div');
            contenido.className = 'admin-instrument-body';
            var nombre = document.createElement('strong');
            nombre.textContent = inst.nombre;
            var meta = document.createElement('small');
            meta.textContent = inst.codigo || 'Sin código de fabricante';
            contenido.append(nombre, meta);

            var acciones = document.createElement('div');
            acciones.className = 'mant-acciones admin-instrument-actions';
            var editar = document.createElement('button');
            editar.className = 'btn btn-ghost';
            editar.type = 'button';
            editar.textContent = 'Editar';
            editar.disabled = retirado;
            editar.addEventListener('click', function () {
                limpiarFormularioInstrumento();
                document.getElementById('instrumento-nombre').value = inst.nombre;
                document.getElementById('instrumento-codigo').value = inst.codigo;
                document.getElementById('instrumento-tamano').value = inst.tamano || '';
                document.getElementById('instrumento-form-titulo').textContent = 'Editar instrumento';
                document.getElementById('instrumento-tipo-conjunto').value = String(inst.tipo_conjunto_id || '');
                instrumentoEditando = inst.id;
                instrumentoQuitarFoto = false;
                document.getElementById('instrumento-guardar').textContent = 'Guardar cambios';
                if (inst.imagen_url) establecerPreviewInstrumento(inst.imagen_url);
                mostrarTab('nuevo-instrumento');
            });
            var retirar = document.createElement('button');
            retirar.className = 'btn btn-ghost';
            retirar.type = 'button';
            retirar.textContent = retirado ? 'Retirado' : 'Retirar';
            retirar.disabled = retirado;
            retirar.addEventListener('click', function () {
                instrumentosRetirados.add(clave);
                pintarInstrumentos(document.getElementById('buscar-instrumentos').value);
            });
            acciones.append(editar, retirar);
            card.append(foto, contenido, acciones);
            mural.appendChild(card);
        });
        document.getElementById('instrumentos-vacio').hidden = filtrados.length > 0;
        document.getElementById('instrumentos-cuenta').textContent = String(filtrados.length);
        document.getElementById('instrumentos-meta').textContent = filtrados.length + (filtrados.length === 1 ? ' instrumento mostrado' : ' instrumentos mostrados');
    }
    document.querySelectorAll('[data-instrument-view]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            vistaInstrumentos = boton.dataset.instrumentView;
            document.querySelectorAll('[data-instrument-view]').forEach(function (b) {
                var activo = b === boton;
                b.classList.toggle('is-active', activo);
                b.setAttribute('aria-pressed', String(activo));
            });
            pintarInstrumentos(document.getElementById('buscar-instrumentos').value);
        });
    });
    document.getElementById('buscar-instrumentos').addEventListener('input', function (e) { pintarInstrumentos(e.target.value); });
    var instrumentoFotoInput = document.getElementById('instrumento-foto');
    var instrumentoFotoPreviewUrl = null;
    var instrumentoFotoArrastrada = null;
    var instrumentoEditando = null;
    var instrumentoQuitarFoto = false;
    var instrumentoGuardando = false;
    function establecerPreviewInstrumento(url) {
        var img = document.getElementById('instrumento-preview-img');
        img.src = url || img.dataset.placeholder;
        document.getElementById('instrumento-preview').classList.toggle('has-image', !!url);
    }
    function limpiarFormularioInstrumento() {
        document.getElementById('instrumento-form').reset();
        document.getElementById('instrumento-form-titulo').textContent = 'Registrar nuevo instrumento';
        instrumentoEditando = null;
        instrumentoQuitarFoto = false;
        document.getElementById('instrumento-guardar').textContent = 'Guardar instrumento';
        document.getElementById('instrumento-guardar').disabled = false;
        document.getElementById('instrumento-guardar').removeAttribute('title');
        instrumentoFotoArrastrada = null;
        document.getElementById('instrumento-mensaje').hidden = true;
        document.getElementById('instrumento-foto-error').hidden = true;
        if (instrumentoFotoPreviewUrl) URL.revokeObjectURL(instrumentoFotoPreviewUrl);
        instrumentoFotoPreviewUrl = null;
        establecerPreviewInstrumento(null);
    }
    function cargarFotoInstrumento(file) {
        var error = document.getElementById('instrumento-foto-error');
        error.hidden = true;
        if (!file) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
            error.textContent = 'Selecciona una imagen JPG, PNG o WEBP de máximo 5 MB.';
            error.hidden = false;
            instrumentoFotoInput.value = '';
            instrumentoFotoArrastrada = null;
            return;
        }
        if (instrumentoFotoPreviewUrl) URL.revokeObjectURL(instrumentoFotoPreviewUrl);
        instrumentoFotoArrastrada = file;
        instrumentoQuitarFoto = false;
        instrumentoFotoPreviewUrl = URL.createObjectURL(file);
        establecerPreviewInstrumento(instrumentoFotoPreviewUrl);
    }
    instrumentoFotoInput.addEventListener('change', function () { instrumentoFotoArrastrada = null; cargarFotoInstrumento(this.files[0]); });
    document.getElementById('instrumento-cambiar-foto').addEventListener('click', function () { instrumentoFotoInput.click(); });
    document.getElementById('instrumento-quitar-foto').addEventListener('click', function () {
        instrumentoFotoInput.value = '';
        instrumentoFotoArrastrada = null;
        if (instrumentoFotoPreviewUrl) URL.revokeObjectURL(instrumentoFotoPreviewUrl);
        instrumentoFotoPreviewUrl = null;
        establecerPreviewInstrumento(null);
        instrumentoQuitarFoto = !!instrumentoEditando;
        document.getElementById('instrumento-foto-error').hidden = true;
    });
    document.getElementById('instrumento-limpiar').addEventListener('click', function () {
        limpiarFormularioInstrumento();
        mostrarTab('instrumentos');
    });
    var instrumentoDropzone = document.getElementById('instrumento-dropzone');
    ['dragenter', 'dragover'].forEach(function (tipo) {
        instrumentoDropzone.addEventListener(tipo, function (e) { e.preventDefault(); instrumentoDropzone.classList.add('is-dragging'); });
    });
    ['dragleave', 'drop'].forEach(function (tipo) {
        instrumentoDropzone.addEventListener(tipo, function (e) { e.preventDefault(); instrumentoDropzone.classList.remove('is-dragging'); });
    });
    instrumentoDropzone.addEventListener('drop', function (e) {
        var file = e.dataTransfer.files[0];
        if (file) cargarFotoInstrumento(file);
    });
    document.getElementById('instrumento-guardar').addEventListener('click', function () {
        document.getElementById('instrumento-form').requestSubmit();
    });
    document.getElementById('instrumento-form').addEventListener('submit', async function (evento) {
        evento.preventDefault();
        if (instrumentoGuardando) return;
        var mensaje = document.getElementById('instrumento-mensaje');
        var boton = document.getElementById('instrumento-guardar');
        var nombreNuevo = document.getElementById('instrumento-nombre').value.trim();
        if (!nombreNuevo) {
            mensaje.textContent = 'El nombre del instrumento es obligatorio.';
            mensaje.style.color = '#b42318';
            mensaje.hidden = false;
            return;
        }
        if (!document.getElementById('instrumento-tipo-conjunto').value) {
            mensaje.textContent = 'Selecciona un tipo de conjunto.';
            mensaje.style.color = '#b42318';
            mensaje.hidden = false;
            return;
        }
        var datos = new FormData(this);
        datos.set('nombre', nombreNuevo);
        var archivo = instrumentoFotoInput.files[0] || instrumentoFotoArrastrada;
        if (archivo) datos.set('foto', archivo);
        if (instrumentoEditando) {
            datos.set('_method', 'PUT');
            if (instrumentoQuitarFoto && !archivo) datos.set('quitar_foto', '1');
        }
        var idEnEdicion = instrumentoEditando;
        instrumentoGuardando = true;
        boton.disabled = true;
        boton.textContent = 'Guardando…';
        mensaje.hidden = true;
        try {
            var urlGuardar = idEnEdicion
                ? @json(route('mockups.catalogo.instrumentos.actualizar', ['instrumento' => '__ID__'])).replace('__ID__', encodeURIComponent(idEnEdicion))
                : @json(route('mockups.catalogo.instrumentos.guardar'));
            var respuesta = await fetch(urlGuardar, {
                method: 'POST',
                body: datos,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            var resultado = await respuesta.json();
            if (!respuesta.ok) {
                var errores = resultado.errors ? Object.values(resultado.errors).flat().join(' ') : (resultado.message || 'No fue posible guardar el instrumento.');
                throw new Error(errores);
            }
            if (idEnEdicion) {
                var indice = instrumentosDemo.findIndex(function (inst) { return Number(inst.id) === Number(idEnEdicion); });
                if (indice !== -1) instrumentosDemo[indice] = resultado.instrumento;
            } else {
                instrumentosDemo.push(resultado.instrumento);
            }
            limpiarFormularioInstrumento();
            pintarInstrumentos(document.getElementById('buscar-instrumentos').value);
            mostrarTab('instrumentos');
            var panel = document.getElementById('tab-instrumentos');
            var aviso = document.createElement('p');
            aviso.className = 'adv-ok';
            aviso.setAttribute('role', 'status');
            aviso.textContent = idEnEdicion
                ? 'Instrumento actualizado correctamente en la base de datos.'
                : 'Instrumento registrado correctamente en la base de datos.';
            var anterior = panel.querySelector('.instrumento-aviso-guardado');
            if (anterior) anterior.remove();
            aviso.classList.add('instrumento-aviso-guardado');
            panel.prepend(aviso);
        } catch (fallo) {
            mensaje.textContent = fallo.message || 'Error al guardar el instrumento.';
            mensaje.style.color = '#b42318';
            mensaje.hidden = false;
        } finally {
            instrumentoGuardando = false;
            boton.disabled = false;
            boton.textContent = instrumentoEditando ? 'Guardar cambios' : 'Guardar instrumento';
        }
    });
    pintarInstrumentos('');
    var formFila = null;
    var fichaEditandoId = null;
    var serviciosFicha = @json($serviciosFicha);
    var tiposFicha = @json($tiposConjuntoInstrumento);
    var titulo = document.getElementById('ficha-titulo');
    var modo = document.getElementById('ficha-modo');
    var guardar = document.getElementById('ficha-guardar');
    var cancelar = document.getElementById('ficha-cancelar');
    var ok = document.getElementById('ficha-ok');
    var error = document.getElementById('ficha-error');
    var codigo = document.getElementById('codigo');
    var nombre = document.getElementById('nombre');
    var tipo = document.getElementById('tipo');
    var servicio = document.getElementById('servicio');
    var guia = document.getElementById('guia');
    var foto = document.getElementById('foto');
    var piezas = document.getElementById('piezas');
    var mosaico = document.getElementById('preview-mosaico');

    function aviso(texto, esError) {
        ok.hidden = esError || !texto;
        error.hidden = !esError || !texto;
        (esError ? error : ok).textContent = texto || '';
    }

    function opcionesConjunto() {
        return instrumentosDemo.filter(function (x) { return String(x.tipo_conjunto_id) === tipo.value; });
    }
    // Los tipos se filtran solamente al cambiar el servicio.
    // Al elegir un tipo primero NO se reconstruye el select abierto:
    // hacerlo durante el evento del propio select puede dejar su texto visual desactualizado.
    function poblarTipos(seleccion) {
        var servicioId = String(servicio.value || '');
        var elegidoId = seleccion === undefined ? String(tipo.value || '') : String(seleccion || '');
        var disponibles = tiposFicha.filter(function (registro) {
            return !servicioId || String(registro.servicio_id) === servicioId;
        });

        tipo.replaceChildren();
        var textoInicial = servicioId && disponibles.length === 0
            ? 'No hay tipos de conjunto registrados para este servicio'
            : 'Selecciona el tipo de conjunto';
        tipo.add(new Option(textoInicial, ''));
        disponibles.forEach(function (registro) {
            tipo.add(new Option(registro.nombre_conjunto, String(registro.id)));
        });
        tipo.value = disponibles.some(function (registro) {
            return String(registro.id) === elegidoId;
        }) ? elegidoId : '';
        // Mantener el select habilitado para mostrar el motivo de la lista vacía.
        tipo.disabled = false;
    }

    function cambioServicioFicha() {
        // El servicio elegido manda: si no hay tipos, el desplegable queda sin resultados.
        poblarTipos('');
        pintarPiezas([]);
        aviso('', false);
        actualizarVista();
    }

    function cambioTipoFicha() {
        var elegido = tiposFicha.find(function (registro) {
            return String(registro.id) === String(tipo.value);
        });
        if (elegido) {
            // Actualizar el campo de servicio sin reconstruir el selector de tipo
            // durante su propio evento de cambio.
            servicio.value = String(elegido.servicio_id);
            if (servicio.value !== String(elegido.servicio_id)) {
                aviso('No se encontró el servicio asociado al tipo de conjunto.', true);
                return;
            }
        }
        pintarPiezas([]);
        actualizarVista();
    }

    // Usar solo change: reconstruir opciones en input/focus produce conflictos
    // con el menú nativo de algunos navegadores.
    servicio.addEventListener('change', cambioServicioFicha);
    tipo.addEventListener('change', cambioTipoFicha);
    function filaPieza(pieza) {
        var fila = document.createElement('div');
        fila.className = 'mant-pieza';
        var numero = document.createElement('span');
        numero.className = 'ficha-numero';
        numero.textContent = String(piezas.children.length + 1);
        var selector = document.createElement('select');
        selector.className = 'pieza-nombre';
        selector.setAttribute('aria-label', 'Instrumento o material');
        selector.add(new Option('Selecciona un instrumento', ''));
        opcionesConjunto().forEach(function (inst) {
            selector.add(new Option(inst.nombre + (inst.tamano ? ' · ' + inst.tamano : ''), inst.id));
        });
        selector.value = pieza && pieza.id ? String(pieza.id) : '';
        var cantidad = document.createElement('input');
        cantidad.className = 'pieza-cant';
        cantidad.type = 'number';
        cantidad.min = '1';
        cantidad.step = '1';
        cantidad.placeholder = 'Cant.';
        cantidad.setAttribute('aria-label', 'Cantidad');
        cantidad.value = pieza && pieza.cantidad ? pieza.cantidad : 1;
        var quitar = document.createElement('button');
        quitar.className = 'btn btn-ghost js-quitar';
        quitar.type = 'button';
        quitar.textContent = 'Quitar';
        fila.append(numero, selector, cantidad, quitar);
        return fila;
    }
    function pintarPiezas(lista) {
        piezas.replaceChildren();
        (lista || []).forEach(function (pieza) { piezas.appendChild(filaPieza(pieza)); });
        actualizarVista();
    }
    function leerPiezas() {
        return Array.from(piezas.querySelectorAll('.mant-pieza')).map(function (fila) {
            return { instrumento_id: Number(fila.querySelector('.pieza-nombre').value), cantidad: Number(fila.querySelector('.pieza-cant').value) };
        });
    }

    var previewFotoUrl = null;
    var previewFotoArchivo = null;
    function actualizarVista() {
        var conGuia = guia.checked;
        document.getElementById('preview-kicker').textContent = conGuia ? 'Con guía visual' : 'Sin guía visual';
        document.getElementById('preview-codigo').textContent = codigo.value.trim().toUpperCase() || 'SET-XXX-00';
        document.getElementById('preview-titulo').textContent = nombre.value.trim() || 'Nombre del conjunto';
        document.getElementById('preview-tipo').textContent = tipo.value ? tipo.selectedOptions[0].textContent : 'Tipo de conjunto';
        document.getElementById('preview-servicio').textContent = servicio.value ? servicio.selectedOptions[0].textContent : 'Servicio clínico';
        var cantidad = leerPiezas().reduce(function (total, pieza) { return total + (pieza.cantidad || 0); }, 0);
        document.getElementById('preview-cantidad').textContent = cantidad + (cantidad === 1 ? ' pieza' : ' piezas');
        document.getElementById('preview-guia-texto').textContent = conGuia ? 'Con guía visual' : 'Sin guía visual';
        var img = document.getElementById('preview-foto');
        var archivoActual = foto.files && foto.files[0] ? foto.files[0] : null;
        if (archivoActual !== previewFotoArchivo) {
            if (previewFotoUrl) URL.revokeObjectURL(previewFotoUrl);
            previewFotoArchivo = archivoActual;
            previewFotoUrl = archivoActual ? URL.createObjectURL(archivoActual) : null;
        }
        img.src = previewFotoUrl || img.dataset.placeholder;
        document.getElementById('foto-label').textContent = foto.files && foto.files[0] ? foto.files[0].name : 'Seleccionar archivo';
        document.getElementById('preview-texto').textContent = previewFotoUrl ? 'Vista previa de la fotografía seleccionada.' : 'Si el conjunto no tiene foto, se mostrará una imagen de referencia.';
        var mosaico = document.getElementById('preview-mosaico');
        mosaico.replaceChildren();
        mosaico.hidden = true;
        document.getElementById('piezas-vacio').hidden = piezas.children.length > 0;
        piezas.querySelectorAll('.mant-pieza').forEach(function (fila, i) { fila.querySelector('.ficha-numero').textContent = String(i + 1); });
    }

    function resumir() {
        var cards = document.querySelectorAll('#fichas-mural .mant-card');
        var vigentes = 0;
        var sinGuia = 0;
        var retiradas = 0;
        cards.forEach(function (card) {
            if (card.dataset.estado === 'retirada') {
                retiradas += 1;
                return;
            }
            vigentes += 1;
            if (card.dataset.guia !== '1') {
                sinGuia += 1;
            }
        });
        document.getElementById('fichas-cuenta').textContent = String(cards.length);
        var texto = vigentes + ' vigentes · ' + sinGuia + ' sin guía visual';
        if (retiradas) {
            texto += ' · ' + retiradas + (retiradas === 1 ? ' retirada' : ' retiradas');
        }
        document.getElementById('fichas-meta').textContent = texto;
        construirFiltrosServicio();
    }

    function limpiarAlta() {
        if (formFila) {
            formFila.classList.remove('is-editing');
        }
        formFila = null;
        fichaEditandoId = null;
        titulo.textContent = 'Nueva ficha';
        modo.textContent = 'Alta';
        guardar.textContent = 'Crear ficha';
        cancelar.hidden = false;
        codigo.value = '';
        nombre.value = '';
        servicio.value = '';
        poblarTipos('');
        guia.checked = false;
        foto.value = '';
        pintarPiezas([]);
        aviso('', false);
    }

    function htmlCard() {
        return '<div class="cat-photo is-empty"><span class="js-guia-label">Sin guía visual</span></div><div class="cat-body"><strong class="js-codigo"></strong><h2 class="js-nombre"></h2><span class="cat-servicio js-servicio"></span><p class="js-detalle"></p><div class="mant-acciones"><button class="btn btn-ghost js-editar" type="button">Editar</button><button class="btn btn-ghost js-retirar" type="button">Retirar</button></div></div>';
    }

    function escribirCard(card, datos) {
        var total = datos.instrumentos.reduce(function (suma, pieza) { return suma + Number(pieza.cantidad || 0); }, 0);
        card.dataset.estado = datos.estado || 'vigente';
        card.dataset.guia = datos.guia ? '1' : '0';
        card.dataset.servicio = datos.servicio_real || 'Sin servicio asignado';
        card.classList.toggle('mant-retirada', card.dataset.estado === 'retirada');
        var photo = card.querySelector('.cat-photo');
        var retirada = card.dataset.estado === 'retirada';
        photo.classList.toggle('is-empty', retirada || !datos.guia);
        card.querySelector('.js-guia-label').textContent = retirada ? 'Retirada' : (datos.guia ? 'Con guía visual' : 'Sin guía visual');
        card.querySelector('.js-codigo').textContent = datos.codigo;
        card.querySelector('.js-nombre').textContent = datos.nombre;
        card.querySelector('.js-servicio').textContent = datos.servicio_real || datos.servicio;
        card.querySelector('.js-detalle').textContent = datos.tipo + ' · ' + total + (total === 1 ? ' pieza' : ' piezas');
    }

    document.getElementById('agregar-pieza').addEventListener('click', function () {
        if (!tipo.value) { aviso('Selecciona primero el tipo de conjunto.', true); return; }
        if (!opcionesConjunto().length) { aviso('Este tipo de conjunto no tiene instrumentos registrados.', true); return; }
        piezas.appendChild(filaPieza(null));
        actualizarVista();
    });

    piezas.addEventListener('click', function (evento) {
        var quitar = evento.target.closest('.js-quitar');
        if (!quitar) {
            return;
        }
        quitar.closest('.mant-pieza').remove();
        if (!piezas.children.length) {
            pintarPiezas([]);
            return;
        }
        actualizarVista();
    });

    document.getElementById('ficha-form').addEventListener('input', actualizarVista);
    document.getElementById('ficha-form').addEventListener('change', actualizarVista);

    document.getElementById('fichas-mural').addEventListener('click', function (evento) {
        var editar = evento.target.closest('.js-editar');
        var retirar = evento.target.closest('.js-retirar');
        if (editar) {
            if (formFila) {
                formFila.classList.remove('is-editing');
            }
            formFila = editar.closest('.mant-card');
            formFila.classList.add('is-editing');
            var clave = formFila.querySelector('.js-codigo').textContent;
            var ficha = fichas[clave];
            codigo.value = clave;
            nombre.value = ficha.nombre;
            servicio.value = String(ficha.servicio_id || '');
            poblarTipos(String(ficha.tipo_conjunto_id || ''));
            fichaEditandoId = ficha.id || null;
            guia.checked = !!ficha.guia;
            foto.value = '';
            pintarPiezas(ficha.instrumentos);
            titulo.textContent = 'Editar ficha';
            modo.textContent = 'Cambio';
            guardar.textContent = 'Guardar cambios';
            cancelar.hidden = false;
            aviso('', false);
            document.getElementById('ficha-form').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            document.getElementById('ficha-encabezado').textContent = 'Editar ficha';
            mostrarTab('nueva-ficha');
            nombre.focus();
        }
        if (!retirar) {
            return;
        }
        var card = retirar.closest('.mant-card');
        if (card.dataset.estado === 'retirada') {
            return;
        }
        var claveRetiro = card.querySelector('.js-codigo').textContent;
        card.dataset.estado = 'retirada';
        card.classList.add('mant-retirada');
        card.querySelector('.cat-photo').classList.add('is-empty');
        card.querySelector('.js-guia-label').textContent = 'Retirada';
        retirar.disabled = true;
        retirar.textContent = 'Retirada';
        if (fichas[claveRetiro]) {
            fichas[claveRetiro].estado = 'retirada';
        }
        aviso(claveRetiro + ' se marcó como retirada solo en esta vista temporal.', false);
        resumir();
    });

    cancelar.addEventListener('click', function () {
        limpiarAlta();
        aviso('', false);
        mostrarTab('catalogo');
    });

    document.getElementById('ficha-form').addEventListener('submit', async function (evento) {
        evento.preventDefault();
        var contenido = leerPiezas();
        if (!codigo.value.trim() || !nombre.value.trim() || !servicio.value || !tipo.value) {
            aviso('Completa código, nombre, servicio y tipo de conjunto.', true); return;
        }
        if (!contenido.length || contenido.some(function (p) { return !p.instrumento_id || !Number.isInteger(p.cantidad) || p.cantidad < 1; })) {
            aviso('Selecciona al menos un instrumento y una cantidad válida por fila.', true); return;
        }
        if (new Set(contenido.map(function (p) { return p.instrumento_id; })).size !== contenido.length) {
            aviso('No repitas instrumentos. Modifica su cantidad en la fila existente.', true); return;
        }
        var datos = new FormData(this);
        datos.set('codigo', codigo.value.trim().toUpperCase());
        datos.set('nombre', nombre.value.trim());
        datos.set('servicio_id', servicio.value);
        datos.set('tipo_conjunto_id', tipo.value);
        datos.set('guia_visual', guia.checked ? '1' : '0');
        contenido.forEach(function (p, i) {
            datos.set('instrumentos[' + i + '][instrumento_id]', String(p.instrumento_id));
            datos.set('instrumentos[' + i + '][cantidad]', String(p.cantidad));
        });
        if (fichaEditandoId) datos.set('_method', 'PUT');
        guardar.disabled = true;
        guardar.textContent = 'Guardando…';
        aviso('', false);
        try {
            var url = fichaEditandoId
                ? @json(route('mockups.catalogo.fichas.actualizar', ['ficha' => '__ID__'])).replace('__ID__', encodeURIComponent(fichaEditandoId))
                : @json(route('mockups.catalogo.fichas.guardar'));
            var respuesta = await fetch(url, {
                method: 'POST', body: datos, credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            var resultado = await respuesta.json();
            if (!respuesta.ok) throw new Error(resultado.errors ? Object.values(resultado.errors).flat().join(' ') : (resultado.message || 'No fue posible guardar la ficha.'));
            window.location.href = @json(route('mockups.catalogo.admin')) + '?ficha_guardada=1';
        } catch (e) {
            aviso(e.message || 'Error al guardar la ficha.', true);
        } finally {
            guardar.disabled = false;
            guardar.textContent = fichaEditandoId ? 'Guardar cambios' : 'Crear ficha';
        }
    });
    if (new URLSearchParams(window.location.search).has('ficha_guardada')) {
        var avisoCatalogo = document.createElement('p');
        avisoCatalogo.className = 'adv-ok';
        avisoCatalogo.textContent = 'Ficha guardada correctamente en la base de datos.';
        document.getElementById('tab-catalogo').prepend(avisoCatalogo);
    }
    // CORRECCIÓN: El selector visual global .sigte-lista mantiene una copia
    // desactualizada cuando poblarTipos() modifica las opciones del SELECT real.
    // Estos DOS campos son dependientes y deben funcionar como SELECT nativos.
    // No afecta a los demás desplegables de SIGTE.
    (function mantenerSelectsNativosDeFicha() {
        var panelFicha = document.getElementById('tab-nueva-ficha');

        function restaurarSelects() {
            ['tipo', 'servicio'].forEach(function (id) {
                var selector = document.getElementById(id);
                if (!selector) return;
                var envoltorio = selector.closest('.sigte-lista');
                if (envoltorio) {
                    // Reutilizar el SELECT original conserva listeners y valores.
                    envoltorio.replaceWith(selector);
                }
                selector.classList.remove('sigte-lista-nativa');
                selector.setAttribute('data-sigte-lista', '1');
                // Por si el inicializador también ocultó el SELECT en línea.
                ['display', 'position', 'opacity', 'visibility', 'pointer-events', 'width', 'height'].forEach(function (prop) {
                    selector.style.removeProperty(prop);
                });
            });
        }

        // Detecta también si el script global envuelve los SELECT después.
        var observador = new MutationObserver(restaurarSelects);
        observador.observe(panelFicha, { childList: true, subtree: true });
        document.addEventListener('DOMContentLoaded', restaurarSelects);
        window.addEventListener('load', restaurarSelects);
        restaurarSelects();
    })();

    poblarTipos('');
    pintarPiezas([]);
    agruparPorArea();
    construirFiltrosServicio();
</script>
@endsection
