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
