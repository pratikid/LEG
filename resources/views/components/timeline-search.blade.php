<div class="bg-white p-4 rounded-lg shadow-sm mb-6">
    <form action="{{ route('timeline.index') }}" method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search Term -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                        class="block w-full rounded-md border-gray-300 pr-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                        placeholder="Search by title or description">
                </div>
            </div>

            <!-- Event Type -->
            <div>
                <label for="event_type" class="block text-sm font-medium text-gray-700">Event Type</label>
                <select name="event_type" id="event_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Types</option>
                    <option value="birth" {{ request('event_type') === 'birth' ? 'selected' : '' }}>Birth</option>
                    <option value="death" {{ request('event_type') === 'death' ? 'selected' : '' }}>Death</option>
                    <option value="marriage" {{ request('event_type') === 'marriage' ? 'selected' : '' }}>Marriage</option>
                    <option value="divorce" {{ request('event_type') === 'divorce' ? 'selected' : '' }}>Divorce</option>
                    <option value="other" {{ request('event_type') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-700">Date Range</label>
                <select name="date_range" id="date_range" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Time</option>
                    <option value="last_week" {{ request('date_range') === 'last_week' ? 'selected' : '' }}>Last Week</option>
                    <option value="last_month" {{ request('date_range') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="last_year" {{ request('date_range') === 'last_year' ? 'selected' : '' }}>Last Year</option>
                    <option value="custom" {{ request('date_range') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
        </div>

        <!-- Custom Date Range -->
        <div id="custom_date_range" class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ request('date_range') === 'custom' ? 'block' : 'hidden' }}">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>

        <!-- Additional Filters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Location -->
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                <input type="text" name="location" id="location" value="{{ request('location') }}" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                    placeholder="Filter by location">
            </div>

            <!-- Visibility -->
            <div>
                <label for="visibility" class="block text-sm font-medium text-gray-700">Visibility</label>
                <select name="visibility" id="visibility" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All</option>
                    <option value="public" {{ request('visibility') === 'public' ? 'selected' : '' }}>Public</option>
                    <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>Private</option>
                </select>
            </div>

            <!-- Sort -->
            <div>
                <label for="sort" class="block text-sm font-medium text-gray-700">Sort By</label>
                <select name="sort" id="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="date_desc" {{ request('sort') === 'date_desc' ? 'selected' : '' }}>Date (Newest First)</option>
                    <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Date (Oldest First)</option>
                    <option value="title_asc" {{ request('sort') === 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                    <option value="title_desc" {{ request('sort') === 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                    <option value="type_asc" {{ request('sort') === 'type_asc' ? 'selected' : '' }}>Type (A-Z)</option>
                    <option value="type_desc" {{ request('sort') === 'type_desc' ? 'selected' : '' }}>Type (Z-A)</option>
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('timeline.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                Reset
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Search
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateRangeSelect = document.getElementById('date_range');
        const customDateRange = document.getElementById('custom_date_range');

        dateRangeSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateRange.classList.remove('hidden');
                customDateRange.classList.add('block');
            } else {
                customDateRange.classList.add('hidden');
                customDateRange.classList.remove('block');
            }
        });
    });
</script>
@endpush 