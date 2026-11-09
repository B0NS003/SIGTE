/* TRANSICIÓN Y NAVEGACIÓN DE CONFIGURACIÓN DE MÍNIMOS */
document.addEventListener('DOMContentLoaded', () => {

    const contenedor = document.getElementById('inv-vistas-contenedor');

    const botonesAbrir = document.querySelectorAll('.inv-configurar-minimo');

    const botonVolver = document.getElementById('inv-volver-minimo');

    const vistaInventario = contenedor?.querySelector('.inv-book');
    const vistaMinimo = document.getElementById('inv-vista-minimo');

    const fechaHora = document.getElementById('inv-minimo-fecha-hora');

    if (
        !contenedor ||
        !vistaInventario ||
        !vistaMinimo ||
        !botonVolver
    ) {
        return;
    }

    /* AJUSTAR ALTURA DEL CONTENEDOR */
    function ajustarAlturaVista(vista) {
        if (!vista) return;

        contenedor.style.height = `${vista.scrollHeight}px`;
    }

    /* ACTUALIZAR FECHA Y HORA */
    let temporizadorFechaHora = null;

    function iniciarFechaHora() {
        actualizarFechaHoraCompartida(fechaHora);

        if (temporizadorFechaHora) {
            clearInterval(temporizadorFechaHora);
        }

        temporizadorFechaHora = setInterval(() => {
            actualizarFechaHoraCompartida(fechaHora);
        }, 1000);
    }

    function detenerFechaHora() {
        clearInterval(temporizadorFechaHora);
        temporizadorFechaHora = null;
    }

    ajustarAlturaVista(vistaInventario);

    /* ABRIR LA VISTA DE CONFIGURACIÓN */
    botonesAbrir.forEach(boton => {
        boton.addEventListener('click', () => {

            document.getElementById('minimo-codigo').value = boton.dataset.codigo;
            document.getElementById('minimo-nombre').textContent = boton.dataset.nombre;
            document.getElementById('minimo-stock').textContent = boton.dataset.stock;
            document.getElementById('minimo-actual').textContent = boton.dataset.minimo;

            document.getElementById('minimo-nuevo').value = boton.dataset.minimo;

            document.getElementById('minimo-error').hidden = true;
            document.getElementById('minimo-mensaje').hidden = true;

            contenedor.classList.add('mostrar-minimo');

            iniciarFechaHora();

            vistaInventario.setAttribute('aria-hidden', 'true');
            vistaMinimo.setAttribute('aria-hidden', 'false');

            requestAnimationFrame(() => {
                ajustarAlturaVista(vistaMinimo);
            });
        });
    });

    /* VOLVER AL INVENTARIO */
    botonVolver.addEventListener('click', () => {

        contenedor.classList.remove('mostrar-minimo');

        detenerFechaHora();

        vistaInventario.setAttribute('aria-hidden', 'false');
        vistaMinimo.setAttribute('aria-hidden', 'true');

        requestAnimationFrame(() => {
            ajustarAlturaVista(vistaInventario);
        });
    });

    /* AJUSTAR ALTURA AL CAMBIAR EL TAMAÑO DE LA VENTANA */
    window.addEventListener('resize', () => {

        if (contenedor.classList.contains('mostrar-minimo')) {
            ajustarAlturaVista(vistaMinimo);
        } else {
            ajustarAlturaVista(vistaInventario);
        }

    });

    /* CONFIGURAR Y VALIDAR EL STOCK MÍNIMO */
    const formulario = document.getElementById('form-configurar-minimo');
    const nuevoMinimo = document.getElementById('minimo-nuevo');
    const errorMinimo = document.getElementById('minimo-error');
    const botonGuardar = document.getElementById('inv-guardar-minimo');

    const toastMinimo = document.getElementById('inv-toast-minimo');
    const toastMinimoTexto = document.getElementById('inv-toast-minimo-texto');

    formulario?.addEventListener('submit', async (event) => {

        event.preventDefault();

        const valor = nuevoMinimo.value.trim();
        const unidades = Number(valor);

        const valido =
            valor !== '' &&
            Number.isInteger(unidades) &&
            unidades >= 0 &&
            unidades <= 9999;

        if (!valido) {
            errorMinimo.hidden = false;
            nuevoMinimo.classList.add('is-invalid');

            requestAnimationFrame(() => {
                ajustarAlturaVista(vistaMinimo);
            });

            nuevoMinimo.focus();

            return;
        }

        errorMinimo.hidden = true;
        nuevoMinimo.classList.remove('is-invalid');

        const restaurarBoton = iniciarEnvioBoton(
            botonGuardar,
            'Guardando...'
        );

        try {
            const respuesta = await fetch(formulario.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(formulario)
            });

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.ok) {
                throw new Error(
                    datos.mensaje ||
                    datos.message ||
                    'No fue posible guardar el mínimo.'
                );
            }

            const elemento = datos.elemento;

            // ACTUALIZAR LOS VALORES DE LA VISTA
            document.getElementById('minimo-stock').textContent = elemento.stock;
            document.getElementById('minimo-actual').textContent = elemento.minimo;
            nuevoMinimo.value = elemento.minimo;

            // ACTUALIZAR EL MÍNIMO GUARDADO
            const botonElemento = document.querySelector(
                `.inv-configurar-minimo[data-codigo="${CSS.escape(elemento.codigo)}"]`
            );

            if (botonElemento) {
                botonElemento.dataset.minimo = elemento.minimo;

                // ACTUALIZAR TAMBIÉN LA CELDA DEL MÍNIMO EN LA MISMA FILA
                const fila = botonElemento.closest('tr');
                const celdaMinimo = fila?.querySelector('[data-minimo-celda]');

                if (celdaMinimo) {
                    celdaMinimo.textContent = elemento.minimo;
                }

                // ACTUALIZAR EL ESTADO Y COLOR DE LA FILA
                if (fila) {
                    const stock = Number(elemento.stock);
                    const minimo = Number(elemento.minimo);

                    let estado;

                    if (stock <= 0 || (minimo > 0 && stock <= Math.floor(minimo / 2))) {
                        estado = 'critico';
                    } else if (stock < minimo) {
                        estado = 'bajo';
                    } else {
                        estado = 'ok';
                    }

                    fila.classList.remove('stock-critico', 'stock-bajo', 'stock-ok');
                    fila.classList.add(`stock-${estado}`);
                    fila.dataset.estado = estado;

                    const celdaEstado = fila.querySelector('.pill');

                    if (celdaEstado) {
                        celdaEstado.classList.remove('pill-ok', 'pill-warn', 'pill-danger');

                        if (estado === 'critico') {
                            celdaEstado.classList.add('pill-danger');
                            celdaEstado.innerHTML = '<span class="sigte-icon"></span> Crítico';
                        } else if (estado === 'bajo') {
                            celdaEstado.classList.add('pill-warn');
                            celdaEstado.innerHTML = 'Bajo mínimo';
                        } else {
                            celdaEstado.classList.add('pill-ok');
                            celdaEstado.innerHTML = 'OK';
                        }
                    }
                }
            }

            // MOSTRAR CONFIRMACIÓN
            toastMinimo.classList.remove('inv-toast-minimo--error');

            mostrarToastCompartido(
                toastMinimo,
                toastMinimoTexto,
                datos.mensaje || 'Configuración guardada correctamente.'
            );

        } catch (error) {

            toastMinimo.classList.add('inv-toast-minimo--error');

            mostrarToastCompartido(
                toastMinimo,
                toastMinimoTexto,
                error.message || 'Ocurrió un error al guardar el mínimo.'
            );

        } finally {

            restaurarBoton();

            requestAnimationFrame(() => {
                ajustarAlturaVista(vistaMinimo);
            });
        }
    });

    /* OCULTAR EL ERROR AL ESCRIBIR */
    nuevoMinimo?.addEventListener('input', () => {
        errorMinimo.hidden = true;
        nuevoMinimo.classList.remove('is-invalid');
    });
});
