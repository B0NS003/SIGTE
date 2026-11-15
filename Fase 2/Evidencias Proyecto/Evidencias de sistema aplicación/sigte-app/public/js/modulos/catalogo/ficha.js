// Ficha del operador: checklist de revisión con progreso guardado en la sesión del navegador.
(function () {
    const lista = document.getElementById('ficha-checklist');
    if (!lista) return;
    const checks = Array.from(lista.querySelectorAll('.ficha-operador-check'));
    const texto = document.getElementById('ficha-progreso-texto');
    const barra = document.getElementById('ficha-progreso-barra');
    const porcentaje = document.getElementById('ficha-progreso-porcentaje');
    const completo = document.getElementById('ficha-completada');
    const botonTodos = document.getElementById('ficha-marcar-todos');
    const botonReset = document.getElementById('ficha-reiniciar');
    const clave = 'sigte-revision-ficha:' + lista.dataset.fichaCodigo;
    try {
        const guardados = JSON.parse(sessionStorage.getItem(clave) || '[]');
        if (Array.isArray(guardados)) checks.forEach((check, i) => { check.checked = guardados.includes(i); });
    } catch (_) { /* La revisión sigue funcionando aunque el navegador no permita almacenamiento. */ }
    function actualizar() {
        const marcados = checks.filter(c => c.checked).length;
        const total = checks.length;
        const pct = total ? Math.round(marcados / total * 100) : 0;
        texto.textContent = marcados + ' de ' + total;
        porcentaje.textContent = pct + '%';
        barra.style.width = pct + '%';
        checks.forEach(c => c.closest('li').classList.toggle('is-revisado', c.checked));
        completo.hidden = !total || marcados !== total;
        botonTodos.disabled = !total || marcados === total;
        try { sessionStorage.setItem(clave, JSON.stringify(checks.flatMap((c, i) => c.checked ? [i] : []))); } catch (_) {}
    }
    checks.forEach(c => c.addEventListener('change', actualizar));
    botonTodos.addEventListener('click', () => { checks.forEach(c => { c.checked = true; }); actualizar(); });
    botonReset.addEventListener('click', () => { checks.forEach(c => { c.checked = false; }); actualizar(); });
    actualizar();
})();
