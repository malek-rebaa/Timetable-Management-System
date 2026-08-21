<x-layout.app>
    @php
        $days = config('timetable.days');
        $dayLabels = [
            'MONDAY' => 'Lundi',
            'TUESDAY' => 'Mardi',
            'WEDNESDAY' => 'Mercredi',
            'THURSDAY' => 'Jeudi',
            'FRIDAY' => 'Vendredi',
            'SATURDAY' => 'Samedi',
        ];
        $grid = new \App\Services\Timetable\SlotGrid();
    @endphp

    {{-- Filtres et actions --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">
            Emploi du Temps
        </h2>

        <div class="flex flex-wrap gap-2">
            {{-- Sélecteur de vue --}}
            <div x-data="{ viewMode: '{{ request('view_mode', 'class') }}' }" class="flex gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                <button @click="viewMode='class'; updateUrl('view_mode', 'class')"
                    :class="viewMode === 'class' ? 'bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500'"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-all">
                    Par Classe
                </button>
            </div>

            {{-- Filtre classe --}}
            @if(isset($classRooms) && $classRooms->count())
                <x-form.select name="filter_class"
                    x-data="{ current: '{{ request('filter_class', '') }}' }"
                    @change="updateUrl('filter_class', $event.target.value)">
                    <option value="">Toutes les classes</option>
                    @foreach($classRooms as $class)
                        <option value="{{ $class->id }}" {{ request('filter_class') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ $class->level->name }})
                        </option>
                    @endforeach
                </x-form.select>
            @endif

            {{-- (Filtres Enseignant et Salle retirés à la demande) --}}

            {{-- Filtre emploi du temps --}}
            @if(isset($timetables) && $timetables->count())
                <x-form.select name="timetable_id"
                    x-data="{ current: '{{ request('timetable_id', '') }}' }"
                    @change="updateUrl('timetable_id', $event.target.value)">
                    <option value="">Saisie manuelle</option>
                    @foreach($timetables as $tt)
                        <option value="{{ $tt->id }}" {{ request('timetable_id') == $tt->id ? 'selected' : '' }}>
                            {{ $tt->name }}
                            @if($tt->status === 'COMPLETED')
                                <span class="text-xs text-success-500">✓</span>
                            @elseif($tt->status === 'FAILED')
                                <span class="text-xs text-error-500">✗</span>
                            @endif
                        </option>
                    @endforeach
                </x-form.select>
            @endif

            @unless(auth()->user()->role === 'TEACHER')
            @if($selectedTimetable)
                <form method="POST" action="{{ route('timetable.destroy', $selectedTimetable) }}"
                    onsubmit="return confirm('Supprimer cet emploi du temps et toutes ses séances, y compris les séances verrouillées ?');">
                    @csrf
                    @method('DELETE')
                    <x-form.button type="submit" variant="danger">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer
                    </x-form.button>
                </form>
            @endif

            <x-form.button variant="primary"
                @click="$dispatch('open-modal', 'add-session')">
                <i data-lucide="plus" class="w-4 h-4"></i> Nouvelle Séance
            </x-form.button>

            <x-form.button variant="secondary"
                x-on:click="generateTimetable"
                >
                <i data-lucide="zap" class="w-4 h-4"></i> Générer
            </x-form.button>
            @endunless
        </div>
    </div>

    {{-- Messages d'erreur de génération --}}
    @if(session('generation_errors'))
        <div class="mb-4 rounded-lg bg-error-50 border border-error-200 p-4 dark:bg-error-900/20 dark:border-error-800">
            <h4 class="text-sm font-semibold text-error-700 dark:text-error-400 mb-2">
                <i data-lucide="alert-circle" class="w-4 h-4 inline"></i>
                Erreurs de génération
            </h4>
            <ul class="list-disc list-inside text-xs text-error-600 dark:text-error-300">
                @foreach(session('generation_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('generation_success'))
        <div class="mb-4 rounded-lg bg-success-50 border border-success-200 p-3 dark:bg-success-900/20 dark:border-success-800">
            <span class="text-xs font-medium text-success-700 dark:text-success-400">
                <i data-lucide="check-circle" class="w-4 h-4 inline"></i>
                {{ session('generation_success') }}
            </span>
        </div>
    @endif

    @if(isset($selectedTimetable) && in_array($selectedTimetable->status, ['PENDING', 'RUNNING'], true))
        <div class="mb-4 rounded-lg bg-warning-50 border border-warning-200 p-3 dark:bg-warning-900/20 dark:border-warning-800">
            <span class="text-xs font-medium text-warning-700 dark:text-warning-300">
                <i data-lucide="loader-circle" class="w-4 h-4 inline animate-spin"></i>
                Génération en cours pour {{ $selectedTimetable->name }}. La page se rafraîchira automatiquement.
            </span>
        </div>
    @endif

    {{-- Grille d'emploi du temps --}}
    @php
        $showAllClasses = ! request()->filled('filter_class');
        $timetableGroups = $showAllClasses
            ? $classRooms->mapWithKeys(fn ($classRoom) => [
                $classRoom->id => $sessions->where('class_room_id', $classRoom->id),
            ])
            : collect([null => $sessions]);
    @endphp

    <div class="space-y-6">
    @foreach($timetableGroups as $classRoomId => $classSessions)
        @if($showAllClasses)
            @php
                $classRoom = $classRooms->firstWhere('id', $classRoomId);
            @endphp
            <h3 class="mb-2 text-lg font-semibold text-gray-800 dark:text-white">
                {{ $classRoom->name }}
                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $classRoom->level->name }})</span>
            </h3>
        @endif
    <x-ui.card>
        <div class="overflow-x-auto">
            <div class="min-w-[800px]">
                @if($classSessions->isNotEmpty())
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

                        foreach($classSessions as $session) {
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
                        foreach($classSessions as $session) {
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

                        {{-- Lignes de créneaux --}}
                        @foreach($slotsData as $slotIndex => $slot)
                            <div class="bg-white dark:bg-gray-900 p-2 text-center text-xs font-medium text-gray-500 flex items-center justify-center border-r border-gray-100 dark:border-gray-800">
                                {{ $slot['start'] }} - {{ $slot['end'] }}
                            </div>

                            @foreach($days as $day)
                                @php
                                    $key = $day . '|' . $slot['start'];
                                    $slotSessions = $uniqueByStart[$key] ?? [];
                                @endphp
                                <div class="bg-white dark:bg-gray-900 p-1 min-h-[60px]">
                                    @if(count($slotSessions) > 0)
                                        <div class="flex flex-col gap-1 h-full">
                                            @foreach($slotSessions as $session)
                                                @php
                                                    $isTheory = $session->subjectPlan->teaching_type === 'THEORY';
                                                    $colorClass = $isTheory
                                                        ? 'bg-brand-50 border-brand-500 text-brand-600'
                                                        : 'bg-success-50 border-success-500 text-success-600';
                                                    $typeLabel = $isTheory ? 'THEORY' : 'TP';
                                                @endphp
                                                <div class="rounded-md border-l-4 {{ $colorClass }} p-2 shadow-theme-xs text-xs">
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
                                                        <div class="mt-1 text-[10px] font-semibold bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded inline-block">
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
                    <div class="{{ $showAllClasses ? 'py-10' : 'py-16' }} text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                            <i data-lucide="calendar" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Aucune séance</h3>
                        <p class="text-sm text-gray-500 mb-4">Ajoutez une séance manuellement ou générez un emploi du temps.</p>
                        @unless($showAllClasses || auth()->user()->role === 'TEACHER')
                        <div class="flex justify-center gap-2">
                            <x-form.button variant="primary"
                                @click="$dispatch('open-modal', 'add-session')">
                                <i data-lucide="plus" class="w-4 h-4"></i> Ajouter une séance
                            </x-form.button>
                            <x-form.button variant="secondary" x-on:click="generateTimetable">
                                <i data-lucide="zap" class="w-4 h-4"></i> Générer automatiquement
                            </x-form.button>
                        </div>
                        @endunless
                    </div>
                @endif
            </div>
        </div>
    </x-ui.card>
    @endforeach
    </div>

    {{-- Modal: Nouvelle séance --}}
    <x-ui.modal name="add-session" title="Nouvelle séance" :maxWidth="'lg'">
        @unless(auth()->user()->role === 'TEACHER')
        <form action="{{ route('timetable.sessions.store') }}" method="POST">
            @csrf
            @if(request('timetable_id'))
                <input type="hidden" name="timetable_id" value="{{ request('timetable_id') }}">
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Plan de cours --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plan de cours</label>
                    <x-form.select name="subject_plan_id" required>
                        <option value="">Sélectionner un plan</option>
                        @foreach($subjectPlans ?? [] as $plan)
                            <option value="{{ $plan->id }}">
                                {{ $plan->subject->name }} - {{ $plan->level->name }} ({{ $plan->teaching_type }})
                            </option>
                        @endforeach
                    </x-form.select>
                    @error('subject_plan_id') <span class="text-xs text-error-500">{{ $message }}</span> @enderror
                </div>

                {{-- Enseignant --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enseignant</label>
                    <x-form.select name="teacher_id" required>
                        <option value="">Sélectionner</option>
                        @foreach($teachers ?? [] as $teacher)
                            <option value="{{ $teacher->id }}">
                                {{ $teacher->first_name }} {{ $teacher->last_name }}
                            </option>
                        @endforeach
                    </x-form.select>
                    @error('teacher_id') <span class="text-xs text-error-500">{{ $message }}</span> @enderror
                </div>

                {{-- Classe --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Classe</label>
                    <x-form.select name="class_room_id" required>
                        <option value="">Sélectionner</option>
                        @foreach($classRooms ?? [] as $class)
                            <option value="{{ $class->id }}">
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </x-form.select>
                    @error('class_room_id') <span class="text-xs text-error-500">{{ $message }}</span> @enderror
                </div>

                {{-- Salle --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salle</label>
                    <x-form.select name="room_id">
                        <option value="">Sélectionner</option>
                        @foreach($rooms ?? [] as $room)
                            <option value="{{ $room->id }}">
                                {{ $room->name }} ({{ $room->type }} - {{ $room->capacity }} places)
                            </option>
                        @endforeach
                    </x-form.select>
                    @error('room_id') <span class="text-xs text-error-500">{{ $message }}</span> @enderror
                </div>

                {{-- Groupe (pour TP) --}}
                <div x-data="{ showGroup: false }"
                    x-show="showGroup"
                    class="">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Groupe TP</label>
                    <x-form.select name="group_number" x-bind:value="showGroup ? '1' : ''">
                        <option value="">--</option>
                        <option value="1">Groupe 1</option>
                        <option value="2">Groupe 2</option>
                    </x-form.select>
                </div>

                {{-- Jour --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jour</label>
                    <x-form.select name="day" required>
                        <option value="">Sélectionner</option>
                        @foreach($days as $day)
                            <option value="{{ $day }}">{{ $dayLabels[$day] }}</option>
                        @endforeach
                    </x-form.select>
                    @error('day') <span class="text-xs text-error-500">{{ $message }}</span> @enderror
                </div>

                {{-- Heure début --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Heure début</label>
                    <x-form.input name="start_time" type="time" required />
                    @error('start_time') <span class="text-xs text-error-500">{{ $message }}</span> @enderror
                </div>

                {{-- Heure fin --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Heure fin</label>
                    <x-form.input name="end_time" type="time" required />
                    @error('end_time') <span class="text-xs text-error-500">{{ $message }}</span> @enderror
                </div>

                {{-- Verrouiller --}}
                <div class="flex items-center gap-2 pt-4">
                    <input type="checkbox" name="is_locked" id="is_locked" value="1"
                        class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                    <label for="is_locked" class="text-sm text-gray-700 dark:text-gray-300">
                        Verrouiller (ne pas modifier à la génération)
                    </label>
                </div>
            </div>

            {{-- Erreurs de conflit --}}
            @error('conflict')
                <div class="mt-4 rounded-md bg-error-50 border border-error-200 p-3 dark:bg-error-900/20">
                    <span class="text-xs text-error-700 dark:text-error-400">{{ $message }}</span>
                </div>
            @enderror

            <div class="mt-6 flex justify-end gap-2">
                <x-form.button variant="secondary" type="button"
                    @click="$dispatch('close-modal', 'add-session')">
                    Annuler
                </x-form.button>
                <x-form.button variant="primary" type="submit">
                    Enregistrer
                </x-form.button>
            </div>
        </form>
        @endunless
    </x-ui.modal>

    {{-- Script génération --}}
    <script>
        function generateTimetable() {
            if (!confirm('Générer l\'emploi du temps ? Les séances non verrouillées seront remplacées.')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("timetable.generate") }}';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrf);

            const params = new URLSearchParams(window.location.search);
            if (params.get('timetable_id')) {
                const tt = document.createElement('input');
                tt.type = 'hidden';
                tt.name = 'timetable_id';
                tt.value = params.get('timetable_id');
                form.appendChild(tt);
            }
            if (params.get('filter_class')) {
                const fc = document.createElement('input');
                fc.type = 'hidden';
                fc.name = 'filter_class';
                fc.value = params.get('filter_class');
                form.appendChild(fc);
            }
            
            document.body.appendChild(form);
            form.submit();
        }

        function updateUrl(key, value) {
            const url = new URL(window.location.href);
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
            window.location.href = url.toString();
        }

        @if(isset($selectedTimetable) && in_array($selectedTimetable->status, ['PENDING', 'RUNNING'], true))
        setTimeout(() => window.location.reload(), 5000);
        @endif
    </script>
</x-layout.app>
