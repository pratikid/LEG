<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
<body class="font-sans antialiased bg-gray-50">
    <div class="flex min-h-screen">
        <x-sidebar :activeTab="$activeTab ?? ''" />
        <div class="flex-1 min-h-screen flex flex-col">
            <!-- Top Navigation (optional) -->
            @isset($topbar)
                {{ $topbar }}
            @endisset
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