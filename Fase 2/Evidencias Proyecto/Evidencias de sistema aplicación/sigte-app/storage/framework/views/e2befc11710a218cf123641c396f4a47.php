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
            <h2><?php echo e($caja['id']); ?></h2>
            <p><?php echo e($caja['servicio']); ?> · <?php echo e($caja['ubicacion']); ?> · <?php echo e($caja['operadora']); ?></p>
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

    <footer class="track-foot">
        <span><?php echo e($caja['estado']); ?> · <?php echo e($caja['tiempo']); ?> en etapa · desde <?php echo e($caja['hora']); ?></span>
        <div class="track-actions">
            <button class="btn btn-ghost" type="button" disabled title="Detalle después">Modificar</button>
            <a class="btn btn-dark" href="<?php echo e(route('mockups.avanzar', ['caja' => $caja['id']])); ?>">Aceptar avance</a>
        </div>
    </footer>
</article>
<?php /**PATH /var/www/html/resources/views/mockups/partials/tarjeta-caja.blade.php ENDPATH**/ ?>