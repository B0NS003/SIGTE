(function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function duracion(minutos) {
        if (minutos < 60) {
            return minutos + ' min';
        }
        var resto = minutos % 60;
        return Math.floor(minutos / 60) + ' h' + (resto ? ' ' + resto + ' min' : '');
    }

    function pintarEtapa(bloque, ahora) {
        var llegada = Date.parse(bloque.dataset.etapaDesde || '');
        bloque.classList.remove('is-listo');
        bloque.classList.add('is-sin-proceso');
        bloque.hidden = !llegada;
        if (!llegada) {
            return;
        }
        var minutos = Math.max(Math.floor((ahora - llegada) / 60000), 0);
        bloque.querySelector('.proceso-etiqueta').textContent = 'En ' + bloque.dataset.etapaNombre;
        bloque.querySelector('.proceso-texto').textContent = minutos < 1 ? 'Recién llegada' : '+' + duracion(minutos);
        bloque.title = 'Tiempo desde que llegó a ' + bloque.dataset.etapaNombre;
    }

    function cuentaRegresiva(ms) {
        return 'Quedan ' + duracion(Math.ceil(ms / 60000));
    }

    function pintarTextos(bloque, total, hasta, ahora) {
        var anotado = Math.round(total / 60000);
        var etiqueta = bloque.querySelector('.proceso-etiqueta');
        var valor = bloque.querySelector('.proceso-texto');
        if (ahora < hasta) {
            etiqueta.textContent = 'Proceso −' + duracion(anotado);
            valor.textContent = cuentaRegresiva(hasta - ahora);
            bloque.title = duracion(anotado) + ' anotados; se van descontando';
            return;
        }
        var espera = Math.floor((ahora - hasta) / 60000);
        etiqueta.textContent = 'Listo, esperando';
        valor.textContent = espera < 1 ? 'Recién' : '+' + duracion(espera);
        bloque.title = 'Tiempo desde que terminó el proceso de ' + duracion(anotado);
    }

    function pintar(bloque, animar) {
        var desde = Date.parse(bloque.dataset.desde || '');
        var hasta = Date.parse(bloque.dataset.hasta || '');
        var ahora = Date.now();
        if (!desde || !hasta) {
            pintarEtapa(bloque, ahora);
            return;
        }
        bloque.hidden = false;
        bloque.classList.remove('is-sin-proceso');
        var total = Math.max(hasta - desde, 1);
        var queda = Math.max(hasta - ahora, 0);
        var pct = queda === 0 ? 100 : Math.min(100, Math.max(0, ((total - queda) / total) * 100));
        var barra = bloque.querySelector('.proceso-barra');
        var card = bloque.closest('.track-card');
        var aviso = card ? card.querySelector('[data-proceso-listo]') : null;
        bloque.classList.toggle('is-listo', queda === 0);
        pintarTextos(bloque, total, hasta, ahora);

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
                    boxShadow: ['0 0 0 3px rgba(22,163,74,.28)', '0 0 0 6px rgba(22,163,74,0)'],
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
            pintar(bloque, animar);
        });
    }

    window.sigteProceso = { marcar: marcar };
    tick(true);
    window.setInterval(function () {
        tick(false);
    }, 1000);
})();
