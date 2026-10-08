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
    $claves = \App\Models\Caja::etapas();
    $destino = $claves[$caja['fase_idx'] + 1] ?? '';
    $destinoNombre = $fases[$caja['fase_idx'] + 1] ?? '';
    $volverNombre = match ($claves[$caja['fase_idx']] ?? '') {
        'lavado' => 'Recepción',
        'preparacion' => 'Lavado',
        default => '',
    };
?>
<article
    class="track-card <?php echo e($caja['urgente'] ? 'is-urgent' : ''); ?> <?php echo e(! empty($caja['proceso_listo']) ? 'is-proceso-listo' : ''); ?>"
    data-caja="<?php echo e($caja['id']); ?>"
    data-servicio="<?php echo e($caja['servicio']); ?>"
    data-ubicacion="<?php echo e($caja['ubicacion']); ?>"
    data-tiempo="<?php echo e($caja['tiempo']); ?>"
    data-fase="<?php echo e($etapaActual); ?>"
    data-etapa="<?php echo e($claves[$caja['fase_idx']]); ?>"
    data-destino="<?php echo e($destino); ?>"
    data-destino-nombre="<?php echo e($destinoNombre); ?>"
    data-fecha="<?php echo e($caja['fecha']); ?>"
    data-hora="<?php echo e($caja['hora']); ?>"
    data-operadora="<?php echo e($caja['operadora']); ?>"
    data-minutos="<?php echo e($caja['minutos']); ?>"
>
    <header class="track-head">
        <div>
            <div class="track-title">
                <h2><?php echo e($caja['id']); ?></h2>
                <?php if($caja['urgente']): ?>
                    <span class="badge badge-urgent">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 4.5 20.5 19.5h-17L12 4.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                            <path d="M12 10v4.2" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            <path d="M12 17.2h.01" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"/>
                        </svg>
                        Requiere atención
                    </span>
                <?php endif; ?>
                <span class="badge badge-listo" data-proceso-listo <?php if (! (! empty($caja['proceso_listo']))): ?> hidden <?php endif; ?>>
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Listo
                </span>
            </div>
            <p><?php echo e($caja['servicio']); ?> · <?php echo e($caja['ubicacion']); ?> · <?php echo e($caja['operadora']); ?></p>
        </div>
        <div class="track-head-badges">
            <span class="badge badge-sala badge-<?php echo e($sala); ?>"><?php echo e($salaLabel[$sala]); ?></span>
            <span class="badge"><?php echo e($etapaActual); ?></span>
        </div>
    </header>

    <div class="track-pipe track-pipe-mini" aria-label="Etapa actual: <?php echo e($etapaActual); ?>">
        <?php $__currentLoopData = $fases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $fase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="track-step <?php echo e($i < $caja['fase_idx'] ? 'done' : ''); ?> <?php echo e($i === $caja['fase_idx'] ? 'current' : ''); ?>">
                <div class="track-node"></div>
                <div class="track-label"><?php echo e($fase); ?></div>
            </div>
            <?php if(! $loop->last): ?>
                <div class="track-line <?php echo e($i < $caja['fase_idx'] ? 'done' : ''); ?>"></div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div
    class="proceso<?php echo e(! empty($caja['proceso_listo']) ? ' is-listo' : ''); ?>"
        data-desde="<?php echo e($caja['proceso_desde']); ?>"
        data-hasta="<?php echo e($caja['proceso_hasta']); ?>"
        <?php if (! ($caja['proceso_hasta'])): ?> hidden <?php endif; ?>
    >
        <div class="proceso-pista" aria-hidden="true"><span class="proceso-barra"></span></div>
        <p class="proceso-texto"><?php echo e(! empty($caja['proceso_listo']) ? 'Listo para pasar' : 'En curso'); ?></p>
    </div>

    <div class="track-pie">
    <details class="track-more">
        <summary>
            <span class="track-summary-label">Más información</span>
        </summary>

            <dl class="track-facts">
                <div>
                    <dt>Etapa actual</dt>
                    <dd><?php echo e($etapaActual); ?></dd>
                </div>
                <div>
                    <dt>Actividad</dt>
                    <dd data-actividad><?php echo e($caja['actividad'] ?: 'Sin anotar'); ?></dd>
                </div>
                <div>
                    <dt>Ubicación</dt>
                    <dd><?php echo e($caja['ubicacion']); ?></dd>
                </div>
                <div>
                    <dt>Desde</dt>
                    <dd><?php echo e($caja['hora']); ?></dd>
                </div>
                <div>
                    <dt>Responsable</dt>
                    <dd><?php echo e($caja['operadora']); ?></dd>
                </div>
            </dl>
    </details>
    <?php if(($usuario['rol'] ?? '') === 'Operadora'): ?>
        <div class="track-card-actions">
            <?php if($volverNombre !== ''): ?>
                <button class="btn btn-ghost btn-corregir" type="button" data-abrir="retroceso" data-caja="<?php echo e($caja['id']); ?>" data-volver="<?php echo e($volverNombre); ?>">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14.5 6.5 9 12l5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Corregir etapa
                </button>
            <?php endif; ?>
            <?php if($destinoNombre !== ''): ?>
                <button class="btn btn-dark" type="button" data-abrir="etapa" data-caja="<?php echo e($caja['id']); ?>">
                    Pasar de etapa
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9.5 6.5 15 12l-5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            <?php else: ?>
                <a class="btn btn-dark" href="<?php echo e(route('mockups.entrega', ['caja' => $caja['id']])); ?>">Registrar entrega</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </div>
</article>
<?php echo $__env->make('mockups.partials.track-anim', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /var/www/html/resources/views/mockups/partials/tarjeta-caja.blade.php ENDPATH**/ ?>