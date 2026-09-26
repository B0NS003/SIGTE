

<?php $__env->startSection('title', 'SIGTE — Catálogo'); ?>

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
            <a class="active" href="<?php echo e(route('mockups.catalogo')); ?>">Catálogo</a>
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
                <p class="eyebrow">Consulta · fichas maestras</p>
                <h1>Catálogo</h1>
                <p class="main-sub">Base de qué es cada set/caja: código, tipo, piezas y contenido típico. No es stock (eso va en Inventario).</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.inventario')); ?>">Ver inventario</a>
            </div>
        </div>

        <div class="query-toolbar panel">
            <label class="field" style="margin:0; flex:1;">
                <span class="icon" aria-hidden="true">⌕</span>
                <input type="search" placeholder="Buscar por código, nombre o servicio…" value="">
            </label>
            <label class="field field-select" style="margin:0; min-width:12rem;">
                <select>
                    <option>Todos los tipos</option>
                    <option>Set quirurgico</option>
                    <option>Caja de curacion</option>
                    <option>Contenedor</option>
                    <option>Paquete grado medico</option>
                </select>
            </label>
        </div>

        <section class="panel list">
            <div class="panel-head">
                <h2>Fichas del catálogo</h2>
                <span class="badge"><?php echo e(count($items)); ?> ítems</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Piezas</th>
                            <th>Servicio típico</th>
                            <th>Contenido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><strong><?php echo e($item['codigo']); ?></strong></td>
                                <td><?php echo e($item['nombre']); ?></td>
                                <td><?php echo e($item['tipo']); ?></td>
                                <td><?php echo e($item['piezas']); ?></td>
                                <td><?php echo e($item['servicio']); ?></td>
                                <td class="muted-cell"><?php echo e($item['contenido']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="panel-note">Mockup: esta lista es la “base” de datos de ejemplo. Después se convierte en tabla <code>catalogo_items</code>.</div>
        </section>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\sigte-app\resources\views/mockups/catalogo.blade.php ENDPATH**/ ?>