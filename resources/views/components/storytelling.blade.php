@props(['stories' => []])

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Family Stories</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Share and preserve your family's history through stories and narratives.</p>
            </div>
            <div>
                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Story
                </button>
            </div>
        </div>
    </div>

    <!-- Search/Filter Bar -->
    <div class="border-t border-gray-200 px-4 py-5 sm:px-6 flex justify-between items-center">
        <form method="GET" action="{{ route('stories.index') }}" class="flex space-x-2">
            <input type="text" name="search" placeholder="Search stories..." value="{{ request('search') }}" class="border border-gray-300 rounded px-2 py-1" />
            <select name="tag" class="border border-gray-300 rounded px-2 py-1">
                <option value="">All Tags</option>
                @foreach($allTags ?? [] as $tag)
                    <option value="{{ $tag }}">{{ $tag }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Filter</button>
        </form>
    </div>

    <!-- Story Timeline -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($stories as $story)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full bg-amber-500 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            {{ $story->title }}
                                            <span class="font-medium text-gray-900">{{ $story->author }}</span>
                                        </p>
                                        <div class="mt-2 text-sm text-gray-700">
                                            <p>{{ $story->excerpt }}</p>
                                        </div>
                                        <div class="mt-2 flex space-x-2">
                                            @foreach($story->tags as $tag)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                {{ $tag }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                        <time datetime="{{ $story->created_at->format('Y-m-d') }}">{{ $story->created_at->format('M d, Y') }}</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- New Story Modal -->
    <div id="story-modal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Write a New Story
                        </h3>
                        <div class="mt-2">
                            <form action="{{ route('stories.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                    <input type="text" name="title" id="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="content" class="block text-sm font-medium text-gray-700">Story</label>
                                    <textarea name="content" id="content" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm"></textarea>
                                </div>
                                <div>
                                    <label for="tags" class="block text-sm font-medium text-gray-700">Tags</label>
                                    <input type="text" name="tags" id="tags" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm" placeholder="Separate tags with commas">
                                </div>
                                <div>
                                    <label for="related_people" class="block text-sm font-medium text-gray-700">Related People</label>
                                    <select name="related_people[]" id="related_people" multiple class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                        @foreach($relatedPeople as $person)
                                        <option value="{{ $person->id }}">{{ $person->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                                    <input type="date" name="date" id="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                </div>
                                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:col-start-2 sm:text-sm">
                                        Save Story
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle story modal
    const newStoryButton = document.querySelector('button[type="button"]');
    const storyModal = document.getElementById('story-modal');
    const cancelButton = storyModal.querySelector('button[type="button"]');

    newStoryButton.addEventListener('click', () => {
        storyModal.classList.remove('hidden');
    });

    cancelButton.addEventListener('click', () => {
        storyModal.classList.add('hidden');
    });

    // Initialize select2 for related people
    if (typeof $.fn.select2 !== 'undefined') {
        $('#related_people').select2({
            placeholder: 'Select related people',
            allowClear: true,
            theme: 'tailwind'
        });
    }
});
</script>
@endpush 