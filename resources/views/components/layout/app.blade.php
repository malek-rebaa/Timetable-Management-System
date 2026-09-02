<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $activeSchool = app(\App\Multitenancy\CurrentTenant::class)->school();
        $primaryColor = $activeSchool?->primary_color;
        $secondaryColor = $activeSchool?->secondary_color;
        $primaryColor = is_string($primaryColor) && preg_match('/^#[0-9A-Fa-f]{6}$/', $primaryColor) ? $primaryColor : '#4F46E5';
        $secondaryColor = is_string($secondaryColor) && preg_match('/^#[0-9A-Fa-f]{6}$/', $secondaryColor) ? $secondaryColor : '#4338CA';
    @endphp

    <title>{{ $activeSchool?->name ?? config('app.name', 'EduTime') }}</title>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-brand-25: color-mix(in srgb, {{ $primaryColor }} 4%, white);
            --color-brand-50: color-mix(in srgb, {{ $primaryColor }} 10%, white);
            --color-brand-100: color-mix(in srgb, {{ $primaryColor }} 20%, white);
            --color-brand-200: color-mix(in srgb, {{ $primaryColor }} 35%, white);
            --color-brand-300: color-mix(in srgb, {{ $primaryColor }} 55%, white);
            --color-brand-400: color-mix(in srgb, {{ $primaryColor }} 75%, white);
            --color-brand-500: {{ $primaryColor }};
            --color-brand-600: {{ $secondaryColor }};
            --color-brand-700: color-mix(in srgb, {{ $secondaryColor }} 85%, black);
            --color-brand-800: color-mix(in srgb, {{ $secondaryColor }} 70%, black);
            --color-brand-900: color-mix(in srgb, {{ $secondaryColor }} 55%, black);
        }
    </style>
    
    <!-- Alpine.js via CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons via CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar component -->
        <x-layout.sidebar />

        <!-- Main content area -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            
            <!-- Topbar component -->
            <x-layout.topbar />

            <!-- Main Page Content -->
            <main class="w-full max-w-7xl mx-auto p-4 md:p-6 2xl:p-10">
                {{ $slot }}
            </main>
            
        </div>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
        document.addEventListener('alpine:initialized', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
