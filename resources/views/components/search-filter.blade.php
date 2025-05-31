@props(['filters' => []])

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Search & Filter</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Find individuals and families in your tree.</p>
    </div>

    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
        <form action="{{ route('search') }}" method="GET" class="space-y-6">
            <!-- Search Bar -->
            <div>
                <label for="search" class="sr-only">Search</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" class="focus:ring-amber-500 focus:border-amber-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="Search by name, date, location...">
                </div>
            </div>

            <!-- Advanced Filters -->
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <!-- Date Range -->
                <div class="sm:col-span-3">
                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <div class="mt-1">
                        <input type="date" name="date_from" id="date_from" class="shadow-sm focus:ring-amber-500 focus:border-amber-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <div class="mt-1">
                        <input type="date" name="date_to" id="date_to" class="shadow-sm focus:ring-amber-500 focus:border-amber-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

                <!-- Location -->
                <div class="sm:col-span-3">
                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                    <div class="mt-1">
                        <input type="text" name="location" id="location" class="shadow-sm focus:ring-amber-500 focus:border-amber-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="City, State, Country">
                    </div>
                </div>

                <!-- Event Type -->
                <div class="sm:col-span-3">
                    <label for="event_type" class="block text-sm font-medium text-gray-700">Event Type</label>
                    <div class="mt-1">
                        <select id="event_type" name="event_type" class="shadow-sm focus:ring-amber-500 focus:border-amber-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Any</option>
                            <option value="birth">Birth</option>
                            <option value="death">Death</option>
                            <option value="marriage">Marriage</option>
                            <option value="immigration">Immigration</option>
                            <option value="military">Military Service</option>
                        </select>
                    </div>
                </div>

                <!-- Relationship -->
                <div class="sm:col-span-3">
                    <label for="relationship" class="block text-sm font-medium text-gray-700">Relationship</label>
                    <div class="mt-1">
                        <select id="relationship" name="relationship" class="shadow-sm focus:ring-amber-500 focus:border-amber-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Any</option>
                            <option value="parent">Parent</option>
                            <option value="child">Child</option>
                            <option value="spouse">Spouse</option>
                            <option value="sibling">Sibling</option>
                        </select>
                    </div>
                </div>

                <!-- Source Type -->
                <div class="sm:col-span-3">
                    <label for="source_type" class="block text-sm font-medium text-gray-700">Source Type</label>
                    <div class="mt-1">
                        <select id="source_type" name="source_type" class="shadow-sm focus:ring-amber-500 focus:border-amber-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Any</option>
                            <option value="birth_certificate">Birth Certificate</option>
                            <option value="death_certificate">Death Certificate</option>
                            <option value="marriage_certificate">Marriage Certificate</option>
                            <option value="census">Census Record</option>
                            <option value="immigration">Immigration Record</option>
                            <option value="military">Military Record</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Filter Tags -->
            <div class="flex flex-wrap gap-2">
                @foreach($filters as $key => $value)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                    {{ $key }}: {{ $value }}
                    <button type="button" class="ml-1 inline-flex items-center p-0.5 rounded-full text-amber-400 hover:bg-amber-200 hover:text-amber-500 focus:outline-none focus:bg-amber-500 focus:text-white">
                        <span class="sr-only">Remove filter</span>
                        <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                            <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                        </svg>
                    </button>
                </span>
                @endforeach
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <button type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    Reset
                </button>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Search Results -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h4 class="text-lg font-medium text-gray-900">Search Results</h4>
                <div class="mt-2 flex space-x-2">
                    <button class="result-tab px-3 py-1 rounded bg-amber-600 text-white" data-type="individuals">Individuals</button>
                    <button class="result-tab px-3 py-1 rounded bg-gray-200 text-gray-700" data-type="trees">Trees</button>
                    <button class="result-tab px-3 py-1 rounded bg-gray-200 text-gray-700" data-type="events">Events</button>
                    <button class="result-tab px-3 py-1 rounded bg-gray-200 text-gray-700" data-type="sources">Sources</button>
                </div>
            </div>
            <div class="flex space-x-2">
                <button class="px-3 py-2 bg-amber-500 text-white rounded hover:bg-amber-600">Export Results</button>
                <button class="px-3 py-2 bg-white border border-gray-300 rounded text-gray-700 hover:bg-gray-50">Save Search</button>
            </div>
        </div>
        <div class="border-t border-gray-200">
            <ul role="list" class="divide-y divide-gray-200">
                @foreach($results ?? [] as $result)
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img class="h-12 w-12 rounded-full" src="{{ $result->profile_photo_url }}" alt="{{ $result->name }}">
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $result->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $result->birth_date }} - {{ $result->death_date ?? 'Present' }}</p>
                                </div>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex">
                                <a href="{{ route('individuals.show', $result) }}" class="font-medium text-amber-600 hover:text-amber-500">View Profile</a>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    {{ $result->location }}
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <p>
                                    {{ $result->event_type }}
                                </p>
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle filter tag removal
    document.querySelectorAll('.filter-tag button').forEach(button => {
        button.addEventListener('click', function() {
            const tag = this.parentElement;
            const filterName = tag.dataset.filter;
            const input = document.querySelector(`[name="${filterName}"]`);
            if (input) {
                input.value = '';
            }
            tag.remove();
        });
    });

    // Handle reset button
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
        document.querySelectorAll('.filter-tag').forEach(tag => tag.remove());
        document.querySelector('form').reset();
    });
});
</script>
@endpush 