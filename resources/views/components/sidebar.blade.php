<aside 
    x-data="{ open: true }"
    class="flex flex-col h-screen bg-white border-r transition-all duration-200 fixed inset-y-0 left-0 z-30"
    :class="{ 'w-64': open, 'w-20': !open }"
>
    <!-- Collapse/Expand Button -->
    <div class="flex items-center justify-between h-16 px-4 border-b">
        <span class="font-bold text-lg" x-show="open">LEG</span>
        <button @click="open = !open" class="p-2 rounded hover:bg-gray-100">
            <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
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
        <button type="submit" class="flex items-center w-full px-4 py-2 text-red-600 hover:bg-red-50 rounded transition font-semibold">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>
            <span x-show="open">Logout</span>
        </button>
    </form>
</aside> 