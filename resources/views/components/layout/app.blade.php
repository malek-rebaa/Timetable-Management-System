<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Timetable App') }} - @yield('title', 'Dashboard')</title>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-bodytext bg-lightgray dark:bg-dark">
    
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main Content -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            
            <!-- Topbar -->
            <x-topbar />

            <!-- Page Content -->
            <main class="w-full px-4 sm:px-6 lg:px-8 py-8 mx-auto max-w-7xl">
                <!-- Header slot (optional) -->
                @if (isset($header))
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-dark dark:text-white">{{ $header }}</h1>
                    </div>
                @endif
                
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
