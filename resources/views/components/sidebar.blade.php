<aside 
    x-data
    x-modelable="open"
    :open="$store && $store.open !== undefined ? $store.open : open"
    class="flex flex-col h-screen bg-gray-900 text-white border-r border-gray-800 transition-all duration-200 relative"
    :class="{ 'w-64': open, 'w-20': !open }"
>
    <!-- Collapse/Expand Button -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-800">
        <span class="font-bold text-lg" x-show="open" @click="window.location.href = '{{ config('app.url') }}'; $dispatch('update:model', !open); open = !open">LEG</span>
        <button class="p-2 rounded hover:bg-gray-800">
            <svg x-show="open" class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <svg x-show="!open" class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto">
        <ul class="py-4 space-y-2">
            @include('components.sidebar-items', ['activeTab' => $activeTab ?? ''])
        </ul>
    </nav>
    <!-- Logout Button at Bottom -->
    <form method="POST" action="{{ route('logout') }}" class="mt-auto px-4 pb-6" x-data>
        @csrf
        <button type="submit" class="flex items-center w-full px-4 py-2 text-red-400 hover:bg-gray-800 rounded transition font-semibold">
            <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>
            <span x-show="open">Logout</span>
        </button>
    </form>
</aside> 