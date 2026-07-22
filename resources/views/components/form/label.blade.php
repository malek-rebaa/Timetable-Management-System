@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-dark dark:text-white mb-1']) }}>
    {{ $value ?? $slot }}
</label>
