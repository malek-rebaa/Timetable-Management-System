@props(['variant' => 'primary'])

@php
$classes = match($variant) {
    'primary' => 'bg-brand-500 text-white hover:bg-brand-600 focus:ring-brand-500/20',
    'secondary' => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-500/20 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
    'danger' => 'bg-error-500 text-white hover:bg-error-600 focus:ring-error-500/20',
    default => 'bg-brand-500 text-white hover:bg-brand-600 focus:ring-brand-500/20',
};
@endphp

<button {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-all focus:outline-none focus:ring-4 ' . $classes]) }}>
    {{ $slot }}
</button>
