document.addEventListener('DOMContentLoaded', () => {

    // ELEMENTOS PRINCIPALES DEL MODAL
    const modal = document.getElementById('inv-modal-consumo');
    const overlay = document.getElementById('inv-modal-consumo-overlay');
    const formulario = document.getElementById('form-inv-consumo');

    if (!modal || !formulario) return;

    // Botones del modal
    const btnCerrar = document.getElementById('inv-cerrar-consumo');
    const btnCancelar = document.getElementById('inv-cancelar-consumo');
    const btnRegistrar = document.getElementById('inv-registrar-consumo');

    // Campos del formulario
    const campoElemento = document.getElementById('inv-consumo-elemento');
    const campoNombre = document.getElementById('inv-consumo-nombre');
    const campoStock = document.getElementById('inv-consumo-stock');
    const campoCantidad = document.getElementById('inv-consumo-cantidad');
    const campoObservacion = document.getElementById('inv-consumo-observacion');

    // Contador de caracteres de la observación
    const contadorObservacion = document.getElementById('inv-consumo-contador');

    // Mensajes relacionados con la cantidad
    const mensajeCantidad = document.getElementById('inv-consumo-cantidad-error');
    const mensajeQueda = document.getElementById('inv-consumo-queda');

    // Mensaje de confirmación
    const toast = document.getElementById('inv-consumo-toast');
    const toastTexto = document.getElementById('inv-consumo-toast-texto');

    // Elementos del historial
    const historialLista = document.getElementById('inv-consumo-historial-lista');
    const historialCargando = document.getElementById('inv-consumo-historial-cargando');
    const historialVacio = document.getElementById('inv-consumo-historial-vacio');
    const historialError = document.getElementById('inv-consumo-historial-error');

    // VARIABLES DE CONTROL
    let botonOrigen = null;
    let stockDisponible = 0;
    let stockMinimo = 0;

    // CONTADOR DE CARACTERES

    // Actualiza el contador según los caracteres escritos en el campo de observación.
    const actualizarContadorObservacion = () => {
        if (!campoObservacion || !contadorObservacion) return;

        contadorObservacion.textContent =
            `${campoObservacion.value.length}/255`;
    };

    // Detecta cuando el operador escribe o elimina texto.
    campoObservacion?.addEventListener(
        'input',
        actualizarContadorObservacion
    );

    // MENSAJES Y CONFIRMACIÓN
    const ocultarMensajes = () => {
        mensajeCantidad.hidden = true;
        mensajeQueda.hidden = true;
        toast.hidden = true;
    };

    const mostrarToast = (mensaje) => {
        console.log('Mensaje recibido:', mensaje);
        console.log('Elemento toast:', toast);
        console.log('Texto del toast:', toastTexto);

        mostrarToastCompartido(
            toast,
            toastTexto,
            mensaje
        );

        console.log('Estado del toast:', toast.hidden);
    };

    // FECHA Y HORA DE CHILE
    function actualizarFechaHora() {
        const encabezado = document.getElementById('inv-consumo-fecha-hora');
        actualizarFechaHoraCompartida(encabezado);
    }

    // VALIDACIÓN DE CANTIDAD
    const validarCantidad = () => {
        const cantidad = Number(campoCantidad.value);

        mensajeCantidad.hidden = true;
        mensajeQueda.hidden = true;

        if (
            campoCantidad.value.trim() === '' ||
            !Number.isInteger(cantidad) ||
            cantidad < 1 ||
            cantidad > 9999
        ) {
            mensajeCantidad.textContent = 'Ingresa una cantidad válida.';
            mensajeCantidad.hidden = false;
            return false;
        }

        if (cantidad > stockDisponible) {
            mensajeCantidad.textContent =
                `No puedes registrar ${cantidad} unidades. Solo hay ${stockDisponible} disponibles.`;
            mensajeCantidad.hidden = false;
            return false;
        }

        mensajeQueda.textContent =
            `Después del consumo quedarán ${stockDisponible - cantidad} unidades.`;
        mensajeQueda.hidden = false;

        return true;
    };

    // CARGAR HISTORIAL DE CONSUMOS
    const cargarHistorial = async (sala, codigo) => {
        historialLista.replaceChildren();
        historialCargando.hidden = false;
        historialVacio.hidden = true;
        historialError.hidden = true;

        try {
            const url = `/consulta/inventario/${encodeURIComponent(sala)}/${encodeURIComponent(codigo)}/historial-consumos`;

            const respuesta = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!respuesta.ok) {
                throw new Error('No fue posible obtener el historial.');
            }

            const datos = await respuesta.json();
            historialCargando.hidden = true;

            if (!datos.historial || datos.historial.length === 0) {
                historialVacio.hidden = false;
                return;
            }

            datos.historial.forEach((movimiento) => {
                const item = document.createElement('li');
                item.classList.add('inv-consumo-historial-item');

                // Datos principales del consumo
                const cabecera = document.createElement('div');
                cabecera.classList.add('inv-consumo-historial-cabecera');

                const detalle = document.createElement('strong');
                detalle.classList.add('inv-consumo-historial-detalle');
                detalle.textContent = `Consumo · ${movimiento.cantidad} unidades`;

                const fecha = document.createElement('span');
                fecha.classList.add('inv-consumo-historial-fecha');
                fecha.textContent = `${movimiento.fecha} · ${movimiento.autor}`;

                cabecera.append(detalle, fecha);
                item.appendChild(cabecera);

                // Observación, si existe
                if (movimiento.observacion) {
                    const observacion = document.createElement('div');
                    observacion.classList.add('inv-consumo-historial-observacion');

                    const etiqueta = document.createElement('span');
                    etiqueta.classList.add('inv-consumo-historial-etiqueta');
                    etiqueta.textContent = 'Observación';

                    const texto = document.createElement('p');
                    texto.classList.add('inv-consumo-historial-texto');
                    texto.textContent = movimiento.observacion;

                    observacion.append(etiqueta, texto);
                    item.appendChild(observacion);
                }

                historialLista.appendChild(item);
            });

        } catch (error) {
            historialCargando.hidden = true;
            historialError.hidden = false;
        }
    };

    // ABRIR MODAL
    const abrirModal = (boton) => {
        botonOrigen = boton;

        const codigo = boton.dataset.codigo;
        const sala = boton.dataset.sala || 'lavado';
        const nombre = boton.dataset.nombre || 'Elemento seleccionado';

        stockDisponible = Number(boton.dataset.stock) || 0;
        stockMinimo = Number(boton.dataset.minimo) || 0;

        campoElemento.value = codigo;
        campoNombre.textContent = nombre;
        campoStock.textContent = `Stock actual: ${stockDisponible} · Mínimo: ${stockMinimo}`;

        campoCantidad.value = '';
        campoCantidad.max = String(stockDisponible);

        // Limpiar observación y reiniciar su contador
        if (campoObservacion) {
            campoObservacion.value = '';
            actualizarContadorObservacion();
        }

        ocultarMensajes();
        actualizarFechaHora();

        modal.hidden = false;
        document.body.style.overflow = 'hidden';

        cargarHistorial(sala, codigo);

        campoCantidad.focus();
    };

    // CERRAR MODAL
    const cerrarModal = () => {
        modal.hidden = true;
        document.body.style.overflow = '';

        formulario.reset();

        // Reiniciar el contador después de limpiar el formulario
        actualizarContadorObservacion();

        ocultarMensajes();

        historialLista.replaceChildren();
        historialCargando.hidden = true;
        historialVacio.hidden = true;
        historialError.hidden = true;

        botonOrigen?.focus();
        botonOrigen = null;
    };

    // ACTUALIZACIÓN AUTOMÁTICA DE FECHA Y HORA
    const temporizadorFechaHora = setInterval(function () {
        if (!modal.hidden) {
            actualizarFechaHora();
        }
    }, 1000);

    // EVENTOS DE BOTONES Y FORMULARIO

    // Detectar qué insumo seleccionó el operador
    document.addEventListener('click', (event) => {
        const boton = event.target.closest('.inv-registrar-consumo');

        if (boton) {
            abrirModal(boton);
        }
    });

    // Validar cantidad mientras se escribe
    campoCantidad.addEventListener('input', validarCantidad);

    // Cerrar o cancelar el modal
    btnCerrar?.addEventListener('click', cerrarModal);
    btnCancelar?.addEventListener('click', cerrarModal);
    overlay?.addEventListener('click', cerrarModal);

    // Cerrar con la tecla Escape
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            cerrarModal();
        }
    });

    // REGISTRAR CONSUMO
    formulario.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!validarCantidad()) return;

        const restaurarBoton = iniciarEnvioBoton(btnRegistrar);

        try {
            const respuesta = await fetch(formulario.action, {
                method: 'POST',
                body: new FormData(formulario),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.ok) {
                throw new Error(datos.mensaje || 'No fue posible registrar el consumo.');
            }

            const nuevoStock = Number(datos.elemento.stock);

            // Actualizar el stock en la tabla
            if (botonOrigen) {
                botonOrigen.dataset.stock = String(nuevoStock);

                const fila = botonOrigen.closest('tr');

                if (fila) {
                    fila.dataset.stock = String(nuevoStock);

                    const celdaStock = fila.cells[3];

                    if (celdaStock) {
                        celdaStock.textContent = nuevoStock;
                    }
                }
            }

            stockDisponible = nuevoStock;
            campoStock.textContent = `Stock actual: ${nuevoStock} · Mínimo: ${stockMinimo}`;

            campoCantidad.value = '';
            campoCantidad.max = String(nuevoStock);

            // Limpiar observación y reiniciar el contador
            if (campoObservacion) {
                campoObservacion.value = '';
                actualizarContadorObservacion();
            }

            ocultarMensajes();
            mostrarToast(datos.mensaje);

            await cargarHistorial('lavado', campoElemento.value);

        } catch (error) {
            mostrarToast(error.message);

        } finally {
            restaurarBoton();
        }
    });

    // INICIALIZACIÓN DEL CONTADOR

    // Mostrar el valor inicial al cargar la página.
    actualizarContadorObservacion();

});
