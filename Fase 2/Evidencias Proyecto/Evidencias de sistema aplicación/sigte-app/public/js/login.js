(function () {
    var form = document.getElementById('form-login');
    var boton = document.getElementById('btn-ingresar');
    var tarjeta = document.querySelector('.auth-card');
    var correo = document.getElementById('email');
    var clave = document.getElementById('password');
    var verClave = document.getElementById('toggle-password');

    if (!form || !correo || !clave) return;

    // Mostrar y ocultar la contraseña
    verClave?.addEventListener('click', function () {
        var abrir = clave.type === 'password';
        clave.type = abrir ? 'text' : 'password';
        verClave.classList.toggle('is-abierto', abrir);
        verClave.setAttribute('aria-pressed', abrir ? 'true' : 'false');
        verClave.setAttribute('aria-label', abrir ? 'Ocultar contraseña' : 'Mostrar contraseña');
        verClave.classList.remove('is-parpadeo');
        void verClave.offsetWidth;
        verClave.classList.add('is-parpadeo');
    });

    // Validación del formulario de login
    form.addEventListener('submit', function (event) {
        var errores = validar();

        if (errores.email || errores.password) {
            event.preventDefault();
            mostrar(errores);
            sacudir();
            return;
        }

        if (!boton) return;
        boton.disabled = true;
        boton.classList.add('is-loading');
        var texto = boton.querySelector('.btn-label');
        if (texto) texto.textContent = 'Ingresando…';
    });

    // Validación de los campos del formulario
    function validar() {
        var errores = { email: '', password: '' };
        var valorCorreo = correo.value.trim();

        if (!valorCorreo) {
            errores.email = 'Escribe tu correo.';
        } else if (!correo.checkValidity()) {
            errores.email = 'El correo no tiene un formato válido. Ejemplo: nombre@hospital.cl';
        }

        if (!clave.value) {
            errores.password = 'Escribe tu contraseña.';
        }

        return errores;
    }

    // Mostrar los errores de validación
    function mostrar(errores) {
        document.querySelector('.auth-error')?.setAttribute('hidden', '');
        pintar('email', errores.email);
        pintar('password', errores.password);
    }

    // Pintar los errores de validación
    function pintar(campo, mensaje) {
        var input = document.getElementById(campo);
        var aviso = document.getElementById(campo + '-error');
        var caja = input?.closest('.field');

        if (aviso) {
            aviso.textContent = mensaje;
            aviso.hidden = !mensaje;
        }

        caja?.classList.toggle('is-invalid', Boolean(mensaje));
        if (mensaje) {
            input?.setAttribute('aria-invalid', 'true');
            input?.setAttribute('aria-describedby', campo + '-error');
        } else {
            input?.removeAttribute('aria-invalid');
        }
    }

    // Sacudir la tarjeta de login
    function sacudir() {
        if (!tarjeta) return;
        tarjeta.classList.remove('is-shaking');
        void tarjeta.offsetWidth;
        tarjeta.classList.add('is-shaking');
    }
})();
