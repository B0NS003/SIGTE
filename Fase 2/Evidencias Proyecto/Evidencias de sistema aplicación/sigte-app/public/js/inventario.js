document.addEventListener('DOMContentLoaded', function () {

    const formulario = document.getElementById('form-busqueda-inventario');
    const buscador = document.getElementById('busqueda-inventario');
    const botonLimpiar = document.getElementById('inv-limpiar-busqueda');
    const cuerpo = document.getElementById('cuerpo-inventario');

    if (!formulario || !buscador || !cuerpo) {
        return;
    }

    const filas = Array.from(
        cuerpo.querySelectorAll('tr[data-stock]')
    );

    const mensajeVacio = document.getElementById('inv-sin-resultados');

    const totalStock = document.getElementById('inv-total-stock');
    const totalProceso = document.getElementById('inv-total-proceso');
    const totalBajo = document.getElementById('inv-total-bajo');
    const alertaResumen = document.getElementById('inv-alerta-resumen');

    /* PAGINACIÓN */
    const paginacion = document.getElementById('inv-paginacion');
    const itemsPagina = document.getElementById('inv-items-pagina');
    const rango = document.getElementById('inv-rango');
    const paginas = document.getElementById('inv-paginas');
    const paginaAnterior = document.getElementById('inv-pagina-anterior');
    const paginaSiguiente = document.getElementById('inv-pagina-siguiente');

    let paginaActual = 1;
    let cantidadPorPagina = Number(itemsPagina?.value || 5);

    function normalizar(texto) {
        return texto
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function obtenerFilasCoincidentes() {

        const busqueda = normalizar(
            buscador.value.trim()
        );

        return filas.filter(function (fila) {
            return normalizar(fila.textContent).includes(busqueda);
        });
    }

    function actualizarResumen(filasVisibles) {

        let stock = 0;
        let enProceso = 0;
        let bajoMinimo = 0;

        filasVisibles.forEach(function (fila) {

            stock += Number(fila.dataset.stock);
            enProceso += Number(fila.dataset.enProceso);

            const estado = normalizar(
                fila.dataset.estado
            );

            if (
                estado === 'bajo' ||
                estado === 'critico'
            ) {
                bajoMinimo++;
            }
        });

        if (totalStock) {
            totalStock.textContent = stock;
        }

        if (totalProceso) {
            totalProceso.textContent = enProceso;
        }

        if (totalBajo) {
            totalBajo.textContent = bajoMinimo;
        }

        if (alertaResumen) {
            alertaResumen.classList.toggle(
                'is-alert',
                bajoMinimo > 0
            );
        }
    }

    function renderizarPaginacion(filasFiltradas) {

        if (
            !paginacion ||
            !itemsPagina ||
            !rango ||
            !paginas ||
            !paginaAnterior ||
            !paginaSiguiente
        ) {
            return;
        }

        const total = filasFiltradas.length;

        cantidadPorPagina = Number(
            itemsPagina.value
        );

        const totalPaginas = Math.ceil(
            total / cantidadPorPagina
        );

        /* Si al filtrar desaparece la página actual, volvemos a la última página disponible. */
        if (
            totalPaginas > 0 &&
            paginaActual > totalPaginas
        ) {
            paginaActual = totalPaginas;
        }

        if (total === 0) {
            paginaActual = 1;
        }

        const inicio =
            total === 0
                ? 0
                : (paginaActual - 1) * cantidadPorPagina;

        const fin =
            total === 0
                ? 0
                : Math.min(
                    inicio + cantidadPorPagina,
                    total
                );

        /* Ocultar todas las filas primero. */
        filas.forEach(function (fila) {
            fila.hidden = true;
        });

        /* Mostrar solamente las filas de la página actual. */
        filasFiltradas
            .slice(inicio, fin)
            .forEach(function (fila) {
                fila.hidden = false;
            });

        /* Mensaje cuando no hay resultados. */
        if (mensajeVacio) {
            mensajeVacio.hidden = total !== 0;
        }

        /* Texto: 1–10 de 37 */
        if (total > 0) {
            rango.textContent =
                `${inicio + 1}–${fin} de ${total}`;
        } else {
            rango.textContent = '0–0 de 0';
        }

        /* Botones anterior/siguiente. */
        paginaAnterior.disabled =
            paginaActual <= 1;

        paginaSiguiente.disabled =
            paginaActual >= totalPaginas ||
            totalPaginas === 0;

        /* Números de página. */
        paginas.innerHTML = '';

        for (
            let i = 1;
            i <= totalPaginas;
            i++
        ) {

            const boton =
                document.createElement('button');

            boton.type = 'button';
            boton.className = 'inv-pagina-numero';

            if (i === paginaActual) {
                boton.classList.add('is-active');
                boton.setAttribute(
                    'aria-current',
                    'page'
                );
            }

            boton.textContent = i;

            boton.addEventListener(
                'click',
                function () {

                    paginaActual = i;

                    actualizarVista();
                }
            );

            paginas.appendChild(boton);
        }

        /* Si solamente hay una página, no necesitamos mostrar los controles. */
        paginacion.hidden = false;
    }

    function actualizarVista() {

        const filasFiltradas =
            obtenerFilasCoincidentes();

        actualizarResumen(
            filasFiltradas
        );

        renderizarPaginacion(
            filasFiltradas
        );

        /* Mostrar / ocultar la X. */
        if (botonLimpiar) {
            botonLimpiar.hidden =
                buscador.value.length === 0;
        }
    }

    /* Evita que la búsqueda quede guardada en la URL. */
    buscador.removeAttribute('name');

    /* Buscar mientras se escribe. */
    buscador.addEventListener(
        'input',
        function () {

            /* Cuando cambia la búsqueda, volvemos a la página 1. */
            paginaActual = 1;

            actualizarVista();
        }
    );

    /* Evita recargar la página al presionar Enter o el botón Buscar. */
    formulario.addEventListener(
        'submit',
        function (evento) {

            evento.preventDefault();

            paginaActual = 1;

            actualizarVista();
        }
    );

    /* Limpiar búsqueda. */
    if (botonLimpiar) {

        botonLimpiar.addEventListener(
            'click',
            function () {

                buscador.value = '';

                paginaActual = 1;

                actualizarVista();

                buscador.focus();
            }
        );
    }

    /* Cambiar cantidad de elementos por página. */
    if (itemsPagina) {

        itemsPagina.addEventListener(
            'change',
            function () {

                cantidadPorPagina =
                    Number(itemsPagina.value);

                paginaActual = 1;

                actualizarVista();
            }
        );
    }

    /* Página anterior. */
    if (paginaAnterior) {

        paginaAnterior.addEventListener(
            'click',
            function () {

                if (paginaActual > 1) {

                    paginaActual--;

                    actualizarVista();
                }
            }
        );
    }

    /* Página siguiente. */
    if (paginaSiguiente) {

        paginaSiguiente.addEventListener(
            'click',
            function () {

                const filasFiltradas =
                    obtenerFilasCoincidentes();

                const totalPaginas =
                    Math.ceil(
                        filasFiltradas.length /
                        cantidadPorPagina
                    );

                if (
                    paginaActual <
                    totalPaginas
                ) {

                    paginaActual++;

                    actualizarVista();
                }
            }
        );
    }

    /* Vista inicial. */
    actualizarVista();
});

/* Modal para actualizar el elemento */
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
    const salaInput = modal.querySelector('input[name="sala"]');
    const historialLista = document.getElementById('inv-historial-lista');
    const historialCargando = document.getElementById('inv-historial-cargando');
    const historialVacio = document.getElementById('inv-historial-vacio');
    const historialError = document.getElementById('inv-historial-error');

    if (
        !modal || !cerrar || !overlay || !elemento ||
        !nombreElemento || !stockInfo || !cantidad || !queda
    ) {
        return;
    }

    let botonAnterior = null;

    function abrirModal(boton) {
        botonAnterior = boton;

        // Cargar los datos de la fila seleccionada.
        elemento.value = boton.dataset.codigo;
        
        if (salaInput) {
            salaInput.value = boton.dataset.sala;
        }
        
        cargarHistorial(boton);
        
        elemento.dataset.stock = boton.dataset.stock;
        elemento.dataset.minimo = boton.dataset.minimo;

        nombreElemento.textContent = boton.dataset.nombre;
        stockInfo.textContent =
            'Stock actual: ' + boton.dataset.stock +
            ' · Mínimo: ' + boton.dataset.minimo;

        cantidad.value = '';

        queda.hidden = true;
        cantidadError.hidden = true;
        cantidad.classList.remove('is-invalid');    

        modal.hidden = false;
        document.body.style.overflow = 'hidden';

        cantidad.focus();
    }

    function cerrarModal() {
        modal.hidden = true;
        document.body.style.overflow = '';

        if (botonAnterior) {
            botonAnterior.focus();
        }
    }

    function vistaReposicion() {
        const unidades = Number(cantidad.value);

        if (!cantidad.value || !Number.isInteger(unidades) || unidades < 1) {
            cantidadError.hidden = false;
            cantidad.classList.add('is-invalid');

            queda.hidden = true;

            return;
        }

        cantidadError.hidden = true;
        cantidad.classList.remove('is-invalid');

        const stock = Number(elemento.dataset.stock);
        const minimo = Number(elemento.dataset.minimo);
        const total = stock + unidades;

        queda.hidden = false;
        queda.textContent = total >= minimo
            ? 'Quedaría en ' + total + '. La alerta de stock se apaga.'
            : 'Quedaría en ' + total +
            '. Sigue bajo el mínimo (' + minimo + ').';
    }

    async function cargarHistorial(boton) {
        historialLista.replaceChildren();
        historialCargando.hidden = false;
        historialVacio.hidden = true;
        historialError.hidden = true;
        
        try {
            const sala = boton.dataset.sala;
            const codigo = boton.dataset.codigo;
            
            const url = `/consulta/inventario/${encodeURIComponent(sala)}/${encodeURIComponent(codigo)}/historial`;
            
            const respuesta = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
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
                
                detalle.textContent = `Reposición · ${reposicion.cantidad} unidades`;
                fecha.textContent = `${reposicion.fecha} · ${reposicion.autor}`;
                
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
    cantidad.addEventListener('input', vistaReposicion);

    const formularioReposicion = cantidad.closest('form');

    formularioReposicion.addEventListener('submit', function (evento) {
        const unidades = Number(cantidad.value);

        if (
            !cantidad.value ||
            !Number.isInteger(unidades) ||
            unidades < 1 ||
            unidades > 9999
        ) {
            evento.preventDefault();

            cantidadError.hidden = false;
            cantidad.classList.add('is-invalid');
            queda.hidden = false;
            queda.style.visibility = 'hidden';

            cantidad.focus();

            return;
        }

        cantidadError.hidden = true;
        cantidad.classList.remove('is-invalid');
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            cerrarModal();
        }
    });
    
});
