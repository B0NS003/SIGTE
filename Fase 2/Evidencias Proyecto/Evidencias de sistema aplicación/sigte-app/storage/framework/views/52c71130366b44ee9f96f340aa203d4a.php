<?php $__env->startSection('title', 'SIGTE — Custodia / auditoría'); ?>

<?php $__env->startSection('content'); ?>
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="<?php echo e(asset('images/logo-hospital-circular.png')); ?>" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Administración</div>
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
                <p class="eyebrow">Gestión · trazabilidad de responsabilidad</p>
                <h1>Custodia / auditoría</h1>
                <p class="main-sub">Dos lecturas: la <strong>cadena de custodia</strong> de cada caja (quién entregó / recibió) y la <strong>bitácora</strong> de quién cambió qué en el sistema.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'administradora')); ?>">Volver al resumen</a>
                <button class="btn btn-dark" type="button">Exportar bitácora</button>
            </div>
        </div>

        <?php if($falla): ?>
            <div class="ops-empty">
                <strong>No se pudo consultar la auditoría</strong>
                <p>El registro no está disponible en este momento.</p>
                <p><a href="<?php echo e(route('mockups.custodia')); ?>">Intentar de nuevo</a></p>
            </div>
        <?php else: ?>
            <div class="query-toolbar panel">
                <label class="field" style="margin:0; flex:1;">
                    <span class="icon" aria-hidden="true">⌕</span>
                    <input id="aud-buscar" type="search" placeholder="Buscar caja, usuario o acción…" aria-label="Buscar caja, usuario o acción">
                </label>
                <label class="field field-select" style="margin:0; min-width:11rem;">
                    <select id="aud-cuando" aria-label="Período">
                        <option value="hoy" selected>Hoy</option>
                        <option value="semana">Últimos 7 días</option>
                        <option value="mes">Este mes</option>
                    </select>
                </label>
                <label class="field field-select" style="margin:0; min-width:11rem;">
                    <select id="aud-vista" aria-label="Qué ver">
                        <option value="todo" selected>Todo</option>
                        <option value="custodia">Solo custodia</option>
                        <option value="auditoria">Solo auditoría</option>
                        <option value="alertas">Solo alertas</option>
                    </select>
                </label>
            </div>

            <div class="grid-2" id="aud-paneles">
                <section class="panel" id="aud-cadenas">
                    <div class="panel-head">
                        <h2>Cadenas de custodia</h2>
                        <span class="badge">Por caja</span>
                    </div>
                    <div id="aud-cadenas-vacio" class="ops-empty" hidden>
                        <strong>Sin resultados</strong>
                        <p>Ninguna caja coincide con esa búsqueda.</p>
                    </div>
                    <div id="aud-cadenas-lista">
                        <?php $__currentLoopData = $cadenas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cadena): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article class="custody-card <?php echo e($cadena['alerta'] ? 'is-alert' : ''); ?>" data-alerta="<?php echo e($cadena['alerta'] ? '1' : '0'); ?>" data-cuando="<?php echo e($cadena['cuando']); ?>" data-texto="<?php echo e(mb_strtolower($cadena['caja'].' '.$cadena['servicio'].' '.collect($cadena['eventos'])->pluck('de')->implode(' ').' '.collect($cadena['eventos'])->pluck('nota')->implode(' '))); ?>">
                                <header class="custody-head">
                                    <div>
                                        <strong><?php echo e($cadena['caja']); ?></strong>
                                        <span><?php echo e($cadena['servicio']); ?></span>
                                    </div>
                                    <?php if($cadena['alerta']): ?>
                                        <span class="pill pill-danger"><?php echo e($cadena['estado']); ?></span>
                                    <?php else: ?>
                                        <span class="pill pill-ok"><?php echo e($cadena['estado']); ?></span>
                                    <?php endif; ?>
                                </header>
                                <ol class="custody-timeline">
                                    <?php $__currentLoopData = $cadena['eventos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="<?php echo e($ev['tipo'] === 'Alerta' ? 'is-alert' : ''); ?>">
                                            <div class="ct-time"><?php echo e($ev['hora']); ?></div>
                                            <div class="ct-body">
                                                <strong><?php echo e($ev['tipo']); ?></strong>
                                                <span><?php echo e($ev['nota']); ?></span>
                                                <small>
                                                    <?php if($ev['a'] !== '—'): ?>
                                                        <?php echo e($ev['de']); ?> → <?php echo e($ev['a']); ?>

                                                    <?php else: ?>
                                                        <?php echo e($ev['de']); ?>

                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ol>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>

                <section class="panel list" id="aud-bitacora">
                    <div class="panel-head">
                        <h2 id="aud-titulo">Bitácora de auditoría</h2>
                        <span class="badge">Quién · qué · cuándo</span>
                    </div>
                    <div id="aud-vacio" class="ops-empty" hidden>
                        <strong>Sin resultados</strong>
                        <p>Ninguna acción coincide con esa búsqueda o con esos filtros.</p>
                    </div>
                    <div class="table-wrap" id="aud-tabla">
                        <table>
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Actor</th>
                                    <th>Acción</th>
                                    <th>Objeto</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody id="aud-cuerpo"></tbody>
                        </table>
                    </div>
                </section>
            </div>
        <?php endif; ?>
    </main>
</div>
<?php if (! ($falla)): ?>
<script>
(function () {
    var registros = <?php echo json_encode($auditoria, 15, 512) ?>;
    var buscar = document.getElementById('aud-buscar');
    var cuando = document.getElementById('aud-cuando');
    var vista = document.getElementById('aud-vista');
    var titulo = document.getElementById('aud-titulo');
    var cuerpo = document.getElementById('aud-cuerpo');
    var tabla = document.getElementById('aud-tabla');
    var vacio = document.getElementById('aud-vacio');
    var panelCadenas = document.getElementById('aud-cadenas');
    var listaCadenas = document.getElementById('aud-cadenas-lista');
    var vacioCadenas = document.getElementById('aud-cadenas-vacio');
    var tarjetas = document.querySelectorAll('#aud-cadenas-lista .custody-card');

    function enPeriodo(marca) {
        if (cuando.value === 'hoy') {
            return marca === 'hoy';
        }
        if (cuando.value === 'semana') {
            return marca === 'hoy' || marca === 'ayer';
        }
        return true;
    }

    function pintarCadenas(texto) {
        var modo = vista.value;
        var mostrarPanel = modo === 'todo' || modo === 'custodia' || modo === 'alertas';
        panelCadenas.hidden = !mostrarPanel;
        if (!mostrarPanel) {
            return;
        }
        var visibles = 0;
        tarjetas.forEach(function (tarjeta) {
            var alerta = tarjeta.getAttribute('data-alerta') === '1';
            var sirvePeriodo = enPeriodo(tarjeta.getAttribute('data-cuando'));
            var sirveTexto = !texto || tarjeta.getAttribute('data-texto').indexOf(texto) !== -1;
            var sirveModo = modo !== 'alertas' || alerta;
            var visible = sirvePeriodo && sirveTexto && sirveModo;
            tarjeta.hidden = !visible;
            if (visible) {
                visibles += 1;
            }
        });
        listaCadenas.hidden = visibles === 0;
        vacioCadenas.hidden = visibles !== 0;
    }

    function pintar() {
        var texto = buscar.value.trim().toLocaleLowerCase('es');
        var modo = vista.value;
        pintarCadenas(texto);

        var mostrarBitacora = modo === 'todo' || modo === 'auditoria';
        document.getElementById('aud-bitacora').hidden = !mostrarBitacora;
        if (!mostrarBitacora) {
            return;
        }

        var elegidos = registros.filter(function (registro) {
            if (!enPeriodo(registro.cuando)) {
                return false;
            }
            if (!texto) {
                return true;
            }
            var bolsa = (registro.actor + ' ' + registro.accion + ' ' + registro.objeto + ' ' + registro.detalle).toLocaleLowerCase('es');
            return bolsa.indexOf(texto) !== -1;
        });

        var actores = elegidos.map(function (registro) { return registro.actor; }).filter(function (actor, indice, lista) {
            return lista.indexOf(actor) === indice;
        });
        titulo.textContent = texto && actores.length === 1
            ? 'Historial de ' + actores[0]
            : 'Bitácora de auditoría';

        if (elegidos.length === 0) {
            tabla.hidden = true;
            vacio.hidden = false;
            return;
        }

        vacio.hidden = true;
        tabla.hidden = false;
        cuerpo.innerHTML = elegidos.map(function (registro) {
            return '<tr><td><strong>' + registro.hora + '</strong><small>' + registro.fecha + '</small></td><td><strong>' + registro.actor + '</strong></td><td>' + registro.accion + '</td><td>' + registro.objeto + '</td><td class="muted-cell">' + registro.detalle + '</td></tr>';
        }).join('');
    }

    buscar.addEventListener('input', pintar);
    cuando.addEventListener('change', pintar);
    vista.addEventListener('change', pintar);
    pintar();
})();
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/custodia.blade.php ENDPATH**/ ?>