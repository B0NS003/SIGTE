// Pestañas de Reportes (Administración): panel visible, filtros por pestaña y tab en export/URL.
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.reportes-tab');
    const panels = document.querySelectorAll('.reportes-panel');
    const tabOculta = document.getElementById('reportes-tab-seleccionada');
    const disponibles = ['produccion', 'trazabilidad', 'entregas'];

    function ajustarFiltros(tab) {
        const produccion = tab === 'produccion';
        const tipo = document.querySelector('.reportes-filtro-tipo');
        const servicio = document.querySelector('.reportes-filtro-servicio');
        if (tipo) tipo.hidden = tab !== 'trazabilidad';
        if (servicio) servicio.hidden = !produccion;
        document.querySelectorAll('[data-ayuda], [data-periodo]').forEach(function (nodo) {
            nodo.hidden = (nodo.dataset.ayuda || nodo.dataset.periodo) !== tab;
        });
    }

    function actualizarEnlacesExport(tab) {
        document.querySelectorAll('[data-descargar-reporte]').forEach(function (enlace) {
            try {
                const url = new URL(enlace.href, window.location.href);
                url.searchParams.set('tab', tab);
                enlace.href = url.toString();
            } catch (e) {
                // Si el href no es una URL válida, se deja como está.
            }
        });
        document.querySelectorAll('.reportes-paginacion a[href]').forEach(function (enlace) {
            try {
                const destino = new URL(enlace.href, window.location.href);
                destino.searchParams.set('tab', tab);
                enlace.href = destino.toString();
            } catch (e) {
                // Ignorar enlaces malformados.
            }
        });
    }

    function seleccionarTab(nombre, actualizarUrl) {
        const tabElegida = disponibles.includes(nombre) ? nombre : 'produccion';
        ajustarFiltros(tabElegida);
        if (tabOculta) tabOculta.value = tabElegida;
        tabs.forEach(function (tab) {
            const active = tab.dataset.tab === tabElegida;
            tab.classList.toggle('activo', active);
            tab.setAttribute('aria-selected', String(active));
        });
        panels.forEach(function (panel) {
            const active = panel.id === 'panel-' + tabElegida;
            panel.classList.toggle('activo', active);
            panel.hidden = !active;
        });
        actualizarEnlacesExport(tabElegida);
        if (actualizarUrl) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabElegida);
            window.history.replaceState(null, '', url);
        }
    }

    seleccionarTab(tabOculta ? tabOculta.value : 'produccion', false);
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            seleccionarTab(tab.dataset.tab, true);
        });
    });
});
