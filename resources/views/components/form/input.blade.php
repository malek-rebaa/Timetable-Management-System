@props(['disabled' => false, 'error' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' => 'rounded-md border text-sm w-full transition-colors focus:outline-none focus:ring-0 ' . 
    ($error 
        ? 'border-error text-error focus:border-error bg-lighterror/20 dark:bg-error/10' 
        : 'border-border dark:border-darkborder bg-transparent dark:bg-darkgray text-dark dark:text-white focus:border-primary dark:focus:border-primary') . 
    ($disabled ? ' opacity-60 bg-lightgray dark:bg-darkmuted cursor-not-allowed' : '')
]) !!}>
