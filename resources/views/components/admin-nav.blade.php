<nav class="bg-gray-900 border-b border-gray-700 mb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <h1 class="text-white font-bold text-lg">Admin Panel</h1>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="{{ route('admin.users') }}" 
                           class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.users') ? 'bg-gray-700 text-white' : '' }}">
                            Users
                        </a>
                        <a href="{{ route('admin.logs') }}" 
                           class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.logs') ? 'bg-gray-700 text-white' : '' }}">
                            Logs
                        </a>
                        <a href="{{ route('admin.import-metrics') }}" 
                           class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.import-metrics') ? 'bg-gray-700 text-white' : '' }}">
                            Import Metrics
                        </a>
                        <a href="{{ route('admin.settings') }}" 
                           class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.settings') ? 'bg-gray-700 text-white' : '' }}">
                            Settings
                        </a>
                        <a href="{{ route('admin.notifications') }}" 
                           class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.notifications') ? 'bg-gray-700 text-white' : '' }}">
                            Notifications
                        </a>
                    </div>
                </div>
            </div>
            <div class="md:hidden">
                <button type="button" class="text-gray-300 hover:text-white focus:outline-none focus:text-white" onclick="toggleMobileMenu()">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile menu -->
    <div class="md:hidden hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <a href="{{ route('admin.users') }}" 
               class="text-gray-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.users') ? 'bg-gray-700 text-white' : '' }}">
                Users
            </a>
            <a href="{{ route('admin.logs') }}" 
               class="text-gray-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.logs') ? 'bg-gray-700 text-white' : '' }}">
                Logs
            </a>
            <a href="{{ route('admin.import-metrics') }}" 
               class="text-gray-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.import-metrics') ? 'bg-gray-700 text-white' : '' }}">
                Import Metrics
            </a>
            <a href="{{ route('admin.settings') }}" 
               class="text-gray-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.settings') ? 'bg-gray-700 text-white' : '' }}">
                Settings
            </a>
            <a href="{{ route('admin.notifications') }}" 
               class="text-gray-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.notifications') ? 'bg-gray-700 text-white' : '' }}">
                Notifications
            </a>
        </div>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    mobileMenu.classList.toggle('hidden');
}
</script> 