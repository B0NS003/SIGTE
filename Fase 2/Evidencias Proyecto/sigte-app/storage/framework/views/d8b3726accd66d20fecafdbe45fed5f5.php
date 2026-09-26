

<?php $__env->startSection('title', 'SIGTE — Usuarios'); ?>

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

        <div class="nav-label">Gestión</div>
        <nav class="nav">
            <a href="<?php echo e(route('mockups.panel', 'administradora')); ?>">Resumen</a>
            <a href="<?php echo e(route('mockups.catalogo')); ?>">Catálogo</a>
            <a href="<?php echo e(route('mockups.inventario')); ?>">Inventario</a>
            <a class="active" href="<?php echo e(route('mockups.usuarios')); ?>">Usuarios</a>
            <a href="<?php echo e(route('mockups.reportes')); ?>">Reportes</a>
            <a href="<?php echo e(route('mockups.custodia')); ?>">Custodia / auditoría</a>
            <a href="<?php echo e(route('mockups.alertas')); ?>">Alertas <span class="nav-badge">3</span></a>
        </nav>

        <div class="nav-label">Supervisión</div>
        <nav class="nav">
            <a href="<?php echo e(route('mockups.panel', 'operador')); ?>">Ver flujo de cajas</a>
        </nav>

        <div class="sidebar-foot">
            <div class="side-user">
                <div class="avatar"><?php echo e(strtoupper(substr($usuario['nombre'], 0, 1))); ?></div>
                <div>
                    <strong><?php echo e($usuario['nombre']); ?></strong>
                    <small><?php echo e($usuario['rol']); ?></small>
                </div>
            </div>
            <a class="side-logout" href="<?php echo e(route('mockups.login')); ?>">Cambiar rol</a>
        </div>
    </aside>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Gestión · solo administradora</p>
                <h1>Usuarios</h1>
                <p class="main-sub">Crear, asignar rol y desactivar cuentas. Enfermera y operadora no entran aquí.</p>
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

        <div class="grid-2 users-layout">
            <section class="panel list">
                <div class="panel-head">
                    <h2>Cuentas</h2>
                    <span class="badge"><?php echo e(count($usuarios)); ?></span>
                </div>
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
                            <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="<?php echo e($u['estado'] === 'inactivo' ? 'user-inactive' : ''); ?>">
                                    <td>
                                        <strong><?php echo e($u['nombre']); ?></strong>
                                        <div class="muted-cell"><?php echo e($u['email']); ?></div>
                                    </td>
                                    <td><span class="badge"><?php echo e($u['rol']); ?></span></td>
                                    <td>
                                        <?php if($u['estado'] === 'activo'): ?>
                                            <span class="pill pill-ok">Activo</span>
                                        <?php else: ?>
                                            <span class="pill pill-warn">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($u['ultimo']); ?></td>
                                    <td class="user-actions">
                                        <button class="btn btn-ghost" type="button">Editar</button>
                                        <?php if($u['estado'] === 'activo'): ?>
                                            <button class="btn btn-ghost" type="button">Desactivar</button>
                                        <?php else: ?>
                                            <button class="btn btn-dark" type="button">Reactivar</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="panel-note">Mockup: los botones no guardan. En el sistema real, desactivar bloquea login sin borrar historial.</div>
            </section>

            <form class="panel recv-form" action="<?php echo e(route('mockups.usuarios')); ?>" method="get">
                <div class="panel-head">
                    <h2>Nueva cuenta</h2>
                    <span class="badge">Alta</span>
                </div>

                <label class="field-label" for="nombre">Nombre completo</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 19c1.5-3 4-4.5 7-4.5S17.5 16 19 19"/></svg>
                    </span>
                    <input id="nombre" name="nombre" type="text" placeholder="Ej: Daniela Soto" value="Daniela Soto">
                </label>

                <label class="field-label" for="email">Correo institucional</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">@</span>
                    <input id="email" name="email" type="email" placeholder="usuario@hsjmelipilla.cl" value="dsoto@hsjmelipilla.cl">
                </label>

                <label class="field-label" for="rol">Rol</label>
                <label class="field field-select">
                    <select id="rol" name="rol">
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option <?php echo e($r === 'Operadora' ? 'selected' : ''); ?>><?php echo e($r); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="field-label" for="pass">Contraseña temporal</label>
                <label class="field">
                    <span class="icon" aria-hidden="true">*</span>
                    <input id="pass" name="pass" type="text" value="Cambiar123">
                </label>

                <div class="recv-checks">
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Debe cambiar contraseña en el primer ingreso</span>
                    </label>
                    <label class="check">
                        <input type="checkbox" checked>
                        <span>Cuenta activa de inmediato</span>
                    </label>
                </div>

                <div class="recv-actions">
                    <button class="btn btn-primary" type="submit" style="width:auto;min-width:11rem;">Crear usuario</button>
                </div>

                <div class="panel-note" style="margin-top:1rem;">Solo administradora crea usuarios. El rol define qué ve al entrar (login por rol en el mockup).</div>
            </form>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\sigte-app\resources\views/mockups/usuarios.blade.php ENDPATH**/ ?>