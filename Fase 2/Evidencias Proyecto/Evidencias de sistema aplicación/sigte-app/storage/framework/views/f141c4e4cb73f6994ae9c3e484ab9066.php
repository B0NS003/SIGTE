<aside class="sidebar">
    <div class="logo-wrap">
        <img src="<?php echo e(asset('images/logo-hospital-circular.png')); ?>" alt="Logo hospital">
        <div>
            <div class="logo">SIG<span>TE</span></div>
            <div class="logo-sub">Secretaría</div>
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
<?php /**PATH /var/www/html/resources/views/mockups/partials/sidebar-secretaria.blade.php ENDPATH**/ ?>