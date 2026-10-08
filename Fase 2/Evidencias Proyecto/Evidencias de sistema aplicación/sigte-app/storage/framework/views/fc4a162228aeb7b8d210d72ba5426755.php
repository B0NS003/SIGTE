<?php $__env->startSection('title', 'SIGTE — Historial de entregas'); ?>

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

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Gestión · solo administradora</p>
                <h1>Historial de entregas</h1>
                <p class="main-sub">Cada salida queda con las cajas, el servicio y las dos personas: quien entrega y quien recibe.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'administradora')); ?>">Volver al resumen</a>
            </div>
        </div>

        <?php if($falla): ?>
            <div class="ops-empty">
                <strong>No se pudo cargar el historial</strong>
                <p>El listado no está disponible en este momento.</p>
                <p><a href="<?php echo e(route('mockups.historial_entregas')); ?>">Intentar de nuevo</a></p>
            </div>
        <?php else: ?>
            <?php
                $ordenSectores = ['Pabellón', 'Dental', 'UCI', 'Urgencia', 'Maternidad', 'Curaciones'];
                $slugSector = [
                    'Pabellón' => 'pabellon',
                    'Dental' => 'dental',
                    'UCI' => 'uci',
                    'Urgencia' => 'urgencia',
                    'Maternidad' => 'maternidad',
                    'Curaciones' => 'curaciones',
                ];
                $grupos = collect($entregas)->groupBy('servicio')->sortBy(function ($grupo, $servicio) use ($ordenSectores) {
                    $indice = array_search($servicio, $ordenSectores, true);

                    return $indice === false ? 99 : $indice;
                });
            ?>
            <div class="hist-sectores" id="sectores">
                <?php $__currentLoopData = $grupos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $servicio => $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $ultima = $grupo->first(); ?>
                    <button class="hist-sector sec-<?php echo e($slugSector[$servicio] ?? 'otro'); ?>" type="button" data-servicio="<?php echo e($servicio); ?>">
                        <strong><?php echo e($servicio); ?></strong>
                        <span class="n"><?php echo e($grupo->count()); ?></span>
                        <small><?php echo e($grupo->count() === 1 ? 'entrega' : 'entregas'); ?></small>
                        <small>Última <?php echo e($ultima['hora']); ?> · <?php echo e($ultima['fecha']); ?></small>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <form class="query-toolbar panel" id="filtro-entregas" action="<?php echo e(route('mockups.historial_entregas')); ?>" method="get" onsubmit="return false">
                <label class="field" style="margin:0; flex:1;">
                    <span class="icon" aria-hidden="true">⌕</span>
                    <input id="q" type="search" placeholder="Código, persona o número de entrega" autocomplete="off">
                </label>
                <label class="field field-select" style="margin:0; min-width:11rem;">
                    <select id="cuando">
                        <option value="todas">Todas</option>
                        <option value="hoy">Hoy</option>
                        <option value="ayer">Ayer</option>
                        <option value="semana">Esta semana</option>
                    </select>
                </label>
            </form>

            <div class="mant-head">
                <div>
                    <h2 id="lista-titulo">Últimas entregas</h2>
                    <p id="entregas-meta">Las <?php echo e(min(3, count($entregas))); ?> más recientes</p>
                </div>
                <button class="btn btn-ghost" id="ver-ultimas" type="button" hidden>Ver últimas</button>
            </div>

            <div class="ops-empty" id="entregas-vacio" hidden>
                <strong>No hay entregas con ese criterio</strong>
                <p>Probá con otro código, servicio o persona.</p>
            </div>

            <div class="hist-lista" id="entregas-lista">
                <?php $__currentLoopData = $entregas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entrega): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $texto = mb_strtolower(implode(' ', [
                            $entrega['id'],
                            $entrega['servicio'],
                            $entrega['entrega'],
                            $entrega['recibe'],
                            collect($entrega['cajas'])->pluck('codigo')->implode(' '),
                            collect($entrega['cajas'])->pluck('nombre')->implode(' '),
                            implode(' ', $entrega['materiales']),
                        ]));
                    ?>
                    <article class="hist-card sec-<?php echo e($slugSector[$entrega['servicio']] ?? 'otro'); ?>" data-servicio="<?php echo e($entrega['servicio']); ?>" data-cuando="<?php echo e($entrega['cuando']); ?>" data-texto="<?php echo e($texto); ?>">
                        <div class="hist-cuando">
                            <strong><?php echo e($entrega['hora']); ?></strong>
                            <span><?php echo e($entrega['fecha']); ?></span>
                            <small><?php echo e($entrega['id']); ?></small>
                        </div>
                        <div class="hist-cuerpo">
                            <div class="hist-top">
                                <h2><?php echo e($entrega['servicio']); ?></h2>
                                <span class="badge"><?php echo e(count($entrega['cajas'])); ?> <?php echo e(count($entrega['cajas']) === 1 ? 'caja' : 'cajas'); ?></span>
                            </div>
                            <div class="hist-cajas">
                                <?php $__currentLoopData = $entrega['cajas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $caja): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="hist-chip"><?php echo e($caja['codigo']); ?> <small><?php echo e($caja['nombre']); ?></small></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php $__empty_1 = true; $__currentLoopData = $entrega['materiales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <span class="hist-chip is-material"><?php echo e($material); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span class="hist-chip is-material">Sin material extra</span>
                                <?php endif; ?>
                            </div>
                            <div class="entrega-datos">
                                <div class="entrega-dato">
                                    <span>Quién entrega</span>
                                    <strong><?php echo e($entrega['entrega']); ?></strong>
                                    <small><?php echo e($entrega['entrega_rol']); ?></small>
                                </div>
                                <div class="entrega-dato">
                                    <span>Quién recibe</span>
                                    <strong><?php echo e($entrega['recibe']); ?></strong>
                                    <small><?php echo e($entrega['recibe_rol']); ?></small>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </main>
</div>
<?php if (! ($falla)): ?>
<script>
    var q = document.getElementById('q');
    var cuando = document.getElementById('cuando');
    var meta = document.getElementById('entregas-meta');
    var titulo = document.getElementById('lista-titulo');
    var vacio = document.getElementById('entregas-vacio');
    var verUltimas = document.getElementById('ver-ultimas');
    var cards = document.querySelectorAll('#entregas-lista .hist-card');
    var sectorActivo = '';

    function marcarSector() {
        document.querySelectorAll('.hist-sector').forEach(function (boton) {
            boton.classList.toggle('is-on', boton.dataset.servicio === sectorActivo);
        });
        verUltimas.hidden = sectorActivo === '';
        titulo.textContent = sectorActivo === '' ? 'Últimas entregas' : sectorActivo;
    }

    function aplicar() {
        var texto = q.value.trim().toLowerCase();
        var coinciden = [];
        cards.forEach(function (card) {
            var coincideTexto = texto === '' || card.dataset.texto.indexOf(texto) !== -1;
            var coincideSector = sectorActivo === '' || card.dataset.servicio === sectorActivo;
            var coincideCuando = cuando.value === 'todas'
                || (cuando.value === 'semana' && card.dataset.cuando !== 'anterior')
                || card.dataset.cuando === cuando.value;
            card.hidden = true;
            if (coincideTexto && coincideSector && coincideCuando) {
                coinciden.push(card);
            }
        });
        var mostrar = sectorActivo === '' ? coinciden.slice(0, 3) : coinciden;
        mostrar.forEach(function (card) {
            card.hidden = false;
        });
        vacio.hidden = mostrar.length !== 0;
        meta.hidden = mostrar.length === 0;
        if (sectorActivo === '') {
            meta.textContent = mostrar.length === 1 ? 'La más reciente' : 'Las ' + mostrar.length + ' más recientes';
        } else {
            meta.textContent = mostrar.length === 1 ? '1 entrega' : mostrar.length + ' entregas';
        }
    }

    document.getElementById('sectores').addEventListener('click', function (evento) {
        var boton = evento.target.closest('.hist-sector');
        if (!boton) {
            return;
        }
        sectorActivo = sectorActivo === boton.dataset.servicio ? '' : boton.dataset.servicio;
        marcarSector();
        aplicar();
    });

    verUltimas.addEventListener('click', function () {
        sectorActivo = '';
        marcarSector();
        aplicar();
    });

    q.addEventListener('input', aplicar);
    cuando.addEventListener('change', aplicar);
    aplicar();
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/historial-entregas.blade.php ENDPATH**/ ?>