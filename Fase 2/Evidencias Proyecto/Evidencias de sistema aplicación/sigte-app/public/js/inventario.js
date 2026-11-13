document.addEventListener('DOMContentLoaded', function () {

    const formulario = document.getElementById('form-busqueda-inventario');
    const buscador = document.getElementById('busqueda-inventario');
    const botonLimpiar = document.getElementById('inv-limpiar-busqueda');
    const cuerpo = document.getElementById('cuerpo-inventario');

    if (!formulario || !buscador || !cuerpo) {
        return;
    }

    /* SELECCIONAR FICHAS DE INSUMOS */
    const filas = Array.from(
        cuerpo.querySelectorAll(
            '.inv-insumo-card[data-stock], .inv-esteril-fila'
        )
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

            stock += Number(fila.dataset.stock || 0);

            if (fila.dataset.enProceso !== undefined) {
                enProceso += Number(fila.dataset.enProceso || 0);
            }

            if (fila.dataset.estado !== undefined) {

                const estado = normalizar(fila.dataset.estado);

                if (
                    estado === 'bajo' ||
                    estado === 'critico'
                ) {
                    bajoMinimo++;
                }
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

        /* OCULTAR TODAS LAS FICHAS ANTES DE MOSTRAR LA PÁGINA ACTUAL. */
        filas.forEach(function (fila) {
            fila.hidden = true;
            fila.classList.add('is-pagina-oculta');
        });

        /* MOSTRAR SOLAMENTE LAS FICHAS DE LA PÁGINA ACTUAL. */
        filasFiltradas
            .slice(inicio, fin)
            .forEach(function (fila) {
                fila.hidden = false;
                fila.classList.remove('is-pagina-oculta');
            });

        /* Mensaje cuando no hay resultados. */
        if (mensajeVacio) {
            mensajeVacio.hidden = total !== 0;
        }

        /* Información de resultados */
        const numeroFormato = new Intl.NumberFormat('es-CL');

        rango.textContent = total > 0
            ? `Total ${numeroFormato.format(total)} resultados · Pág. ${paginaActual}/${Math.max(1, totalPaginas)} · ${numeroFormato.format(inicio + 1)}–${numeroFormato.format(fin)}`
            : 'Total 0 resultados · Pág. 1/1 · 0–0';

        /* Botones anterior/siguiente. */
        paginaAnterior.disabled =
            paginaActual <= 1;

        paginaSiguiente.disabled =
            paginaActual >= totalPaginas ||
            totalPaginas === 0;

        /* Números de página compactos. */
        paginas.innerHTML = '';

        const numeros = [];
        const alrededor = 2;

        if (totalPaginas <= 9) {
            for (let i = 1; i <= totalPaginas; i++) {
                numeros.push(i);
            }
        } else {
            numeros.push(1);

            const desde = Math.max(2, paginaActual - alrededor);
            const hasta = Math.min(totalPaginas - 1, paginaActual + alrededor);

            if (desde > 2) {
                numeros.push(null);
            }

            for (let i = desde; i <= hasta; i++) {
                numeros.push(i);
            }

            if (hasta < totalPaginas - 1) {
                numeros.push(null);
            }

            numeros.push(totalPaginas);
        }

        numeros.forEach(function (numero) {
            const boton = document.createElement('button');

            boton.type = 'button';
            boton.className = 'inv-pagina-numero';

            if (numero === null) {
                boton.classList.add('is-gap');
                boton.textContent = '…';
                boton.disabled = true;
            } else {
                boton.textContent = numero;

                if (numero === paginaActual) {
                    boton.classList.add('is-active');
                    boton.setAttribute('aria-current', 'page');
                }

                boton.addEventListener('click', function () {
                    paginaActual = numero;
                    actualizarVista();
                });
            }

            paginas.appendChild(boton);
        });

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
