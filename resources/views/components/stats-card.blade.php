@props(['title', 'value', 'icon', 'color' => 'primary'])

@php
    $colorClasses = [
        'primary' => 'bg-lightprimary text-primary',
        'secondary' => 'bg-lightsecondary text-secondary',
        'success' => 'bg-lightsuccess text-success',
        'warning' => 'bg-lightwarning text-warning',
        'error' => 'bg-lighterror text-error',
        'info' => 'bg-lightinfo text-info',
    ];

    $iconBgClass = $colorClasses[$color] ?? $colorClasses['primary'];
@endphp

<div class="bg-white dark:bg-darkgray p-6 rounded-lg shadow-sm border border-border dark:border-darkborder flex items-center gap-4 hover:shadow-md transition-shadow">
    <div class="w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0 {{ $iconBgClass }}">
        <!-- Icon rendering -->
        <span class="w-7 h-7 flex items-center justify-center">
            {{ $icon }}
        </span>
    </div>
    
    <div>
        <h4 class="text-2xl font-bold text-dark dark:text-white">{{ $value }}</h4>
        <p class="text-sm font-medium text-bodytext">{{ $title }}</p>
    </div>
</div>
