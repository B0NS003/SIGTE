// Administrar catálogo: pestañas, filtros, instrumentos y alta/edición de fichas.
var datosCatalogo = JSON.parse(document.getElementById('catalogo-admin-datos').textContent);
var fichas = datosCatalogo.fichas;
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
var serviciosDisponibles = datosCatalogo.servicios;
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
var instrumentosDemo = datosCatalogo.instrumentos;
var imagenInstrumentoRespaldo = datosCatalogo.imagenRespaldo;
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
            ? datosCatalogo.rutas.instrumentoActualizar.replace('__ID__', encodeURIComponent(idEnEdicion))
            : datosCatalogo.rutas.instrumentoGuardar;
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
var serviciosFicha = datosCatalogo.serviciosFicha;
var tiposFicha = datosCatalogo.tiposFicha;
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
    card.querySelector('.sigte-guide-text').textContent = 'Retirada';
    retirar.disabled = true;
    retirar.querySelector('span').textContent = 'Retirada';
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
            ? datosCatalogo.rutas.fichaActualizar.replace('__ID__', encodeURIComponent(fichaEditandoId))
            : datosCatalogo.rutas.fichaGuardar;
        var respuesta = await fetch(url, {
            method: 'POST', body: datos, credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        var resultado = await respuesta.json();
        if (!respuesta.ok) throw new Error(resultado.errors ? Object.values(resultado.errors).flat().join(' ') : (resultado.message || 'No fue posible guardar la ficha.'));
        window.location.href = datosCatalogo.rutas.admin + '?ficha_guardada=1';
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
