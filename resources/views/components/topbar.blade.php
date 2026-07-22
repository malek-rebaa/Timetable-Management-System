<header class="sticky top-0 z-30 flex items-center justify-between px-6 py-4 bg-white dark:bg-darkgray border-b border-border dark:border-darkborder h-16">
    <div class="flex items-center">
        <!-- Mobile sidebar toggle -->
        <button @click="sidebarOpen = true" class="text-bodytext focus:outline-none lg:hidden mr-4 hover:text-primary">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

    <div class="flex items-center gap-4">
        <!-- User Profile Dropdown (Alpine.js) -->
        <div x-data="{ dropdownOpen: false }" class="relative">
            <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-3 focus:outline-none">
                <div class="hidden md:flex flex-col text-right">
                    <span class="text-sm font-semibold text-dark dark:text-white">{{ Auth::user()->name ?? 'Utilisateur' }}</span>
                    <span class="text-xs text-bodytext">{{ Auth::user()->role ?? 'Admin' }}</span>
                </div>
                <div class="w-10 h-10 rounded-full bg-lightprimary flex items-center justify-center text-primary font-bold">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
            </button>

            <!-- Dropdown Menu -->
            <div 
                x-show="dropdownOpen" 
                @click.away="dropdownOpen = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 w-48 mt-2 py-2 bg-white dark:bg-dark border border-border dark:border-darkborder rounded-md shadow-lg z-50"
                style="display: none;"
            >
                <a href="#" class="block px-4 py-2 text-sm text-dark dark:text-white hover:bg-lightgray dark:hover:bg-darkmuted">Mon Profil</a>
                <a href="#" class="block px-4 py-2 text-sm text-dark dark:text-white hover:bg-lightgray dark:hover:bg-darkmuted">Changer mot de passe</a>
                
                <div class="border-t border-border dark:border-darkborder my-1"></div>
                
                <form method="POST" action="{{ route('logout') ?? '#' }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-error hover:bg-lighterror dark:hover:bg-darkmuted">
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
