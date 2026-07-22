@props(['type' => 'info', 'title' => null])

@php
    $typeClasses = [
        'info' => 'bg-lightinfo text-info border-info',
        'success' => 'bg-lightsuccess text-success border-success',
        'warning' => 'bg-lightwarning text-warning-emphasis border-warning',
        'error' => 'bg-lighterror text-error-emphasis border-error',
    ];

    $iconPaths = [
        'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
        'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    ];

    $classes = $typeClasses[$type] ?? $typeClasses['info'];
    $icon = $iconPaths[$type] ?? $iconPaths['info'];
@endphp

<div {{ $attributes->merge(['class' => "p-4 mb-4 border-l-4 rounded-r-md $classes"]) }} role="alert">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        </div>
        <div class="ml-3 w-full">
            @if($title)
                <h3 class="text-sm font-medium">{{ $title }}</h3>
            @endif
            <div class="text-sm mt-1">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
