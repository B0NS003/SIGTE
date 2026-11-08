(function () {
    var CLAVE = 'sigte-tema';
    var OSCURO = 'oscuro';

    function esOscuro() {
        return document.documentElement.getAttribute('data-tema') === OSCURO;
    }

    function aplicar(oscuro, persistir) {
        if (oscuro) {
            document.documentElement.setAttribute('data-tema', OSCURO);
        } else {
            document.documentElement.removeAttribute('data-tema');
        }

        if (persistir) {
            try {
                if (oscuro) {
                    localStorage.setItem(CLAVE, OSCURO);
                } else {
                    localStorage.removeItem(CLAVE);
                }
            } catch (error) {}
        }

        document.querySelectorAll('[data-sigte-tema]').forEach(function (boton) {
            boton.setAttribute('aria-pressed', oscuro ? 'true' : 'false');
            boton.setAttribute('aria-label', oscuro ? 'Desactivar modo oscuro' : 'Activar modo oscuro');
            var label = boton.querySelector('.side-tema-label');
            var hint = boton.querySelector('.side-tema-hint');
            if (label) {
                label.textContent = 'Modo oscuro';
            }
            if (hint) {
                hint.textContent = oscuro ? 'Encendido' : 'Apagado';
            }
        });
    }

    aplicar(esOscuro(), false);

    document.addEventListener('click', function (evento) {
        var boton = evento.target.closest('[data-sigte-tema]');
        if (!boton) {
            return;
        }
        aplicar(!esOscuro(), true);
    });
})();
