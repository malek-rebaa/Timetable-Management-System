<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['disabled' => false, 'error' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['disabled' => false, 'error' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<select <?php echo e($disabled ? 'disabled' : ''); ?> <?php echo $attributes->merge(['class' => 'w-full rounded-lg border ' . ($error ? 'border-error-300 focus:border-error-500 focus:ring-error-500/20' : 'border-gray-300 focus:border-brand-500 focus:ring-brand-500/20') . ' bg-white px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-4 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-brand-500 appearance-none']); ?>>
    <?php echo e($slot); ?>

</select>
<?php /**PATH C:\xampp\htdocs\timetable-app\resources\views/components/form/select.blade.php ENDPATH**/ ?>