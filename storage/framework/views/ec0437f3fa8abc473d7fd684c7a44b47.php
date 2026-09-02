<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <?php
        $activeSchool = app(\App\Multitenancy\CurrentTenant::class)->school();
        $primaryColor = $activeSchool?->primary_color;
        $secondaryColor = $activeSchool?->secondary_color;
        $primaryColor = is_string($primaryColor) && preg_match('/^#[0-9A-Fa-f]{6}$/', $primaryColor) ? $primaryColor : '#4F46E5';
        $secondaryColor = is_string($secondaryColor) && preg_match('/^#[0-9A-Fa-f]{6}$/', $secondaryColor) ? $secondaryColor : '#4338CA';
    ?>

    <title><?php echo e($activeSchool?->name ?? config('app.name', 'EduTime')); ?></title>

    <!-- Scripts and Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        :root {
            --color-brand-25: color-mix(in srgb, <?php echo e($primaryColor); ?> 4%, white);
            --color-brand-50: color-mix(in srgb, <?php echo e($primaryColor); ?> 10%, white);
            --color-brand-100: color-mix(in srgb, <?php echo e($primaryColor); ?> 20%, white);
            --color-brand-200: color-mix(in srgb, <?php echo e($primaryColor); ?> 35%, white);
            --color-brand-300: color-mix(in srgb, <?php echo e($primaryColor); ?> 55%, white);
            --color-brand-400: color-mix(in srgb, <?php echo e($primaryColor); ?> 75%, white);
            --color-brand-500: <?php echo e($primaryColor); ?>;
            --color-brand-600: <?php echo e($secondaryColor); ?>;
            --color-brand-700: color-mix(in srgb, <?php echo e($secondaryColor); ?> 85%, black);
            --color-brand-800: color-mix(in srgb, <?php echo e($secondaryColor); ?> 70%, black);
            --color-brand-900: color-mix(in srgb, <?php echo e($secondaryColor); ?> 55%, black);
        }
    </style>
    
    <!-- Alpine.js via CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons via CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar component -->
        <?php if (isset($component)) { $__componentOriginal3623d0faebbae10085f2828f046806b2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3623d0faebbae10085f2828f046806b2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3623d0faebbae10085f2828f046806b2)): ?>
<?php $attributes = $__attributesOriginal3623d0faebbae10085f2828f046806b2; ?>
<?php unset($__attributesOriginal3623d0faebbae10085f2828f046806b2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3623d0faebbae10085f2828f046806b2)): ?>
<?php $component = $__componentOriginal3623d0faebbae10085f2828f046806b2; ?>
<?php unset($__componentOriginal3623d0faebbae10085f2828f046806b2); ?>
<?php endif; ?>

        <!-- Main content area -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            
            <!-- Topbar component -->
            <?php if (isset($component)) { $__componentOriginal42bcea9100bf9f0679e044f50c8c7875 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42bcea9100bf9f0679e044f50c8c7875 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.topbar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42bcea9100bf9f0679e044f50c8c7875)): ?>
<?php $attributes = $__attributesOriginal42bcea9100bf9f0679e044f50c8c7875; ?>
<?php unset($__attributesOriginal42bcea9100bf9f0679e044f50c8c7875); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42bcea9100bf9f0679e044f50c8c7875)): ?>
<?php $component = $__componentOriginal42bcea9100bf9f0679e044f50c8c7875; ?>
<?php unset($__componentOriginal42bcea9100bf9f0679e044f50c8c7875); ?>
<?php endif; ?>

            <!-- Main Page Content -->
            <main class="w-full max-w-7xl mx-auto p-4 md:p-6 2xl:p-10">
                <?php echo e($slot); ?>

            </main>
            
        </div>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
        document.addEventListener('alpine:initialized', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\timetable-app\resources\views/components/layout/app.blade.php ENDPATH**/ ?>