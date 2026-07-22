@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg border border-border bg-white shadow-sm dark:border-darkborder dark:bg-darkgray']) }}>
    @if($title || $subtitle || isset($header))
        <div class="flex items-center justify-between border-b border-border px-6 py-4 dark:border-darkborder">
            <div>
                @if($title)
                    <h3 class="text-lg font-semibold text-dark dark:text-white">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="mt-1 text-sm text-bodytext">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($header))
                <div>{{ $header }}</div>
            @endif
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="border-t border-border bg-lightgray/50 px-6 py-4 dark:border-darkborder dark:bg-darkmuted/20">
            {{ $footer }}
        </div>
    @endif
</div>
