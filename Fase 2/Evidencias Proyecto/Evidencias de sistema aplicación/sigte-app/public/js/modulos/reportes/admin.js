// Pestañas de Reportes (Administración): cambia el panel visible y los filtros que aplican a cada reporte.
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.reportes-tab');
    const panels = document.querySelectorAll('.reportes-panel');
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
    ajustarFiltros('produccion');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            ajustarFiltros(tab.dataset.tab);
            tabs.forEach(function (item) {
                const active = item === tab;
                item.classList.toggle('activo', active);
                item.setAttribute('aria-selected', String(active));
            });
            panels.forEach(function (panel) {
                const active = panel.id === 'panel-' + tab.dataset.tab;
                panel.classList.toggle('activo', active);
                panel.hidden = !active;
            });
        });
    });
});
