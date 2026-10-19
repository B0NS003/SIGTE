<?php $__env->startSection('title', 'SIGTE — Secretaria'); ?>

<?php $__env->startSection('content'); ?>
<div class="shell">
    <?php echo $__env->make('mockups.partials.sidebar-secretaria', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Hospital San José de Melipilla</p>
                <h1>Panel de secretaría</h1>
                <p class="main-sub">Carga de consumos y litros, control de insumos y reportes mensuales por servicio. Sin operación del ciclo de esterilización ni gestión de usuarios.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-dark" href="<?php echo e(route('secretaria.produccion')); ?>">Cargar producción</a>
            </div>
        </div>

        <p class="section-title"><?php echo e($resumen['periodo']); ?></p>
        <div class="kpi-row">
            <div class="kpi">
                <div class="l">Servicios con consumo</div>
                <div class="n"><?php echo e($resumen['servicios']); ?></div>
                <div class="h">Registros del mes</div>
            </div>
            <div class="kpi ok">
                <div class="l">Litros del mes</div>
                <div class="n"><?php echo e(number_format($resumen['litros'], 0, ',', '.')); ?></div>
                <div class="h">Suma por servicio</div>
            </div>
            <div class="kpi">
                <div class="l">Consumo del mes</div>
                <div class="n"><?php echo e($resumen['consumo']); ?></div>
                <div class="h">Unidades registradas</div>
            </div>
            <div class="kpi <?php echo e($resumen['bajo_minimo'] > 0 ? 'warn' : 'ok'); ?>">
                <div class="l">Insumos a revisar</div>
                <div class="n"><?php echo e($resumen['bajo_minimo']); ?></div>
                <div class="h">Bajo mínimo o críticos</div>
            </div>
        </div>

        <p class="section-title">Trabajo de secretaría</p>
        <div class="action-row admin-tools">
            <a class="action-card" href="<?php echo e(route('secretaria.produccion')); ?>">
                <strong>Producción y litros</strong>
                <span>Ingresar o actualizar el consumo de cada servicio clínico en el mes.</span>
            </a>
            <a class="action-card" href="<?php echo e(route('secretaria.insumos')); ?>">
                <strong>Insumos</strong>
                <span>Consultar stock y registrar entradas o salidas de bodega.</span>
            </a>
            <a class="action-card" href="<?php echo e(route('secretaria.reportes')); ?>">
                <strong>Reportes</strong>
                <span>Producción mensual por servicio y exportación a CSV.</span>
            </a>
        </div>

        <section class="panel">
            <div class="panel-head"><h2>Qué corresponde a este rol</h2></div>
            <ul class="why-list">
                <li><strong>Consumos</strong> — carga y corrige litros y producción por servicio y período.</li>
                <li><strong>Insumos</strong> — consulta el inventario y deja constancia de cada movimiento.</li>
                <li><strong>Reportes</strong> — consolida la estadística mensual y la exporta.</li>
            </ul>
            <div class="panel-note">La recepción, el avance de cajas, las autoclaves y la entrega quedan en la operadora. Las cuentas de usuario quedan en la administradora.</div>
        </section>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/panel-secretaria.blade.php ENDPATH**/ ?>