@props(['variant' => 'primary', 'type' => 'button'])

@php
    $baseClasses = 'inline-flex items-center justify-center rounded-md border border-transparent px-4 py-2 text-xs font-semibold uppercase tracking-widest transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

    $variants = [
        'primary' => 'bg-primary text-white shadow-sm hover:bg-primary-emphasis focus:ring-primary',
        'secondary' => 'bg-secondary text-white shadow-sm hover:bg-secondary-emphasis focus:ring-secondary',
        'danger' => 'bg-error text-white shadow-sm hover:bg-error-emphasis focus:ring-error',
        'warning' => 'bg-warning text-white shadow-sm hover:bg-warning-emphasis focus:ring-warning',
        'outline' => 'border-border bg-transparent text-dark hover:bg-lightgray focus:ring-primary dark:border-darkborder dark:text-white dark:hover:bg-darkmuted',
    ];

    $classes = $variants[$variant] ?? $variants['primary'];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$baseClasses $classes"]) }}>
    {{ $slot }}
</button>
