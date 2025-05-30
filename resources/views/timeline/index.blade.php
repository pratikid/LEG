<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight" data-tutorial="timeline-header">
                {{ __('Timeline') }}
            </h2>
            <div class="flex space-x-4">
                @auth
                    <a href="{{ route('timeline.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700" data-tutorial="add-event">
                        Add Event
                    </a>
                    <a href="{{ route('timeline.reports.generate') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500" data-tutorial="generate-report">
                        Generate Report
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Login to Add Events
                    </a>
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filter Component -->
            <x-timeline-search />

            @auth
                <!-- Node Customization Component -->
                <x-node-customization />
            @endauth

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Timeline -->
                    <div class="flow-root" data-tutorial="event-list">
                        <ul role="list" class="-mb-8">
                            @forelse($events as $event)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                @php
                                                    $nodeColor = auth()->user()?->preferences['node_color'] ?? 'amber';
                                                    $nodeShape = auth()->user()?->preferences['node_shape'] ?? 'circle';
                                                    $nodeSize = auth()->user()?->preferences['node_size'] ?? 'medium';
                                                    
                                                    $shapeClasses = [
                                                        'circle' => 'rounded-full',
                                                        'square' => 'rounded',
                                                        'diamond' => 'rotate-45 rounded',
                                                    ];
                                                    
                                                    $sizeClasses = [
                                                        'small' => 'h-6 w-6',
                                                        'medium' => 'h-8 w-8',
                                                        'large' => 'h-10 w-10',
                                                    ];
                                                @endphp
                                                <span class="{{ $sizeClasses[$nodeSize] }} {{ $shapeClasses[$nodeShape] }} bg-{{ $nodeColor }}-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">
                                                        {{ $event->title }}
                                                        <span class="font-medium text-gray-900">{{ $event->event_type }}</span>
                                                    </p>
                                                    @if(!auth()->user() || (auth()->user()->preferences['show_dates'] ?? true))
                                                        <p class="mt-0.5 text-sm text-gray-500">
                                                            {{ $event->event_date->format('F j, Y') }}
                                                            @if($event->location && (!auth()->user() || (auth()->user()->preferences['show_location'] ?? true)))
                                                                at {{ $event->location }}
                                                            @endif
                                                        </p>
                                                    @endif
                                                    @if($event->description && (!auth()->user() || (auth()->user()->preferences['show_description'] ?? false)))
                                                        <p class="mt-2 text-sm text-gray-700">{{ $event->description }}</p>
                                                    @endif
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    @auth
                                                        @if(auth()->id() === $event->user_id)
                                                            <a href="{{ route('timeline.edit', $event) }}" class="font-medium text-amber-600 hover:text-amber-500">Edit</a>
                                                            <form action="{{ route('timeline.destroy', $event) }}" method="POST" class="inline-block ml-2">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="font-medium text-red-600 hover:text-red-500" onclick="return confirm('Are you sure you want to delete this event?')">Delete</button>
                                                            </form>
                                                        @endif
                                                        <a href="{{ route('timeline.reports.event', $event) }}" class="ml-2 font-medium text-green-600 hover:text-green-500">Report</a>
                                                    @endauth
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center text-gray-500 py-4">No events found.</li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @auth
        @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                window.tutorial = Alpine.data('tutorial', () => ({
                    ...tutorial
                }));
                
                // Start the timeline tutorial if it hasn't been completed
                if (!window.userPreferences?.completed_tutorials?.includes('timeline')) {
                    tutorial.startTutorial('timeline');
                }
            });
        </script>
        @endpush
    @endauth
</x-app-layout> 