<?php $__env->startSection('title', 'SIGTE — Inventario'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $conteoSalas = collect($libros)->keyBy('clave');
    $totalElementos = collect($libros)->sum('tipos');
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
                <p class="eyebrow">Consulta · tres libros</p>
                <h1>Inventario</h1>
                <p class="main-sub">Cada sala tiene su libro. El conjunto muestra cómo están las tres.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.catalogo')); ?>">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6.5 5.5h11A1.5 1.5 0 0 1 19 7v12.5H5V7A1.5 1.5 0 0 1 6.5 5.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                        <path d="M9 9.5h6M9 13h6M9 16.5h4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                    Ver catálogo
                </a>
            </div>
        </div>

        <p class="ops-filter-label">
            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4.5 6.5h15l-5.6 6.6V18l-3.8 1.8v-6.7L4.5 6.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
            </svg>
            Filtrar por sala
        </p>
        <div class="ops-filters sala-tabs" aria-label="Filtrar por sala">
            <a class="ops-filter sala-tab sala-todos <?php echo e($sala === 'todos' ? 'is-active active' : ''); ?>"
               href="<?php echo e(route('mockups.inventario', ['sala' => 'todos'])); ?>">
                Los tres <em><?php echo e($totalElementos); ?></em>
            </a>
            <?php $__currentLoopData = $salas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="ops-filter sala-tab sala-<?php echo e($key); ?> <?php echo e($sala === $key ? 'is-active active' : ''); ?>"
                   href="<?php echo e(route('mockups.inventario', ['sala' => $key])); ?>">
                    <span class="sala-dot" aria-hidden="true"></span>
                    <?php echo e($label); ?> <em><?php echo e($conteoSalas[$key]['tipos'] ?? 0); ?></em>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if($sala === 'todos'): ?>
            <div class="inv-libros">
                <?php $__currentLoopData = $libros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $libro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a class="inv-libro sala-<?php echo e($libro['clave']); ?>" href="<?php echo e(route('mockups.inventario', ['sala' => $libro['clave']])); ?>">
                        <span class="badge badge-sala badge-<?php echo e($libro['clave']); ?>"><?php echo e($libro['nombre']); ?></span>
                        <strong><?php echo e($libro['stock']); ?></strong>
                        <em>unidades en sala</em>
                        <ul>
                            <li><?php echo e($libro['tipos']); ?> <?php echo e($libro['tipos'] === 1 ? 'elemento' : 'elementos'); ?></li>
                            <li><?php echo e($libro['en_proceso']); ?> en proceso</li>
                            <li class="<?php echo e($libro['bajo'] > 0 ? 'is-alert' : ''); ?>"><?php echo e($libro['bajo']); ?> bajo mínimo</li>
                        </ul>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <section class="panel inv-mira">
                <div class="panel-head">
                    <h2 class="inv-titulo">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 4.5 20.5 19.5h-17L12 4.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                            <path d="M12 10v4.2" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            <path d="M12 17.2h.01" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"/>
                        </svg>
                        Para mirar ahora
                    </h2>
                    <span class="badge badge-urgent"><?php echo e(count($atencion)); ?></span>
                </div>
                <?php if($atencion === []): ?>
                    <p class="recv-help">Ningún elemento está bajo el mínimo.</p>
                <?php else: ?>
                    <ul class="inv-atencion">
                        <?php $__currentLoopData = $atencion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <span class="badge badge-sala badge-<?php echo e($item['sala']); ?>"><?php echo e($salas[$item['sala']]); ?></span>
                                <div>
                                    <strong><?php echo e($item['nombre']); ?></strong>
                                    <span><?php echo e($item['ubicacion']); ?> · hay <?php echo e($item['stock']); ?> · mínimo <?php echo e($item['minimo']); ?></span>
                                </div>
                                <?php if($item['estado'] === 'critico'): ?>
                                    <span class="pill pill-danger">Crítico</span>
                                <?php else: ?>
                                    <span class="pill pill-warn">Bajo mínimo</span>
                                <?php endif; ?>
                                <?php if($puedeReponer): ?>
                                    <a class="panel-link" href="<?php echo e(route('mockups.inventario', ['sala' => $item['sala'], 'elemento' => $item['codigo']])); ?>">Reponer</a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php endif; ?>
            </section>
        <?php else: ?>

        <div class="inv-libro inv-sala-hero sala-<?php echo e($sala); ?>">
            <div>
                <span class="badge badge-sala badge-<?php echo e($sala); ?>"><?php echo e($salas[$sala]); ?></span>
                <strong><?php echo e($resumen['en_almacen']); ?></strong>
                <em>unidades en sala</em>
            </div>
            <ul>
                <li><?php echo e($resumen['tipos']); ?> <?php echo e($resumen['tipos'] === 1 ? 'elemento' : 'elementos'); ?></li>
                <li><?php echo e($resumen['en_proceso']); ?> en proceso</li>
                <li class="<?php echo e($resumen['bajo_minimo'] > 0 ? 'is-alert' : ''); ?>"><?php echo e($resumen['bajo_minimo']); ?> bajo mínimo</li>
            </ul>
        </div>

        <section class="panel list inv-book sala-<?php echo e($sala); ?>">
            <div class="panel-head">
                <h2 class="inv-titulo">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6.5 5.5h11A1.5 1.5 0 0 1 19 7v12.5H5V7A1.5 1.5 0 0 1 6.5 5.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                        <path d="M9 9.5h6M9 13h6M9 16.5h4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                    Libro · <?php echo e($salas[$sala]); ?>

                </h2>
                <span class="badge badge-sala badge-<?php echo e($sala); ?>"><?php echo e($salas[$sala]); ?></span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Ubicación</th>
                            <th>Stock</th>
                            <th>Mínimo</th>
                            <th>En proceso</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="stock-<?php echo e($item['estado']); ?>">
                                <td><strong><?php echo e($item['codigo']); ?></strong></td>
                                <td><?php echo e($item['nombre']); ?></td>
                                <td><?php echo e($item['ubicacion']); ?></td>
                                <td><?php echo e($item['stock']); ?></td>
                                <td><?php echo e($item['minimo']); ?></td>
                                <td><?php echo e($item['en_proceso']); ?></td>
                                <td>
                                    <?php if($item['estado'] === 'ok'): ?>
                                        <span class="pill pill-ok">
                                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            OK
                                        </span>
                                    <?php elseif($item['estado'] === 'bajo'): ?>
                                        <span class="pill pill-warn">Bajo mínimo</span>
                                    <?php else: ?>
                                        <span class="pill pill-danger">Crítico</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php if($puedeReponer): ?>
        <form class="recv-form panel entrega-panel inv-mov sala-<?php echo e($sala); ?>" action="<?php echo e(route('mockups.inventario')); ?>" method="get" onsubmit="return false">
            <div class="entrega-cuerpo">
                <section>
                    <h2 class="inv-titulo">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                        </svg>
                        Registrar reposición
                    </h2>
                    <label class="field-label" for="elemento">Elemento de <?php echo e($salas[$sala]); ?></label>
                    <label class="field field-select">
                        <select id="elemento" name="elemento">
                            <option value="">Elige el elemento</option>
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($item['codigo']); ?>" data-stock="<?php echo e($item['stock']); ?>" data-minimo="<?php echo e($item['minimo']); ?>" data-nombre="<?php echo e($item['nombre']); ?>" <?php if($elemento === $item['codigo']): echo 'selected'; endif; ?>>
                                    <?php echo e($item['nombre']); ?> · hay <?php echo e($item['stock']); ?><?php if($item['estado'] !== 'ok'): ?> · bajo mínimo <?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>

                    <label class="field-label" for="cantidad">Cantidad que entra</label>
                    <label class="field">
                        <input id="cantidad" name="cantidad" type="number" min="1" step="1" placeholder="Cuántas unidades se reponen" value="">
                    </label>
                    <p class="recv-help" id="inv-queda" hidden></p>
                </section>

                <section>
                    <h2 class="inv-titulo">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.2" fill="none" stroke="currentColor" stroke-width="1.75"/>
                            <path d="M5.5 18.5c1.4-2.8 3.7-4.2 6.5-4.2s5.1 1.4 6.5 4.2" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                        </svg>
                        Quién y cuándo
                    </h2>
                    <div class="entrega-datos">
                        <div class="entrega-dato entrega-dato-ancho">
                            <span>Quién repone</span>
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
                    <p class="auth-error act-aviso" id="inv-aviso" role="alert" hidden></p>
                    <button class="btn btn-primary" id="inv-anotar" type="button" style="width:auto; min-width:12rem;">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Registrar reposición
                    </button>
                </section>
            </div>

            <section class="act-suma">
                <h2>Reposiciones de esta sala</h2>
                <p class="recv-help" id="inv-vacio">Todavía no hay reposiciones en esta pantalla.</p>
                <ol class="act-lista" id="inv-lista"></ol>
            </section>
        </form>
        <?php endif; ?>
        <?php endif; ?>
    </main>
</div>
<?php if($sala !== 'todos' && $puedeReponer): ?>
<script>
    var elemento = document.getElementById('elemento');
    var cantidad = document.getElementById('cantidad');
    var queda = document.getElementById('inv-queda');

    function vistaReposicion() {
        var opcion = elemento.selectedOptions[0];
        var unidades = Number(cantidad.value);
        if (!elemento.value || !unidades || unidades < 1) {
            queda.hidden = true;
            return;
        }
        var stock = Number(opcion.dataset.stock);
        var minimo = Number(opcion.dataset.minimo);
        var total = stock + unidades;
        queda.hidden = false;
        queda.textContent = total >= minimo
            ? 'Quedaría en ' + total + '. La alerta de stock se apaga.'
            : 'Quedaría en ' + total + '. Sigue bajo el mínimo (' + minimo + ').';
    }

    elemento.addEventListener('change', vistaReposicion);
    cantidad.addEventListener('input', vistaReposicion);
    vistaReposicion();

    document.getElementById('inv-anotar').addEventListener('click', function () {
        var aviso = document.getElementById('inv-aviso');
        var opcion = elemento.selectedOptions[0];
        var unidades = Number(cantidad.value);
        if (!elemento.value || !Number.isInteger(unidades) || unidades < 1) {
            aviso.textContent = 'Elige el elemento e indica una cantidad mayor que cero.';
            aviso.hidden = false;
            return;
        }
        aviso.hidden = true;
        var li = document.createElement('li');
        var titulo = document.createElement('strong');
        var meta = document.createElement('span');
        titulo.textContent = 'Reposición · ' + unidades + ' · ' + opcion.dataset.nombre;
        meta.textContent = <?php echo json_encode($fecha.' · '.$hora.' · '.$usuario['nombre'], 15, 512) ?>;
        li.append(titulo, meta);
        document.getElementById('inv-lista').prepend(li);
        document.getElementById('inv-vacio').hidden = true;
        cantidad.value = '';
        queda.hidden = true;
    });
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/inventario.blade.php ENDPATH**/ ?>