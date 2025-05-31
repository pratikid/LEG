<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family History Hub - Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-white min-h-screen">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <div x-data="{ open: false }" class="md:flex">
        <aside :class="{'block': open, 'hidden': !open}" class="w-64 bg-gray-800 flex flex-col py-8 px-4 space-y-4 md:block md:relative md:translate-x-0 fixed z-30 inset-y-0 left-0 transform -translate-x-full transition-transform duration-200 ease-in-out">
            <div class="flex items-center mb-8">
                <span class="text-xl font-bold">Family History Hub</span>
                <button @click="open = false" class="ml-auto md:hidden text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <nav class="flex-1 space-y-2">
                <a href="{{ route('trees.index') }}" class="flex items-center px-4 py-2 rounded-lg bg-gray-700 text-white font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    My Tree
                </a>
                <a href="#" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h8"/></svg>
                    Discover
                </a>
                <a href="{{ route('timeline.index') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Research
                </a>
                <a href="#" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                    DNA
                </a>
            </nav>
        </aside>
        <button @click="open = true" class="md:hidden fixed top-4 left-4 z-40 bg-gray-800 p-2 rounded text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
    </div>
    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Top Nav -->
        <header class="flex items-center justify-between px-8 py-4 border-b border-gray-700">
            <nav class="flex space-x-8">
                <a href="{{ route('trees.index') }}" class="text-white font-medium hover:text-blue-400">My Tree</a>
                <a href="#" class="text-white font-medium hover:text-blue-400">Discover</a>
                <a href="{{ route('timeline.index') }}" class="text-white font-medium hover:text-blue-400">Research</a>
                <a href="#" class="text-white font-medium hover:text-blue-400">DNA</a>
            </nav>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input type="text" placeholder="Search" class="bg-gray-800 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <span class="absolute right-3 top-2.5 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                </div>
                <button class="relative">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </button>
                <img src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" alt="User avatar" class="w-9 h-9 rounded-full border-2 border-gray-700" />
            </div>
        </header>
        <!-- Content -->
        <main class="flex-1 p-10">
            <h1 class="text-3xl font-bold mb-6">
                Welcome back, {{ optional(Auth::user())->name ?? 'User' }}
            </h1>
            <!-- Recent Activity -->
            <section class="mb-10">
                <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
                <div class="space-y-6">
                    @if(isset($activities) && $activities && count($activities))
                        @foreach($activities as $activity)
                            <div class="flex items-start space-x-4">
                                <span class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-700">{!! activityIcon($activity->action ?? '') !!}</span>
                                <div>
                                    <p>{{ $activity->action ?? 'Activity' }} @if(!empty($activity->model_type)) on {{ class_basename($activity->model_type) }}@endif</p>
                                    <span class="text-gray-400 text-sm">{{ optional($activity->created_at)->diffForHumans() ?? '-' }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-gray-400">No recent activity</div>
                    @endif
                </div>
            </section>
            <!-- Statistics -->
            <section class="mb-10">
                <h2 class="text-xl font-semibold mb-4">Family Tree Statistics</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-800 rounded-xl p-6 flex flex-col items-start">
                        <span class="text-gray-400 mb-2">Total Members</span>
                        <span class="text-3xl font-bold">{{ $totalMembers ?? 0 }}</span>
                    </div>
                    <div class="bg-gray-800 rounded-xl p-6 flex flex-col items-start">
                        <span class="text-gray-400 mb-2">Generations</span>
                        <span class="text-3xl font-bold">{{ $generations ?? '-' }}</span>
                    </div>
                    <div class="bg-gray-800 rounded-xl p-6 flex flex-col items-start">
                        <span class="text-gray-400 mb-2">Photos</span>
                        <span class="text-3xl font-bold">{{ $totalPhotos ?? 0 }}</span>
                    </div>
                </div>
            </section>
            <!-- User's Trees -->
            <section class="mb-10 mt-10">
                <h2 class="text-xl font-semibold mb-4">Your Trees</h2>
                <ul>
                    @if(isset($userTrees) && $userTrees && count($userTrees))
                        @foreach($userTrees as $tree)
                            <li><a href="{{ route('trees.show', $tree->id) }}" class="text-blue-400 hover:underline">{{ $tree->name ?? 'Unnamed Tree' }}</a></li>
                        @endforeach
                    @else
                        <li class="text-gray-400">No trees found.</li>
                    @endif
                </ul>
            </section>
            <!-- Recent Individuals -->
            <section class="mb-10">
                <h2 class="text-xl font-semibold mb-4">Recently Added Individuals</h2>
                <ul>
                    @if(isset($recentIndividuals) && $recentIndividuals && count($recentIndividuals))
                        @foreach($recentIndividuals as $individual)
                            <li><a href="{{ route('individuals.show', $individual->id) }}" class="text-blue-400 hover:underline">{{ $individual->first_name ?? '' }} {{ $individual->last_name ?? '' }}</a></li>
                        @endforeach
                    @else
                        <li class="text-gray-400">No recent individuals.</li>
                    @endif
                </ul>
            </section>
            <!-- Quick Actions -->
            <section class="flex flex-wrap items-center gap-4 justify-between mt-10">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                    <a href="{{ route('trees.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full">View My Tree</a>
                    <a href="{{ route('individuals.create') }}" class="ml-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-full">Add New Individual</a>
                    <a href="{{ route('trees.create') }}" class="ml-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-2 rounded-full">Start New Tree</a>
                </div>
                <a href="{{ route('timeline.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold px-6 py-2 rounded-full">Start Research</a>
            </section>
        </main>
    </div>
</div>
</body>
</html> 