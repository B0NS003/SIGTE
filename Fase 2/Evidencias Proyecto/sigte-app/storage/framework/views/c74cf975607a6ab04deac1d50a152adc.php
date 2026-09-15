

<?php $__env->startSection('title', 'SIGTE — Enfermera de turno'); ?>

<?php $__env->startSection('content'); ?>
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="<?php echo e(asset('images/logo-hospital-circular.png')); ?>" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Turno</div>
            </div>
        </div>
        <div class="nav-label">Operación</div>
        <nav class="nav">
            <a class="active" href="<?php echo e(route('mockups.panel', 'enfermera')); ?>">Resumen turno</a>
            <a href="<?php echo e(route('mockups.panel', 'operador')); ?>">Flujo de cajas</a>
            <a href="<?php echo e(route('mockups.recepcion')); ?>">Recepción</a>
            <a href="<?php echo e(route('mockups.entrega')); ?>">Entrega</a>
        </nav>
        <div class="nav-label">Gestión de turno</div>
        <nav class="nav">
            <a href="<?php echo e(route('mockups.inventario')); ?>">Inventario</a>
            <a href="<?php echo e(route('mockups.alertas')); ?>">Alertas <span class="nav-badge">3</span></a>
            <a href="<?php echo e(route('mockups.cierre_turno')); ?>">Cierre / traspaso</a>
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
                <p class="eyebrow">Enfermera de turno</p>
                <h1>Supervisión del turno</h1>
                <p class="main-sub">Opera y supervisa el día. Al terminar, cierra el turno dejando responsable, pendientes y alertas abiertas para el relevo.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Ir al flujo</a>
                <a class="btn btn-dark" href="<?php echo e(route('mockups.cierre_turno')); ?>">Cerrar turno</a>
            </div>
        </div>

        <section class="turno-banner panel">
            <div>
                <span class="adv-k">A cargo ahora</span>
                <strong><?php echo e($usuario['nombre']); ?></strong>
                <small>15 sep 2026 · Mañana 08:00–16:00 · Operadoras: Y. Maureira, C. Muñoz</small>
            </div>
            <a class="btn btn-ghost" href="<?php echo e(route('mockups.cierre_turno')); ?>">Traspasar / cerrar</a>
        </section>

        <section class="alert-strip">
            <div class="alert-strip-title">Atención del turno</div>
            <div class="alert-strip-list">
                <?php $__currentLoopData = $alertas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="alert-item alert-<?php echo e($a['nivel']); ?>">
                        <strong><?php echo e($a['titulo']); ?></strong>
                        <span><?php echo e($a['detalle']); ?></span>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <p class="section-title">Carga por fase</p>
        <div class="phases">
            <?php $__currentLoopData = $fases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $fase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="phase">
                    <div class="phase-step"><?php echo e($i + 1); ?></div>
                    <div class="n"><?php echo e(collect($cajas)->where('fase_idx', $i)->count()); ?></div>
                    <div class="l"><?php echo e($fase); ?></div>
                </div>
                <?php if(!$loop->last): ?>
                    <div class="phase-sep">&#8250;</div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="panel-note">Sin menú Usuarios ni reportes de período: eso es administradora. El “reporte” de enfermera es el cierre / traspaso de turno.</div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\sigte-app\resources\views/mockups/panel-enfermera.blade.php ENDPATH**/ ?>