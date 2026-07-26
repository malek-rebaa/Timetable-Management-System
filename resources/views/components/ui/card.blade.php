<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900']) }}>
    @if(isset($header))
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800 sm:px-6">
            {{ $header }}
        </div>
    @endif
    
    <div class="p-5 sm:p-6">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800 sm:px-6">
            {{ $footer }}
        </div>
    @endif
</div>
