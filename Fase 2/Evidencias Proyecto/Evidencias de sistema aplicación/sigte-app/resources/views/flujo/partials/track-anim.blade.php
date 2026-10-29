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
                        // El pulso suave queda en CSS (.track-step.current .track-node).
                        // Anime solo marca la llegada a la etapa actual.
                        anime({
                            targets: actual,
                            scale: [0.86, 1],
                            delay: espera + (lineas.length * 80) + 120,
                            duration: 380,
                            easing: 'easeOutCubic'
                        });
                    }
                });
            })();
        </script>
    @endpush
@endonce
