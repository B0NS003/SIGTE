<?php $__env->startSection('title', 'SIGTE — Usuarios'); ?>

<!-- Sección de contenido de la página de usuarios -->
<?php $__env->startSection('content'); ?>
<!-- Contenedor principal de la página -->
<div class="shell">
    <!-- Barra lateral de navegación -->
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

    <!-- Contenido principal de la página -->
    <main class="main">
        <!-- Encabezado de la página -->
        <div class="main-top">
            <div>
                <p class="eyebrow">Gestión · solo administradora</p>
                <h1>Usuarios</h1>
                <p class="main-sub">La jefatura crea las cuentas, asigna el rol y puede desactivarlas. La enfermera y la operadora no entran aquí.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.panel', 'administradora')); ?>">Volver al resumen</a>
            </div>
        </div>

        <div class="kpi-row">
            <div class="kpi">
                <div class="l">Total</div>
                <div class="n"><?php echo e($resumen['total']); ?></div>
                <div class="h">Cuentas registradas</div>
            </div>
            <div class="kpi ok">
                <div class="l">Activos</div>
                <div class="n"><?php echo e($resumen['activos']); ?></div>
                <div class="h">Pueden iniciar sesión</div>
            </div>
            <div class="kpi">
                <div class="l">Inactivos</div>
                <div class="n"><?php echo e($resumen['inactivos']); ?></div>
                <div class="h">Desactivados</div>
            </div>
            <div class="kpi">
                <div class="l">Administradoras</div>
                <div class="n"><?php echo e($resumen['admin']); ?></div>
                <div class="h">Con gestión de usuarios</div>
            </div>
        </div>
        <!-- Grilla de dos columnas para la lista de usuarios -->
        <div class="users-page">
            <section class="panel list">
                <!-- Encabezado de la lista de usuarios -->
                <div class="panel-head">
                    <h2>Cuentas</h2>
                    <span class="badge"><?php echo e(count($usuarios)); ?></span>
                </div>
                <!-- Tabla de usuarios -->
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Último acceso</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Iteración sobre los usuarios -->
                            <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="<?php echo e($u['estado'] === 'inactivo' ? 'user-inactive' : ''); ?>" data-estado="<?php echo e($u['estado']); ?>">
                                    <td>
                                        <strong class="js-nombre"><?php echo e($u['nombre']); ?></strong>
                                        <div class="muted-cell js-email"><?php echo e($u['email']); ?></div>
                                    </td>
                                    <td><span class="badge rol-pill js-rol <?php echo e($u['rol'] === 'Administradora' ? 'rol-admin' : ($u['rol'] === 'Operadora' ? 'rol-operadora' : 'rol-enfermera')); ?>"><?php echo e($u['rol']); ?></span></td>
                                    <td>
                                        <span class="pill js-estado <?php echo e($u['estado'] === 'activo' ? 'pill-ok' : 'pill-warn'); ?>"><?php echo e($u['estado'] === 'activo' ? 'Activo' : 'Inactivo'); ?></span>
                                    </td>
                                    <td><?php echo e($u['ultimo']); ?></td>
                                    <td class="user-actions">
                                        <button class="btn btn-ghost js-editar" type="button">Editar</button>
                                        <button class="btn <?php echo e($u['estado'] === 'activo' ? 'btn-ghost' : 'btn-dark'); ?> js-estado-btn" type="button"><?php echo e($u['estado'] === 'activo' ? 'Desactivar' : 'Reactivar'); ?></button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <form class="panel cuenta-alta" id="cuenta-form" action="<?php echo e(route('mockups.usuarios')); ?>" method="get" onsubmit="return false">
                <div class="panel-head">
                    <h2 id="cuenta-titulo">Nueva cuenta</h2>
                    <span class="badge" id="cuenta-modo">Alta</span>
                </div>
                <p class="adv-ok" id="cuenta-ok" role="status" hidden></p>
                <p class="auth-error" id="cuenta-error" role="alert" hidden></p>
                <div class="cuenta-grid">
                    <div>
                        <label class="field-label" for="nombre">Nombre completo</label>
                        <label class="field">
                            <input id="nombre" name="nombre" type="text" placeholder="Nombre y apellido" value="">
                        </label>
                    </div>
                    <div>
                        <label class="field-label" for="email">Correo institucional</label>
                        <label class="field">
                            <input id="email" name="email" type="email" placeholder="nombre@hsjmelipilla.cl" value="">
                        </label>
                    </div>
                    <div>
                        <label class="field-label" for="rol">Rol</label>
                        <label class="field field-select">
                            <select id="rol" name="rol">
                                <option value="">Elige el rol</option>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($r); ?>"><?php echo e($r); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label class="field-label" for="pass">Contraseña temporal</label>
                        <label class="field">
                            <input id="pass" name="pass" type="text" placeholder="La cambia al entrar" value="">
                        </label>
                    </div>
                </div>
                <div class="cuenta-pie">
                    <label class="check">
                        <input id="cambiar-clave" type="checkbox" checked>
                        <span>Debe cambiar contraseña en el primer ingreso</span>
                    </label>
                    <div class="recv-actions">
                        <button class="btn btn-ghost" id="cuenta-cancelar" type="button" hidden>Cancelar</button>
                        <button class="btn btn-primary" id="cuenta-guardar" type="button">Crear usuario</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
    var formFila = null;
    var titulo = document.getElementById('cuenta-titulo');
    var modo = document.getElementById('cuenta-modo');
    var guardar = document.getElementById('cuenta-guardar');
    var cancelar = document.getElementById('cuenta-cancelar');
    var ok = document.getElementById('cuenta-ok');
    var error = document.getElementById('cuenta-error');
    var nombre = document.getElementById('nombre');
    var email = document.getElementById('email');
    var rol = document.getElementById('rol');
    var pass = document.getElementById('pass');

    function claseRol(valor) {
        if (valor === 'Administradora') return 'rol-admin';
        if (valor === 'Operadora') return 'rol-operadora';
        return 'rol-enfermera';
    }

    function aviso(texto, esError) {
        ok.hidden = esError || !texto;
        error.hidden = !esError || !texto;
        (esError ? error : ok).textContent = texto || '';
    }

    function limpiarAlta() {
        formFila = null;
        titulo.textContent = 'Nueva cuenta';
        modo.textContent = 'Alta';
        guardar.textContent = 'Crear usuario';
        cancelar.hidden = true;
        nombre.value = '';
        email.value = '';
        rol.value = '';
        pass.value = '';
    }

    document.querySelector('.users-page tbody').addEventListener('click', function (evento) {
        var editar = evento.target.closest('.js-editar');
        var estadoBtn = evento.target.closest('.js-estado-btn');
        if (editar) {
            formFila = editar.closest('tr');
            nombre.value = formFila.querySelector('.js-nombre').textContent;
            email.value = formFila.querySelector('.js-email').textContent;
            rol.value = formFila.querySelector('.js-rol').textContent;
            pass.value = '';
            titulo.textContent = 'Editar cuenta';
            modo.textContent = 'Cambio';
            guardar.textContent = 'Guardar cambios';
            cancelar.hidden = false;
            aviso('', false);
            nombre.focus();
        }
        if (!estadoBtn) {
            return;
        }
        var fila = estadoBtn.closest('tr');
        var persona = fila.querySelector('.js-nombre').textContent;
        var activo = fila.dataset.estado === 'activo';
        var pill = fila.querySelector('.js-estado');
        fila.dataset.estado = activo ? 'inactivo' : 'activo';
        fila.classList.toggle('user-inactive', activo);
        pill.textContent = activo ? 'Inactivo' : 'Activo';
        pill.classList.toggle('pill-ok', !activo);
        pill.classList.toggle('pill-warn', activo);
        estadoBtn.textContent = activo ? 'Reactivar' : 'Desactivar';
        estadoBtn.classList.toggle('btn-dark', activo);
        estadoBtn.classList.toggle('btn-ghost', !activo);
        aviso(activo
            ? persona + ' quedó inactiva. No puede entrar. El historial se mantiene.'
            : persona + ' puede volver a entrar.', false);
    });

    cancelar.addEventListener('click', function () {
        limpiarAlta();
        aviso('', false);
    });

    guardar.addEventListener('click', function () {
        if (!nombre.value.trim() || !email.value.trim() || !rol.value || (formFila === null && !pass.value.trim())) {
            aviso('Completa nombre, correo, rol' + (formFila === null ? ' y contraseña temporal.' : '.'), true);
            return;
        }
        if (!email.value.includes('@')) {
            aviso('El correo institucional no es válido.', true);
            return;
        }
        if (formFila) {
            formFila.querySelector('.js-nombre').textContent = nombre.value.trim();
            formFila.querySelector('.js-email').textContent = email.value.trim();
            var marca = formFila.querySelector('.js-rol');
            marca.textContent = rol.value;
            marca.classList.remove('rol-admin', 'rol-enfermera', 'rol-operadora');
            marca.classList.add(claseRol(rol.value));
            aviso('Listo. Quedaron los datos y el rol de ' + nombre.value.trim() + '.', false);
            limpiarAlta();
            return;
        }
        var cuerpo = document.querySelector('.users-page tbody');
        var fila = document.createElement('tr');
        fila.dataset.estado = 'activo';
        fila.innerHTML = '<td><strong class="js-nombre"></strong><div class="muted-cell js-email"></div></td><td><span class="badge rol-pill js-rol"></span></td><td><span class="pill pill-ok js-estado">Activo</span></td><td>Hoy</td><td class="user-actions"><button class="btn btn-ghost js-editar" type="button">Editar</button><button class="btn btn-ghost js-estado-btn" type="button">Desactivar</button></td>';
        fila.querySelector('.js-nombre').textContent = nombre.value.trim();
        fila.querySelector('.js-email').textContent = email.value.trim();
        fila.querySelector('.js-rol').textContent = rol.value;
        fila.querySelector('.js-rol').classList.add(claseRol(rol.value));
        cuerpo.prepend(fila);
        aviso('Listo. ' + nombre.value.trim() + ' quedó como ' + rol.value + '.', false);
        limpiarAlta();
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/usuarios.blade.php ENDPATH**/ ?>