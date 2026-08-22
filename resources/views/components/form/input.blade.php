@props(['disabled' => false, 'error' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full rounded-lg border ' . ($error ? 'border-error-300 focus:border-error-500 focus:ring-error-500/20' : 'border-gray-300 focus:border-brand-500 focus:ring-brand-500/20') . ' bg-white px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-4 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-brand-500']) !!}>
