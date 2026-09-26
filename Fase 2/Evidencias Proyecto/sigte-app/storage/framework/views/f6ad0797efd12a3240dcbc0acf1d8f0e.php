<form method="POST" action="<?php echo e(route('logout')); ?>">
    <?php echo csrf_field(); ?>
    <button class="side-logout" type="submit">Cerrar sesión</button>
</form>
<?php /**PATH /var/www/html/resources/views/partials/logout.blade.php ENDPATH**/ ?>