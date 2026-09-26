

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

        <div class="nav-label">Gestión</div>
        <nav class="nav">
            <a class="active" href="<?php echo e(route('mockups.panel', 'administradora')); ?>">Resumen</a>
            <a href="<?php echo e(route('mockups.catalogo')); ?>">Catálogo</a>
            <a href="<?php echo e(route('mockups.inventario')); ?>">Inventario</a>
            <a href="<?php echo e(route('mockups.usuarios')); ?>">Usuarios</a>
            <a href="<?php echo e(route('mockups.reportes')); ?>">Reportes</a>
            <a href="<?php echo e(route('mockups.custodia')); ?>">Custodia / auditoría</a>
            <a href="<?php echo e(route('mockups.alertas')); ?>">Alertas <span class="nav-badge">3</span></a>
        </nav>

        <div class="nav-label">Supervisión</div>
        <nav class="nav">
            <a href="<?php echo e(route('mockups.panel', 'operador')); ?>">Ver flujo de cajas</a>
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
                <p class="eyebrow">Hospital San José de Melipilla</p>
                <h1>Resumen de gestión</h1>
                <p class="main-sub">No opera el ciclo caja a caja: configura la base (catálogo, stock, usuarios) y supervisa alertas, custodia y reportes.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Ver vista operadora</a>
                <a class="btn btn-dark" href="<?php echo e(route('mockups.catalogo')); ?>">Mantener catálogo</a>
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

        <p class="section-title">Indicadores del día</p>
        <div class="kpi-row">
            <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="kpi <?php echo e($kpi['tone']); ?>">
                    <div class="l"><?php echo e($kpi['label']); ?></div>
                    <div class="n"><?php echo e($kpi['value']); ?></div>
                    <div class="h"><?php echo e($kpi['hint']); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <p class="section-title">Herramientas de gestión</p>
        <div class="action-row admin-tools">
            <a class="action-card" href="<?php echo e(route('mockups.catalogo')); ?>">
                <strong>Catálogo</strong>
                <span>Fichas maestras: qué es cada set/caja y su contenido.</span>
            </a>
            <a class="action-card" href="<?php echo e(route('mockups.inventario')); ?>">
                <strong>Inventario</strong>
                <span>Stock, mínimos y alertas de reposición.</span>
            </a>
            <a class="action-card" href="<?php echo e(route('mockups.usuarios')); ?>">
                <strong>Usuarios</strong>
                <span>Crear / desactivar cuentas y asignar rol (solo admin).</span>
            </a>
            <a class="action-card" href="<?php echo e(route('mockups.reportes')); ?>">
                <strong>Reportes</strong>
                <span>Producción del período, entregas, auditoría.</span>
            </a>
        </div>

        <div class="grid-2">
            <section class="panel list">
                <div class="panel-head">
                    <h2>Carga del ciclo (solo lectura)</h2>
                    <a class="panel-link" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Abrir flujo operadora</a>
                </div>
                <div class="phases phases-compact">
                    <?php $__currentLoopData = $fases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $fase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="phase">
                            <div class="phase-step"><?php echo e($i + 1); ?></div>
                            <div class="n"><?php echo e(collect($cajas)->where('fase_idx', $i)->count()); ?></div>
                            <div class="l"><?php echo e($fase); ?></div>
                        </div>
                        <?php if(!$loop->last): ?>
                            <div class="phase-sep" aria-hidden="true">&#8250;</div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <table>
                    <thead>
                        <tr><th>Caja</th><th>Fase</th><th>Servicio</th><th>Hora</th></tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $cajas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><strong><?php echo e($c['id']); ?></strong></td>
                                <td><span class="badge"><?php echo e($fases[$c['fase_idx']]); ?></span></td>
                                <td><?php echo e($c['servicio']); ?></td>
                                <td><?php echo e($c['hora']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Cómo se reparte el trabajo</h2></div>
                <ul class="why-list">
                    <li><strong>Administradora</strong> — dueña de la información: catálogo, stock mínimo, usuarios, reportes y auditoría.</li>
                    <li><strong>Enfermera de turno</strong> — supervisa el día (alertas, inventario, entregas). Sin menú Usuarios.</li>
                    <li><strong>Operadora</strong> — ejecuta el ciclo: recepción → etapas → entrega. Consulta catálogo/inventario.</li>
                </ul>
                <div class="panel-note">Supervisión = solo mirar el flujo. Recepción y entrega viven en la vista operadora; admin entra ahí si cubre un turno.</div>
            </section>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\sigte-app\resources\views/mockups/panel-admin.blade.php ENDPATH**/ ?>