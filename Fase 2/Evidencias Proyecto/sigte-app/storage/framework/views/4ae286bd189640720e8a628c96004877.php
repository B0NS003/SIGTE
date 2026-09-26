

<?php $__env->startSection('title', 'SIGTE — Inventario'); ?>

<?php $__env->startSection('content'); ?>
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
            <a href="<?php echo e(route('mockups.panel', 'operador')); ?>">Flujo de cajas</a>
            <a href="<?php echo e(route('mockups.recepcion')); ?>">Nueva recepción</a>
            <a href="<?php echo e(route('mockups.avanzar')); ?>">Avanzar etapa</a>
            <a href="<?php echo e(route('mockups.entrega')); ?>">Registrar entrega</a>
        </nav>
        <div class="nav-label">Consulta</div>
        <nav class="nav">
            <a href="<?php echo e(route('mockups.catalogo')); ?>">Catálogo</a>
            <a class="active" href="<?php echo e(route('mockups.inventario')); ?>">Inventario</a>
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
                <p class="eyebrow">Consulta · tres libros / tres salas</p>
                <h1>Inventario</h1>
                <p class="main-sub">Como en terreno: cada sala anota su propio inventario. Lavado, armado y material estéril no comparten el mismo “libro”.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.catalogo')); ?>">Ver catálogo</a>
            </div>
        </div>

        <div class="sala-tabs">
            <?php $__currentLoopData = $salas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="sala-tab sala-<?php echo e($key); ?> <?php echo e($sala === $key ? 'active' : ''); ?>"
                   href="<?php echo e(route('mockups.inventario', ['sala' => $key])); ?>"><?php echo e($label); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="kpi-row">
            <div class="kpi">
                <div class="l">Ítems en esta sala</div>
                <div class="n"><?php echo e($resumen['tipos']); ?></div>
                <div class="h"><?php echo e($salas[$sala]); ?></div>
            </div>
            <div class="kpi ok">
                <div class="l">Stock visible</div>
                <div class="n"><?php echo e($resumen['en_almacen']); ?></div>
                <div class="h">Unidades</div>
            </div>
            <div class="kpi">
                <div class="l">En proceso</div>
                <div class="n"><?php echo e($resumen['en_proceso']); ?></div>
                <div class="h">Ligadas al flujo</div>
            </div>
            <div class="kpi warn">
                <div class="l">Bajo mínimo</div>
                <div class="n"><?php echo e($resumen['bajo_minimo']); ?></div>
                <div class="h">De esta sala</div>
            </div>
        </div>

        <section class="panel list">
            <div class="panel-head">
                <h2>Libro · <?php echo e($salas[$sala]); ?></h2>
                <span class="badge badge-sala badge-<?php echo e($sala); ?>"><?php echo e($salas[$sala]); ?></span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Ubicación</th>
                            <th>Stock</th>
                            <th>Mínimo</th>
                            <th>En proceso</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="stock-<?php echo e($item['estado']); ?>">
                                <td><strong><?php echo e($item['codigo']); ?></strong></td>
                                <td><?php echo e($item['nombre']); ?></td>
                                <td><?php echo e($item['ubicacion']); ?></td>
                                <td><?php echo e($item['stock']); ?></td>
                                <td><?php echo e($item['minimo']); ?></td>
                                <td><?php echo e($item['en_proceso']); ?></td>
                                <td>
                                    <?php if($item['estado'] === 'ok'): ?>
                                        <span class="pill pill-ok">OK</span>
                                    <?php elseif($item['estado'] === 'bajo'): ?>
                                        <span class="pill pill-warn">Bajo mínimo</span>
                                    <?php else: ?>
                                        <span class="pill pill-danger">Crítico</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="panel-note">Mockup: pestañas = los 3 libros de sala. La trazabilidad de cajas sigue en “Flujo”; aquí es stock por área.</div>
        </section>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\sigte-app\resources\views/mockups/inventario.blade.php ENDPATH**/ ?>