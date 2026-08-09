<header class="sticky top-0 z-999 flex w-full bg-white shadow-theme-xs dark:bg-gray-900">
    <div class="flex flex-grow items-center justify-between px-4 py-4 shadow-2 md:px-6 2xl:px-11">
        
        <!-- Hamburger Menu (Mobile) -->
        <div class="flex items-center gap-2 sm:gap-4 lg:hidden">
            <button class="z-99999 block rounded-sm bg-white p-1.5 shadow-sm dark:bg-gray-800" @click.stop="sidebarOpen = !sidebarOpen">
                <i data-lucide="menu" class="w-5 h-5 text-gray-500"></i>
            </button>
        </div>

        <div class="hidden sm:block">
            <span class="text-sm text-gray-500 font-medium">{{ now()->translatedFormat('l j F Y') }}</span>
        </div>

        <div class="flex items-center gap-3 2xsm:gap-7">
            <!-- User Profile Dropdown -->
            @auth
            <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                <button class="flex items-center gap-4" @click.prevent="dropdownOpen = !dropdownOpen">
                    <span class="hidden text-right lg:block">
                        <span class="block text-sm font-medium text-gray-800 dark:text-white">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                        <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                            @switch(auth()->user()->role)
                                @case('SUPER_ADMIN') Super Admin @break
                                @case('ADMIN') Admin @break
                                @case('TEACHER') Enseignant @break
                            @endswitch
                        </span>
                    </span>

                    <span class="h-10 w-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-lg">
                        {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
                    </span>

                    <i data-lucide="chevron-down" class="hidden w-4 h-4 sm:block text-gray-500"></i>
                </button>

                <!-- Dropdown -->
                <div x-show="dropdownOpen"
                     x-transition
                     style="display: none;"
                     class="absolute right-0 mt-4 flex w-56 flex-col rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                    <ul class="flex flex-col gap-1 border-b border-gray-200 p-2 dark:border-gray-800">
                        <li>
                            <a href="{{ route('profile.password') }}" class="menu-dropdown-item menu-dropdown-item-inactive">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                                Mot de passe
                            </a>
                        </li>
                    </ul>
                    <div class="p-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full menu-dropdown-item menu-dropdown-item-inactive text-error-600 hover:bg-error-50 dark:hover:bg-error-500/10">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </div>
</header>
