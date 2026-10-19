<?php $__env->startSection('title', 'SIGTE — Administradora'); ?>

<?php $__env->startSection('content'); ?>
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="<?php echo e(asset('images/logo-hospital-circular.png')); ?>" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Administración</div>
            </div>
        </div>

                <?php echo $__env->make('partials.menu-rol', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="sidebar-foot">
            <div class="side-user">
                <div class="avatar"><?php echo e(strtoupper(substr($usuario['nombre'], 0, 1))); ?></div>
                <div>
                    <strong><?php echo e($usuario['nombre']); ?></strong>
                    <small><?php echo e($usuario['rol']); ?></small>
                </div>
            </div>
            <?php echo $__env->make('partials.logout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </aside>
    <!-- Contenido principal de la página -->
    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Hospital San José de Melipilla</p>
                <h1>Resumen de gestión</h1>
                <p class="main-sub">El volumen del período y, en este momento, en qué etapa está cada caja.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Ver vista operadora</a>
                <a class="btn btn-dark" href="<?php echo e(route('mockups.catalogo.admin')); ?>">Mantener catálogo</a>
            </div>
        </div>

        <section class="alert-strip" aria-label="Alertas">
            <div class="alert-strip-title">Requieren atención</div>
            <div class="alert-strip-list">
                <?php $__currentLoopData = $alertas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="alert-item alert-<?php echo e($a['nivel']); ?>">
                        <strong><?php echo e($a['titulo']); ?></strong>
                        <span><?php echo e($a['detalle']); ?></span>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <section aria-label="Indicadores del día">
            <p class="section-title">Indicadores del día</p>
            <div class="kpi-row">
                <article class="kpi">
                    <div class="l">En el ciclo</div>
                    <div class="n" data-kpi data-valor="<?php echo e($panorama['total']); ?>"><?php echo e($panorama['total']); ?></div>
                    <div class="h">Cajas con etapa actual</div>
                </article>
                <article class="kpi ok">
                    <div class="l">En almacén</div>
                    <div class="n" data-kpi data-valor="<?php echo e($panorama['en_almacen']); ?>"><?php echo e($panorama['en_almacen']); ?></div>
                    <div class="h">Listas para salir</div>
                </article>
                <article class="kpi">
                    <div class="l">En entrega</div>
                    <div class="n" data-kpi data-valor="<?php echo e($panorama['en_entrega']); ?>"><?php echo e($panorama['en_entrega']); ?></div>
                    <div class="h">En esa etapa ahora</div>
                </article>
                <article class="kpi <?php echo e(count($panorama['detenidas']) > 0 ? 'warn' : ''); ?>">
                    <div class="l">Se están quedando</div>
                    <div class="n" data-kpi data-valor="<?php echo e(count($panorama['detenidas'])); ?>"><?php echo e(count($panorama['detenidas'])); ?></div>
                    <div class="h">Sobre la guía de esta pantalla</div>
                </article>
            </div>
        </section>

        <section class="prod-resumen" aria-label="Producción de la central">
            <div class="mant-head">
                <div>
                    <h2>Producción de la central</h2>
                    <p id="periodo-etiqueta">Etapa actual de cada caja</p>
                </div>
                <label class="field field-select" style="margin:0; min-width:12rem;">
                    <select id="periodo">
                        <option value="ahora" selected>Ahora</option>
                        <option value="hoy">Hoy</option>
                        <option value="semana">Esta semana</option>
                        <option value="mes">Este mes</option>
                    </select>
                </label>
            </div>

            <?php if($falla_produccion): ?>
                <div class="ops-empty prod-espera" id="produccion-error">
                    <strong>No se pudo consultar la producción</strong>
                    <p>El panel no está disponible en este momento.</p>
                    <p><a href="<?php echo e(route('mockups.panel', 'administradora')); ?>">Intentar de nuevo</a></p>
                </div>
            <?php else: ?>
                <?php
                    $maxEtapa = 0;
                    foreach ($panorama['etapas'] as $etapaMax) {
                        $maxEtapa = max($maxEtapa, $etapaMax['cantidad']);
                    }
                ?>
                <div id="produccion-vacio" class="ops-empty" hidden>
                    <strong>Sin volumen del período</strong>
                    <p>El volumen del período se arma cuando cada paso queda registrado. Hoy solo se ve la etapa actual.</p>
                </div>
                <div class="panel dash-ciclo prod-espera" id="produccion-panel">
                    <div class="prod-ciclo-top">
                        <div>
                            <span>Cajas en el ciclo</span>
                            <strong id="prod-total" data-total="<?php echo e($panorama['total']); ?>"><?php echo e($panorama['total']); ?></strong>
                        </div>
                        <p>Haz clic en una etapa para ver sus cajas. La línea roja y la barra roja marcan la guía de tiempo de esta pantalla.</p>
                    </div>
                    <figure class="prod-grafico" id="prod-grafico" aria-label="Gráfico de cajas por etapa">
                        <figcaption>Cajas por etapa</figcaption>
                        <div class="prod-barras" id="prod-barras">
                            <?php $__currentLoopData = $panorama['etapas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $altoBarra = ($maxEtapa > 0 && $etapa['cantidad'] > 0)
                                        ? round($etapa['cantidad'] / $maxEtapa * 100, 2)
                                        : 0;
                                ?>
                                <button
                                    class="prod-col <?php echo e($etapa['tarde'] > 0 ? 'is-late' : ''); ?>"
                                    type="button"
                                    data-etapa="<?php echo e($etapa['indice']); ?>"
                                    data-cantidad="<?php echo e($etapa['cantidad']); ?>"
                                    aria-pressed="false"
                                >
                                    <span class="prod-col-n"><?php echo e($etapa['cantidad']); ?></span>
                                    <span class="prod-col-pista" aria-hidden="true">
                                        <span class="prod-col-barra" style="--alto: <?php echo e($altoBarra); ?>%"></span>
                                    </span>
                                    <span class="prod-col-nombre"><?php echo e($etapa['nombre']); ?></span>
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <p class="prod-leyenda"><span class="prod-leyenda-muestra" aria-hidden="true"></span> Barra roja: esa etapa ya pasó la guía de tiempo de esta pantalla.</p>
                    </figure>
                    <div class="phases phases-compact dash-fases" id="prod-fases">
                        <?php $__currentLoopData = $panorama['etapas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button class="phase <?php echo e($etapa['cantidad'] > 0 ? 'is-live' : ''); ?> <?php echo e($etapa['tarde'] > 0 ? 'is-late' : ''); ?>" type="button" data-etapa="<?php echo e($etapa['indice']); ?>" aria-pressed="false">
                                <div class="n"><?php echo e($etapa['cantidad']); ?></div>
                                <div class="l"><?php echo e($etapa['nombre']); ?></div>
                            </button>
                            <?php if(! $loop->last): ?>
                                <div class="phase-sep" aria-hidden="true">&#8250;</div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php $__currentLoopData = $panorama['etapas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="prod-detalle" data-etapa="<?php echo e($etapa['indice']); ?>" hidden>
                            <h3><?php echo e($etapa['nombre']); ?></h3>
                            <?php if($etapa['cajas'] === []): ?>
                                <p>No hay cajas en esta etapa.</p>
                            <?php else: ?>
                                <ul>
                                    <?php $__currentLoopData = $etapa['cajas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $caja): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="<?php echo e($caja['tarde'] ? 'is-late' : ''); ?>">
                                            <strong><?php echo e($caja['id']); ?></strong>
                                            <span><?php echo e($caja['servicio']); ?></span>
                                            <em><?php echo e($caja['espera']); ?></em>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<noscript>
<style>
  #produccion-panel.prod-espera,
  #produccion-error.prod-espera { opacity: 1; }
  .prod-col-barra { transform: none; }
</style>
</noscript>
<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/anime.min.js')); ?>"></script>
<script>
(function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var puedeAnimar = !reduce && typeof anime === 'function';
    var error = document.getElementById('produccion-error');
    var panel = document.getElementById('produccion-panel');

    function contarIndicadores() {
        var indicadores = document.querySelectorAll('[data-kpi]');
        if (!puedeAnimar) {
            return;
        }
        indicadores.forEach(function (el, i) {
            var destino = Number(el.getAttribute('data-valor')) || 0;
            var estado = { n: 0 };
            el.textContent = '0';
            anime({
                targets: estado,
                n: destino,
                round: 1,
                delay: i * 90,
                duration: 900,
                easing: 'easeOutCubic',
                update: function () {
                    el.textContent = String(estado.n);
                },
                complete: function () {
                    el.textContent = String(destino);
                }
            });
        });
    }

    contarIndicadores();

    if (error) {
        if (!puedeAnimar) {
            error.classList.remove('prod-espera');
            return;
        }
        anime({
            targets: error,
            opacity: [0, 1],
            translateY: [12, 0],
            duration: 420,
            easing: 'easeOutCubic',
            complete: function () {
                error.classList.remove('prod-espera');
            }
        });
        return;
    }

    var select = document.getElementById('periodo');
    var etiqueta = document.getElementById('periodo-etiqueta');
    var vacio = document.getElementById('produccion-vacio');
    var totalEl = document.getElementById('prod-total');
    var total = Number(totalEl.getAttribute('data-total')) || 0;
    var fases = document.querySelectorAll('#prod-fases .phase');
    var columnas = document.querySelectorAll('#prod-barras .prod-col');
    var barras = document.querySelectorAll('#prod-barras .prod-col-barra');
    var detalles = document.querySelectorAll('.prod-detalle');
    var ticket = 0;

    function cerrarDetalle() {
        fases.forEach(function (fase) {
            fase.classList.remove('is-open');
            fase.setAttribute('aria-pressed', 'false');
        });
        columnas.forEach(function (col) {
            col.classList.remove('is-open');
            col.setAttribute('aria-pressed', 'false');
        });
        detalles.forEach(function (detalle) { detalle.hidden = true; });
    }

    function abrirEtapa(indice) {
        var fase = document.querySelector('#prod-fases .phase[data-etapa="' + indice + '"]');
        if (!fase) {
            return;
        }
        var abierta = fase.classList.contains('is-open');
        cerrarDetalle();
        if (abierta) {
            return;
        }
        fase.classList.add('is-open');
        fase.setAttribute('aria-pressed', 'true');
        var col = document.querySelector('#prod-barras .prod-col[data-etapa="' + indice + '"]');
        if (col) {
            col.classList.add('is-open');
            col.setAttribute('aria-pressed', 'true');
        }
        var detalle = document.querySelector('.prod-detalle[data-etapa="' + indice + '"]');
        if (detalle) {
            detalle.hidden = false;
        }
    }

    function dibujarBarras(instantaneo) {
        if (typeof anime === 'function') {
            anime.remove(barras);
        }
        if (instantaneo || !puedeAnimar) {
            barras.forEach(function (barra) {
                barra.style.transform = 'scaleY(1)';
            });
            return;
        }
        barras.forEach(function (barra) {
            barra.style.transform = 'scaleY(0.02)';
        });
        anime({
            targets: barras,
            scaleY: [0.02, 1],
            delay: anime.stagger(70, { start: 120 }),
            duration: 720,
            easing: 'easeOutCubic'
        });
    }

    function contarTotal(actual) {
        if (!puedeAnimar) {
            totalEl.textContent = String(total);
            return;
        }
        var estado = { n: 0 };
        totalEl.textContent = '0';
        anime({
            targets: estado,
            n: total,
            round: 1,
            duration: 900,
            easing: 'easeOutCubic',
            update: function () {
                if (actual !== ticket) {
                    return;
                }
                totalEl.textContent = String(estado.n);
            },
            complete: function () {
                if (actual === ticket) {
                    totalEl.textContent = String(total);
                }
            }
        });
    }

    function mostrarAhora() {
        var actual = ++ticket;
        if (typeof anime === 'function') {
            anime.remove(panel);
            anime.remove(vacio);
        }
        cerrarDetalle();
        panel.hidden = false;
        vacio.hidden = true;
        etiqueta.textContent = 'Etapa actual de cada caja';
        if (!puedeAnimar) {
            panel.classList.remove('prod-espera');
            panel.style.opacity = '1';
            panel.style.transform = 'none';
            totalEl.textContent = String(total);
            dibujarBarras(true);
            return;
        }
        contarTotal(actual);
        dibujarBarras(false);
        anime({
            targets: panel,
            opacity: [0, 1],
            translateY: [14, 0],
            duration: 480,
            easing: 'easeOutCubic',
            complete: function () {
                if (actual === ticket) {
                    panel.classList.remove('prod-espera');
                }
            }
        });
    }

    function mostrarVacio() {
        var actual = ++ticket;
        etiqueta.textContent = 'Ese período todavía no tiene historial de pasos';
        cerrarDetalle();

        function revelar() {
            if (actual !== ticket) {
                return;
            }
            panel.hidden = true;
            panel.style.opacity = '';
            panel.style.transform = '';
            vacio.hidden = false;
            if (!puedeAnimar) {
                vacio.style.opacity = '1';
                vacio.style.transform = 'none';
                return;
            }
            vacio.style.opacity = '0';
            anime.remove(vacio);
            anime({
                targets: vacio,
                opacity: [0, 1],
                translateY: [10, 0],
                duration: 380,
                easing: 'easeOutCubic'
            });
        }

        if (panel.hidden || !puedeAnimar) {
            revelar();
            return;
        }

        if (typeof anime === 'function') {
            anime.remove(panel);
        }
        anime({
            targets: panel,
            opacity: [1, 0],
            translateY: [0, 12],
            duration: 260,
            easing: 'easeInCubic',
            complete: revelar
        });
    }

    panel.addEventListener('click', function (evento) {
        var origen = evento.target.closest('.phase, .prod-col');
        if (!origen || !panel.contains(origen)) {
            return;
        }
        abrirEtapa(origen.getAttribute('data-etapa'));
    });

    select.addEventListener('change', function () {
        if (select.value === 'ahora') {
            mostrarAhora();
            return;
        }
        mostrarVacio();
    });

    mostrarAhora();
})();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/panel-admin.blade.php ENDPATH**/ ?>