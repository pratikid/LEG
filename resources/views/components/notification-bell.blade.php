@php
    $unreadNotifications = auth()->user() ? auth()->user()->unreadNotifications()->take(5)->get() : collect();
@endphp

<div class="relative" x-data="{ open: false }">
    <!-- Notification Bell -->
    <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM10.5 3.75a6 6 0 0 1 6 6v4.5l2.25 2.25a.75.75 0 0 1-.75 1.25H3a.75.75 0 0 1-.75-.75V14.25a.75.75 0 0 1 1.25-.75L6 14.25V9.75a6 6 0 0 1 6-6Z"/>
        </svg>
        
        <!-- Notification Badge -->
        @if($unreadNotifications->count() > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                {{ $unreadNotifications->count() > 9 ? '9+' : $unreadNotifications->count() }}
            </span>
        @endif
    </button>

    <!-- Notification Dropdown -->
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
        
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
        </div>

        <div class="max-h-96 overflow-y-auto">
            @if($unreadNotifications->count() > 0)
                @foreach($unreadNotifications as $notification)
                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                         onclick="handleNotificationClick('{{ $notification->id }}', '{{ $notification->data['action_url'] ?? '' }}')">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                @if($notification->data['type'] === 'gedcom_import_completed')
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @elseif($notification->data['type'] === 'gedcom_import_failed')
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-2">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                                @if(isset($notification->data['action_text']) && isset($notification->data['action_url']))
                                    <p class="text-xs text-blue-600 mt-1 font-medium">
                                        {{ $notification->data['action_text'] }} →
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="p-4 text-center text-gray-500">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM10.5 3.75a6 6 0 0 1 6 6v4.5l2.25 2.25a.75.75 0 0 1-.75 1.25H3a.75.75 0 0 1-.75-.75V14.25a.75.75 0 0 1 1.25-.75L6 14.25V9.75a6 6 0 0 1 6-6Z"/>
                    </svg>
                    <p>No new notifications</p>
                </div>
            @endif
        </div>

        @if($unreadNotifications->count() > 0)
            <div class="p-4 border-t border-gray-200">
                <button onclick="markAllAsRead()" class="text-sm text-blue-600 hover:text-blue-800">
                    Mark all as read
                </button>
            </div>
        @endif
    </div>
</div>

<script>
function handleNotificationClick(notificationId, actionUrl) {
    // Mark notification as read
    fetch(`/notifications/${notificationId}/mark-as-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    });

    // Navigate to the action URL if provided
    if (actionUrl) {
        window.location.href = actionUrl;
    }
}

function markAllAsRead() {
    fetch('/notifications/mark-all-as-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    }).then(() => {
        window.location.reload();
    });
}
</script> 