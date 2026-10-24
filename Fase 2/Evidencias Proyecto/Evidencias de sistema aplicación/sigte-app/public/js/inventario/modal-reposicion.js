/* MODAL PARA ACTUALIZAR EL ELEMENTO */
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('inv-modal-reposicion');
    const botones = document.querySelectorAll('.inv-editar-stock');
    const cerrar = document.getElementById('inv-cerrar-reposicion');
    const overlay = document.getElementById('inv-modal-overlay');
    const elemento = document.getElementById('elemento');
    const nombreElemento = document.getElementById('inv-elemento-nombre');
    const stockInfo = document.getElementById('inv-modal-stock');
    const cantidad = document.getElementById('cantidad');
    const cantidadError = document.getElementById('inv-cantidad-error');
    const queda = document.getElementById('inv-queda');
    const salaInput = modal?.querySelector('input[name="sala"]');
    const historialLista = document.getElementById('inv-historial-lista');
    const historialCargando = document.getElementById('inv-historial-cargando');
    const historialVacio = document.getElementById('inv-historial-vacio');
    const historialError = document.getElementById('inv-historial-error');
    const formularioReposicion = cantidad?.closest('form');
    const toastConfirmacion = document.getElementById('inv-toast-confirmacion');
    const toastConfirmacionTexto = document.getElementById('inv-toast-confirmacion-texto');

    if (
        !modal || !cerrar || !overlay || !elemento ||
        !nombreElemento || !stockInfo || !cantidad ||
        !cantidadError || !queda || !formularioReposicion
    ) {
        return;
    }

    let botonAnterior = null;

    let temporizadorConfirmacion;

    function mostrarConfirmacion(mensaje) {
        if (!toastConfirmacion || !toastConfirmacionTexto) {
            return;
        }

        clearTimeout(temporizadorConfirmacion);

        toastConfirmacionTexto.textContent = mensaje;
        toastConfirmacion.hidden = false;

        temporizadorConfirmacion = setTimeout(function () {
            toastConfirmacion.hidden = true;
        }, 2000);
    }

    function validarCantidad() {
        const valor = cantidad.value.trim();
        const unidades = Number(valor);

        const valida =
            valor !== '' &&
            Number.isInteger(unidades) &&
            unidades >= 1 &&
            unidades <= 9999;

        // Mostrar u ocultar el error según la cantidad ingresada.
        cantidadError.hidden = valida;
        cantidad.classList.toggle('is-invalid', !valida);

        // Si la cantidad no es válida, ocultar la vista previa.
        if (!valida) {
            queda.textContent = '';
            queda.hidden = true;
            return false;
        }

        const stock = Number(elemento.dataset.stock);
        const minimo = Number(elemento.dataset.minimo);
        const total = stock + unidades;

        // Actualizar la vista previa del stock.
        queda.textContent = total >= minimo
            ? `Quedaría en ${total}. La alerta de stock se apaga.`
            : `Quedaría en ${total}. Sigue bajo el mínimo (${minimo}).`;

        queda.hidden = false;

        return true;
    }

    function abrirModal(boton) {
        botonAnterior = boton;

        elemento.value = boton.dataset.codigo;

        if (salaInput) {
            salaInput.value = boton.dataset.sala;
        }

        elemento.dataset.stock = boton.dataset.stock;
        elemento.dataset.minimo = boton.dataset.minimo;

        nombreElemento.textContent = boton.dataset.nombre;
        stockInfo.textContent =
            `Stock actual: ${boton.dataset.stock} · Mínimo: ${boton.dataset.minimo}`;

        
        cantidad.value = '';

        cantidadError.hidden = true;
        cantidad.classList.remove('is-invalid');

        queda.textContent = '';
        queda.hidden = true;

        modal.hidden = false;
        document.body.style.overflow = 'hidden';

        cargarHistorial(boton);
        cantidad.focus();

    }

    function cerrarModal() {
        modal.hidden = true;
        document.body.style.overflow = '';

        if (botonAnterior) {
            botonAnterior.focus();
        }
    }

    async function cargarHistorial(boton) {
        historialLista.replaceChildren();
        historialCargando.hidden = false;
        historialVacio.hidden = true;
        historialError.hidden = true;

        try {
            const sala = boton.dataset.sala;
            const codigo = boton.dataset.codigo;

            const url =
                `/consulta/inventario/${encodeURIComponent(sala)}/${encodeURIComponent(codigo)}/historial`;

            const respuesta = await fetch(url, {
                headers: { Accept: 'application/json' }
            });

            if (!respuesta.ok) {
                throw new Error('No se pudo cargar el historial.');
            }

            const datos = await respuesta.json();

            if (!datos.historial || datos.historial.length === 0) {
                historialVacio.hidden = false;
                return;
            }

            datos.historial.forEach(function (reposicion) {
                const item = document.createElement('li');
                const detalle = document.createElement('strong');
                const fecha = document.createElement('span');

                detalle.textContent =
                    `Reposición · ${reposicion.cantidad} unidades`;
                fecha.textContent =
                    `${reposicion.fecha} · ${reposicion.autor}`;

                item.append(detalle, fecha);
                historialLista.appendChild(item);
            });

        } catch (error) {
            historialError.hidden = false;
        } finally {
            historialCargando.hidden = true;
        }
    }

    botones.forEach(function (boton) {
        boton.addEventListener('click', function () {
            abrirModal(boton);
        });
    });

    cerrar.addEventListener('click', cerrarModal);
    overlay.addEventListener('click', cerrarModal);

    cantidad.addEventListener('input', validarCantidad);

    
    formularioReposicion.addEventListener('submit', async function (evento) {
        evento.preventDefault();

        const valida = validarCantidad();

        if (!valida) {
            cantidad.focus();
            return;
        }

        const botonEnviar = formularioReposicion.querySelector(
            'button[type="submit"], input[type="submit"]'
        );

        const textoOriginal = botonEnviar
            ? botonEnviar.textContent
            : '';

        if (botonEnviar) {
            botonEnviar.disabled = true;
            if (botonEnviar.tagName === 'BUTTON') {
                botonEnviar.textContent = 'Registrando...';
            }
        }

        try {
            const respuesta = await fetch(formularioReposicion.action, {
                method: 'POST',
                body: new FormData(formularioReposicion),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            let datos;

            try {
                datos = await respuesta.json();
            } catch (error) {
                throw new Error(
                    'El servidor no devolvió una respuesta JSON válida.'
                );
            }

            if (!respuesta.ok || datos.ok !== true) {
                const primerErrorValidacion = datos.errors
                    ? Object.values(datos.errors).flat()[0]
                    : null;

                throw new Error(
                    datos.mensaje ||
                    primerErrorValidacion ||
                    datos.message ||
                    'No se pudo registrar la reposición.'
                );
            }

            if (
                !datos.elemento ||
                datos.elemento.stock === undefined ||
                datos.elemento.minimo === undefined ||
                datos.elemento.codigo === undefined ||
                datos.elemento.sala === undefined
            ) {
                throw new Error(
                    'La reposición se registró, pero la respuesta del servidor está incompleta. Verifica el stock antes de intentarlo nuevamente.'
                );
            }

            const nuevoStock = Number(datos.elemento.stock);
            const minimo = Number(datos.elemento.minimo);
            const codigo = String(datos.elemento.codigo);
            const sala = String(datos.elemento.sala);

            // Actualizar los datos del botón de la tabla.
            if (botonAnterior) {
                botonAnterior.dataset.stock = nuevoStock;
                botonAnterior.dataset.minimo = minimo;

                const fila = botonAnterior.closest('tr');

                if (fila) {
                    fila.dataset.stock = nuevoStock;

                    if (fila.cells.length > 3) {
                        fila.cells[3].textContent = nuevoStock;
                    }
                }
            }

            // Actualizar el stock mostrado dentro del modal.
            elemento.dataset.stock = nuevoStock;
            elemento.dataset.minimo = minimo;

            stockInfo.textContent =
                `Stock actual: ${nuevoStock} · Mínimo: ${minimo}`;

            // Limpiar la cantidad y los mensajes de validación.
            cantidad.value = '';
            cantidadError.hidden = true;
            cantidad.classList.remove('is-invalid');
            queda.textContent = '';
            queda.hidden = true;

            // Mostrar la confirmación sin cerrar el modal.
            mostrarConfirmacion(
                datos.mensaje || 'Reposición registrada correctamente.'
            );

            // Actualizar el historial con los datos guardados.
            if (botonAnterior) {
                await cargarHistorial(botonAnterior);
            }

        } catch (error) {
            alert(error.message || 'Ocurrió un error al registrar la reposición.');
        } finally {
            if (botonEnviar) {
                botonEnviar.disabled = false;

                if (botonEnviar.tagName === 'BUTTON') {
                    botonEnviar.textContent = textoOriginal || 'Registrar';
                }
            }
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            cerrarModal();
        }
    });
});
