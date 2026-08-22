<x-layout.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md font-semibold text-gray-800 dark:text-white">
            Dashboard
        </h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-gray-500 hover:text-brand-500" href="{{ url('/') }}">Accueil /</a></li>
                <li class="font-medium text-brand-500">Dashboard</li>
            </ol>
        </nav>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:gap-7.5">
        
        <x-ui.card class="border-l-4 border-l-brand-500">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-title-sm font-bold text-gray-800 dark:text-white">{{ $teachersCount }}</h4>
                    <span class="text-sm font-medium text-gray-500">Enseignants</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="border-l-4 border-l-success-500">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-title-sm font-bold text-gray-800 dark:text-white">{{ $classRoomsCount }}</h4>
                    <span class="text-sm font-medium text-gray-500">Classes</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-success-50 text-success-500 dark:bg-success-500/10">
                    <i data-lucide="layers" class="w-6 h-6"></i>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="border-l-4 border-l-warning-500">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-title-sm font-bold text-gray-800 dark:text-white">{{ $roomsCount }}</h4>
                    <span class="text-sm font-medium text-gray-500">Salles</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-warning-50 text-warning-500 dark:bg-warning-500/10">
                    <i data-lucide="school" class="w-6 h-6"></i>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="border-l-4 border-l-error-500">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-title-sm font-bold text-gray-800 dark:text-white">{{ $failedCount }}</h4>
                    <span class="text-sm font-medium text-error-500">Échecs de Génération</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/10">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
            </div>
        </x-ui.card>

    </div>

    <!-- Main Content Area -->
    <div class="mt-4 grid grid-cols-1 gap-4 md:mt-6 md:gap-6 2xl:mt-7.5 2xl:gap-7.5">
        <x-ui.card>
            <x-slot:header>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Derniers échecs de génération</h3>
            </x-slot:header>
            
            <x-ui.table :headers="['Emploi du temps', 'Date', 'Action']">
                @forelse($failedTimetables as $timetable)
                    <tr>
                        <td class="px-5 py-4 text-sm font-medium text-error-500">{{ $timetable->name }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $timetable->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4">
                            <a href="{{ route('timetable.index', ['timetable_id' => $timetable->id]) }}">
                                <x-form.button variant="secondary" class="py-1 px-3 text-xs">Voir</x-form.button>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-4 text-sm text-center text-gray-500 dark:text-gray-400">
                            Aucun échec de génération récent.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layout.app>
