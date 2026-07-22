<div class="overflow-x-auto w-full">
    <table class="w-full text-sm text-left text-bodytext">
        @if(isset($head))
            <thead class="text-xs text-dark dark:text-white uppercase bg-lightgray dark:bg-darkmuted">
                <tr>
                    {{ $head }}
                </tr>
            </thead>
        @endif
        
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
