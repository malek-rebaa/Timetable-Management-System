<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['variant' => 'primary']));

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

foreach (array_filter((['variant' => 'primary']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$classes = match($variant) {
    'primary' => 'bg-brand-500 text-white hover:bg-brand-600 focus:ring-brand-500/20',
    'secondary' => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-500/20 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
    'danger' => 'bg-error-500 text-white hover:bg-error-600 focus:ring-error-500/20',
    default => 'bg-brand-500 text-white hover:bg-brand-600 focus:ring-brand-500/20',
};
?>

<button <?php echo e($attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-all focus:outline-none focus:ring-4 ' . $classes])); ?>>
    <?php echo e($slot); ?>

</button>
<?php /**PATH C:\xampp\htdocs\timetable-app\resources\views/components/form/button.blade.php ENDPATH**/ ?>