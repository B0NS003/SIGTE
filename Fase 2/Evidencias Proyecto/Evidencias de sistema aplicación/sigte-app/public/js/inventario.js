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
