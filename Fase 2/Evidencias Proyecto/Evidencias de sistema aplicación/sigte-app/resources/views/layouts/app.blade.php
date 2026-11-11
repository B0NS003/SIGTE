<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIGTE')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ @filemtime(public_path('favicon.png')) }}">
    <script>
        (function () {
            try {
                if (localStorage.getItem('sigte-tema') === 'oscuro') {
                    document.documentElement.setAttribute('data-tema', 'oscuro');
                }
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,500;0,600;0,700;0,800;0,900;1,900&family=Inter:wght@400;500;600;700;800&display=swap">
    @include('layouts.partials.estilos-base')
    @stack('styles')
</head>
<body>
    <div id="sigte-carga" class="sigte-carga" role="status" aria-live="polite" aria-busy="false" hidden>
        <div class="sigte-carga-panel">
            <div class="sigte-carga-card">
                <div class="sigte-carga-logo-wrap" aria-hidden="true">
                    <span class="sigte-carga-halo"></span>
                    <div class="sigte-carga-marca">
                        @foreach ([1, 2, 3] as $figura)
                            <img
                                class="sigte-carga-figura"
                                src="{{ asset('images/logo-sigte-figura-'.$figura.'.png') }}"
                                alt=""
                                width="80"
                                height="80"
                            >
                        @endforeach
                    </div>
                </div>

                <svg class="sigte-carga-word" viewBox="0 0 220 48" aria-hidden="true">
                    <text class="sigte-carga-letras" x="110" y="36" text-anchor="middle">SIG<tspan class="sigte-carga-te">TE</tspan></text>
                </svg>

                <p class="sigte-carga-sub">Hospital San José de Melipilla</p>
                <div class="sigte-carga-pista" aria-hidden="true">
                    <span class="sigte-carga-barra"></span>
                </div>
            </div>
        </div>
    </div>

    @yield('content')
    @stack('scripts')
    <script src="{{ asset('js/anime.min.js') }}"></script>
    <script>
        (function () {
            var capa = document.getElementById('sigte-carga');
            var panel = capa ? capa.querySelector('.sigte-carga-panel') : null;
            var figuras = capa ? capa.querySelectorAll('.sigte-carga-figura') : [];
            var halo = capa ? capa.querySelector('.sigte-carga-halo') : null;
            var letras = capa ? capa.querySelectorAll('.sigte-carga-letras') : [];
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
                figuras.forEach(function (figura) {
                    figura.style.opacity = '1';
                    figura.style.transform = 'none';
                });
                if (halo) {
                    halo.style.opacity = '1';
                    halo.style.transform = 'none';
                }
                letras.forEach(function (letra) {
                    letra.style.opacity = '1';
                    letra.style.transform = 'translateY(0)';
                });
                panel.style.opacity = '1';
                capa.classList.add('is-vivo');
            }

            function prepararDibujo() {
                capa.classList.remove('is-vivo');
                figuras.forEach(function (figura) {
                    figura.style.opacity = '0';
                    figura.style.transform = 'translateY(18px) scale(0.86)';
                });
                if (halo) {
                    halo.style.opacity = '0';
                    halo.style.transform = 'scale(0.5)';
                }
                letras.forEach(function (letra) {
                    letra.style.opacity = '0';
                    letra.style.transform = 'translateY(8px)';
                });
                panel.style.opacity = '1';
                dibujoListo = false;
                saliendo = false;
                capa.classList.remove('is-listo');
            }

            function dibujarMarca(alTerminar) {
                var terminado = false;
                var piezas = [halo].concat(
                    Array.prototype.slice.call(figuras),
                    Array.prototype.slice.call(letras)
                ).filter(Boolean);

                function terminar() {
                    if (terminado) {
                        return;
                    }
                    terminado = true;
                    if (animeOk) {
                        anime.remove(piezas);
                    }
                    estadoListoVisual();
                    dibujoListo = true;
                    if (alTerminar) {
                        alTerminar();
                    }
                }

                // Con la pestaña oculta el navegador pausa requestAnimationFrame y anime.js no avanza.
                if (reduce || !animeOk || document.hidden) {
                    terminar();
                    return;
                }

                window.setTimeout(terminar, 2600);
                anime.remove(piezas);
                anime.timeline({
                    easing: 'easeOutCubic',
                    complete: terminar
                })
                    .add({
                        targets: halo,
                        opacity: [0, 1],
                        scale: [0.5, 1],
                        duration: 560
                    })
                    .add({
                        targets: figuras,
                        opacity: [0, 1],
                        translateY: [18, 0],
                        scale: [0.86, 1],
                        delay: anime.stagger(150),
                        duration: 680,
                        easing: 'easeOutBack'
                    }, '-=420')
                    .add({
                        targets: letras,
                        opacity: [0, 1],
                        translateY: [8, 0],
                        delay: anime.stagger(80),
                        duration: 420
                    }, '-=360');
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
    <script src="{{ asset('js/sigte-tema.js') }}?v={{ @filemtime(public_path('js/sigte-tema.js')) }}"></script>
    <script src="{{ asset('js/sigte-controles.js') }}?v={{ @filemtime(public_path('js/sigte-controles.js')) }}"></script>
</body>
</html>
