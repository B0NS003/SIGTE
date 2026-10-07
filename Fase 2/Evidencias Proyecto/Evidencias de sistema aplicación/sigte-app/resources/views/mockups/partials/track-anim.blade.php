@once
    @push('scripts')
        <script src="{{ asset('js/anime.min.js') }}"></script>
        <script>
            (function () {
                var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (reduce || typeof anime !== 'function') {
                    return;
                }

                document.querySelectorAll('.track-pipe').forEach(function (barra, indice) {
                    var lineas = barra.querySelectorAll('.track-line.done');
                    var hechos = barra.querySelectorAll('.track-step.done .track-node');
                    var actual = barra.querySelector('.track-step.current .track-node');
                    var espera = indice * 70;

                    lineas.forEach(function (linea) {
                        linea.style.transformOrigin = 'left center';
                        linea.style.transform = 'scaleX(0)';
                    });
                    hechos.forEach(function (punto) {
                        punto.style.transform = 'scale(0)';
                    });

                    if (hechos.length) {
                        anime({
                            targets: hechos,
                            scale: [0, 1],
                            delay: anime.stagger(60, { start: espera }),
                            duration: 420,
                            easing: 'easeOutCubic'
                        });
                    }

                    if (lineas.length) {
                        anime({
                            targets: lineas,
                            scaleX: [0, 1],
                            delay: anime.stagger(80, { start: espera + 30 }),
                            duration: 460,
                            easing: 'easeOutCubic'
                        });
                    }

                    if (actual) {
                        var lista = barra.closest('.track-card');
                        var atencion = lista && lista.classList.contains('is-urgent');
                        var verde = lista && lista.classList.contains('is-proceso-listo');
                        anime({
                            targets: actual,
                            boxShadow: atencion
                                ? ['0 0 0 3px rgba(185,28,28,.28)', '0 0 0 6px rgba(185,28,28,0)']
                                : (verde
                                    ? ['0 0 0 3px rgba(22,163,74,.28)', '0 0 0 6px rgba(22,163,74,0)']
                                    : ['0 0 0 3px rgba(232,90,28,.28)', '0 0 0 6px rgba(232,90,28,0)']),
                            delay: espera + (lineas.length * 80) + 180,
                            loop: true,
                            duration: 1100,
                            easing: 'easeOutCubic'
                        });
                    }
                });
            })();
        </script>
    @endpush
@endonce
