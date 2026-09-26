

<?php $__env->startSection('title', 'SIGTE — Cierre de turno'); ?>

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
                <p class="eyebrow">Traspaso · responsabilidad del turno</p>
                <h1>Cierre de turno</h1>
                <p class="main-sub">Deja constancia de quién estuvo a cargo, qué quedó pendiente y qué alertas siguen abiertas. Si mañana pasa algo, se sabe a quién preguntar.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'enfermera')); ?>">Volver</a>
            </div>
        </div>

        <div class="grid-2">
            <form class="panel recv-form" action="<?php echo e(route('mockups.panel', 'enfermera')); ?>" method="get">
                <div class="panel-head">
                    <h2>Datos del cierre</h2>
                    <span class="badge"><?php echo e($turno['fecha']); ?></span>
                </div>

                <div class="adv-summary">
                    <div>
                        <span class="adv-k">Entrega turno</span>
                        <strong><?php echo e($turno['responsable']); ?></strong>
                        <small><?php echo e($turno['bloque']); ?></small>
                    </div>
                    <div class="adv-arrow" aria-hidden="true">→</div>
                    <div>
                        <span class="adv-k">Recibe relevo</span>
                        <strong><?php echo e($turno['relevo']); ?></strong>
                        <small>Próximo bloque</small>
                    </div>
                </div>

                <label class="field-label" for="relevo">Quién recibe el turno</label>
                <label class="field field-select">
                    <select id="relevo" name="relevo">
                        <option selected>Patricia Vega</option>
                        <option>Alejandra Riquelme</option>
                        <option>Otra enfermera…</option>
                    </select>
                </label>

                <label class="field-label" for="nota">Pendientes / nota al relevo</label>
                <textarea id="nota" class="recv-textarea" name="nota" rows="4"><?php $__currentLoopData = $pendientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>• <?php echo e($p); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></textarea>

                <div class="recv-checks">
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Revisé alertas abiertas con el equipo</span>
                    </label>
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Informé cajas críticas en proceso (ej. autoclave)</span>
                    </label>
                    <label class="check">
                        <input type="checkbox">
                        <span>Stock bajo comunicado a administradora</span>
                    </label>
                </div>

                <div class="recv-actions">
                    <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'enfermera')); ?>">Cancelar</a>
                    <button class="btn btn-primary" type="submit" style="width:auto;min-width:12rem;">Confirmar cierre de turno</button>
                </div>
            </form>

            <aside>
                <section class="panel" style="margin-bottom:1rem;">
                    <div class="panel-head"><h2>Resumen del turno</h2></div>
                    <div class="kpi-row" style="grid-template-columns:1fr 1fr; margin:0;">
                        <?php $__currentLoopData = $resumen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="kpi">
                                <div class="l"><?php echo e($k['label']); ?></div>
                                <div class="n"><?php echo e($k['value']); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <p class="recv-help" style="margin-top:0.85rem;">Operadoras del bloque: <strong><?php echo e($turno['operadoras']); ?></strong></p>
                </section>

                <section class="panel list">
                    <div class="panel-head">
                        <h2>Historial reciente</h2>
                        <span class="badge">Quién estuvo a cargo</span>
                    </div>
                    <table>
                        <thead>
                            <tr><th>Fecha</th><th>Bloque</th><th>Responsable</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $historial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($h['fecha']); ?></td>
                                    <td><?php echo e($h['bloque']); ?></td>
                                    <td><strong><?php echo e($h['responsable']); ?></strong></td>
                                    <td><span class="pill pill-ok"><?php echo e($h['estado']); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                    <div class="panel-note">Esto no reemplaza los reportes de administradora (período/producción). Es la bitácora de responsabilidad del turno.</div>
                </section>
            </aside>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/cierre-turno.blade.php ENDPATH**/ ?>