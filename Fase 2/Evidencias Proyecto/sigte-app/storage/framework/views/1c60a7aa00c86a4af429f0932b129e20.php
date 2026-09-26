

<?php $__env->startSection('title', 'SIGTE — Reportes'); ?>

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
            <a class="active" href="<?php echo e(route('mockups.reportes')); ?>">Reportes</a>
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
                <p class="eyebrow">Gestión · producción y trazabilidad</p>
                <h1>Reportes</h1>
                <p class="main-sub">Resumen del período: volumen del ciclo, tiempos por etapa, carga por servicio e incidencias. Lectura de gestión, no operación del día.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'administradora')); ?>">Volver al resumen</a>
                <button class="btn btn-dark" type="button">Exportar CSV</button>
            </div>
        </div>

        <div class="query-toolbar panel">
            <label class="field field-select" style="margin:0; min-width:11rem;">
                <select>
                    <option>Esta semana</option>
                    <option selected>Últimos 7 días</option>
                    <option>Este mes</option>
                    <option>Personalizado…</option>
                </select>
            </label>
            <label class="field field-select" style="margin:0; min-width:11rem;">
                <select>
                    <option selected>Todos los servicios</option>
                    <option>Pabellon</option>
                    <option>Urgencia</option>
                    <option>Maternidad</option>
                    <option>UCI</option>
                    <option>Curaciones</option>
                </select>
            </label>
            <label class="field field-select" style="margin:0; min-width:11rem;">
                <select>
                    <option selected>Todos los tipos</option>
                    <option>Set quirurgico</option>
                    <option>Caja de curacion</option>
                    <option>Contenedor</option>
                </select>
            </label>
            <span class="report-period">Período: <strong><?php echo e($periodo); ?></strong></span>
        </div>

        <div class="kpi-row">
            <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="kpi <?php echo e($kpi['tone']); ?>">
                    <div class="l"><?php echo e($kpi['label']); ?></div>
                    <div class="n"><?php echo e($kpi['value']); ?></div>
                    <div class="h"><?php echo e($kpi['hint']); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="grid-2">
            <section class="panel list">
                <div class="panel-head">
                    <h2>Por servicio</h2>
                    <span class="badge">Volumen</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Recepciones</th>
                                <th>Entregas</th>
                                <th>Incidencias</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $por_servicio; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><strong><?php echo e($row['servicio']); ?></strong></td>
                                    <td><?php echo e($row['recepciones']); ?></td>
                                    <td><?php echo e($row['entregas']); ?></td>
                                    <td>
                                        <?php if($row['incidencias'] > 0): ?>
                                            <span class="pill pill-warn"><?php echo e($row['incidencias']); ?></span>
                                        <?php else: ?>
                                            <span class="pill pill-ok">0</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel list">
                <div class="panel-head">
                    <h2>Tiempo promedio por etapa</h2>
                    <span class="badge">Horas</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Etapa</th>
                                <th>Promedio</th>
                                <th>Máximo</th>
                                <th>Carga visual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $por_fase; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $pct = min(100, (int) round(($row['promedio_h'] / 8) * 100)); ?>
                                <tr>
                                    <td><strong><?php echo e($row['fase']); ?></strong></td>
                                    <td><?php echo e(number_format($row['promedio_h'], 1)); ?> h</td>
                                    <td><?php echo e(number_format($row['max_h'], 1)); ?> h</td>
                                    <td>
                                        <div class="bar-track" aria-hidden="true">
                                            <div class="bar-fill" style="width: <?php echo e($pct); ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="grid-2" style="margin-top:1rem;">
            <section class="panel list">
                <div class="panel-head">
                    <h2>Sets más usados</h2>
                    <span class="badge">Ciclos del período</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Ciclos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $top_sets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><strong><?php echo e($s['codigo']); ?></strong></td>
                                    <td><?php echo e($s['nombre']); ?></td>
                                    <td><?php echo e($s['ciclos']); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Qué responde este reporte</h2></div>
                <ul class="why-list">
                    <li><strong>Volumen</strong> — cuánto entró / salió en el período.</li>
                    <li><strong>Cuellos de botella</strong> — etapas con mayor tiempo (ej. esterilización / almacén).</li>
                    <li><strong>Servicios</strong> — quién genera más carga e incidencias.</li>
                    <li><strong>Catálogo</strong> — qué sets circulan más (apoya inventario y stock mínimo).</li>
                </ul>
                <div class="panel-note">Mockup: filtros y exportar no calculan datos reales. Enfermera vería un “reporte de turno” más corto; admin ve el período completo.</div>
            </section>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\sigte-app\resources\views/mockups/reportes.blade.php ENDPATH**/ ?>