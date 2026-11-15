/* FUNCIONES COMPARTIDAS ENTRE LOS MODALES DEL INVENTARIO */

/* Obtener fecha y hora de Chile */
function actualizarFechaHoraCompartida(elemento) {
    if (!elemento) {
        return;
    }

    const ahora = new Date();

    const fecha = new Intl.DateTimeFormat('es-CL', {
        timeZone: 'America/Santiago',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(ahora);

    const hora = new Intl.DateTimeFormat('es-CL', {
        timeZone: 'America/Santiago',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    }).format(ahora);

    const usuario = elemento.dataset.usuario;

    elemento.textContent = `${usuario} · ${fecha} · ${hora}`;
}

/* Preparar y restaurar el botón durante el envío */
function iniciarEnvioBoton(boton, textoCargando = 'Registrando...') {
    if (!boton) {
        return () => {};
    }

    const esBoton = boton.tagName === 'BUTTON';
    const textoOriginal = esBoton ? boton.textContent : boton.value;

    boton.disabled = true;

    if (esBoton) {
        boton.textContent = textoCargando;
    } else {
        boton.value = textoCargando;
    }

    return () => {
        boton.disabled = false;

        if (esBoton) {
            boton.textContent = textoOriginal;
        } else {
            boton.value = textoOriginal;
        }
    };
}

/* Mostrar mensaje de confirmación compartido entre los modales */
const temporizadoresToast = new WeakMap();

function mostrarToastCompartido(
    toast,
    toastTexto,
    mensaje,
    duracion = 3000
) {
    if (!toast || !toastTexto) {
        return;
    }

    const temporizadorAnterior = temporizadoresToast.get(toast);

    if (temporizadorAnterior) {
        clearTimeout(temporizadorAnterior);
    }

    toastTexto.textContent = mensaje;
    toast.hidden = false;

    const temporizador = setTimeout(() => {
        toast.hidden = true;
        temporizadoresToast.delete(toast);
    }, duracion);

    temporizadoresToast.set(toast, temporizador);
}

/** Tarjeta de historial de inventario (reposición / movimientos). */
function crearTarjetaHistorialInventario(movimiento) {
    const tarjeta = document.createElement('article');
    tarjeta.className = 'inv-historial-item';

    const cabecera = document.createElement('div');
    cabecera.className = 'inv-historial-item-cabecera';

    const tipo = document.createElement('strong');
    tipo.textContent = movimiento.tipo || 'Reposición';

    const meta = document.createElement('span');
    meta.className = 'inv-historial-item-meta';
    meta.textContent = movimiento.fecha || '';

    cabecera.append(tipo, meta);

    const detalle = document.createElement('p');
    detalle.className = 'inv-historial-item-detalle';
    const unidades = Number(movimiento.cantidad) === 1 ? 'unidad' : 'unidades';
    detalle.textContent = `${movimiento.cantidad} ${unidades}`;

    const autor = document.createElement('small');
    autor.className = 'inv-historial-item-autor';
    autor.textContent = movimiento.autor || 'Sin usuario';

    tarjeta.append(cabecera, detalle, autor);

    if (movimiento.observacion) {
        const observacion = document.createElement('p');
        observacion.className = 'inv-historial-item-obs';
        observacion.textContent = movimiento.observacion;
        tarjeta.append(observacion);
    }

    return tarjeta;
}

/**
 * Paginación estilo SIGTE para historiales de inventario.
 * @param {{actual:number,ultima:number,total:number}} paginacion
 * @param {(pagina:number)=>void} alCambiar
 */
function renderPaginacionHistorialInventario(nav, paginacion, alCambiar) {
    if (!nav) {
        return;
    }

    nav.replaceChildren();

    if (!paginacion || paginacion.total <= 0) {
        nav.hidden = true;
        return;
    }

    const info = document.createElement('p');
    info.className = 'sigte-paginacion-info';
    info.textContent = `Total ${paginacion.total} · Pág. ${paginacion.actual}/${paginacion.ultima}`;

    const lista = document.createElement('ul');
    lista.className = 'sigte-paginacion-lista';

    const agregar = (etiqueta, pagina, estado) => {
        const li = document.createElement('li');
        if (estado === 'activa') {
            li.className = 'is-activa';
            const span = document.createElement('span');
            span.textContent = etiqueta;
            li.append(span);
        } else if (estado === 'disabled') {
            li.className = 'is-disabled';
            const span = document.createElement('span');
            span.textContent = etiqueta;
            span.setAttribute('aria-hidden', 'true');
            li.append(span);
        } else {
            const a = document.createElement('a');
            a.href = '#';
            a.textContent = etiqueta;
            a.addEventListener('click', (e) => {
                e.preventDefault();
                alCambiar(pagina);
            });
            li.append(a);
        }
        lista.append(li);
    };

    agregar('‹', paginacion.actual - 1, paginacion.actual <= 1 ? 'disabled' : 'link');

    const maxBotones = 5;
    let desde = Math.max(1, paginacion.actual - 2);
    let hasta = Math.min(paginacion.ultima, desde + maxBotones - 1);
    desde = Math.max(1, hasta - maxBotones + 1);

    for (let n = desde; n <= hasta; n += 1) {
        agregar(String(n), n, n === paginacion.actual ? 'activa' : 'link');
    }

    agregar('›', paginacion.actual + 1, paginacion.actual >= paginacion.ultima ? 'disabled' : 'link');

    lista.hidden = paginacion.ultima <= 1;
    nav.append(info, lista);
    nav.hidden = false;
}
