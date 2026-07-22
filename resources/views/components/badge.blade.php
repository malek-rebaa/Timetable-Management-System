@props(['color' => 'primary'])

@php
    $colorClasses = [
        'primary' => 'bg-lightprimary text-primary',
        'secondary' => 'bg-lightsecondary text-secondary',
        'success' => 'bg-lightsuccess text-success',
        'warning' => 'bg-lightwarning text-warning',
        'error' => 'bg-lighterror text-error',
        'info' => 'bg-lightinfo text-info',
        'gray' => 'bg-lightgray text-bodytext dark:bg-darkmuted dark:text-gray-300',
    ];

    $classes = $colorClasses[$color] ?? $colorClasses['primary'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $classes"]) }}>
    {{ $slot }}
</span>
