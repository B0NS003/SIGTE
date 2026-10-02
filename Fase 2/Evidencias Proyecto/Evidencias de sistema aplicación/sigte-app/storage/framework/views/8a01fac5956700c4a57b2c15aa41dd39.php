<?php $__env->startSection('title', 'SIGTE — Operadora'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $totalTodas = array_sum($conteo_etapas ?? []);
    $urlConsulta = function (?int $etapa = null, ?string $q = null) {
        $params = [];
        $texto = $q ?? ($busqueda ?? '');
        if ($texto !== '') {
            $params['q'] = $texto;
        }
        if ($etapa !== null) {
            $params['etapa'] = $etapa;
        }

        return route('mockups.panel', array_merge(['rol' => 'operador'], $params));
    };
?>
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
                <p class="eyebrow">Operación · consulta de cajas</p>
                <h1>Flujo de cajas</h1>
            </div>
            <div class="main-actions">
                <a class="btn btn-dark" href="<?php echo e(route('mockups.recepcion')); ?>">+ Nueva recepción</a>
            </div>
        </div>

        <form class="ops-consulta" method="get" action="<?php echo e(route('mockups.panel', 'operador')); ?>" role="search">
            <div class="ops-search">
                <label class="sr-only" for="busqueda-caja">Buscar caja quirúrgica</label>
                <input
                    id="busqueda-caja"
                    type="search"
                    name="q"
                    value="<?php echo e($busqueda); ?>"
                    placeholder="Buscar por código, servicio, ubicación o responsable…"
                    autocomplete="off"
                >
                <?php if($etapa_filtro !== null): ?>
                    <input type="hidden" name="etapa" value="<?php echo e($etapa_filtro); ?>">
                <?php endif; ?>
                <button class="btn btn-dark" type="submit">Buscar</button>
                <?php if($hay_filtros): ?>
                    <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="ops-filters" aria-label="Filtrar por etapa">
            <a
                class="ops-filter <?php echo e($etapa_filtro === null ? 'is-active' : ''); ?>"
                href="<?php echo e($urlConsulta(null, $busqueda)); ?>"
            >
                Todas <em><?php echo e($totalTodas); ?></em>
            </a>
            <?php $__currentLoopData = $fases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $fase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    class="ops-filter <?php echo e($etapa_filtro === $i ? 'is-active' : ''); ?>"
                    href="<?php echo e($urlConsulta($i, $busqueda)); ?>"
                >
                    <?php echo e($fase); ?> <em><?php echo e($conteo_etapas[$i] ?? 0); ?></em>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <p class="ops-result-meta">
            <?php if($hay_filtros): ?>
                <?php echo e($total_consulta); ?> <?php echo e($total_consulta === 1 ? 'caja encontrada' : 'cajas encontradas'); ?>

                <?php if($busqueda !== ''): ?>
                    para “<?php echo e($busqueda); ?>”
                <?php endif; ?>
                <?php if($etapa_filtro !== null): ?>
                    en <?php echo e($fases[$etapa_filtro]); ?>

                <?php endif; ?>
            
            <?php endif; ?>
        </p>

        <div class="ops-board">
            <?php if($total_consulta === 0): ?>
                <div class="ops-empty" role="status">
                    <?php if($hay_filtros): ?>
                        <strong>No se encontraron cajas coincidentes</strong>
                        <p>
                            Prueba con otro código o quita el filtro de etapa.
                            <?php if($busqueda !== '' || $etapa_filtro !== null): ?>
                                <a href="<?php echo e(route('mockups.panel', 'operador')); ?>">Ver todas las cajas</a>
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <strong>No hay cajas en el flujo</strong>
                        <p>Cuando se registren recepciones, aparecerán aquí por etapa.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php $__currentLoopData = $cajas_por_etapa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <details class="ops-etapa" open>
                        <summary class="ops-etapa-head">
                            <h2><?php echo e($grupo['nombre']); ?></h2>
                            <span><?php echo e(count($grupo['cajas'])); ?> <?php echo e(count($grupo['cajas']) === 1 ? 'caja' : 'cajas'); ?></span>
                        </summary>
                        <div class="ops-etapa-list">
                            <?php $__currentLoopData = $grupo['cajas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $caja): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo $__env->make('mockups.partials.tarjeta-caja', ['caja' => $caja, 'fases' => $fases], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/panel-operador.blade.php ENDPATH**/ ?>