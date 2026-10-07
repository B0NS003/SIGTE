<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'SIGTE'); ?></title>
    <script>
        (function () {
            try {
                var enLogin = window.location.pathname.indexOf('/login') !== -1;
                if (enLogin) {
                    sessionStorage.removeItem('sigte-carga');
                    document.documentElement.classList.add('sigte-carga-login');
                    return;
                }
                if (sessionStorage.getItem('sigte-carga') === '1') {
                    document.documentElement.classList.add('sigte-carga-omitir');
                }
            } catch (error) {}
        })();
    </script>
    <link rel="stylesheet" href="<?php echo e(asset('css/sigte.css')); ?>?v=<?php echo e(@filemtime(public_path('css/sigte.css'))); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <div id="sigte-carga" class="sigte-carga" role="status" aria-live="polite" aria-busy="false" hidden>
        <div class="sigte-carga-panel">
            <div class="sigte-carga-card">
                <div class="sigte-carga-logo-wrap">
                    <svg class="sigte-carga-ring" viewBox="0 0 88 88" aria-hidden="true">
                        <circle class="sigte-carga-ring-fondo" cx="44" cy="44" r="38"></circle>
                        <circle class="sigte-carga-ring-trazo" cx="44" cy="44" r="38"></circle>
                    </svg>
                    <img
                        class="sigte-carga-logo"
                        src="<?php echo e(asset('images/logo-hospital-circular.png')); ?>"
                        alt=""
                        width="64"
                        height="64"
                    >
                </div>

                <svg class="sigte-carga-word" viewBox="0 0 220 48" aria-hidden="true">
                    <text class="sigte-carga-letras sigte-carga-sig" x="78" y="34" text-anchor="middle">SIG</text>
                    <text class="sigte-carga-letras sigte-carga-te" x="148" y="34" text-anchor="middle">TE</text>
                </svg>

                <p class="sigte-carga-sub">Hospital San José de Melipilla</p>
                <div class="sigte-carga-pista" aria-hidden="true">
                    <span class="sigte-carga-barra"></span>
                </div>
            </div>
        </div>
    </div>

    <?php echo $__env->yieldContent('content'); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script src="<?php echo e(asset('js/anime.min.js')); ?>"></script>
    <script>
        (function () {
            var capa = document.getElementById('sigte-carga');
            var panel = capa ? capa.querySelector('.sigte-carga-panel') : null;
            var ring = capa ? capa.querySelector('.sigte-carga-ring-trazo') : null;
            var letras = capa ? capa.querySelectorAll('.sigte-carga-letras') : [];
            var logo = capa ? capa.querySelector('.sigte-carga-logo') : null;
            if (!capa || !panel) {
                return;
            }

            var enLogin = document.documentElement.classList.contains('sigte-carga-login');
            var omitir = document.documentElement.classList.contains('sigte-carga-omitir');
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var animeOk = typeof anime === 'function';
            var dibujoListo = false;
            var paginaLista = false;
            var saliendo = false;

            function marcarVista() {
                try {
                    sessionStorage.setItem('sigte-carga', '1');
                } catch (error) {}
            }

            function estadoListoVisual() {
                if (ring && typeof ring.getTotalLength === 'function') {
                    ring.style.strokeDasharray = String(ring.getTotalLength());
                    ring.style.strokeDashoffset = '0';
                }
                letras.forEach(function (letra) {
                    letra.style.opacity = '1';
                    letra.style.transform = 'translateY(0)';
                });
                if (logo) {
                    logo.style.opacity = '1';
                    logo.style.transform = 'scale(1)';
                }
                panel.style.opacity = '1';
            }

            function prepararDibujo() {
                if (ring && typeof ring.getTotalLength === 'function') {
                    var largoRing = ring.getTotalLength();
                    ring.style.strokeDasharray = String(largoRing);
                    ring.style.strokeDashoffset = String(largoRing);
                }
                letras.forEach(function (letra) {
                    letra.style.opacity = '0';
                    letra.style.transform = 'translateY(8px)';
                });
                if (logo) {
                    logo.style.opacity = '0';
                    logo.style.transform = 'scale(0.92)';
                }
                panel.style.opacity = '1';
                dibujoListo = false;
                saliendo = false;
                capa.classList.remove('is-listo');
            }

            function dibujarMarca(alTerminar) {
                if (reduce || !animeOk) {
                    estadoListoVisual();
                    dibujoListo = true;
                    if (alTerminar) {
                        alTerminar();
                    }
                    return;
                }

                var piezas = [ring, logo].concat(Array.prototype.slice.call(letras)).filter(Boolean);
                anime.remove(piezas);
                anime.timeline({
                    easing: 'easeOutCubic',
                    complete: function () {
                        dibujoListo = true;
                        if (alTerminar) {
                            alTerminar();
                        }
                    }
                })
                    .add({
                        targets: logo,
                        opacity: [0, 1],
                        scale: [0.92, 1],
                        duration: 420
                    })
                    .add({
                        targets: ring,
                        strokeDashoffset: [anime.setDashoffset, 0],
                        duration: 860
                    }, '-=240')
                    .add({
                        targets: letras,
                        opacity: [0, 1],
                        translateY: [8, 0],
                        delay: anime.stagger(70),
                        duration: 420
                    }, '-=420');
            }

            function salir() {
                if (saliendo) {
                    return;
                }
                saliendo = true;
                marcarVista();
                capa.setAttribute('aria-busy', 'false');
                capa.classList.add('is-listo');

                var cerrar = function () {
                    capa.hidden = true;
                    panel.style.opacity = '1';
                };

                if (reduce || !animeOk) {
                    window.setTimeout(cerrar, reduce ? 0 : 280);
                    return;
                }

                anime.remove(panel);
                anime({
                    targets: panel,
                    opacity: [1, 0],
                    duration: 320,
                    easing: 'easeOutCubic',
                    complete: cerrar
                });
                window.setTimeout(cerrar, 800);
            }

            function intentarSalir() {
                if (dibujoListo && paginaLista) {
                    salir();
                }
            }

            function mostrar(conDibujo) {
                capa.hidden = false;
                capa.setAttribute('aria-busy', 'true');
                capa.classList.remove('is-listo');
                panel.style.opacity = '1';
                saliendo = false;
                if (conDibujo) {
                    prepararDibujo();
                    dibujarMarca(null);
                    return;
                }
                estadoListoVisual();
                dibujoListo = true;
            }

            if (omitir) {
                capa.hidden = true;
                return;
            }

            if (enLogin) {
                capa.hidden = true;
                var formLogin = document.getElementById('form-login');
                if (formLogin) {
                    formLogin.addEventListener('submit', function (evento) {
                        if (evento.defaultPrevented) {
                            return;
                        }
                        mostrar(false);
                    });
                }
                return;
            }

            capa.hidden = false;
            capa.setAttribute('aria-busy', 'true');
            prepararDibujo();
            dibujarMarca(intentarSalir);

            if (document.readyState === 'complete') {
                paginaLista = true;
                intentarSalir();
            } else {
                window.addEventListener('load', function () {
                    paginaLista = true;
                    intentarSalir();
                });
            }

            window.addEventListener('pageshow', function (evento) {
                if (evento.persisted) {
                    marcarVista();
                    capa.hidden = true;
                    capa.setAttribute('aria-busy', 'false');
                }
            });
        })();
    </script>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>