

<?php $__env->startSection('title', 'SIGTE — Operadora'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $salaDe = function (int $i): string {
        if ($i <= 1) return 'lavado';
        if ($i <= 3) return 'armado';
        return 'esteril';
    };
    $salaLabel = [
        'lavado' => 'Sala lavado',
        'armado' => 'Sala armado',
        'esteril' => 'Material estéril',
    ];
?>
<div class="shell shell-ops">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="<?php echo e(asset('images/logo-hospital-circular.png')); ?>" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Operación</div>
            </div>
        </div>
        <div class="nav-label">Mi trabajo</div>
        <nav class="nav">
            <a class="active" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Flujo de cajas</a>
            <a href="<?php echo e(route('mockups.recepcion')); ?>">Nueva recepción</a>
            <a href="<?php echo e(route('mockups.avanzar')); ?>">Avanzar etapa</a>
            <a href="<?php echo e(route('mockups.entrega')); ?>">Registrar entrega</a>
        </nav>
        <div class="nav-label">Consulta</div>
        <nav class="nav">
            <a href="<?php echo e(route('mockups.catalogo')); ?>">Catálogo</a>
            <a href="<?php echo e(route('mockups.inventario')); ?>">Inventario</a>
        </nav>
        <div class="sidebar-foot">
            <div class="side-user">
                <div class="avatar"><?php echo e(strtoupper(substr($usuario['nombre'], 0, 1))); ?></div>
                <div>
                    <strong><?php echo e($usuario['nombre']); ?></strong>
                    <small><?php echo e($usuario['rol']); ?></small>
                </div>
            </div>
            <a class="side-logout" href="<?php echo e(route('mockups.login')); ?>">Cambiar rol</a>
        </div>
    </aside>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Turno · tres salas · trazabilidad</p>
                <h1>Flujo de cajas</h1>
                <p class="main-sub">Unidireccional por salas (cada una con su “libro”): lavado → armado → material estéril. Las etiquetas de color marcan la sala; el tracker sigue en naranja.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-dark" href="<?php echo e(route('mockups.recepcion')); ?>">+ Nueva recepción</a>
            </div>
        </div>

        <div class="sala-legend">
            <div class="sala-chip sala-lavado"><span></span>Sala lavado <small>Recepción · Lavado</small></div>
            <div class="sala-chip sala-armado"><span></span>Sala armado <small>Preparación · Esterilización</small></div>
            <div class="sala-chip sala-esteril"><span></span>Material estéril <small>Almacén · Entrega</small></div>
        </div>

        <div class="ops-lane-legend">
            <?php $__currentLoopData = $fases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $fase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="ops-legend-item">
                    <span class="ops-dot"><?php echo e($i + 1); ?></span>
                    <?php echo e($fase); ?>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="ops-board">
            <?php $__currentLoopData = $cajas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $caja): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $sala = $salaDe($caja['fase_idx']); ?>
                <article class="track-card <?php echo e($caja['urgente'] ? 'is-urgent' : ''); ?>">
                    <header class="track-head">
                        <div>
                            <h2><?php echo e($caja['id']); ?></h2>
                            <p><?php echo e($caja['servicio']); ?> · <?php echo e($caja['hora']); ?> · <?php echo e($caja['operadora']); ?></p>
                        </div>
                        <div class="track-head-badges">
                            <span class="badge badge-sala badge-<?php echo e($sala); ?>"><?php echo e($salaLabel[$sala]); ?></span>
                            <span class="badge"><?php echo e($fases[$caja['fase_idx']]); ?></span>
                        </div>
                    </header>

                    <div class="track-pipe" aria-label="Progreso del ciclo">
                        <?php $__currentLoopData = $fases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $fase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $done = $i < $caja['fase_idx'];
                                $current = $i === $caja['fase_idx'];
                            ?>
                            <div class="track-step <?php echo e($done ? 'done' : ''); ?> <?php echo e($current ? 'current' : ''); ?>">
                                <div class="track-node"></div>
                                <div class="track-label"><?php echo e($fase); ?></div>
                            </div>
                            <?php if(!$loop->last): ?>
                                <div class="track-line <?php echo e($i < $caja['fase_idx'] ? 'done' : ''); ?>"></div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <footer class="track-foot">
                        <span><?php echo e($caja['estado']); ?></span>
                        <div class="track-actions">
                            <button class="btn btn-ghost" type="button" disabled title="Detalle después">Modificar</button>
                            <a class="btn btn-dark" href="<?php echo e(route('mockups.avanzar', ['caja' => $caja['id']])); ?>">Aceptar avance</a>
                        </div>
                    </footer>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\sigte-app\resources\views/mockups/panel-operador.blade.php ENDPATH**/ ?>