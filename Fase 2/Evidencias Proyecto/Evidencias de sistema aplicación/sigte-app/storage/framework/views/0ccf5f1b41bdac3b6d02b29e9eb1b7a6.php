<?php $__env->startSection('title', 'SIGTE — Iniciar sesión'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="auth">
    <section class="auth-form">
        <header class="auth-header">
            <img class="auth-logo-top" src="<?php echo e(asset('images/logo-hospital.png')); ?>" alt="Hospital San José Melipilla">
            <p class="auth-pill-inline">Servicio de esterilización</p>
            <p class="auth-product">SIG<span>TE</span></p>
            <p class="auth-tagline">Trazabilidad del instrumental, de recepción a entrega.</p>
        </header>

        <div class="auth-card">
            <h1 class="auth-login-title">Iniciar sesión</h1>
            <p class="hint">Usa tu cuenta institucional del hospital.</p>

            <?php if($errors->has('login')): ?>
                <p class="auth-error" role="alert"><?php echo e($errors->first('login')); ?></p>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>" id="form-login" novalidate>
                <?php echo csrf_field(); ?>

                <label class="field-label" for="email">Correo</label>
                <label class="field <?php echo e($errors->hasAny(['email', 'login']) ? 'is-invalid' : ''); ?>">
                    <span class="icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/></svg>
                    </span>
                    <input id="email" name="email" type="email" placeholder="nombre@hospital.cl" value="<?php echo e(old('email')); ?>" autocomplete="username" required <?php if($errors->hasAny(['email', 'login'])): ?> aria-invalid="true" <?php endif; ?> <?php if($errors->has('email')): ?> aria-describedby="email-error" <?php endif; ?>>
                </label>
                <p class="field-error" id="email-error" <?php if (! ($errors->has('email'))): ?> hidden <?php endif; ?>><?php echo e($errors->first('email')); ?></p>

                <label class="field-label" for="password">Contraseña</label>
                <label class="field <?php echo e($errors->hasAny(['password', 'login']) ? 'is-invalid' : ''); ?>">
                    <span class="icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                    </span>
                    <input id="password" name="password" type="password" placeholder="••••••••" autocomplete="current-password" required <?php if($errors->hasAny(['password', 'login'])): ?> aria-invalid="true" <?php endif; ?> <?php if($errors->has('password')): ?> aria-describedby="password-error" <?php endif; ?>>
                    <button class="btn-ver-clave" type="button" id="toggle-password" aria-label="Mostrar contraseña" aria-pressed="false">
                        <svg class="icono-ojo" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path class="parpado" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                            <path class="cerrado" d="M4 12.5h16"/>
                            <circle class="pupila" cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </label>
                <p class="field-error" id="password-error" <?php if (! ($errors->has('password'))): ?> hidden <?php endif; ?>><?php echo e($errors->first('password')); ?></p>

                <button class="btn btn-primary" type="submit" id="btn-ingresar">
                    <span class="btn-spinner" aria-hidden="true"></span>
                    <span class="btn-label">Ingresar</span>
                </button>
            </form>
        </div>
    </section>

    <aside class="auth-visual" role="img" aria-label="Fachada del Hospital San José de Melipilla">
        <div class="auth-visual-caption">
            <strong>Hospital San José de Melipilla</strong>
            <span>Menos papel. Más control del ciclo de esterilización.</span>
        </div>
    </aside>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('js/login.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/login.blade.php ENDPATH**/ ?>