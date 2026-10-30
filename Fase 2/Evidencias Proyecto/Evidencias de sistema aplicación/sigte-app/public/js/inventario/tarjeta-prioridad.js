/* PAGINACIÓN INDEPENDIENTE DE PRIORIDADES DE REPOSICIÓN */
document.addEventListener('DOMContentLoaded', function () {
    const listaPrioridades = document.getElementById('inv-prioridades-lista');
    const paginacionPrioridades = document.getElementById('inv-prioridades-paginacion');

    if (listaPrioridades && paginacionPrioridades) {
        const tarjetas = Array.from(
            listaPrioridades.querySelectorAll('.inv-prioridad')
        );

        const itemsPrioridades = document.getElementById('inv-prioridades-items-pagina');
        const rangoPrioridades = document.getElementById('inv-prioridades-rango');
        const paginasPrioridades = document.getElementById('inv-prioridades-paginas');
        const anteriorPrioridades = document.getElementById('inv-prioridades-anterior');
        const siguientePrioridades = document.getElementById('inv-prioridades-siguiente');

        let paginaPrioridades = 1;

        function actualizarPrioridades() {
            const cantidad = Number(itemsPrioridades.value);
            const total = tarjetas.length;
            const totalPaginas = Math.ceil(total / cantidad);

            if (paginaPrioridades > totalPaginas) {
                paginaPrioridades = Math.max(1, totalPaginas);
            }

            const inicio = total === 0 ? 0 : (paginaPrioridades - 1) * cantidad;
            const fin = Math.min(inicio + cantidad, total);

            tarjetas.forEach(function (tarjeta, indice) {
                tarjeta.hidden = indice < inicio || indice >= fin;
            });

            rangoPrioridades.textContent = total > 0
                ? `${inicio + 1}–${fin} de ${total}`
                : '0–0 de 0';

            anteriorPrioridades.disabled = paginaPrioridades <= 1;
            siguientePrioridades.disabled = paginaPrioridades >= totalPaginas;

            paginasPrioridades.innerHTML = '';

            for (let i = 1; i <= totalPaginas; i++) {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'inv-pagina-numero';
                boton.textContent = i;

                if (i === paginaPrioridades) {
                    boton.classList.add('is-active');
                    boton.setAttribute('aria-current', 'page');
                }

                boton.addEventListener('click', function () {
                    paginaPrioridades = i;
                    actualizarPrioridades();
                });

                paginasPrioridades.appendChild(boton);
            }

            paginacionPrioridades.hidden = total <= cantidad;
        }

        itemsPrioridades.addEventListener('change', function () {
            paginaPrioridades = 1;
            actualizarPrioridades();
        });

        anteriorPrioridades.addEventListener('click', function () {
            if (paginaPrioridades > 1) {
                paginaPrioridades--;
                actualizarPrioridades();
            }
        });

        siguientePrioridades.addEventListener('click', function () {
            const totalPaginas = Math.ceil(
                tarjetas.length / Number(itemsPrioridades.value)
            );

            if (paginaPrioridades < totalPaginas) {
                paginaPrioridades++;
                actualizarPrioridades();
            }
        });

        actualizarPrioridades();
    }
});
