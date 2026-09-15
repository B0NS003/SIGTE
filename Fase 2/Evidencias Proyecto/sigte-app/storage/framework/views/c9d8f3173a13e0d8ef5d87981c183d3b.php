

<?php $__env->startSection('title', 'SIGTE — Avanzar etapa'); ?>

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
            <a class="active" href="<?php echo e(route('mockups.avanzar')); ?>">Avanzar etapa</a>
            <a href="<?php echo e(route('mockups.entrega')); ?>">Registrar entrega</a>
        </nav>
        <div class="nav-label">Consulta</div>
        <nav class="nav">
            <a href="<?php echo e(route('mockups.catalogo')); ?>">Catálogo</a>
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
                <p class="eyebrow">Trazabilidad · cambio de etapa</p>
                <h1>Avanzar etapa</h1>
                <p class="main-sub">Mueve la caja al siguiente paso del ciclo (solo hacia adelante). Queda registro de quién y cuándo — mockup visual.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Volver al flujo</a>
            </div>
        </div>

        <div class="recv-layout">
            <form class="recv-form panel" action="<?php echo e(route('mockups.panel', 'operador')); ?>" method="get">
                <div class="panel-head">
                    <h2>Seleccionar caja</h2>
                    <span class="badge"><?php echo e($fase_actual); ?> → <?php echo e($fase_siguiente ?? 'Fin'); ?></span>
                </div>

                <label class="field-label" for="caja">Caja / set en proceso</label>
                <label class="field field-select">
                    <select id="caja" name="caja" onchange="window.location='<?php echo e(url('/operador/avanzar')); ?>?caja='+this.value">
                        <?php $__currentLoopData = $cajas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c['id']); ?>" <?php echo e($c['id'] === $caja['id'] ? 'selected' : ''); ?>>
                                <?php echo e($c['id']); ?> · <?php echo e($fases[$c['fase_idx']]); ?> · <?php echo e($c['servicio']); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <div class="adv-summary">
                    <div>
                        <span class="adv-k">Ahora</span>
                        <strong><?php echo e($fase_actual); ?></strong>
                        <small><?php echo e($caja['estado']); ?></small>
                    </div>
                    <div class="adv-arrow" aria-hidden="true">→</div>
                    <div>
                        <span class="adv-k">Siguiente</span>
                        <?php if($fase_siguiente): ?>
                            <strong><?php echo e($fase_siguiente); ?></strong>
                            <small>Solo se permite esta transición</small>
                        <?php else: ?>
                            <strong>Ya en entrega</strong>
                            <small>Usar “Registrar entrega”</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="track-pipe adv-pipe" aria-label="Progreso">
                    <?php $__currentLoopData = $fases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $fase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $done = $i < $caja['fase_idx'];
                            $current = $i === $caja['fase_idx'];
                            $next = $fase_siguiente && $i === $caja['fase_idx'] + 1;
                        ?>
                        <div class="track-step <?php echo e($done ? 'done' : ''); ?> <?php echo e($current ? 'current' : ''); ?> <?php echo e($next ? 'next' : ''); ?>">
                            <div class="track-node"></div>
                            <div class="track-label"><?php echo e($fase); ?></div>
                        </div>
                        <?php if(!$loop->last): ?>
                            <div class="track-line <?php echo e($i < $caja['fase_idx'] ? 'done' : ''); ?>"></div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <label class="field-label" for="nota">Nota del avance (opcional)</label>
                <textarea id="nota" class="recv-textarea" name="nota" rows="2" placeholder="Ej: ciclo de lavado OK, pasa a armado / control visual OK"></textarea>

                <div class="recv-checks">
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Confirmó que la etapa actual está completa</span>
                    </label>
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>No intenta retroceder el flujo (unidireccional)</span>
                    </label>
                </div>

                <div class="recv-actions">
                    <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Cancelar</a>
                    <?php if($fase_siguiente): ?>
                        <button class="btn btn-primary" type="submit" style="width:auto;min-width:12rem;">Confirmar avance a <?php echo e($fase_siguiente); ?></button>
                    <?php else: ?>
                        <a class="btn btn-primary" style="width:auto;min-width:12rem;" href="<?php echo e(route('mockups.entrega')); ?>">Ir a entrega</a>
                    <?php endif; ?>
                </div>
            </form>

            <aside class="recv-side">
                <section class="panel">
                    <div class="panel-head"><h2>Detalle</h2></div>
                    <ul class="why-list">
                        <li><strong><?php echo e($caja['id']); ?></strong> — <?php echo e($caja['servicio']); ?></li>
                        <li><strong>Responsable actual:</strong> <?php echo e($caja['operadora']); ?></li>
                        <li><strong>Última hora:</strong> <?php echo e($caja['hora']); ?></li>
                    </ul>
                </section>
                <section class="panel">
                    <div class="panel-head"><h2>Regla del mockup</h2></div>
                    <p class="recv-help">Igual que en la entrevista: el material no vuelve atrás en el área limpia. Solo se avanza de etapa en etapa. Si el control de esterilización falla, en el sistema real se bloquearía el avance (aquí solo se muestra la idea).</p>
                    <div class="panel-note">Mockup: confirmar vuelve al flujo de cajas.</div>
                </section>
            </aside>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\sigte-app\resources\views/mockups/avanzar.blade.php ENDPATH**/ ?>