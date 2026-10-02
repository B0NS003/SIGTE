<div class="sidebar-nav">
<?php $__currentLoopData = \App\Support\AccesoPorRol::menu(auth()->user()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="nav-label"><?php echo e($grupo['titulo']); ?></div>
    <nav class="nav">
        <?php $__currentLoopData = $grupo['enlaces']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enlace): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e($enlace['url']); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['active' => $enlace['activo']]); ?>">
                <?php echo e($enlace['texto']); ?>

                <?php if($enlace['badge']): ?>
                    <span class="nav-badge"><?php echo e($enlace['badge']); ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH /var/www/html/resources/views/partials/menu-rol.blade.php ENDPATH**/ ?>