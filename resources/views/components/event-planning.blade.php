@props(['events' => []])

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Family Events</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Plan and manage family gatherings, reunions, and special occasions.</p>
            </div>
            <div>
                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Event
                </button>
            </div>
        </div>
    </div>

    <!-- Search/Filter Bar -->
    <div class="border-t border-gray-200 px-4 py-5 sm:px-6 flex justify-between items-center">
        <form method="GET" action="{{ route('events.index') }}" class="flex space-x-2">
            <input type="text" name="search" placeholder="Search events..." value="{{ request('search') }}" class="border border-gray-300 rounded px-2 py-1" />
            <select name="type" class="border border-gray-300 rounded px-2 py-1">
                <option value="">All Types</option>
                <option value="reunion">Reunion</option>
                <option value="birthday">Birthday</option>
                <option value="wedding">Wedding</option>
                <option value="other">Other</option>
            </select>
            <button type="submit" class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Filter</button>
        </form>
        <button class="px-3 py-2 bg-amber-500 text-white rounded hover:bg-amber-600">Export Calendar</button>
    </div>

    <!-- Calendar View -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <button type="button" class="p-2 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-900 mx-4">January 2024</h2>
                    <button type="button" class="p-2 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center space-x-4">
                    <button type="button" class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        Month
                    </button>
                    <button type="button" class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        Week
                    </button>
                    <button type="button" class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        Day
                    </button>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="grid grid-cols-7 gap-px bg-gray-200">
                <!-- Calendar Header -->
                <div class="bg-gray-50 py-2 text-center text-sm font-semibold text-gray-700">Sun</div>
                <div class="bg-gray-50 py-2 text-center text-sm font-semibold text-gray-700">Mon</div>
                <div class="bg-gray-50 py-2 text-center text-sm font-semibold text-gray-700">Tue</div>
                <div class="bg-gray-50 py-2 text-center text-sm font-semibold text-gray-700">Wed</div>
                <div class="bg-gray-50 py-2 text-center text-sm font-semibold text-gray-700">Thu</div>
                <div class="bg-gray-50 py-2 text-center text-sm font-semibold text-gray-700">Fri</div>
                <div class="bg-gray-50 py-2 text-center text-sm font-semibold text-gray-700">Sat</div>

                <!-- Calendar Days -->
                @for($i = 1; $i <= 31; $i++)
                <div class="bg-white min-h-[100px] p-2">
                    <div class="text-sm text-gray-500">{{ $i }}</div>
                    @foreach($events as $event)
                        @if($event->date->day === $i)
                        <div class="mt-1 p-1 text-xs rounded bg-amber-100 text-amber-800">
                            {{ $event->title }}
                        </div>
                        @endif
                    @endforeach
                </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Events</h3>
            <div class="space-y-4">
                @foreach($events as $event)
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-amber-500 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $event->title }}</p>
                        <p class="text-sm text-gray-500">{{ $event->date->format('F j, Y') }} at {{ $event->time }}</p>
                        <p class="text-sm text-gray-500">{{ $event->location }}</p>
                    </div>
                    <div class="flex-shrink-0">
                        <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- New Event Modal -->
    <div id="event-modal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Create New Event
                        </h3>
                        <div class="mt-2">
                            <form action="{{ route('events.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Event Title</label>
                                    <input type="text" name="title" id="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm"></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                                        <input type="date" name="date" id="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="time" class="block text-sm font-medium text-gray-700">Time</label>
                                        <input type="time" name="time" id="time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                    <input type="text" name="location" id="location" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="attendees" class="block text-sm font-medium text-gray-700">Attendees & RSVP</label>
                                    <div class="space-y-2">
                                        @foreach($familyMembers as $member)
                                        <div class="flex items-center space-x-2">
                                            <input type="checkbox" name="attendees[]" value="{{ $member->id }}" id="attendee-{{ $member->id }}" class="rounded border-gray-300">
                                            <label for="attendee-{{ $member->id }}" class="text-sm text-gray-700">{{ $member->name }}</label>
                                            <select name="rsvp[{{ $member->id }}]" class="ml-2 border border-gray-300 rounded px-2 py-1 text-sm">
                                                <option value="pending">Pending</option>
                                                <option value="accepted">Accepted</option>
                                                <option value="declined">Declined</option>
                                            </select>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:col-start-2 sm:text-sm">
                                        Create Event
                                    </button>
                                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Detail Modal -->
    <div id="event-detail-modal" class="hidden fixed z-20 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                <div id="event-detail-content">
                    <!-- Populated dynamically -->
                </div>
                <div class="mt-5 sm:mt-6">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:text-sm" id="close-event-detail-modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle event modal
    const newEventButton = document.querySelector('button[type="button"]');
    const eventModal = document.getElementById('event-modal');
    const cancelButton = eventModal.querySelector('button[type="button"]');

    newEventButton.addEventListener('click', () => {
        eventModal.classList.remove('hidden');
    });

    cancelButton.addEventListener('click', () => {
        eventModal.classList.add('hidden');
    });

    // Initialize select2 for attendees
    if (typeof $.fn.select2 !== 'undefined') {
        $('#attendees').select2({
            placeholder: 'Select attendees',
            allowClear: true,
            theme: 'tailwind'
        });
    }

    // Handle calendar navigation
    const prevButton = document.querySelector('button svg[stroke-linecap="round"]:first-of-type').parentElement;
    const nextButton = document.querySelector('button svg[stroke-linecap="round"]:last-of-type').parentElement;
    const monthTitle = document.querySelector('h2.text-lg');

    let currentDate = new Date();
    
    function updateCalendar() {
        const monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"];
        monthTitle.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
        // Implement calendar update logic here
    }

    prevButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        updateCalendar();
    });

    nextButton.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        updateCalendar();
    });
});
</script>
@endpush 