<?php $__env->startSection('title', 'SIGTE — Insumos'); ?>

<?php $__env->startSection('content'); ?>
<div class="shell">
    <?php echo $__env->make('mockups.partials.sidebar-secretaria', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Control de stock</p>
                <h1>Insumos</h1>
                <p class="main-sub">Consulta el stock actual y registra entradas o salidas. Cada movimiento queda guardado y ajusta la cantidad disponible.</p>
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

        <div class="kpi-row">
            <div class="kpi">
                <div class="l">Ítems</div>
                <div class="n"><?php echo e($insumos->count()); ?></div>
                <div class="h">En inventario</div>
            </div>
            <div class="kpi ok">
                <div class="l">Unidades</div>
                <div class="n"><?php echo e($insumos->sum('stock')); ?></div>
                <div class="h">Stock visible</div>
            </div>
            <div class="kpi <?php echo e($bajo_minimo > 0 ? 'warn' : ''); ?>">
                <div class="l">Bajo mínimo</div>
                <div class="n"><?php echo e($bajo_minimo); ?></div>
                <div class="h">Requieren reposición</div>
            </div>
        </div>

        <div class="recv-layout">
            <section class="panel list">
                <div class="panel-head">
                    <h2>Stock</h2>
                    <span class="badge">Consulta</span>
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
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><strong><?php echo e($item->codigo); ?></strong></td>
                                    <td><?php echo e($item->nombre); ?></td>
                                    <td><?php echo e($item->ubicacion ?: '—'); ?></td>
                                    <td><?php echo e($item->stock); ?></td>
                                    <td><?php echo e($item->minimo); ?></td>
                                    <td>
                                        <?php if($item->estadoStock() === 'ok'): ?>
                                            <span class="pill pill-ok">OK</span>
                                        <?php elseif($item->estadoStock() === 'bajo'): ?>
                                            <span class="pill pill-warn">Bajo mínimo</span>
                                        <?php else: ?>
                                            <span class="pill pill-danger">Crítico</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6">No hay insumos cargados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <form class="recv-form panel" method="post" action="<?php echo e(route('secretaria.insumos.movimiento')); ?>">
                <?php echo csrf_field(); ?>
                <div class="panel-head">
                    <h2>Registrar movimiento</h2>
                    <span class="badge">Entrada o salida</span>
                </div>

                <label class="field-label" for="insumo_id">Insumo</label>
                <label class="field field-select">
                    <select id="insumo_id" name="insumo_id" required>
                        <?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item->id); ?>" <?php if((string) old('insumo_id') === (string) $item->id): echo 'selected'; endif; ?>>
                                <?php echo e($item->codigo); ?> — <?php echo e($item->nombre); ?> (<?php echo e($item->stock); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="field-label" for="tipo">Tipo</label>
                <label class="field field-select">
                    <select id="tipo" name="tipo" required>
                        <option value="entrada" <?php if(old('tipo') === 'entrada'): echo 'selected'; endif; ?>>Entrada</option>
                        <option value="salida" <?php if(old('tipo', 'salida') === 'salida'): echo 'selected'; endif; ?>>Salida</option>
                    </select>
                </label>

                <label class="field-label" for="cantidad">Cantidad</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">#</span>
                    <input id="cantidad" name="cantidad" type="number" min="1" step="1" required value="<?php echo e(old('cantidad', 1)); ?>">
                </label>

                <label class="field-label" for="observacion">Observación (opcional)</label>
                <textarea id="observacion" class="recv-textarea" name="observacion" rows="3" maxlength="255" placeholder="Ej: reposición de bodega o consumo de la semana"><?php echo e(old('observacion')); ?></textarea>

                <div class="recv-actions">
                    <button class="btn btn-primary" type="submit" style="width:auto; min-width:12rem;">Guardar movimiento</button>
                </div>
            </form>
        </div>

        <section class="panel list" style="margin-top:1rem;">
            <div class="panel-head">
                <h2>Últimos movimientos</h2>
                <span class="badge"><?php echo e($movimientos->count()); ?></span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Insumo</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Observación</th>
                            <th>Quién</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($mov->created_at?->format('d-m-Y H:i')); ?></td>
                                <td><strong><?php echo e($mov->insumo?->codigo); ?></strong> <?php echo e($mov->insumo?->nombre); ?></td>
                                <td>
                                    <?php if($mov->tipo === 'entrada'): ?>
                                        <span class="pill pill-ok">Entrada</span>
                                    <?php else: ?>
                                        <span class="pill pill-warn">Salida</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($mov->cantidad); ?></td>
                                <td><?php echo e($mov->observacion ?: '—'); ?></td>
                                <td><?php echo e($mov->autor?->name ?: '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6">Todavía no hay movimientos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/secretaria/insumos.blade.php ENDPATH**/ ?>