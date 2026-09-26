

<?php $__env->startSection('title', 'SIGTE — Registrar entrega'); ?>

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
                <p class="eyebrow">Almacén estéril · cierre del ciclo</p>
                <h1>Registrar entrega</h1>
                <p class="main-sub">Entrega el material listo al servicio con custodia: quién entrega (Central) y quién retira. Cierra el ciclo en etapa <strong>Entrega</strong>.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Volver al flujo</a>
            </div>
        </div>

        <div class="recv-layout">
            <form class="recv-form panel" action="<?php echo e(route('mockups.panel', 'operador')); ?>" method="get">
                <div class="panel-head">
                    <h2>Custodia de salida</h2>
                    <span class="badge">Paso 6 de 6</span>
                </div>

                <label class="field-label" for="caja">Caja / set lista en almacén</label>
                <label class="field field-select">
                    <select id="caja" name="caja" onchange="window.location='<?php echo e(url('/operador/entrega')); ?>?caja='+this.value">
                        <?php $__empty_1 = true; $__currentLoopData = $listas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <option value="<?php echo e($c['id']); ?>" <?php echo e(($caja && $c['id'] === $caja['id']) ? 'selected' : ''); ?>>
                                <?php echo e($c['id']); ?> · <?php echo e($c['servicio']); ?><?php echo e($c['urgente'] ? ' · urgente' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <option value="">No hay cajas listas</option>
                        <?php endif; ?>
                    </select>
                </label>

                <?php if($caja): ?>
                <div class="adv-summary">
                    <div>
                        <span class="adv-k">Desde</span>
                        <strong>Almacén</strong>
                        <small><?php echo e($caja['estado']); ?></small>
                    </div>
                    <div class="adv-arrow" aria-hidden="true">→</div>
                    <div>
                        <span class="adv-k">Hacia</span>
                        <strong>Entrega</strong>
                        <small><?php echo e($caja['servicio']); ?> · <?php echo e($caja['id']); ?></small>
                    </div>
                </div>
                <?php endif; ?>

                <label class="field-label" for="servicio">Servicio destino</label>
                <label class="field field-select">
                    <select id="servicio" name="servicio">
                        <?php $__currentLoopData = $servicios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option <?php echo e(($caja && $s === $caja['servicio']) ? 'selected' : ''); ?>><?php echo e($s); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="field-label" for="entrega_central">Quién entrega (Central)</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 19c1.5-3 4-4.5 7-4.5S17.5 16 19 19"/></svg>
                    </span>
                    <input id="entrega_central" name="entrega_central" type="text" value="<?php echo e($usuario['nombre']); ?>" readonly>
                </label>

                <label class="field-label" for="retira">Quién retira (servicio)</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 19c1.5-3 4-4.5 7-4.5S17.5 16 19 19"/></svg>
                    </span>
                    <input id="retira" name="retira" type="text" placeholder="Nombre de quien retira el material" value="Enf. Daniela Soto">
                </label>

                <label class="field-label" for="hora">Hora de entrega</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">⏱</span>
                    <input id="hora" name="hora" type="text" value="14:35">
                </label>

                <label class="field-label" for="obs">Observación (opcional)</label>
                <textarea id="obs" class="recv-textarea" name="obs" rows="2" placeholder="Ej: set completo, empaque íntegro, indicador OK…"></textarea>

                <div class="recv-checks">
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Empaque íntegro / indicador de esterilidad OK</span>
                    </label>
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Custodia firmada (quien entrega y quien retira)</span>
                    </label>
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Material sale de almacén estéril</span>
                    </label>
                </div>

                <div class="recv-actions">
                    <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Cancelar</a>
                    <button class="btn btn-primary" type="submit" style="width:auto; min-width:12rem;">Confirmar entrega</button>
                </div>
            </form>

            <aside class="recv-side">
                <section class="panel">
                    <div class="panel-head"><h2>Así queda en el flujo</h2></div>
                    <div class="mini-track">
                        <div class="mini-step"><span>1</span>Recepción</div>
                        <div class="mini-step"><span>2</span>Lavado</div>
                        <div class="mini-step"><span>3</span>Preparación</div>
                        <div class="mini-step"><span>4</span>Esterilización</div>
                        <div class="mini-step"><span>5</span>Almacén</div>
                        <div class="mini-step current"><span>6</span>Entrega</div>
                    </div>
                    <p class="recv-help">Solo aparecen cajas en <strong>Almacén</strong>. Al confirmar, salen del tablero activo y quedan como entregadas con registro de custodia.</p>
                </section>

                <section class="panel">
                    <div class="panel-head"><h2>Por qué estos campos</h2></div>
                    <ul class="why-list">
                        <li><strong>Quién entrega / retira</strong> — cierra la cadena de custodia (igual que en recepción, pero a la inversa).</li>
                        <li><strong>Servicio destino</strong> — a dónde va el material listo.</li>
                        <li><strong>Indicador / empaque</strong> — no se entrega si el control visual falla (en el sistema real se bloquearía).</li>
                        <li><strong>Hora</strong> — trazabilidad de salida del almacén estéril.</li>
                    </ul>
                    <div class="panel-note">Mockup: confirmar vuelve al flujo de cajas (sin guardar en base de datos).</div>
                </section>
            </aside>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/entrega.blade.php ENDPATH**/ ?>