@once
    @push('scripts')
        <script>
            (function () {
                var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                function pad(n) {
                    return n < 10 ? '0' + n : String(n);
                }

                function texto(ms) {
                    if (ms <= 0) {
                        return 'Listo para pasar';
                    }
                    var total = Math.ceil(ms / 1000);
                    var min = Math.floor(total / 60);
                    var seg = total % 60;
                    return 'Quedan ' + min + ':' + pad(seg);
                }

                function pintar(bloque, animar) {
                    var desde = Date.parse(bloque.dataset.desde || '');
                    var hasta = Date.parse(bloque.dataset.hasta || '');
                    if (!desde || !hasta) {
                        return;
                    }
                    var ahora = Date.now();
                    var total = Math.max(hasta - desde, 1);
                    var queda = Math.max(hasta - ahora, 0);
                    var pct = queda === 0 ? 100 : Math.min(100, Math.max(0, ((total - queda) / total) * 100));
                    var barra = bloque.querySelector('.proceso-barra');
                    var card = bloque.closest('.track-card');
                    var aviso = card ? card.querySelector('[data-proceso-listo]') : null;
                    bloque.classList.toggle('is-listo', queda === 0);
                    bloque.querySelector('.proceso-texto').textContent = texto(queda);

                    if (barra) {
                        if (animar && !reduce && typeof anime === 'function') {
                            anime.remove(barra);
                            anime({
                                targets: barra,
                                width: pct + '%',
                                duration: 700,
                                easing: 'linear'
                            });
                        } else {
                            barra.style.width = pct + '%';
                        }
                    }

                    if (queda > 0 || !card) {
                        return;
                    }

                    card.classList.add('is-proceso-listo');
                    if (aviso) {
                        aviso.hidden = false;
                    }
                    if (card.dataset.listoAnim === '1' || !aviso) {
                        return;
                    }
                    card.dataset.listoAnim = '1';
                    if (!reduce && typeof anime === 'function') {
                        anime({
                            targets: aviso,
                            opacity: [0, 1],
                            translateY: [6, 0],
                            duration: 420,
                            easing: 'easeOutCubic'
                        });
                        var nodo = card.querySelector('.track-step.current .track-node');
                        if (nodo) {
                            anime.remove(nodo);
                            anime({
                                targets: nodo,
                                boxShadow: card.classList.contains('is-urgent')
                                    ? ['0 0 0 3px rgba(185,28,28,.28)', '0 0 0 6px rgba(185,28,28,0)']
                                    : ['0 0 0 3px rgba(22,163,74,.28)', '0 0 0 6px rgba(22,163,74,0)'],
                                loop: true,
                                duration: 1100,
                                easing: 'easeOutCubic'
                            });
                        }
                    }
                }

                function marcar(codigo, desde, hasta) {
                    var card = document.querySelector('.track-card[data-caja="' + CSS.escape(codigo) + '"]');
                    if (!card) {
                        return;
                    }
                    var bloque = card.querySelector('.proceso');
                    if (!bloque) {
                        return;
                    }
                    bloque.dataset.desde = desde || '';
                    bloque.dataset.hasta = hasta || '';
                    bloque.hidden = !hasta;
                    card.classList.remove('is-proceso-listo');
                    delete card.dataset.listoAnim;
                    var nodo = card.querySelector('.track-step.current .track-node');
                    if (nodo && !reduce && typeof anime === 'function') {
                        anime.remove(nodo);
                        anime({
                            targets: nodo,
                            boxShadow: card.classList.contains('is-urgent')
                                ? ['0 0 0 3px rgba(185,28,28,.28)', '0 0 0 6px rgba(185,28,28,0)']
                                : ['0 0 0 3px rgba(232,90,28,.28)', '0 0 0 6px rgba(232,90,28,0)'],
                            loop: true,
                            duration: 1100,
                            easing: 'easeOutCubic'
                        });
                    }
                    var aviso = card.querySelector('[data-proceso-listo]');
                    if (aviso) {
                        aviso.hidden = true;
                        aviso.style.opacity = '';
                        aviso.style.transform = '';
                    }
                    pintar(bloque, true);
                }

                function tick(animar) {
                    document.querySelectorAll('.proceso').forEach(function (bloque) {
                        if (!bloque.dataset.hasta) {
                            return;
                        }
                        pintar(bloque, animar);
                    });
                }

                window.sigteProceso = { marcar: marcar };
                tick(true);
                window.setInterval(function () {
                    document.querySelectorAll('.proceso').forEach(function (bloque) {
                        if (!bloque.dataset.hasta) {
                            return;
                        }
                        pintar(bloque, false);
                    });
                }, 1000);
            })();
        </script>
    @endpush
@endonce
