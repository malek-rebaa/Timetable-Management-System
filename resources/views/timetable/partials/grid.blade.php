@props(['sessions', 'days', 'dayLabels', 'grid'])

<div class="overflow-x-auto">
    <div class="min-w-[800px]">
        @if(isset($sessions) && $sessions->count() > 0)
            @php
                // Regrouper les séances par jour puis par créneau
                $slotsPerDay = $grid->slotsPerDay;
                $maxSlots = $grid->slotsPerDay;
                $slotsData = [];
                foreach($grid->slotsForDay($days[0] ?? 'MONDAY') as $index => $slot) {
                    $slotsData[$index] = $slot;
                }

                // Organiser: day -> slotIndex -> [sessions]
                $organized = [];
                foreach($days as $day) {
                    $organized[$day] = array_fill(0, $maxSlots, []);
                }

                foreach($sessions as $session) {
                    $normalizedStart = \Carbon\Carbon::parse($session->start_time)->format('H:i');
                    $startSlot = $grid->indexOf($session->day, $normalizedStart);
                    $duration = $grid->durationToSlots($session->subjectPlan->session_duration ?? 120);
                    if ($startSlot !== null) {
                        for ($i = 0; $i < $duration && $startSlot + $i < $maxSlots; $i++) {
                            $organized[$session->day][$startSlot + $i][] = $session;
                        }
                    }
                }

                // Déterminer les séances uniques par créneau de début
                $uniqueByStart = [];
                foreach($sessions as $session) {
                    $key = $session->day . '|' . \Carbon\Carbon::parse($session->start_time)->format('H:i');
                    if (!isset($uniqueByStart[$key])) {
                        $uniqueByStart[$key] = [];
                    }
                    $uniqueByStart[$key][] = $session;
                }
            @endphp

            <div class="grid gap-px bg-gray-200 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800"
                style="grid-template-columns: 100px repeat({{ count($days) }}, 1fr);">

                {{-- En-têtes --}}
                <div class="bg-gray-50 dark:bg-gray-900 p-3 text-center text-xs font-semibold text-gray-500 uppercase">Heure</div>
                @foreach($days as $day)
                    <div class="bg-gray-50 dark:bg-gray-900 p-3 text-center text-xs font-semibold text-gray-800 dark:text-white uppercase">
                        {{ $dayLabels[$day] ?? $day }}
                    </div>
                @endforeach

                @php
                    $coveredSlots = [];
                @endphp

                {{-- Lignes de créneaux --}}
                @foreach($slotsData as $slotIndex => $slot)
                    <div class="bg-white dark:bg-gray-900 p-2 text-center text-xs font-medium text-gray-500 flex items-center justify-center border-r border-gray-100 dark:border-gray-800">
                        {{ $slot['start'] }} - {{ $slot['end'] }}
                    </div>

                    @foreach($days as $day)
                        @php
                            if (isset($coveredSlots[$day][$slotIndex]) && $coveredSlots[$day][$slotIndex] > 0) {
                                continue;
                            }

                            $key = $day . '|' . $slot['start'];
                            $slotSessions = $uniqueByStart[$key] ?? [];
                            $maxSpan = 1;

                            if (count($slotSessions) > 0) {
                                foreach ($slotSessions as $session) {
                                    $duration = $grid->durationToSlots($session->subjectPlan->session_duration ?? 120);
                                    if ($duration > $maxSpan) {
                                        $maxSpan = $duration;
                                    }
                                }

                                if ($maxSpan > 1) {
                                    for ($i = 1; $i < $maxSpan; $i++) {
                                        $coveredSlots[$day][$slotIndex + $i] = 1;
                                    }
                                }
                            }
                        @endphp
                        <div class="bg-white dark:bg-gray-900 p-1 min-h-[60px]" style="{{ $maxSpan > 1 ? 'grid-row: span ' . $maxSpan . ';' : '' }}">
                            @if(count($slotSessions) > 0)
                                <div class="flex flex-col gap-1 h-full">
                                    @foreach($slotSessions as $session)
                                        @php
                                            $isTheory = $session->subjectPlan->teaching_type === 'THEORY';
                                            $colorClass = $isTheory
                                                ? 'bg-brand-50 border-brand-400 text-brand-800 dark:bg-brand-900/40 dark:border-brand-500 dark:text-brand-300'
                                                : 'bg-success-50 border-success-400 text-success-800 dark:bg-success-900/40 dark:border-success-500 dark:text-success-300';
                                            $typeLabel = $isTheory ? 'THEORY' : 'TP';
                                        @endphp
                                        <div class="rounded-md border-l-4 {{ $colorClass }} p-2 shadow-theme-xs text-xs h-full flex flex-col justify-center">
                                            <div class="font-bold mb-0.5">
                                                {{ $session->subjectPlan->subject->name ?? '-' }}
                                                @if(!$isTheory && $session->group_number)
                                                    (G{{ $session->group_number }})
                                                @endif
                                            </div>
                                            <div class="text-gray-600 dark:text-gray-300 flex items-center gap-1 mb-0.5">
                                                <i data-lucide="user" class="w-3 h-3"></i>
                                                {{ $session->teacher ? $session->teacher->first_name . ' ' . $session->teacher->last_name : '-' }}
                                            </div>
                                            @if($session->room)
                                                <div class="text-gray-600 dark:text-gray-300 flex items-center gap-1">
                                                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                                                    {{ $session->room->name }}
                                                </div>
                                            @endif
                                            @if($session->is_locked)
                                                <div class="mt-1 text-[10px] font-semibold bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded inline-block w-max">
                                                    🔒 Verrouillée
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        @else
            {{-- Pas de séances --}}
            <div class="py-16 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                    <i data-lucide="calendar" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Aucune séance</h3>
                <p class="text-sm text-gray-500 mb-4">Ajoutez une séance manuellement ou générez un emploi du temps.</p>
                <div class="flex justify-center gap-2">
                    <x-form.button variant="primary"
                        @click="$dispatch('open-modal', 'add-session')">
                        <i data-lucide="plus" class="w-4 h-4"></i> Ajouter une séance
                    </x-form.button>
                    <x-form.button variant="secondary" x-on:click="generateTimetable">
                        <i data-lucide="zap" class="w-4 h-4"></i> Générer automatiquement
                    </x-form.button>
                </div>
            </div>
        @endif
    </div>
</div>
