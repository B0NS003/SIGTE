
document.addEventListener('DOMContentLoaded', () => {
    const botones = document.querySelectorAll('[data-inv-tab]');
    const paneles = document.querySelectorAll('[data-inv-panel]');

    botones.forEach((boton) => {
        boton.addEventListener('click', () => {
            const destino = boton.dataset.invTab;

            botones.forEach((elemento) => {
                const activo = elemento === boton;

                elemento.classList.toggle('is-active', activo);
                elemento.setAttribute('aria-selected', activo ? 'true' : 'false');
            });

            paneles.forEach((panel) => {
                panel.hidden = panel.dataset.invPanel !== destino;
            });
        });
    });
});
