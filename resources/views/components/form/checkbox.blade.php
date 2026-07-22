@props(['disabled' => false, 'error' => false])

<input type="checkbox" {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' => 'rounded border-border text-primary shadow-sm focus:ring-primary dark:border-darkborder dark:bg-darkgray dark:checked:bg-primary ' . 
    ($error ? 'border-error text-error focus:ring-error' : '') .
    ($disabled ? ' opacity-60 cursor-not-allowed' : '')
]) !!}>
