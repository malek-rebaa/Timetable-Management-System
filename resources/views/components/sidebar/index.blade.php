<aside 
    class="flex-shrink-0 w-64 bg-white dark:bg-darkgray border-r border-border dark:border-darkborder transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 absolute inset-y-0 left-0 z-50 transform"
    :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
>
    <!-- Logo area -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-border dark:border-darkborder">
        <a href="{{ route('dashboard') ?? '#' }}" class="flex items-center gap-2 text-xl font-bold text-dark dark:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            </svg>
            EduTime
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden text-bodytext hover:text-primary">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <div class="p-4 overflow-y-auto h-[calc(100vh-4rem)]">
        <ul class="space-y-1">
            <!-- COMMON -->
            <x-sidebar.item route="dashboard" icon="layout-dashboard" label="Dashboard" />
            <x-sidebar.item route="timetable" icon="calendar-time" label="Mon Emploi du Temps" />

            <!-- ADMIN / SUPER ADMIN -->
            {{-- @hasanyrole('Super Admin|Admin') --}}
            <li class="pt-4 pb-2 text-xs font-semibold uppercase text-bodytext/70">Gestion</li>
            
            <x-sidebar.item route="levels.index" icon="layers" label="Niveaux (Levels)" />
            <x-sidebar.item route="classrooms.index" icon="users" label="Classes" />
            <x-sidebar.item route="subjects.index" icon="book" label="Matières" />
            <x-sidebar.item route="rooms.index" icon="building" label="Salles" />
            <x-sidebar.item route="teachers.index" icon="chalkboard" label="Enseignants" />
            
            <li class="pt-4 pb-2 text-xs font-semibold uppercase text-bodytext/70">Planification</li>
            <x-sidebar.item route="subject-plans.index" icon="clipboard-list" label="Plan de Matières" />
            <x-sidebar.item route="teacher-subjects.index" icon="user-check" label="Affectations" />
            <x-sidebar.item route="sessions.index" icon="clock" label="Séances (Sessions)" />
            {{-- @endhasanyrole --}}
        </ul>
    </div>
</aside>

<!-- Mobile Overlay -->
<div 
    x-show="sidebarOpen" 
    @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-dark/50 lg:hidden"
    style="display: none;"
></div>
