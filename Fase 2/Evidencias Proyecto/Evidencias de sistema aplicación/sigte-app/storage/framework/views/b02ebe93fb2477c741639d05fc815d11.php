

<?php $__env->startSection('title', 'SIGTE — Nueva recepción'); ?>

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
                <p class="eyebrow">Área sucia · inicio del ciclo</p>
                <h1>Nueva recepción</h1>
                <p class="main-sub">Registra el ingreso de material desde un servicio. En la entrevista: anotar ficha del servicio, dejar en área sucia y partir remojo/lavado. Todo queda en etapa <strong>Recepción</strong>.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Volver al flujo</a>
            </div>
        </div>

        <div class="recv-layout">
            <form class="recv-form panel" action="<?php echo e(route('mockups.panel', 'operador')); ?>" method="get">
                <div class="panel-head">
                    <h2>Datos del ingreso</h2>
                    <span class="badge">Paso 1 de 6</span>
                </div>

                <label class="field-label" for="codigo">Identificador de caja / set</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">#</span>
                    <input id="codigo" name="codigo" type="text" placeholder="Ej: SET-061 o CAJA-120" value="SET-061">
                </label>

                <label class="field-label" for="tipo">Tipo de material</label>
                <label class="field field-select">
                    <select id="tipo" name="tipo">
                        <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option><?php echo e($t); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="field-label" for="servicio">Servicio de origen</label>
                <label class="field field-select">
                    <select id="servicio" name="servicio">
                        <?php $__currentLoopData = $servicios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option><?php echo e($s); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="field-label" for="quien">Quién entrega (servicio / clínica)</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 19c1.5-3 4-4.5 7-4.5S17.5 16 19 19"/></svg>
                    </span>
                    <input id="quien" name="quien" type="text" placeholder="Nombre de quien trae el material" value="Enf. Carla Muñoz">
                </label>

                <label class="field-label" for="recibe">Quién recibe (Central)</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 19c1.5-3 4-4.5 7-4.5S17.5 16 19 19"/></svg>
                    </span>
                    <input id="recibe" name="recibe" type="text" value="<?php echo e($usuario['nombre']); ?>" readonly>
                </label>

                <label class="field-label" for="obs">Observación (opcional)</label>
                <textarea id="obs" class="recv-textarea" name="obs" rows="3" placeholder="Ej: material con restos orgánicos visibles, faltante aparente, contenedor dañado…"></textarea>

                <div class="recv-checks">
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Material dejado en área sucia</span>
                    </label>
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Ficha / registro de servicio anotado</span>
                    </label>
                    <label class="check">
                        <input type="checkbox">
                        <span>EPP utilizado (pechera, gorro, guantes)</span>
                    </label>
                </div>

                <div class="recv-actions">
                    <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'operador')); ?>">Cancelar</a>
                    <button class="btn btn-primary" type="submit" style="width:auto; min-width:12rem;">Registrar recepción</button>
                </div>
            </form>

            <aside class="recv-side">
                <section class="panel">
                    <div class="panel-head"><h2>Así queda en el flujo</h2></div>
                    <div class="mini-track">
                        <div class="mini-step current"><span>1</span>Recepción</div>
                        <div class="mini-step"><span>2</span>Lavado</div>
                        <div class="mini-step"><span>3</span>Preparación</div>
                        <div class="mini-step"><span>4</span>Esterilización</div>
                        <div class="mini-step"><span>5</span>Almacén</div>
                        <div class="mini-step"><span>6</span>Entrega</div>
                    </div>
                    <p class="recv-help">Al guardar, la caja aparece en el tablero de operadora en <strong>Recepción</strong>. Después se avanza a lavado/remojo (no se puede “devolver” hacia atrás en el ciclo limpio).</p>
                </section>

                <section class="panel">
                    <div class="panel-head"><h2>Por qué estos campos</h2></div>
                    <ul class="why-list">
                        <li><strong>Servicio de origen</strong> — en terreno anotan de dónde viene el material.</li>
                        <li><strong>Quién entrega / recibe</strong> — base de custodia desde el primer paso.</li>
                        <li><strong>Área sucia</strong> — confirma que el ingreso partió donde corresponde.</li>
                        <li><strong>Observación</strong> — sangre, óxido, faltantes: evita sorpresas en armado.</li>
                    </ul>
                    <div class="panel-note">Mockup: el botón vuelve al flujo de cajas (sin guardar en base de datos).</div>
                </section>
            </aside>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/recepcion.blade.php ENDPATH**/ ?>