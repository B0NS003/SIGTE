document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.getElementById('inv-vistas-contenedor');
    const botonesAbrir = document.querySelectorAll('.inv-abrir-movimiento');
    let botonSeleccionado = null;
    const botonVolver = document.getElementById('inv-volver-inventario');

    const vistaInventario = contenedor?.querySelector('.inv-book');
    const vistaRegistro = document.getElementById('inv-vista-registro');

    /* AJUSTE DINÁMICO DE ALTURA
       Adapta el contenedor a la altura de la vista que está activa. */
       
    function ajustarAlturaVista(vista) {
        if (!vista) return;

        // Obtiene la altura completa del contenido de la vista.
        const altura = vista.scrollHeight;

        // Aplica esa altura al contenedor principal.
        contenedor.style.height = `${altura}px`;
    }

    const fechaHora = document.getElementById('inv-registro-fecha-hora');

    const historialLista = document.getElementById('inv-movimiento-historial-lista');
    const historialCargando = document.getElementById('inv-movimiento-historial-cargando');
    const historialVacio = document.getElementById('inv-movimiento-historial-vacio');
    const historialError = document.getElementById('inv-movimiento-historial-error');

    const nombre = document.getElementById('inv-movimiento-nombre');
    const stock = document.getElementById('inv-movimiento-stock');
    const proceso = document.getElementById('inv-movimiento-proceso');

    const codigo = document.getElementById('inv-movimiento-elemento');
    const tipo = document.getElementById('inv-movimiento-tipo');

    const selectorTipo = document.getElementById('inv-selector-tipo');
    const selectorBoton = document.getElementById('inv-selector-tipo-boton');
    const selectorValor = document.getElementById('inv-selector-tipo-valor');
    const selectorOpciones = document.getElementById('inv-selector-tipo-opciones');
    const opcionesTipo = selectorOpciones?.querySelectorAll('[data-value]') ?? [];

    // ABRIR Y CERRAR EL SELECTOR PERSONALIZADO
    function cerrarSelectorTipo() {
        selectorOpciones.hidden = true;
        selectorBoton.setAttribute('aria-expanded', 'false');
    }

    selectorBoton.addEventListener('click', () => {
        const estaAbierto = selectorBoton.getAttribute('aria-expanded') === 'true';

        selectorOpciones.hidden = estaAbierto;
        selectorBoton.setAttribute('aria-expanded', String(!estaAbierto));
    });

    // SELECCIONAR EL TIPO DE MOVIMIENTO
    opcionesTipo.forEach((opcion) => {
        opcion.addEventListener('click', () => {
            const valor = opcion.dataset.value;

            tipo.value = valor;
            selectorValor.textContent = opcion.textContent.trim();

            opcionesTipo.forEach((item) => {
                item.setAttribute(
                    'aria-selected',
                    String(item.dataset.value === valor)
                );
            });

            tipo.dispatchEvent(new Event('change', { bubbles: true }));

            cerrarSelectorTipo();
            selectorBoton.focus();
        });
    });

    // CERRAR EL SELECTOR AL HACER CLIC FUERA
    document.addEventListener('click', (e) => {
        if (!selectorTipo.contains(e.target)) {
            cerrarSelectorTipo();
        }
    });

    // CERRAR EL SELECTOR CON ESCAPE
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarSelectorTipo();
        }
    });

    const cantidad = document.getElementById('inv-movimiento-cantidad');
    const cantidadLabel = document.getElementById('inv-movimiento-cantidad-label');
    const ayuda = document.getElementById('inv-movimiento-ayuda');
    const error = document.getElementById('inv-movimiento-error');
    const formulario = document.getElementById('form-inv-movimiento');
    const guardar = document.getElementById('inv-movimiento-guardar');
    const mensaje = document.getElementById('inv-movimiento-mensaje');

    const observacion = document.getElementById('inv-movimiento-observacion');
    const contador = document.getElementById('inv-movimiento-contador');

    if (
        !contenedor ||
        !vistaInventario ||
        !vistaRegistro ||
        !botonVolver
    ) {
        return;
    }

    // OBSERVAR CAMBIOS DE ALTURA EN EL REGISTRO
    const observadorAltura = new MutationObserver(() => {
        if (!contenedor.classList.contains('mostrar-registro')) {
            return;
        }

        requestAnimationFrame(() => {
            ajustarAlturaVista(vistaRegistro);
        });
    });

    observadorAltura.observe(vistaRegistro, {
        childList: true,
        subtree: true,
        characterData: true,
        attributes: true,
        attributeFilter: ['hidden', 'class', 'style']
    });

    ajustarAlturaVista(vistaInventario);

    // Controla la actualización de fecha y hora.
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

    async function cargarHistorial() {
        historialLista.replaceChildren();
        historialCargando.hidden = false;
        historialVacio.hidden = true;
        historialError.hidden = true;

        const plantilla = vistaRegistro.dataset.historialUrl;

        const url = plantilla
            .replace(
                '__SALA__',
                encodeURIComponent(formulario.querySelector('input[name="sala"]').value)
            )
            .replace('__CODIGO__', encodeURIComponent(codigo.value));

        try {
            const respuesta = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!respuesta.ok) {
                throw new Error('No se pudo consultar el historial.');
            }

            const datos = await respuesta.json();

            historialCargando.hidden = true;

            if (!datos.historial.length) {
                historialVacio.hidden = false;
                return;
            }

            datos.historial.forEach((movimiento) => {
                const tarjeta = document.createElement('article');
                tarjeta.className = 'inv-historial-movimiento';

                const titulo = document.createElement('strong');
                titulo.textContent = movimiento.tipo;

                const detalle = document.createElement('p');
                detalle.textContent = `${movimiento.cantidad} unidades · ${movimiento.fecha}`;

                const autor = document.createElement('small');
                autor.textContent = `Registrado por: ${movimiento.autor}`;

                tarjeta.append(titulo, detalle, autor);

                if (movimiento.observacion) {
                    const observacion = document.createElement('p');
                    observacion.textContent = movimiento.observacion;
                    tarjeta.append(observacion);
                }

                historialLista.append(tarjeta);
            });

        } catch (e) {
            historialCargando.hidden = true;
            historialError.hidden = false;

        } finally {
            /* ACTUALIZAR ALTURA DESPUÉS DE CARGAR EL HISTORIAL */
            if (contenedor.classList.contains('mostrar-registro')) {
                requestAnimationFrame(() => {
                    ajustarAlturaVista(vistaRegistro);
                });
            }
        }
    }

    function validarCantidad() {
        const movimiento = tipo.value;
        const valor = Number(cantidad.value);
        const disponible = Number(stock.textContent);
        const enProceso = Number(proceso.textContent);

        error.hidden = true;
        guardar.disabled = true;

        if (!movimiento) {
            return;
        }

        if (['consumo', 'uso'].includes(movimiento) && valor > disponible) {
            error.textContent = `Solo hay ${disponible} unidades disponibles.`;
            error.hidden = false;
            return;
        }

        if (movimiento === 'devolucion' && valor > enProceso) {
            error.textContent = `Solo hay ${enProceso} unidades en proceso.`;
            error.hidden = false;
            return;
        }

        guardar.disabled = false;
    }

    tipo.addEventListener('change', () => {
        const movimiento = tipo.value;

        selectorValor.textContent = movimiento
            ? tipo.options[tipo.selectedIndex].text
            : 'Selecciona una opción';

        cantidad.disabled = false;
        cantidad.value = '';
        error.hidden = true;

        const configuracion = {
            consumo: {
                etiqueta: 'Cantidad que se consumirá',
                ayuda: 'Se descontará permanentemente del stock disponible.',
                boton: 'Registrar',
                observacion: 'Ej.: Material utilizado durante un procedimiento.'
            },
            uso: {
                etiqueta: 'Cantidad que se utilizará',
                ayuda: 'Se descontará del stock y pasará a unidades en proceso.',
                boton: 'Registrar',
                observacion: 'Ej.: Material destinado a una curación.'
            },
            devolucion: {
                etiqueta: 'Cantidad que se devolverá',
                ayuda: 'Se sumará al stock y se descontará de unidades en proceso.',
                boton: 'Registrar',
                observacion: 'Ej.: Unidades devueltas sin uso y en buen estado.'
            }
        };

        const datos = configuracion[movimiento];

        cantidadLabel.textContent = datos.etiqueta;
        ayuda.textContent = datos.ayuda;
        guardar.textContent = datos.boton;
        observacion.placeholder = datos.observacion;

        validarCantidad();
    });

    cantidad.addEventListener('input', () => {
        validarCantidad();

        requestAnimationFrame(() => {
            if (contenedor.classList.contains('mostrar-registro')) {
                ajustarAlturaVista(vistaRegistro);
            }
        });
    });

    formulario.addEventListener('submit', async (e) => {
        e.preventDefault();

        error.hidden = true;
        mensaje.hidden = true;

        if (!tipo.value) {
            error.textContent = 'Selecciona un tipo de movimiento.';
            error.hidden = false;
            return;
        }

        if (cantidad.value.trim() === '') {
            error.textContent = 'Este campo no puede quedar vacío.';
            error.hidden = false;
            return;
        }

        validarCantidad();

        if (guardar.disabled) {
            return;
        }

        const textoOriginal = guardar.textContent;
        guardar.disabled = true;
        guardar.textContent = 'Registrando...';

        try {
            const respuesta = await fetch(formulario.action, {
                method: 'POST',
                body: new FormData(formulario),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.ok) {
                throw new Error(
                    datos.mensaje || 'No fue posible registrar el movimiento.'
                );
            }

            mensaje.textContent = datos.mensaje;
            mensaje.hidden = false;

            stock.textContent = datos.elemento.stock;
            proceso.textContent = datos.elemento.en_proceso;

            if (botonSeleccionado) {
                botonSeleccionado.dataset.stock = datos.elemento.stock;
                botonSeleccionado.dataset.enProceso = datos.elemento.en_proceso;

                /* ACTUALIZAR LA FICHA DEL INSUMO */
                const tarjeta = botonSeleccionado.closest('.inv-insumo-card');

                if (tarjeta) {

                    const stockAnterior = Number(tarjeta.dataset.stock);
                    const procesoAnterior = Number(tarjeta.dataset.enProceso);
                    const estadoAnterior = tarjeta.dataset.estado;

                    // Calcular el nuevo estado del insumo.
                    const nuevoEstado = datos.elemento.stock <= 0 ||
                        (datos.elemento.minimo > 0 &&
                            datos.elemento.stock <= Math.floor(datos.elemento.minimo / 2))
                        ? 'critico'
                        : datos.elemento.stock < datos.elemento.minimo
                            ? 'bajo'
                            : 'ok';

                    // Actualizar los datos internos de la ficha.
                    tarjeta.dataset.stock = datos.elemento.stock;
                    tarjeta.dataset.enProceso = datos.elemento.en_proceso;
                    tarjeta.dataset.estado = nuevoEstado;

                    // Actualizar los indicadores de stock.
                    tarjeta.querySelector('.inv-insumo-stock').textContent =
                        datos.elemento.stock;

                    tarjeta.querySelector('.inv-insumo-proceso').textContent =
                        datos.elemento.en_proceso;

                    // Actualizar el color superior de la ficha.
                    tarjeta.classList.remove(
                        'stock-ok',
                        'stock-bajo',
                        'stock-critico'
                    );

                    tarjeta.classList.add(`stock-${nuevoEstado}`);

                    // Actualizar la etiqueta del estado.
                    const contenedorEstado = tarjeta.querySelector('.inv-insumo-estado');

                    if (contenedorEstado) {

                        contenedorEstado.replaceChildren();

                        const indicador = document.createElement('span');

                        indicador.className = nuevoEstado === 'ok'
                            ? 'pill pill-ok'
                            : nuevoEstado === 'bajo'
                                ? 'pill pill-warn'
                                : 'pill pill-danger';

                        indicador.textContent = nuevoEstado === 'ok'
                            ? 'OK'
                            : nuevoEstado === 'bajo'
                                ? 'Bajo mínimo'
                                : 'Crítico';

                        contenedorEstado.append(indicador);
                    }

                    // Actualizar los datos de los botones de acción de la ficha.
                    tarjeta.querySelectorAll('button').forEach((boton) => {
                        boton.dataset.stock = datos.elemento.stock;
                        boton.dataset.enProceso = datos.elemento.en_proceso;
                        boton.dataset.minimo = datos.elemento.minimo;
                    });

                    // Actualizar los contadores generales del inventario.
                    const totalStock = document.getElementById('inv-total-stock');
                    const totalProceso = document.getElementById('inv-total-proceso');
                    const totalBajo = document.getElementById('inv-total-bajo');
                    const alertaResumen = document.getElementById('inv-alerta-resumen');

                    if (totalStock) {
                        totalStock.textContent =
                            Number(totalStock.textContent) +
                            Number(datos.elemento.stock) - stockAnterior;
                    }

                    if (totalProceso) {
                        totalProceso.textContent =
                            Number(totalProceso.textContent) +
                            Number(datos.elemento.en_proceso) - procesoAnterior;
                    }

                    if (totalBajo) {
                        totalBajo.textContent =
                            Number(totalBajo.textContent) +
                            (nuevoEstado !== 'ok' ? 1 : 0) -
                            (estadoAnterior !== 'ok' ? 1 : 0);
                    }

                    if (alertaResumen && totalBajo) {
                        alertaResumen.classList.toggle(
                            'is-alert',
                            Number(totalBajo.textContent) > 0
                        );
                    }
                }
                cantidad.value = '';
                guardar.disabled = true;
                cargarHistorial();

            }

        } catch (e) {
            error.textContent = e.message;
            error.hidden = false;
        } finally {
            guardar.textContent = textoOriginal;
        }
    });

    observacion.addEventListener('input', () => {
        contador.textContent = `${observacion.value.length}/255`;
    });

    function reiniciarFormulario() {
        formulario.reset();

        tipo.value = '';

        selectorValor.textContent = 'Selecciona una opción';
        cerrarSelectorTipo();

        opcionesTipo.forEach((opcion) => {
            opcion.setAttribute('aria-selected', 'false');
        });

        cantidad.value = '';
        cantidad.disabled = true;

        guardar.disabled = true;
        guardar.textContent = 'Registrar';

        cantidadLabel.textContent = 'Cantidad';
        ayuda.textContent = 'Primero selecciona el tipo de movimiento.';

        observacion.placeholder = 'Selecciona un movimiento para ver un ejemplo.';
        contador.textContent = '0/255';

        error.hidden = true;
        mensaje.hidden = true;
    }

    botonesAbrir.forEach((boton) => {
        boton.addEventListener('click', () => {

            reiniciarFormulario();

            botonSeleccionado = boton;

            nombre.textContent = boton.dataset.nombre;
            stock.textContent = boton.dataset.stock;
            proceso.textContent = boton.dataset.enProceso;

            codigo.value = boton.dataset.codigo;

            const campoSala = formulario.querySelector(
                'input[name="sala"]'
            );

            campoSala.value = boton.dataset.sala;

            // Activa la transición lateral hacia el registro.
            contenedor.classList.add('mostrar-registro');

            vistaInventario.setAttribute('aria-hidden', 'true');
            vistaRegistro.setAttribute('aria-hidden', 'false');

            iniciarFechaHora();

            // Ajusta la altura del contenedor al contenido del registro.
            requestAnimationFrame(() => {
                ajustarAlturaVista(vistaRegistro);
            });

            selectorBoton.focus({ preventScroll: true });

            cargarHistorial();
        });
    });

    /* VOLVER AL INVENTARIO
       Recupera la altura natural de la vista de inventario. */
    botonVolver.addEventListener('click', () => {
        contenedor.classList.remove('mostrar-registro');

        vistaInventario.setAttribute('aria-hidden', 'false');
        vistaRegistro.setAttribute('aria-hidden', 'true');

        detenerFechaHora();

        // Ajusta la altura al contenido actual del inventario.
        requestAnimationFrame(() => {
            ajustarAlturaVista(vistaInventario);
        });
    });

    /* ADAPTACIÓN AL CAMBIAR EL TAMAÑO DE PANTALLA */
    window.addEventListener('resize', () => {

        requestAnimationFrame(() => {

            // Identifica cuál de las dos vistas está visible.
            const vistaActiva = contenedor.classList.contains('mostrar-registro')
                ? vistaRegistro
                : vistaInventario;

            // Ajusta el contenedor a la nueva distribución.
            ajustarAlturaVista(vistaActiva);
        });
    });
});
