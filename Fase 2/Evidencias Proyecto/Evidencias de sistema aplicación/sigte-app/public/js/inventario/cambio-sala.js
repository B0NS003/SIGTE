/* Transición suave al cambiar entre salas del inventario (navegación completa). */
document.addEventListener('DOMContentLoaded', () => {
    const vista = document.querySelector('.inv-vista');
    const panel = document.getElementById('inv-sala-panel');
    const enlaces = document.querySelectorAll('.inv-switch-item[href]');

    if (!vista || !enlaces.length) {
        return;
    }

    const reduceMotion = () =>
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let navegando = false;

    const irA = (href) => {
        if (navegando) {
            return;
        }
        navegando = true;
        window.location.href = href;
    };

    enlaces.forEach((enlace) => {
        enlace.addEventListener('click', (evento) => {
            if (
                enlace.classList.contains('is-active')
                || evento.metaKey
                || evento.ctrlKey
                || evento.shiftKey
                || evento.altKey
                || enlace.target === '_blank'
            ) {
                return;
            }

            if (reduceMotion() || !panel) {
                return;
            }

            evento.preventDefault();
            const destino = enlace.href;

            vista.classList.add('is-cambiando-sala');
            enlace.classList.add('is-destino');

            const alTerminar = (e) => {
                if (e.target !== panel || e.propertyName !== 'opacity') {
                    return;
                }
                panel.removeEventListener('transitionend', alTerminar);
                irA(destino);
            };

            panel.addEventListener('transitionend', alTerminar);
            window.setTimeout(() => irA(destino), 340);
        });
    });
});
