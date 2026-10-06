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
                    var lleva = card.querySelector('.track-lleva');
                    if (lleva && hasta) {
                        lleva.hidden = true;
                    }
                    card.classList.remove('is-proceso-listo');
                    delete card.dataset.listoAnim;
                    var aviso = card.querySelector('[data-proceso-listo]');
                    if (aviso) {
                        aviso.hidden = true;
                        aviso.style.opacity = '';
                        aviso.style.transform = '';
                    }
                    pintar(bloque, true);
                }

                function lleva(ms) {
                    var total = Math.max(0, Math.floor(ms / 1000));
                    var h = Math.floor(total / 3600);
                    var m = Math.floor((total % 3600) / 60);
                    var s = total % 60;
                    if (h > 0) {
                        return h + ':' + pad(m) + ':' + pad(s);
                    }
                    return m + ':' + pad(s);
                }

                function pintarLleva() {
                    var ahora = Date.now();
                    document.querySelectorAll('[data-lleva]').forEach(function (nodo) {
                        var desde = Date.parse(nodo.dataset.lleva || '');
                        if (!desde) {
                            return;
                        }
                        nodo.textContent = lleva(ahora - desde);
                    });
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
                pintarLleva();
                tick(true);
                window.setInterval(function () {
                    pintarLleva();
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
