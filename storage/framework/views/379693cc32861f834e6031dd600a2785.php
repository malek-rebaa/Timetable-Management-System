<div <?php echo e($attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900'])); ?>>
    <?php if(isset($header)): ?>
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800 sm:px-6">
            <?php echo e($header); ?>

        </div>
    <?php endif; ?>
    
    <div class="p-5 sm:p-6">
        <?php echo e($slot); ?>

    </div>

    <?php if(isset($footer)): ?>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800 sm:px-6">
            <?php echo e($footer); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\timetable-app\resources\views/components/ui/card.blade.php ENDPATH**/ ?>