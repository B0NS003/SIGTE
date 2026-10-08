<?php $__env->startSection('title', 'SIGTE — Reportes de secretaría'); ?>

<?php $__env->startSection('content'); ?>
<div class="shell">
    <?php echo $__env->make('mockups.partials.sidebar-secretaria', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Consolidación estadística</p>
                <h1>Reporte mensual</h1>
                <p class="main-sub">Producción y litros por servicio clínico, tomados de los consumos cargados. No incluye el flujo de cajas.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'secretaria')); ?>">Volver al inicio</a>
                <a class="btn btn-dark" href="<?php echo e(route('secretaria.reportes.exportar', ['periodo' => $periodo])); ?>">Exportar CSV</a>
            </div>
        </div>

        <form class="query-toolbar panel" method="get" action="<?php echo e(route('secretaria.reportes')); ?>">
            <label class="field" style="margin:0; min-width:14rem;">
                <span class="icon" aria-hidden="true">📅</span>
                <input type="month" name="periodo" value="<?php echo e($periodo); ?>" required aria-label="Período">
            </label>
            <button class="btn btn-primary" type="submit" style="width:auto;">Ver mes</button>
            <span class="report-period">Período: <strong><?php echo e($periodo_etiqueta); ?></strong></span>
        </form>

        <div class="kpi-row">
            <div class="kpi">
                <div class="l">Servicios</div>
                <div class="n"><?php echo e($filas->count()); ?></div>
                <div class="h">Con datos en el mes</div>
            </div>
            <div class="kpi">
                <div class="l">Consumo</div>
                <div class="n"><?php echo e($total_consumo); ?></div>
                <div class="h">Unidades</div>
            </div>
            <div class="kpi ok">
                <div class="l">Litros</div>
                <div class="n"><?php echo e(number_format((float) $total_litros, 1, ',', '.')); ?></div>
                <div class="h">Total del período</div>
            </div>
        </div>

        <section class="panel list">
            <div class="panel-head">
                <h2>Por servicio clínico</h2>
                <span class="badge"><?php echo e($periodo_etiqueta); ?></span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Consumo</th>
                            <th>Litros</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $filas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fila): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><strong><?php echo e($fila->servicio); ?></strong></td>
                                <td><?php echo e($fila->consumo); ?></td>
                                <td><?php echo e(number_format((float) $fila->litros, 2, ',', '.')); ?></td>
                                <td><?php echo e($fila->observacion ?: '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4">No hay consumos cargados para <?php echo e($periodo_etiqueta); ?>.</td>
                            </tr>
                        <?php endif; ?>
                        <?php if($filas->isNotEmpty()): ?>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong><?php echo e($total_consumo); ?></strong></td>
                                <td><strong><?php echo e(number_format((float) $total_litros, 2, ',', '.')); ?></strong></td>
                                <td></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/secretaria/reportes.blade.php ENDPATH**/ ?>