document.addEventListener('DOMContentLoaded', function () {
    const campo = document.getElementById('servicio');
    const toggle = document.getElementById('entrega-servicio-toggle');
    const lista = document.getElementById('entrega-servicio-lista');
    const buscador = document.getElementById('entrega-servicio-buscar');
    const valor = document.getElementById('entrega-servicio-valor');
    const vacio = document.getElementById('entrega-servicio-vacio');
    const error = document.getElementById('entrega-servicio-error-js');
    if (!campo || !toggle || !lista || !buscador || !valor || !vacio || !error) return;

    const opciones = Array.from(lista.querySelectorAll('.entrega-servicio-opcion'));
    const normalizar = texto => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es').trim();

    function sincronizar() {
        const seleccionada = opciones.find(op => op.dataset.value === campo.value);
        valor.textContent = seleccionada ? seleccionada.textContent.trim() : 'Elige el servicio';
        toggle.classList.toggle('sin-seleccion', !seleccionada);
        opciones.forEach(op => op.setAttribute('aria-selected', String(op === seleccionada)));
        if (seleccionada) {
            error.hidden = true;
            toggle.classList.remove('entrega-campo-invalido');
            toggle.removeAttribute('aria-invalid');
        }
    }

    function filtrar() {
        const texto = normalizar(buscador.value);
        let disponibles = 0;
        opciones.forEach(op => {
            op.hidden = !normalizar(op.textContent).includes(texto);
            if (!op.hidden) disponibles++;
        });
        vacio.hidden = disponibles > 0;
    }

    function cerrar() {
        lista.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
    }

    function abrir() {
        lista.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        buscador.value = '';
        filtrar();
        buscador.focus();
    }

    toggle.addEventListener('click', function () {
        lista.hidden ? abrir() : cerrar();
    });
    buscador.addEventListener('input', filtrar);

    opciones.forEach(op => {
        op.addEventListener('click', function () {
            campo.value = op.dataset.value;
            campo.dispatchEvent(new Event('change', { bubbles: true }));
            sincronizar();
            cerrar();
            toggle.focus();
        });
        op.addEventListener('keydown', function (evento) {
            if (evento.key !== 'ArrowDown' && evento.key !== 'ArrowUp') return;
            evento.preventDefault();
            const visibles = opciones.filter(x => !x.hidden);
            const indice = visibles.indexOf(op);
            visibles[(indice + (evento.key === 'ArrowDown' ? 1 : -1) + visibles.length) % visibles.length]?.focus();
        });
    });

    buscador.addEventListener('keydown', function (evento) {
        if (evento.key === 'ArrowDown' || evento.key === 'Enter') {
            const primera = opciones.find(x => !x.hidden);
            if (primera) {
                evento.preventDefault();
                primera.focus();
            }
        }
    });

    document.addEventListener('click', function (evento) {
        if (!toggle.contains(evento.target) && !lista.contains(evento.target)) cerrar();
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && !lista.hidden) {
            cerrar();
            toggle.focus();
        }
    });

    campo.closest('form')?.addEventListener('submit', function (evento) {
        if (campo.value) return;
        evento.preventDefault();
        error.hidden = false;
        toggle.classList.add('entrega-campo-invalido');
        toggle.setAttribute('aria-invalid', 'true');
        toggle.focus();
    });

    sincronizar();
});
