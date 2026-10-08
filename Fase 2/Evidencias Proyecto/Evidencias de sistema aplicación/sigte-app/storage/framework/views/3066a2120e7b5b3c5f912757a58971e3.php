<?php $__env->startSection('title', 'SIGTE — Producción y litros'); ?>

<?php $__env->startSection('content'); ?>
<div class="shell">
    <?php echo $__env->make('mockups.partials.sidebar-secretaria', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Datos de consumo</p>
                <h1>Producción y litros</h1>
                <p class="main-sub">Ingresa o actualiza el consumo de un servicio clínico para un mes. Si el servicio ya tiene datos en ese período, se reemplazan.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'secretaria')); ?>">Volver al inicio</a>
            </div>
        </div>

        <?php if(session('ok')): ?>
            <p class="panel-note" role="status"><?php echo e(session('ok')); ?></p>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <p class="auth-error" role="alert"><?php echo e($errors->first()); ?></p>
        <?php endif; ?>

        <div class="recv-layout">
            <form class="recv-form panel" method="post" action="<?php echo e(route('secretaria.produccion.guardar')); ?>">
                <?php echo csrf_field(); ?>
                <div class="panel-head">
                    <h2><?php echo e($editando ? 'Actualizar consumo' : 'Nuevo consumo'); ?></h2>
                    <span class="badge">Por servicio y mes</span>
                </div>

                <label class="field-label" for="servicio">Servicio clínico</label>
                <label class="field field-select">
                    <select id="servicio" name="servicio" required>
                        <?php $__currentLoopData = $servicios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $servicio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($servicio); ?>" <?php if(old('servicio', $editando->servicio ?? '') === $servicio): echo 'selected'; endif; ?>><?php echo e($servicio); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="field-label" for="periodo">Período</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">📅</span>
                    <input id="periodo" name="periodo" type="month" required value="<?php echo e(old('periodo', $periodo_form)); ?>">
                </label>

                <label class="field-label" for="consumo">Consumo (unidades del servicio)</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">#</span>
                    <input id="consumo" name="consumo" type="number" min="0" step="1" required value="<?php echo e(old('consumo', $editando->consumo ?? '')); ?>" placeholder="Ej: 24">
                </label>

                <label class="field-label" for="litros">Litros del período</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">L</span>
                    <input id="litros" name="litros" type="number" min="0" step="0.01" required value="<?php echo e(old('litros', $editando->litros ?? '')); ?>" placeholder="Ej: 180">
                </label>

                <label class="field-label" for="observacion">Observación (opcional)</label>
                <textarea id="observacion" class="recv-textarea" name="observacion" rows="3" maxlength="255" placeholder="Notas del cierre mensual"><?php echo e(old('observacion', $editando->observacion ?? '')); ?></textarea>

                <div class="recv-actions">
                    <a class="btn btn-ghost" href="<?php echo e(route('secretaria.produccion')); ?>">Limpiar</a>
                    <button class="btn btn-primary" type="submit" style="width:auto; min-width:12rem;">Guardar consumo</button>
                </div>
            </form>

            <aside class="recv-side">
                <section class="panel">
                    <div class="panel-head"><h2>Cómo se usa</h2></div>
                    <ul class="why-list">
                        <li><strong>Un registro por mes</strong> — Pabellón en octubre es una fila. Volver a guardarlo lo actualiza.</li>
                        <li><strong>Litros</strong> — quedan junto al consumo para el reporte mensual.</li>
                        <li><strong>Servicios</strong> — Pabellón, Dental, Maternidad, Urgencia, UCI y Curaciones.</li>
                    </ul>
                </section>
            </aside>
        </div>

        <section class="panel list" style="margin-top:1rem;">
            <div class="panel-head">
                <h2>Consumos registrados</h2>
                <span class="badge"><?php echo e($consumos->count()); ?></span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Período</th>
                            <th>Consumo</th>
                            <th>Litros</th>
                            <th>Observación</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $consumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fila): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><strong><?php echo e($fila->servicio); ?></strong></td>
                                <td><?php echo e(\App\Models\ConsumoServicio::etiquetaPeriodo($fila->periodo)); ?></td>
                                <td><?php echo e($fila->consumo); ?></td>
                                <td><?php echo e(number_format((float) $fila->litros, 2, ',', '.')); ?></td>
                                <td><?php echo e($fila->observacion ?: '—'); ?></td>
                                <td>
                                    <a href="<?php echo e(route('secretaria.produccion', ['servicio' => $fila->servicio, 'periodo' => $fila->periodo->format('Y-m')])); ?>">Actualizar</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6">Todavía no hay consumos cargados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/secretaria/produccion.blade.php ENDPATH**/ ?>