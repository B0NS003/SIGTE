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
            <div class="ops-search admin-search"><label class="ops-search-field" for="buscar-fichas"><span aria-hidden="true">⌕</span><input type="search" id="buscar-fichas" placeholder="Buscar por código, nombre, tipo o área…" autocomplete="off"></label></div>
            <div class="admin-filter-sections">
                <section class="admin-filter-section">
                    <button class="ops-filter-label admin-filter-toggle" type="button" aria-expanded="false" aria-controls="filtros-servicio" data-filter-toggle="servicio"><span class="admin-filter-chevron" aria-hidden="true">▸</span> Filtrar por servicio</button>
                    <div class="ops-filters" id="filtros-servicio" aria-label="Filtrar fichas por servicio" hidden></div>
                </section>
                <section class="admin-filter-section">
                    <button class="ops-filter-label admin-filter-toggle" type="button" aria-expanded="true" aria-controls="filtros-area" data-filter-toggle="area"><span class="admin-filter-chevron" aria-hidden="true">▾</span> Filtrar por área</button>
                    <div class="ops-filters" id="filtros-area" aria-label="Filtrar fichas por área"></div>
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
                    <article class="cat-card mant-card" data-estado="vigente" data-servicio="{{ $item['servicio_real'] ?? 'Sin servicio asignado' }}" data-area="{{ $item['area'] ?? $item['servicio'] }}" data-guia="{{ $item['guia'] ? '1' : '0' }}">
                        <div class="cat-photo {{ $item['guia'] ? '' : 'is-empty' }}">
                            <span class="js-guia-label">{{ $item['guia'] ? 'Con guía visual' : 'Sin guía visual' }}</span>
                        </div>
                        <div class="cat-body">
                            <strong class="js-codigo">{{ $item['codigo'] }}</strong>
                            <h2 class="js-nombre">{{ $item['nombre'] }}</h2>
                            <span class="cat-servicio js-servicio">{{ $item['area'] ?? $item['servicio'] }}</span>
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
            <div class="mant-head"><div><h2>Instrumentos registrados</h2><p>Consulta los instrumentos utilizados en las fichas. Más adelante se conectará a la tabla de instrumentos.</p></div><span class="badge" id="instrumentos-cuenta">0</span></div>
            <div class="ops-search admin-search"><label class="ops-search-field" for="buscar-instrumentos"><span aria-hidden="true">⌕</span><input type="search" id="buscar-instrumentos" placeholder="Buscar instrumento…" autocomplete="off"></label></div>
            <div class="admin-instrument-grid" id="instrumentos-mural"></div>
            <p class="admin-empty" id="instrumentos-vacio" hidden>No se encontraron instrumentos.</p>
        </section>

        <div class="admin-panel-tab" id="tab-nueva-ficha" role="tabpanel" hidden>
        <div class="mant-head"><div><h2 id="ficha-encabezado">Crear nueva ficha</h2><p>Registra los datos generales y el contenido de la caja.</p></div></div>
        <form class="mant-editor ficha-admin" id="ficha-form" action="{{ route('mockups.catalogo.admin') }}" method="get" onsubmit="return false">
            <div class="ficha-layout">
                <section class="ficha-guia is-empty" id="preview-guia" aria-label="Así se ve la ficha">
                    <span class="ficha-kicker" id="preview-kicker">Sin guía visual</span>
                    <strong id="preview-titulo">Así la va a ver el servicio</strong>
                    <p id="preview-texto">Si la caja no tiene foto, igual se puede crear. La lista de piezas alcanza.</p>
                    <div class="ficha-mosaico" id="preview-mosaico"></div>
                </section>
                <section class="panel ficha-datos">
                    <div class="panel-head">
                        <h2 id="ficha-titulo">Nueva ficha</h2>
                        <span class="badge" id="ficha-modo">Alta</span>
                    </div>
                    <p class="adv-ok" id="ficha-ok" role="status" hidden></p>
                    <p class="auth-error" id="ficha-error" role="alert" hidden></p>
                    <div class="mant-datos">
                        <label for="codigo">Código
                            <input id="codigo" name="codigo" type="text" placeholder="SET-XXX-00" autocomplete="off">
                        </label>
                        <label for="nombre">Nombre
                            <input id="nombre" name="nombre" type="text" placeholder="Nombre de la caja" autocomplete="off">
                        </label>
                        <label for="tipo">Tipo
                            <select id="tipo" name="tipo">
                                <option value="">Elige el tipo</option>
                                @foreach (['Caja quirúrgica', 'Set de instrumental', 'Set de ópticas', 'Set de accesorios', 'Caja de implantes', 'Otros'] as $opcionTipo)
                                    <option value="{{ $opcionTipo }}">{{ $opcionTipo }}</option>
                                @endforeach
                                @foreach ($tipos as $tipo)
                                    @if (!in_array($tipo, ['Caja quirúrgica', 'Set de instrumental', 'Set de ópticas', 'Set de accesorios', 'Caja de implantes', 'Otros']))
                                        <option value="{{ $tipo }}">{{ $tipo }} (anterior)</option>
                                    @endif
                                @endforeach
                            </select>
                        </label>
                        <label for="servicio">Servicio
                            <select id="servicio" name="servicio">
                                <option value="">Elige el servicio</option>
                                @foreach ($servicios as $servicio)
                                    <option value="{{ $servicio }}">{{ $servicio }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label for="area">Área quirúrgica
                            <select id="area" name="area">
                                <option value="">Elige el área</option>
                                @foreach ($areas ?? [] as $areaOpcion)
                                    <option value="{{ $areaOpcion }}">{{ $areaOpcion }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="ficha-guia-row">
                        <label class="check">
                            <input id="guia" type="checkbox">
                            <span>Incluir guía visual</span>
                        </label>
                        <label class="ficha-foto" for="foto">
                            <span>Fotografía general de la caja</span>
                            <input id="foto" name="foto" type="file" accept="image/*">
                        </label>
                    </div>
                </section>
            </div>

            <section class="panel ficha-lista">
                <div class="panel-head">
                    <h2>Instrumentos de la ficha</h2>
                    <button class="btn btn-ghost" id="agregar-pieza" type="button">Agregar pieza</button>
                </div>
                <div id="piezas"></div>
                <div class="cuenta-pie">
                    <p class="ficha-ayuda">En esta etapa los instrumentos se agregan a modo de demostración. Luego se seleccionarán desde los instrumentos registrados.</p>
                    <div class="recv-actions">
                        <button class="btn btn-ghost" id="ficha-cancelar" type="button" hidden>Cancelar</button>
                        <button class="btn btn-primary" id="ficha-guardar" type="button">Crear ficha</button>
                    </div>
                </div>
            </section>
        </form>
        </div>

        <section class="admin-panel-tab" id="tab-nuevo-instrumento" role="tabpanel" hidden>
            <div class="mant-head"><div><h2 id="instrumento-form-titulo">Registrar nuevo instrumento</h2><p>Un instrumento podrá utilizarse en varias fichas, sin duplicar su registro.</p></div></div>
            <form class="panel admin-instrument-form" id="instrumento-form" onsubmit="return false">
                <p class="admin-demo-notice">Formulario visual. El registro en PostgreSQL se habilitará en la siguiente etapa.</p>
                <div class="mant-datos">
                    <label for="instrumento-nombre">Nombre del instrumento <input id="instrumento-nombre" type="text" placeholder="Ej. Pinza Kocher" autocomplete="off"></label>
                    <label for="instrumento-tamano">Tamaño <input id="instrumento-tamano" type="text" placeholder="Ej. 14 cm" autocomplete="off"></label>
                    <label for="instrumento-codigo">Código del fabricante <input id="instrumento-codigo" type="text" placeholder="Código o referencia" autocomplete="off"></label>
                    <label for="instrumento-foto">Fotografía del instrumento <input id="instrumento-foto" type="file" accept="image/jpeg,image/png,image/webp"></label>
                </div>
                <div class="recv-actions admin-form-actions">
                    <button type="button" class="btn btn-ghost" id="instrumento-limpiar">Limpiar</button>
                    <button type="button" class="btn btn-primary" disabled title="Se habilitará al conectar el guardado">Guardar instrumento (próximamente)</button>
                </div>
            </form>
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
                document.getElementById('instrumento-form-titulo').textContent = 'Registrar nuevo instrumento';
            }
            mostrarTab(boton.dataset.tab);
        });
    });
    var servicioActivo = '';
    var areaActiva = '';
    var serviciosDisponibles = @json($servicios);
    var areasDisponibles = @json($areas ?? []);
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
            var area = card.dataset.area || 'Sin área asignada';
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
                && (!areaActiva || card.dataset.area === areaActiva)
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
        pintar('servicio','filtros-servicio',serviciosDisponibles,servicioActivo,function(v){servicioActivo=v;},'area',areaActiva);
        pintar('area','filtros-area',areasDisponibles,areaActiva,function(v){areaActiva=v;},'servicio',servicioActivo);
        filtrarFichas();
    }
    document.getElementById('buscar-fichas').addEventListener('input', filtrarFichas);
    var instrumentosDemo = [];
    Object.values(fichas).forEach(function (ficha) {
        (ficha.instrumentos || []).forEach(function (inst) {
            if (inst.nombre && !instrumentosDemo.some(function (x) { return x.nombre.toLocaleLowerCase() === inst.nombre.toLocaleLowerCase(); })) {
                instrumentosDemo.push({nombre:inst.nombre, codigo:inst.codigo || '', tamano:''});
            }
        });
    });
    function pintarInstrumentos(filtro) {
        var mural = document.getElementById('instrumentos-mural');
        mural.replaceChildren();
        var filtrados = instrumentosDemo.filter(function (x) { return (x.nombre + ' ' + x.codigo).toLocaleLowerCase().includes((filtro || '').toLocaleLowerCase()); });
        filtrados.forEach(function (inst) {
            var card = document.createElement('article');
            card.className = 'admin-instrument-card';
            var icono = document.createElement('span');
            icono.className = 'ficha-thumb is-empty';
            icono.textContent = inst.nombre.charAt(0).toUpperCase();
            var contenido = document.createElement('div');
            var nombre = document.createElement('strong');
            nombre.textContent = inst.nombre;
            var meta = document.createElement('small');
            meta.textContent = inst.codigo || 'Sin código de fabricante';
            contenido.append(nombre,meta);
            var editar = document.createElement('button');
            editar.className = 'btn btn-ghost';
            editar.type = 'button';
            editar.textContent = 'Editar';
            editar.addEventListener('click', function () {
                document.getElementById('instrumento-form').reset();
                document.getElementById('instrumento-nombre').value = inst.nombre;
                document.getElementById('instrumento-codigo').value = inst.codigo;
                document.getElementById('instrumento-form-titulo').textContent = 'Editar instrumento';
                mostrarTab('nuevo-instrumento');
            });
            card.append(icono,contenido,editar);
            mural.appendChild(card);
        });
        document.getElementById('instrumentos-vacio').hidden = filtrados.length > 0;
        document.getElementById('instrumentos-cuenta').textContent = String(filtrados.length);
    }
    document.getElementById('buscar-instrumentos').addEventListener('input', function (e) { pintarInstrumentos(e.target.value); });
    document.getElementById('instrumento-limpiar').addEventListener('click', function () { document.getElementById('instrumento-form').reset(); });
    pintarInstrumentos('');
    var formFila = null;
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
    var area = document.getElementById('area');
    var guia = document.getElementById('guia');
    var foto = document.getElementById('foto');
    var piezas = document.getElementById('piezas');
    var mosaico = document.getElementById('preview-mosaico');

    function aviso(texto, esError) {
        ok.hidden = esError || !texto;
        error.hidden = !esError || !texto;
        (esError ? error : ok).textContent = texto || '';
    }

    function filaPieza(pieza) {
        var fila = document.createElement('div');
        fila.className = 'mant-pieza';
        fila.innerHTML = '<span class="ficha-thumb is-empty js-letra" aria-hidden="true">—</span><input class="pieza-nombre" type="text" placeholder="Instrumento o material" autocomplete="off"><input class="pieza-cant" type="number" min="1" placeholder="Cant."><label class="check"><input class="pieza-foto" type="checkbox"><span>Con foto</span></label><button class="btn btn-ghost js-quitar" type="button">Quitar</button>';
        if (pieza && pieza.nombre) {
            fila.querySelector('.pieza-nombre').value = pieza.nombre;
            fila.querySelector('.pieza-cant').value = pieza.cantidad || '';
            fila.querySelector('.pieza-foto').checked = !!pieza.imagen;
            marcarLetra(fila);
        }
        return fila;
    }

    function marcarLetra(fila) {
        var texto = fila.querySelector('.pieza-nombre').value.trim();
        var letra = fila.querySelector('.js-letra');
        var conFoto = fila.querySelector('.pieza-foto').checked && texto;
        letra.textContent = conFoto ? texto.charAt(0).toUpperCase() : '—';
        letra.classList.toggle('is-empty', !conFoto);
    }

    function pintarPiezas(lista) {
        piezas.replaceChildren();
        (lista && lista.length ? lista : [{}]).forEach(function (pieza) {
            piezas.appendChild(filaPieza(pieza));
        });
        actualizarVista();
    }

    function leerPiezas() {
        return Array.prototype.map.call(piezas.querySelectorAll('.mant-pieza'), function (fila) {
            return {
                nombre: fila.querySelector('.pieza-nombre').value.trim(),
                cantidad: Number(fila.querySelector('.pieza-cant').value),
                imagen: fila.querySelector('.pieza-foto').checked
            };
        }).filter(function (pieza) {
            return pieza.nombre !== '' || pieza.cantidad;
        });
    }

    function actualizarVista() {
        var seccion = document.getElementById('preview-guia');
        var conGuia = guia.checked;
        var tituloCaja = nombre.value.trim();
        seccion.classList.toggle('is-empty', !conGuia);
        document.getElementById('preview-kicker').textContent = conGuia ? 'Guía visual' : 'Sin guía visual';
        document.getElementById('preview-titulo').textContent = tituloCaja || (conGuia ? 'Cómo se reconoce el set' : 'Así la va a ver el servicio');
        document.getElementById('preview-texto').textContent = conGuia
            ? 'Referencia de las piezas que tienen foto.'
            : 'Si la caja no tiene foto, igual se puede crear. La lista de piezas alcanza.';
        mosaico.replaceChildren();
        if (!conGuia) {
            return;
        }
        piezas.querySelectorAll('.mant-pieza').forEach(function (fila) {
            marcarLetra(fila);
            var texto = fila.querySelector('.pieza-nombre').value.trim();
            if (!fila.querySelector('.pieza-foto').checked || !texto) {
                return;
            }
            var figura = document.createElement('figure');
            var thumb = document.createElement('span');
            thumb.className = 'ficha-thumb';
            thumb.textContent = texto.charAt(0).toUpperCase();
            var caption = document.createElement('figcaption');
            caption.textContent = texto;
            figura.appendChild(thumb);
            figura.appendChild(caption);
            mosaico.appendChild(figura);
        });
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
        titulo.textContent = 'Nueva ficha';
        modo.textContent = 'Alta';
        guardar.textContent = 'Crear ficha';
        cancelar.hidden = true;
        codigo.value = '';
        nombre.value = '';
        tipo.value = '';
        servicio.value = '';
        area.value = '';
        guia.checked = false;
        foto.value = '';
        pintarPiezas([{}]);
    }

    function htmlCard() {
        return '<div class="cat-photo is-empty"><span class="js-guia-label">Sin guía visual</span></div><div class="cat-body"><strong class="js-codigo"></strong><h2 class="js-nombre"></h2><span class="cat-servicio js-servicio"></span><p class="js-detalle"></p><div class="mant-acciones"><button class="btn btn-ghost js-editar" type="button">Editar</button><button class="btn btn-ghost js-retirar" type="button">Retirar</button></div></div>';
    }

    function escribirCard(card, datos) {
        var total = datos.instrumentos.reduce(function (suma, pieza) { return suma + Number(pieza.cantidad || 0); }, 0);
        card.dataset.estado = datos.estado || 'vigente';
        card.dataset.guia = datos.guia ? '1' : '0';
        card.dataset.servicio = datos.servicio_real || 'Sin servicio asignado';
        card.dataset.area = datos.area || 'Sin área asignada';
        card.classList.toggle('mant-retirada', card.dataset.estado === 'retirada');
        var photo = card.querySelector('.cat-photo');
        var retirada = card.dataset.estado === 'retirada';
        photo.classList.toggle('is-empty', retirada || !datos.guia);
        card.querySelector('.js-guia-label').textContent = retirada ? 'Retirada' : (datos.guia ? 'Con guía visual' : 'Sin guía visual');
        card.querySelector('.js-codigo').textContent = datos.codigo;
        card.querySelector('.js-nombre').textContent = datos.nombre;
        card.querySelector('.js-servicio').textContent = datos.area;
        card.querySelector('.js-detalle').textContent = datos.tipo + ' · ' + total + (total === 1 ? ' pieza' : ' piezas');
    }

    document.getElementById('agregar-pieza').addEventListener('click', function () {
        piezas.appendChild(filaPieza(null));
    });

    piezas.addEventListener('click', function (evento) {
        var quitar = evento.target.closest('.js-quitar');
        if (!quitar) {
            return;
        }
        quitar.closest('.mant-pieza').remove();
        if (!piezas.children.length) {
            pintarPiezas([{}]);
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
            tipo.value = ficha.tipo;
            servicio.value = ficha.servicio_real || '';
            area.value = ficha.area || ficha.servicio || '';
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

    guardar.addEventListener('click', function () {
        var clave = codigo.value.trim().toUpperCase();
        var contenido = leerPiezas();
        if (!clave || !nombre.value.trim() || !tipo.value || !servicio.value || !area.value) {
            aviso('Completa código, nombre, tipo, servicio y área.', true);
            return;
        }
        if (!contenido.length || contenido.some(function (pieza) { return !pieza.nombre || !(pieza.cantidad >= 1); })) {
            aviso('Cada pieza necesita nombre y cantidad.', true);
            return;
        }
        var repetida = Array.prototype.some.call(document.querySelectorAll('#fichas-mural .js-codigo'), function (celda) {
            var esLaMisma = formFila && celda.closest('.mant-card') === formFila;
            return !esLaMisma && celda.textContent.toUpperCase() === clave;
        });
        if (repetida) {
            aviso(clave + ' ya está en el catálogo.', true);
            return;
        }
        var datos = {
            codigo: clave,
            nombre: nombre.value.trim(),
            tipo: tipo.value,
            servicio: area.value,
            servicio_real: servicio.value,
            area: area.value,
            guia: guia.checked,
            instrumentos: contenido,
            estado: formFila ? formFila.dataset.estado : 'vigente'
        };
        if (formFila) {
            var anterior = formFila.querySelector('.js-codigo').textContent;
            if (anterior !== clave) {
                delete fichas[anterior];
            }
            fichas[clave] = datos;
            escribirCard(formFila, datos);
            aviso('Vista previa actualizada. Los cambios aún no están guardados en la base de datos.', false);
            limpiarAlta();
            agruparPorArea();
            resumir();
            return;
        }
        var card = document.createElement('article');
        card.className = 'cat-card mant-card';
        card.innerHTML = htmlCard();
        fichas[clave] = datos;
        escribirCard(card, datos);
        document.getElementById('fichas-mural').prepend(card);
        aviso('Ficha agregada a esta vista temporal. No está guardada en la base de datos.', false);
        limpiarAlta();
        agruparPorArea();
        resumir();
    });

    pintarPiezas([{}]);
    agruparPorArea();
    construirFiltrosServicio();
</script>
@endsection
