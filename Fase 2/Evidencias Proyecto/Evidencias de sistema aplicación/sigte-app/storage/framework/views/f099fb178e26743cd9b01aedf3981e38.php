<?php $__env->startSection('title', 'SIGTE — Entrega'); ?>

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
                <p class="eyebrow">Almacén estéril · salida</p>
                <h1>Entrega</h1>
                <p class="main-sub">Marca las cajas que salen y anota quién las retira en el servicio. Tú quedas como quien entrega.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'operador')); ?>">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14.5 6.5 9 12l5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Volver al flujo
                </a>
            </div>
        </div>

        <form class="recv-form panel entrega-panel" action="<?php echo e(route('mockups.entrega')); ?>" method="get" onsubmit="return false">
            <div class="entrega-cuerpo">
                <section>
                    <h2 class="entrega-titulo">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 13.2V18a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 20 18v-4.8" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                            <path d="M4 13.2 6.3 7.2A1.6 1.6 0 0 1 7.8 6.2h8.4a1.6 1.6 0 0 1 1.5 1l2.3 6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                            <path d="M4 13.2h4.1a2 2 0 0 0 1.9 1.3h4a2 2 0 0 0 1.9-1.3H20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                        </svg>
                        Qué sale
                    </h2>
                    <p class="field-label">Cajas en almacén</p>
                    <?php if($listas === []): ?>
                        <p class="recv-help">No hay cajas en almacén.</p>
                    <?php else: ?>
                        <div class="entrega-opciones">
                            <?php $__currentLoopData = collect($listas)->sortBy('servicio'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="check">
                                    <input type="checkbox" name="cajas[]" value="<?php echo e($c['id']); ?>" <?php if($caja && $c['id'] === $caja['id']): echo 'checked'; endif; ?>>
                                    <span><?php echo e($c['servicio']); ?> · <?php echo e($c['id']); ?><?php if($c['urgente']): ?> · requiere atención <?php endif; ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <label class="field-label" for="material">Material que no es caja</label>
                    <label class="field field-select">
                        <select id="material" name="material" onchange="document.getElementById('otro-wrap').hidden = this.value !== 'otro'">
                            <option value="">Sin material extra</option>
                            <?php $__currentLoopData = $materiales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($material); ?>"><?php echo e($material); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <option value="otro">Otro</option>
                        </select>
                    </label>
                    <div id="otro-wrap" hidden>
                        <label class="field-label" for="otro">Cuál</label>
                        <label class="field">
                            <input id="otro" name="otro" type="text" placeholder="Nombre del material" value="">
                        </label>
                    </div>
                </section>

                <section>
                    <h2 class="entrega-titulo">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.2" fill="none" stroke="currentColor" stroke-width="1.75"/>
                            <path d="M5.5 18.5c1.4-2.8 3.7-4.2 6.5-4.2s5.1 1.4 6.5 4.2" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                        </svg>
                        Quién y cuándo
                    </h2>
                    <div class="entrega-datos">
                        <div class="entrega-dato entrega-dato-ancho">
                            <span>Quién entrega</span>
                            <strong><?php echo e($usuario['nombre']); ?></strong>
                        </div>
                        <div class="entrega-dato">
                            <span>Fecha</span>
                            <strong><?php echo e($fecha); ?></strong>
                        </div>
                        <div class="entrega-dato">
                            <span>Hora</span>
                            <strong><?php echo e($hora); ?></strong>
                        </div>
                    </div>

                    <label class="field-label" for="servicio">Servicio que lo recibe</label>
                    <label class="field field-select">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4.5 19.5v-9.2L12 4.8l7.5 5.5v9.2" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                            <path d="M9.5 19.5v-5h5v5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                        </svg>
                        <select id="servicio" name="servicio">
                            <option value="">Elige el servicio</option>
                            <?php $__currentLoopData = $servicios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($s); ?>" <?php if($caja && $s === $caja['servicio']): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>

                    <label class="field-label" for="retira">Quién retira en el servicio</label>
                    <label class="field">
                        <span class="icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 19c1.5-3 4-4.5 7-4.5S17.5 16 19 19"/></svg>
                        </span>
                        <input id="retira" name="retira" type="text" placeholder="Nombre de quien retira" value="">
                    </label>
                </section>
            </div>

            <details class="entrega-obs">
                <summary>
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6.5 5.5h11A1.5 1.5 0 0 1 19 7v12.2l-3.2-2H6.5A1.5 1.5 0 0 1 5 15.7V7A1.5 1.5 0 0 1 6.5 5.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                        <path d="M9 10h6M9 13.2h4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                    Agregar observación
                </summary>
                <textarea id="obs" class="recv-textarea" name="obs" rows="2" placeholder="Empaque, indicador, faltante…"></textarea>
            </details>

            <div class="recv-actions">
                <button class="btn btn-primary" type="submit" style="width:auto; min-width:12rem;">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Registrar entrega
                </button>
            </div>
        </form>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/entrega.blade.php ENDPATH**/ ?>