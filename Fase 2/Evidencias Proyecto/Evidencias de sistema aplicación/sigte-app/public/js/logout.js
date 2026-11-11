document.addEventListener('DOMContentLoaded', () => {
    const btnAbrir = document.getElementById('btn-abrir-logout');
    const modal = document.getElementById('modal-logout');
    const contenido = modal?.querySelector('.logout-contenido');
    const form = document.getElementById('form-logout');
    const btnCancelar = modal?.querySelector('.logout-btn-cancelar');
    const btnConfirmar = form?.querySelector('button[type="submit"]');

    if (!btnAbrir || !modal || !contenido || !form || !btnCancelar || !btnConfirmar) {
        return;
    }

    let elementoAnterior = null;

    // ABRIR MODAL
    function abrirModal() {
        elementoAnterior = document.activeElement;

        modal.hidden = false;
        document.body.classList.add('logout-modal-abierto');

        btnCancelar.focus();
    }

    // CERRAR MODAL
    function cerrarModal() {
        modal.hidden = true;
        document.body.classList.remove('logout-modal-abierto');

        if (elementoAnterior) {
            elementoAnterior.focus();
        }
    }

    btnAbrir.addEventListener('click', abrirModal);

    modal.querySelectorAll('[data-logout-cerrar]').forEach(elemento => {
        elemento.addEventListener('click', cerrarModal);
    });

    // CERRAR CON ESCAPE
    document.addEventListener('keydown', evento => {
        if (modal.hidden) return;

        if (evento.key === 'Escape') {
            evento.preventDefault();
            cerrarModal();
            return;
        }

        // MANTENER EL FOCO DENTRO DEL MODAL
        if (evento.key === 'Tab') {
            const elementos = [btnCancelar, btnConfirmar];

            if (evento.shiftKey && document.activeElement === elementos[0]) {
                evento.preventDefault();
                elementos[1].focus();
            } else if (!evento.shiftKey && document.activeElement === elementos[1]) {
                evento.preventDefault();
                elementos[0].focus();
            }
        }
    });

    // CONFIRMAR CIERRE DE SESIÓN
    form.addEventListener('submit', () => {
        try {
            sessionStorage.removeItem('sigte-carga');
        } catch (error) {
        }

        btnConfirmar.disabled = true;
        btnConfirmar.textContent = 'Confirmando...';
    });
});
