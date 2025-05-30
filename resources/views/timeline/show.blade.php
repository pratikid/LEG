<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $timelineEvent->title }}
            </h2>
            <div class="flex space-x-4">
                @can('update', $timelineEvent)
                    <a href="{{ route('timeline.edit', $timelineEvent) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700" data-tutorial="edit-event">
                        Edit Event
                    </a>
                @endcan
                @auth
                    <a href="{{ route('timeline.reports.event', $timelineEvent) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500" data-tutorial="event-report">
                        Generate Report
                    </a>
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6" data-tutorial="event-details">
                        <h3 class="text-lg font-semibold text-gray-900">Event Details</h3>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Event Type</p>
                                <p class="mt-1 text-sm text-gray-900">{{ ucfirst($timelineEvent->event_type) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Date</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $timelineEvent->event_date->format('F j, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Location</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $timelineEvent->location ?? 'Not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Visibility</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $timelineEvent->is_public ? 'Public' : 'Private' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900">Description</h3>
                        <div class="mt-4 prose max-w-none">
                            {{ $timelineEvent->description }}
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('timeline.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Back to Timeline
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            window.tutorial = Alpine.data('tutorial', () => ({
                ...tutorial
            }));
            
            // Start the event tutorial if it hasn't been completed
            if (!window.userPreferences?.completed_tutorials?.includes('event')) {
                tutorial.startTutorial('event');
            }
        });
    </script>
    @endpush
</x-app-layout> 