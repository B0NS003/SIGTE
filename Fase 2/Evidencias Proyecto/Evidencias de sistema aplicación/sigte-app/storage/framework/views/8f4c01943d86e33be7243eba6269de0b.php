<?php $__env->startSection('title', 'SIGTE — Catálogo'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $urlCatalogo = function (?string $servicioFiltro = null) use ($busqueda) {
        $params = [];
        if ($busqueda !== '') {
            $params['q'] = $busqueda;
        }
        if ($servicioFiltro !== null && $servicioFiltro !== '') {
            $params['servicio'] = $servicioFiltro;
        }

        return route('mockups.catalogo', $params);
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
                <p class="eyebrow">Consulta · fichas maestras</p>
                <h1>Catálogo</h1>
                <p class="main-sub">Elige una caja para ver qué debe llevar. El stock está en Inventario.</p>
            </div>
        </div>

        <form class="ops-consulta" method="get" action="<?php echo e(route('mockups.catalogo')); ?>" role="search">
            <div class="ops-search">
                <label class="sr-only" for="busqueda-catalogo">Buscar en el catálogo</label>
                <div class="ops-search-field">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.25" fill="none" stroke="currentColor" stroke-width="1.75"/>
                        <path d="M16 16.5 20 20.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                    <input
                        id="busqueda-catalogo"
                        type="search"
                        name="q"
                        value="<?php echo e($busqueda); ?>"
                        placeholder="Buscar por código, nombre o servicio…"
                        autocomplete="off"
                    >
                </div>
                <?php if($servicio !== ''): ?>
                    <input type="hidden" name="servicio" value="<?php echo e($servicio); ?>">
                <?php endif; ?>
                <button class="btn btn-dark" type="submit">Buscar</button>
                <?php if($hay_filtros): ?>
                    <a class="btn btn-ghost" href="<?php echo e(route('mockups.catalogo')); ?>">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>

        <p class="ops-filter-label">
            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4.5 6.5h15l-5.6 6.6V18l-3.8 1.8v-6.7L4.5 6.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
            </svg>
            Filtrar por servicio
        </p>
        <div class="ops-filters" aria-label="Filtrar por servicio">
            <a
                class="ops-filter <?php echo e($servicio === '' ? 'is-active' : ''); ?>"
                href="<?php echo e($urlCatalogo(null)); ?>"
            >
                Todos <em><?php echo e($total_servicios); ?></em>
            </a>
            <?php $__currentLoopData = $servicios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opcion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    class="ops-filter <?php echo e($servicio === $opcion ? 'is-active' : ''); ?>"
                    href="<?php echo e($urlCatalogo($opcion)); ?>"
                >
                    <?php echo e($opcion); ?> <em><?php echo e($conteo_servicios[$opcion] ?? 0); ?></em>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if(count($items) === 0): ?>
            <div class="ops-empty" role="status">
                <strong>No hay cajas coincidentes</strong>
                <p>
                    Prueba con otro código, nombre o servicio.
                    <?php if($hay_filtros): ?>
                        <a href="<?php echo e(route('mockups.catalogo')); ?>">Ver todo el catálogo</a>
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <p class="ops-result-meta">
                <?php echo e(count($items)); ?> <?php echo e(count($items) === 1 ? 'caja' : 'cajas'); ?>

                <?php if($servicio !== ''): ?>
                    en <?php echo e($servicio); ?>

                <?php else: ?>
                    en el catálogo
                <?php endif; ?>
            </p>
            <div class="cat-grid">
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a class="cat-card" href="<?php echo e(route('mockups.ficha', $item['codigo'])); ?>">
                        <div class="cat-photo <?php echo e($item['guia'] ? '' : 'is-empty'); ?>">
                            <span><?php echo e($item['guia'] ? 'Con guía visual' : 'Sin guía visual'); ?></span>
                        </div>
                        <div class="cat-body">
                            <strong><?php echo e($item['codigo']); ?></strong>
                            <h2><?php echo e($item['nombre']); ?></h2>
                            <p>
                                <span class="cat-servicio"><?php echo e($item['servicio']); ?></span>
                                <?php echo e($item['tipo']); ?> · <?php echo e($item['piezas']); ?> <?php echo e($item['piezas'] === 1 ? 'pieza' : 'piezas'); ?>

                            </p>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/catalogo.blade.php ENDPATH**/ ?>