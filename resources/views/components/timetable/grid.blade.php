@props(['days' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'], 'startHour' => 8, 'endHour' => 18])

<div class="bg-white dark:bg-darkgray rounded-lg shadow-sm border border-border dark:border-darkborder overflow-hidden">
    
    <div class="px-6 py-4 border-b border-border dark:border-darkborder flex justify-between items-center bg-lightgray/50 dark:bg-darkmuted/20">
        <h3 class="text-lg font-semibold text-dark dark:text-white">Emploi du temps</h3>
        <div class="flex gap-2">
            <x-badge color="info">THEORY</x-badge>
            <x-badge color="secondary">TP</x-badge>
            <x-badge color="warning">Conflit</x-badge>
        </div>
    </div>

    <!-- Scrollable container -->
    <div class="overflow-x-auto w-full relative">
        <div class="min-w-[800px]">
            <!-- Grid container -->
            <div class="timetable-grid" style="grid-template-columns: 80px repeat({{ count($days) }}, minmax(150px, 1fr));">
                
                <!-- Top-Left empty cell -->
                <div class="timetable-header bg-lightgray/50 dark:bg-darkmuted/20 border-r border-border dark:border-darkborder"></div>
                
                <!-- Days Header -->
                @foreach($days as $day)
                    <div class="timetable-header bg-lightgray/50 dark:bg-darkmuted/20">
                        {{ $day }}
                    </div>
                @endforeach

                <!-- Time slots -->
                @for ($hour = $startHour; $hour < $endHour; $hour++)
                    <!-- 1 hour row -->
                    <div class="timetable-time">
                        {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00<br>
                        <span class="text-xs opacity-50">{{ str_pad($hour+1, 2, '0', STR_PAD_LEFT) }}:00</span>
                    </div>

                    @foreach($days as $day)
                        <div class="timetable-cell border-b border-border dark:border-darkborder">
                            <!-- Slots will be rendered here dynamically via absolute positioning -->
                            <!-- Example placeholder for the slot -->
                            {{ $slot }}
                        </div>
                    @endforeach
                @endfor
            </div>
        </div>
    </div>
</div>
