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

                // BUSCAR LA FICHA DEL INSUMO
                const tarjeta = botonElemento.closest('.inv-insumo-card');

                if (tarjeta) {

                    const stock = Number(elemento.stock);
                    const minimo = Number(elemento.minimo);
                    const estadoAnterior = tarjeta.dataset.estado;

                    // ACTUALIZAR LOS DATOS INTERNOS DE LA FICHA
                    tarjeta.dataset.minimo = minimo;

                    // ACTUALIZAR EL MÍNIMO VISIBLE
                    const indicadorMinimo = tarjeta.querySelector(
                        '.inv-insumo-minimo[data-minimo-celda]'
                    );

                    if (indicadorMinimo) {
                        indicadorMinimo.textContent = minimo;
                    }

                    // CALCULAR EL NUEVO ESTADO DEL INSUMO
                    let estado;

                    if (stock <= 0 || (minimo > 0 && stock <= Math.floor(minimo / 2))) {
                        estado = 'critico';
                    } else if (stock < minimo) {
                        estado = 'bajo';
                    } else {
                        estado = 'ok';
                    }

                    // ACTUALIZAR EL ESTADO INTERNO Y EL COLOR DE LA FICHA
                    tarjeta.dataset.estado = estado;

                    tarjeta.classList.remove(
                        'stock-critico',
                        'stock-bajo',
                        'stock-ok'
                    );

                    tarjeta.classList.add(`stock-${estado}`);

                    // ACTUALIZAR LA ETIQUETA DEL ESTADO
                    const contenedorEstado = tarjeta.querySelector('.inv-insumo-estado');

                    if (contenedorEstado) {

                        contenedorEstado.replaceChildren();

                        const indicador = document.createElement('span');

                        indicador.className = estado === 'ok'
                            ? 'pill pill-ok'
                            : estado === 'bajo'
                                ? 'pill pill-warn'
                                : 'pill pill-danger';

                        indicador.textContent = estado === 'ok'
                            ? 'OK'
                            : estado === 'bajo'
                                ? 'Bajo mínimo'
                                : 'Crítico';

                        contenedorEstado.append(indicador);
                    }

                    // ACTUALIZAR LOS DATOS DE TODOS LOS BOTONES DE LA FICHA
                    tarjeta.querySelectorAll('button').forEach((boton) => {
                        boton.dataset.stock = stock;
                        boton.dataset.minimo = minimo;
                    });

                    // ACTUALIZAR EL CONTADOR GENERAL DE STOCK BAJO
                    const totalBajo = document.getElementById('inv-total-bajo');

                    if (totalBajo) {
                        totalBajo.textContent =
                            Number(totalBajo.textContent) +
                            (estado !== 'ok' ? 1 : 0) -
                            (estadoAnterior !== 'ok' ? 1 : 0);
                    }

                    // ACTUALIZAR LA ALERTA DEL RESUMEN
                    const alertaResumen = document.getElementById('inv-alerta-resumen');

                    if (alertaResumen && totalBajo) {
                        alertaResumen.classList.toggle(
                            'is-alert',
                            Number(totalBajo.textContent) > 0
                        );
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
