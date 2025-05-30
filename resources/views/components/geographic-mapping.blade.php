@props(['locations' => []])

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Geographic Mapping</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Explore and visualize your family's geographic history.</p>
            </div>
            <div>
                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Location
                </button>
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <div class="flex space-x-4 mb-4">
                <div class="flex-1">
                    <label for="map_type" class="block text-sm font-medium text-gray-700">Map Type</label>
                    <select id="map_type" name="map_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm rounded-md">
                        <option value="roadmap">Road Map</option>
                        <option value="satellite">Satellite</option>
                        <option value="hybrid">Hybrid</option>
                        <option value="terrain">Terrain</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label for="time_period" class="block text-sm font-medium text-gray-700">Time Period</label>
                    <select id="time_period" name="time_period" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm rounded-md">
                        <option value="all">All Time</option>
                        <option value="1800s">1800s</option>
                        <option value="1900s">1900s</option>
                        <option value="2000s">2000s</option>
                    </select>
                </div>
            </div>
            <div id="map" class="h-[500px] rounded-lg overflow-hidden"></div>
        </div>
    </div>

    <!-- Location List -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Location History</h3>
            <div class="space-y-4">
                @foreach($locations as $location)
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-amber-500 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $location->name }}</p>
                        <p class="text-sm text-gray-500">{{ $location->address }}</p>
                        <p class="text-sm text-gray-500">{{ $location->date_range }}</p>
                        <div class="mt-2 flex space-x-2">
                            @foreach($location->related_people as $person)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                {{ $person->name }}
                            </span>
                            @endforeach
                        </div>
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

    <!-- Add Location Modal -->
    <div id="location-modal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Add Location
                        </h3>
                        <div class="mt-2">
                            <form action="{{ route('locations.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Location Name</label>
                                    <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                    <input type="text" name="address" id="address" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                        <input type="date" name="start_date" id="start_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                        <input type="date" name="end_date" id="end_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm"></textarea>
                                </div>
                                <div>
                                    <label for="related_people" class="block text-sm font-medium text-gray-700">Related People</label>
                                    <select name="related_people[]" id="related_people" multiple class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                        @foreach($familyMembers as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:col-start-2 sm:text-sm">
                                        Add Location
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
</div>

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap" async defer></script>
<script>
let map;
let markers = [];

function initMap() {
    // Initialize the map
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 0, lng: 0 },
        zoom: 2,
        mapTypeId: 'roadmap'
    });

    // Add markers for each location
    @foreach($locations as $location)
    addMarker({
        lat: {{ $location->latitude }},
        lng: {{ $location->longitude }},
        title: '{{ $location->name }}',
        content: `
            <div class="p-2">
                <h3 class="font-medium">{{ $location->name }}</h3>
                <p class="text-sm text-gray-500">{{ $location->address }}</p>
                <p class="text-sm text-gray-500">{{ $location->date_range }}</p>
            </div>
        `
    });
    @endforeach

    // Handle map type changes
    document.getElementById('map_type').addEventListener('change', (e) => {
        map.setMapTypeId(e.target.value);
    });

    // Handle time period changes
    document.getElementById('time_period').addEventListener('change', (e) => {
        // Implement time period filtering logic here
    });
}

function addMarker({ lat, lng, title, content }) {
    const marker = new google.maps.Marker({
        position: { lat, lng },
        map,
        title,
        animation: google.maps.Animation.DROP
    });

    const infoWindow = new google.maps.InfoWindow({
        content
    });

    marker.addListener('click', () => {
        infoWindow.open(map, marker);
    });

    markers.push(marker);
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle location modal
    const newLocationButton = document.querySelector('button[type="button"]');
    const locationModal = document.getElementById('location-modal');
    const cancelButton = locationModal.querySelector('button[type="button"]');

    newLocationButton.addEventListener('click', () => {
        locationModal.classList.remove('hidden');
    });

    cancelButton.addEventListener('click', () => {
        locationModal.classList.add('hidden');
    });

    // Initialize select2 for related people
    if (typeof $.fn.select2 !== 'undefined') {
        $('#related_people').select2({
            placeholder: 'Select related people',
            allowClear: true,
            theme: 'tailwind'
        });
    }

    // Initialize Google Places Autocomplete for address input
    if (typeof google !== 'undefined' && google.maps && google.maps.places) {
        const addressInput = document.getElementById('address');
        const autocomplete = new google.maps.places.Autocomplete(addressInput);
        
        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (place.geometry) {
                // Update map to show the selected location
                map.setCenter(place.geometry.location);
                map.setZoom(15);
            }
        });
    }
});
</script>
@endpush 