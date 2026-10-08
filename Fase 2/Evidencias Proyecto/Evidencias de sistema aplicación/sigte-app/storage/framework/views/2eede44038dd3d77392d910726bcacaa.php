<?php $__env->startSection('title', 'SIGTE — Reportes de producción'); ?>

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
                <p class="eyebrow">Hospital San José de Melipilla</p>
                <h1>Reportes de producción</h1>
                <p class="main-sub">Arma el consolidado de la central, revísalo en pantalla y elige el formato para entregarlo.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'administradora')); ?>">Volver al resumen</a>
            </div>
        </div>

        <?php if($falla): ?>
            <div class="ops-empty">
                <strong>No se pudo armar el reporte</strong>
                <p>La consulta no está disponible en este momento.</p>
                <p><a href="<?php echo e(route('mockups.reportes')); ?>">Intentar de nuevo</a></p>
            </div>
        <?php else: ?>
            <form class="panel rep-armar" id="rep-armar" action="#" onsubmit="return false;">
                <div class="panel-head">
                    <h2>Qué va en el reporte</h2>
                </div>
                <div class="rep-filtros">
                    <label class="field field-select">
                        <span>Período</span>
                        <select id="rep-periodo" name="periodo">
                            <option value="ahora" selected>Ahora</option>
                            <option value="hoy">Hoy</option>
                            <option value="semana">Esta semana</option>
                            <option value="mes">Este mes</option>
                        </select>
                    </label>
                    <label class="field field-select">
                        <span>Etapa</span>
                        <select id="rep-etapa" name="etapa">
                            <option value="" selected>Todas</option>
                            <?php $__currentLoopData = $fases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $fase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($indice); ?>"><?php echo e($fase); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>
                </div>
                <div class="rep-incluye">
                    <p>Incluir</p>
                    <label><input type="checkbox" id="rep-inc-etapa" checked> Resumen por etapa</label>
                    <label><input type="checkbox" id="rep-inc-cajas" checked> Listado de cajas</label>
                </div>
                <button class="btn btn-dark" type="submit">Ver vista previa</button>
            </form>

            <article class="panel list rep-hoja" id="rep-hoja" aria-label="Vista previa del reporte">
                <header class="rep-hoja-top">
                    <div>
                        <p>Hospital San José de Melipilla</p>
                        <h2>Producción de la central</h2>
                        <p id="rep-periodo-txt">Etapa actual de cada caja</p>
                    </div>
                    <div class="rep-formatos">
                        <button class="btn rep-excel" type="button">Excel</button>
                        <button class="btn rep-pdf" type="button">PDF</button>
                    </div>
                </header>

                <dl class="rep-firma">
                    <div>
                        <dt>Fecha</dt>
                        <dd id="rep-fecha"><?php echo e($fecha); ?></dd>
                    </div>
                    <div>
                        <dt>Hora</dt>
                        <dd id="rep-hora"><?php echo e($hora); ?></dd>
                    </div>
                    <div>
                        <dt>Generó</dt>
                        <dd><?php echo e($usuario['nombre']); ?> · <?php echo e($usuario['rol']); ?></dd>
                    </div>
                </dl>

                <div id="rep-vacio" class="ops-empty" hidden>
                    <strong id="rep-vacio-titulo">Sin datos para ese período</strong>
                    <p id="rep-vacio-texto">El reporte del período se arma cuando cada paso queda registrado. Hoy solo se ve la etapa actual.</p>
                </div>

                <div id="rep-cuerpo">
                    <p class="rep-total">Cajas en el reporte <strong id="rep-total"><?php echo e(count($filas)); ?></strong></p>

                    <section id="rep-bloque-etapa">
                        <h3>Por etapa</h3>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Etapa</th><th>Cajas</th></tr>
                                </thead>
                                <tbody id="rep-etapas">
                                    <?php $__currentLoopData = $fases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $fase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr data-indice="<?php echo e($indice); ?>">
                                            <td><?php echo e($fase); ?></td>
                                            <td><?php echo e(collect($filas)->where('indice', $indice)->count()); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section id="rep-bloque-cajas">
                        <h3>Cajas</h3>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Caja</th><th>Etapa</th></tr>
                                </thead>
                                <tbody id="rep-cajas">
                                    <?php $__currentLoopData = $filas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fila): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><strong><?php echo e($fila['id']); ?></strong></td>
                                            <td><?php echo e($fila['etapa']); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </article>
        <?php endif; ?>
    </main>
