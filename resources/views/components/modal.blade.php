@props(['name', 'title', 'maxWidth' => '2xl'])

@php
    $maxWidthClass = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ][$maxWidth];
@endphp

<div
    x-data="{ show: false, name: '{{ $name }}' }"
    x-show="show"
    x-on:open-modal.window="if ($event.detail === name) show = true"
    x-on:close-modal.window="if ($event.detail === name) show = false"
    x-on:keydown.escape.window="show = false"
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0"
    scroll-region
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-dark/50 backdrop-blur-sm dark:bg-black/60"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 overflow-hidden rounded-lg border border-border bg-white shadow-xl transition-all sm:mx-auto sm:w-full {{ $maxWidthClass }} dark:border-darkborder dark:bg-darkgray"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave-end="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
    >
        @if($title)
            <div class="flex items-center justify-between border-b border-border bg-lightgray/50 px-6 py-4 dark:border-darkborder dark:bg-darkmuted/20">
                <h3 class="text-lg font-semibold text-dark dark:text-white">{{ $title }}</h3>
                <button x-on:click="show = false" class="text-bodytext transition-colors hover:text-error">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        @endif

        <div class="px-6 py-4">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="border-t border-border bg-lightgray/50 px-6 py-4 text-right dark:border-darkborder dark:bg-darkmuted/20">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
