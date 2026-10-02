<?php
    $salaDe = function (int $i): string {
        if ($i <= 1) {
            return 'lavado';
        }
        if ($i <= 3) {
            return 'armado';
        }

        return 'esteril';
    };
    $salaLabel = [
        'lavado' => 'Sala lavado',
        'armado' => 'Sala armado',
        'esteril' => 'Material estéril',
    ];
    $sala = $salaDe($caja['fase_idx']);
    $etapaActual = $fases[$caja['fase_idx']];
?>
<article class="track-card <?php echo e($caja['urgente'] ? 'is-urgent' : ''); ?>">
    <header class="track-head">
        <div>
            <div class="track-title">
                <h2><?php echo e($caja['id']); ?></h2>
                <?php if($caja['urgente']): ?>
                    <span class="badge badge-urgent">Requiere atención</span>
                <?php endif; ?>
            </div>
            <p><?php echo e($caja['servicio']); ?> · <?php echo e($caja['ubicacion']); ?> · <?php echo e($caja['tiempo']); ?> · <?php echo e($caja['operadora']); ?></p>
        </div>
        <div class="track-head-badges">
            <span class="badge badge-sala badge-<?php echo e($sala); ?>"><?php echo e($salaLabel[$sala]); ?></span>
            <span class="badge"><?php echo e($etapaActual); ?></span>
        </div>
    </header>

    <div class="track-pipe" aria-label="Etapa actual: <?php echo e($etapaActual); ?>">
            <?php $__currentLoopData = $fases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $fase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $done = $i < $caja['fase_idx'];
                    $current = $i === $caja['fase_idx'];
                ?>
                <div class="track-step <?php echo e($done ? 'done' : ''); ?> <?php echo e($current ? 'current' : ''); ?>">
                    <div class="track-node"></div>
                    <div class="track-label"><?php echo e($fase); ?></div>
                </div>
                <?php if(! $loop->last): ?>
                    <div class="track-line <?php echo e($i < $caja['fase_idx'] ? 'done' : ''); ?>"></div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <details class="track-more">
        <summary>
            <span class="track-summary-label">Más información</span>
            <a class="btn btn-dark" href="<?php echo e(route('mockups.avanzar', ['caja' => $caja['id']])); ?>" onclick="event.stopPropagation()">Aceptar avance</a>
        </summary>

            <dl class="track-facts">
                <div>
                    <dt>Etapa actual</dt>
                    <dd><?php echo e($etapaActual); ?></dd>
                </div>
                <div>
                    <dt>Ubicación</dt>
                    <dd><?php echo e($caja['ubicacion']); ?></dd>
                </div>
                <div>
                    <dt>Tiempo en la etapa</dt>
                    <dd><?php echo e($caja['tiempo']); ?> <small>desde <?php echo e($caja['hora']); ?></small></dd>
                </div>
                <div>
                    <dt>Responsable</dt>
                    <dd><?php echo e($caja['operadora']); ?></dd>
                </div>
            </dl>

            <footer class="track-foot">
                <span><?php echo e($caja['estado']); ?></span>
                <div class="track-actions">
                    <button class="btn btn-ghost" type="button" disabled title="Detalle después">Modificar</button>
                </div>
            </footer>
    </details>
</article>
<?php /**PATH /var/www/html/resources/views/mockups/partials/tarjeta-caja.blade.php ENDPATH**/ ?>