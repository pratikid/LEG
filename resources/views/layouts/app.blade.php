<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LEG') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-900 text-white">
    <div x-data="{ open: true }" class="flex min-h-screen">
        <x-sidebar :activeTab="$activeTab ?? ''" x-model="open" />
        <div class="flex-1 min-h-screen flex flex-col transition-all duration-200">
            <!-- Top Navigation -->
            <nav class="w-full flex items-center justify-between px-8 py-3 bg-gray-900 border-b border-gray-700">
                <div class="text-xl font-bold">Family History Hub</div>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <input type="text" placeholder="Search" class="bg-gray-800 text-gray-200 rounded-lg px-4 py-2 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <span class="absolute left-3 top-2.5 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                    </div>
                    <button class="relative p-2 rounded-full bg-gray-800 hover:bg-gray-700">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </button>
                    <img src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" alt="User avatar" class="w-9 h-9 rounded-full border-2 border-gray-700" />
                </div>
            </nav>
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>
    @livewireScripts
    <script src="//unpkg.com/alpinejs" defer></script>
    @stack('scripts')
</body>
</html> 