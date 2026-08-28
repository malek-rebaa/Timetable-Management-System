<aside 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="absolute left-0 top-0 z-9999 flex h-screen w-72.5 flex-col overflow-y-hidden bg-white shadow-theme-md duration-300 ease-linear dark:bg-gray-900 lg:static lg:translate-x-0"
    @click.outside="sidebarOpen = false"
>
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between gap-2 px-6 py-5.5 lg:py-6.5">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <i data-lucide="book-open-check" class="text-brand-500 w-8 h-8"></i>
            <span class="text-xl font-bold text-gray-800 dark:text-white">EduCentre</span>
        </a>

        <button class="block lg:hidden text-gray-500 hover:text-gray-700" @click.stop="sidebarOpen = !sidebarOpen">
            <i data-lucide="arrow-left" class="w-6 h-6"></i>
        </button>
    </div>

    <!-- Sidebar Menu -->
    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <nav class="mt-5 py-4 px-4 lg:mt-9 lg:px-6">

            @auth
            {{-- Super Admin / Admin Menu --}}
            @if(in_array(auth()->user()->role, ['SUPER_ADMIN', 'ADMIN']))
            <div class="mb-6">
                <h3 class="mb-4 ml-4 text-sm font-semibold text-gray-400 uppercase tracking-wider">Gestion</h3>
                
                <ul class="mb-6 flex flex-col gap-1.5">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('dashboard') }}" class="group menu-item {{ request()->routeIs('dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Emploi du temps -->
                    <li>
                        <a href="{{ route('timetable.index') }}" class="group menu-item {{ request()->routeIs('timetable.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i data-lucide="calendar-days" class="w-5 h-5 {{ request()->routeIs('timetable.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text">Emplois du Temps</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="mb-6">
                <h3 class="mb-4 ml-4 text-sm font-semibold text-gray-400 uppercase tracking-wider">Pédagogie</h3>
                
                <ul class="mb-6 flex flex-col gap-1.5">
                    <!-- Enseignants -->
                    <li>
                        <a href="{{ route('teachers.index') }}" class="group menu-item {{ request()->routeIs('teachers.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i data-lucide="users" class="w-5 h-5 {{ request()->routeIs('teachers.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text">Enseignants</span>
                        </a>
                    </li>
                    
                    <!-- Niveaux -->
                    <li>
                        <a href="{{ route('levels.index') }}" class="group menu-item {{ request()->routeIs('levels.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i data-lucide="layers" class="w-5 h-5 {{ request()->routeIs('levels.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text">Niveaux & Classes</span>
                        </a>
                    </li>
                    
                    <!-- Matières & Plan -->
                    <li>
                        <a href="{{ route('subjects.index') }}" class="group menu-item {{ request()->routeIs('subjects.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i data-lucide="book" class="w-5 h-5 {{ request()->routeIs('subjects.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text">Matières & Programme</span>
                        </a>
                    </li>

                    <!-- Salles -->
                    <li>
                        <a href="{{ route('rooms.index') }}" class="group menu-item {{ request()->routeIs('rooms.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i data-lucide="school" class="w-5 h-5 {{ request()->routeIs('rooms.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text">Salles</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endif
            
            {{-- Super Admin Only --}}
            @if(auth()->user()->role === 'SUPER_ADMIN')
            <div>
                <h3 class="mb-4 ml-4 text-sm font-semibold text-gray-400 uppercase tracking-wider">Administration</h3>
                <ul class="mb-6 flex flex-col gap-1.5">
                    <li>
                        <a href="{{ route('admins.index') }}" class="group menu-item {{ request()->routeIs('admins.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i data-lucide="shield" class="w-5 h-5 {{ request()->routeIs('admins.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text">Administrateurs</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            {{-- Teacher Menu --}}
            @if(auth()->user()->role === 'TEACHER')
            <div>
                <h3 class="mb-4 ml-4 text-sm font-semibold text-gray-400 uppercase tracking-wider">Mon Espace</h3>
                <ul class="mb-6 flex flex-col gap-1.5">
                    <li>
                        <a href="{{ route('teacher.timetable') }}" class="group menu-item {{ request()->routeIs('teacher.timetable') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <i data-lucide="calendar-check" class="w-5 h-5 {{ request()->routeIs('teacher.timetable') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"></i>
                            <span class="menu-item-text">Mon Emploi du Temps</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endif
            @endauth

        </nav>
    </div>
</aside>
