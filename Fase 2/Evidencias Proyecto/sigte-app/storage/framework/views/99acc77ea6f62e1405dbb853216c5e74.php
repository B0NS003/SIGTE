

<?php $__env->startSection('title', 'SIGTE — Custodia / auditoría'); ?>

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
            <a href="<?php echo e(route('mockups.panel', 'administradora')); ?>">Resumen</a>
            <a href="<?php echo e(route('mockups.catalogo')); ?>">Catálogo</a>
            <a href="<?php echo e(route('mockups.inventario')); ?>">Inventario</a>
            <a href="<?php echo e(route('mockups.usuarios')); ?>">Usuarios</a>
            <a href="<?php echo e(route('mockups.reportes')); ?>">Reportes</a>
            <a class="active" href="<?php echo e(route('mockups.custodia')); ?>">Custodia / auditoría</a>
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
                <p class="eyebrow">Gestión · trazabilidad de responsabilidad</p>
                <h1>Custodia / auditoría</h1>
                <p class="main-sub">Dos lecturas: la <strong>cadena de custodia</strong> de cada caja (quién entregó / recibió) y la <strong>bitácora</strong> de quién cambió qué en el sistema.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'administradora')); ?>">Volver al resumen</a>
                <button class="btn btn-dark" type="button">Exportar bitácora</button>
            </div>
        </div>

        <div class="query-toolbar panel">
            <label class="field" style="margin:0; flex:1;">
                <span class="icon" aria-hidden="true">⌕</span>
                <input type="search" placeholder="Buscar caja, usuario o acción…" value="SET-007">
            </label>
            <label class="field field-select" style="margin:0; min-width:11rem;">
                <select>
                    <option selected>Hoy</option>
                    <option>Últimos 7 días</option>
                    <option>Este mes</option>
                </select>
            </label>
            <label class="field field-select" style="margin:0; min-width:11rem;">
                <select>
                    <option selected>Todo</option>
                    <option>Solo custodia</option>
                    <option>Solo auditoría</option>
                    <option>Solo alertas</option>
                </select>
            </label>
        </div>

        <div class="grid-2">
            <section class="panel">
                <div class="panel-head">
                    <h2>Cadenas de custodia</h2>
                    <span class="badge">Por caja</span>
                </div>

                <?php $__currentLoopData = $cadenas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cadena): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="custody-card <?php echo e($cadena['estado'] === 'Incidencia' ? 'is-alert' : ''); ?>">
                        <header class="custody-head">
                            <div>
                                <strong><?php echo e($cadena['caja']); ?></strong>
                                <span><?php echo e($cadena['servicio']); ?></span>
                            </div>
                            <?php if($cadena['estado'] === 'Incidencia'): ?>
                                <span class="pill pill-danger"><?php echo e($cadena['estado']); ?></span>
                            <?php else: ?>
                                <span class="pill pill-ok"><?php echo e($cadena['estado']); ?></span>
                            <?php endif; ?>
                        </header>
                        <ol class="custody-timeline">
                            <?php $__currentLoopData = $cadena['eventos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="<?php echo e($ev['tipo'] === 'Alerta' ? 'is-alert' : ''); ?>">
                                    <div class="ct-time"><?php echo e($ev['hora']); ?></div>
                                    <div class="ct-body">
                                        <strong><?php echo e($ev['tipo']); ?></strong>
                                        <span><?php echo e($ev['nota']); ?></span>
                                        <small>
                                            <?php if($ev['a'] !== '—'): ?>
                                                <?php echo e($ev['de']); ?> → <?php echo e($ev['a']); ?>

                                            <?php else: ?>
                                                <?php echo e($ev['de']); ?>

                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ol>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>

            <section class="panel list">
                <div class="panel-head">
                    <h2>Bitácora de auditoría</h2>
                    <span class="badge">Quién · qué · cuándo</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Actor</th>
                                <th>Acción</th>
                                <th>Objeto</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $auditoria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($row['hora']); ?></td>
                                    <td><strong><?php echo e($row['actor']); ?></strong></td>
                                    <td><?php echo e($row['accion']); ?></td>
                                    <td><?php echo e($row['objeto']); ?></td>
                                    <td class="muted-cell"><?php echo e($row['detalle']); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="panel-note">Custodia = responsabilidad del material. Auditoría = cambios en el sistema (etapas, stock, usuarios). Mockup sin BD.</div>
            </section>
        </div>

        <section class="panel" style="margin-top:1rem;">
            <div class="panel-head"><h2>Por qué van juntas</h2></div>
            <ul class="why-list" style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem 1.5rem;">
                <li><strong>Recepción / entrega</strong> — firma de quién entrega y quién recibe (cadena).</li>
                <li><strong>Avance de etapa</strong> — queda en bitácora quién movió la caja.</li>
                <li><strong>Incidencias</strong> — alertas sin custodia aparecen en la cadena (ej. CAJA-118).</li>
                <li><strong>Admin</strong> — también audita altas de usuario y cambios de stock mínimo.</li>
            </ul>
        </section>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\sigte-app\resources\views/mockups/custodia.blade.php ENDPATH**/ ?>