</div>
<?php if (! ($falla)): ?>
<script>
(function () {
    var filas = <?php echo json_encode($filas, 15, 512) ?>;
    var fases = <?php echo json_encode($fases, 15, 512) ?>;
    var periodo = document.getElementById('rep-periodo');
    var etapa = document.getElementById('rep-etapa');
    var incEtapa = document.getElementById('rep-inc-etapa');
    var incCajas = document.getElementById('rep-inc-cajas');
    var cuerpo = document.getElementById('rep-cuerpo');
    var vacio = document.getElementById('rep-vacio');
    var vacioTitulo = document.getElementById('rep-vacio-titulo');
    var vacioTexto = document.getElementById('rep-vacio-texto');
    var total = document.getElementById('rep-total');
    var cuerpoEtapas = document.getElementById('rep-etapas');
    var cuerpoCajas = document.getElementById('rep-cajas');
    var bloqueEtapa = document.getElementById('rep-bloque-etapa');
    var bloqueCajas = document.getElementById('rep-bloque-cajas');
    var periodoTxt = document.getElementById('rep-periodo-txt');
    var etiquetas = {
        ahora: 'Etapa actual de cada caja',
        hoy: 'Hoy',
        semana: 'Esta semana',
        mes: 'Este mes'
    };

    function pintar() {
        var esAhora = periodo.value === 'ahora';
        periodoTxt.textContent = etiquetas[periodo.value] || etiquetas.ahora;
        var ahora = new Date();
        document.getElementById('rep-fecha').textContent = ahora.toLocaleDateString('es-CL');
        document.getElementById('rep-hora').textContent = ahora.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });

        if (!esAhora) {
            cuerpo.hidden = true;
            vacio.hidden = false;
            vacioTitulo.textContent = 'Sin datos para ese período';
            vacioTexto.textContent = 'El reporte del período se arma cuando cada paso queda registrado. Hoy solo se ve la etapa actual.';
            return;
        }

        var elegidas = filas.filter(function (fila) {
            if (etapa.value !== '' && String(fila.indice) !== etapa.value) {
                return false;
            }
            return true;
        });

        if (elegidas.length === 0) {
            cuerpo.hidden = true;
            vacio.hidden = false;
            vacioTitulo.textContent = 'Ninguna caja coincide';
            vacioTexto.textContent = 'Prueba con otra etapa.';
            return;
        }

        vacio.hidden = true;
        cuerpo.hidden = false;
        total.textContent = String(elegidas.length);
        bloqueEtapa.hidden = !incEtapa.checked;
        bloqueCajas.hidden = !incCajas.checked;

        cuerpoEtapas.innerHTML = fases.map(function (nombre, indice) {
            var cantidad = elegidas.filter(function (fila) { return fila.indice === indice; }).length;
            if (etapa.value !== '' && String(indice) !== etapa.value) {
                return '';
            }
            return '<tr><td>' + nombre + '</td><td>' + cantidad + '</td></tr>';
        }).join('');

        cuerpoCajas.innerHTML = elegidas.map(function (fila) {
            return '<tr><td><strong>' + fila.id + '</strong></td><td>' + fila.etapa + '</td></tr>';
        }).join('');
    }

    document.getElementById('rep-armar').addEventListener('submit', function (evento) {
        evento.preventDefault();
        pintar();
        document.getElementById('rep-hoja').scrollIntoView({ block: 'nearest' });
    });
    [periodo, etapa, incEtapa, incCajas].forEach(function (campo) {
        campo.addEventListener('change', pintar);
    });
})();
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/reportes.blade.php ENDPATH**/ ?>