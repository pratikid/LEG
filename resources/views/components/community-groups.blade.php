@props(['groups', 'user'])

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Community & Groups</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Connect with other family researchers and join groups.</p>
            </div>
            <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                Create Group
            </button>
        </div>
    </div>

    <!-- My Groups -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">My Groups</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($groups as $group)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <img class="h-12 w-12 rounded-full" src="{{ $group->avatar_url }}" alt="{{ $group->name }}">
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">{{ $group->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $group->member_count }} members</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">{{ Str::limit($group->description, 100) }}</p>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    {{ $group->type }}
                                </span>
                                @if($group->is_private)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Private
                                </span>
                                @endif
                            </div>
                            <a href="{{ route('groups.show', $group) }}" class="text-sm font-medium text-amber-600 hover:text-amber-500">
                                View Group
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Discover Groups -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Discover Groups</h3>
            <div class="mb-4">
                <div class="flex space-x-4">
                    <div class="flex-1">
                        <input type="text" id="discover-group-search" name="discover-group-search" placeholder="Search groups..." class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                    </div>
                    <select id="discover-group-category" name="discover-group-category" class="block w-48 border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                        <option value="">All Categories</option>
                        <option value="genealogy">Genealogy</option>
                        <option value="dna">DNA Research</option>
                        <option value="history">Historical Research</option>
                        <option value="culture">Cultural Heritage</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($suggestedGroups as $group)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <img class="h-12 w-12 rounded-full" src="{{ $group->avatar_url }}" alt="{{ $group->name }}">
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">{{ $group->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $group->member_count }} members</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">{{ Str::limit($group->description, 100) }}</p>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    {{ $group->type }}
                                </span>
                                @if($group->is_private)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Private
                                </span>
                                @endif
                            </div>
                            <button type="button" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                                Join Group
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Create Group Modal -->
    <div id="createGroupModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Create New Group</h3>
                    <form action="{{ route('groups.store') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Group Name</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm"></textarea>
                        </div>
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="type" id="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                <option value="genealogy">Genealogy</option>
                                <option value="dna">DNA Research</option>
                                <option value="history">Historical Research</option>
                                <option value="culture">Cultural Heritage</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="is_private" id="is_private" class="focus:ring-amber-500 h-4 w-4 text-amber-600 border-gray-300 rounded">
                            <label for="is_private" class="ml-2 block text-sm text-gray-900">Make this group private</label>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:col-start-2 sm:text-sm">
                                Create Group
                            </button>
                            <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:mt-0 sm:col-start-1 sm:text-sm" onclick="closeModal()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle create group modal
    const createButton = document.querySelector('button:contains("Create Group")');
    const modal = document.getElementById('createGroupModal');

    createButton.addEventListener('click', () => {
        modal.classList.remove('hidden');
    });

    window.closeModal = function() {
        modal.classList.add('hidden');
    };

    // Handle group search
    const searchInput = document.querySelector('input[placeholder="Search groups..."]');
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const groups = document.querySelectorAll('.grid > div');
        
        groups.forEach(group => {
            const name = group.querySelector('h4').textContent.toLowerCase();
            const description = group.querySelector('p').textContent.toLowerCase();
            
            if (name.includes(searchTerm) || description.includes(searchTerm)) {
                group.style.display = '';
            } else {
                group.style.display = 'none';
            }
        });
    });

    // Handle category filter
    const categorySelect = document.querySelector('select');
    categorySelect.addEventListener('change', (e) => {
        const category = e.target.value.toLowerCase();
        const groups = document.querySelectorAll('.grid > div');
        
        groups.forEach(group => {
            const groupCategory = group.querySelector('span').textContent.toLowerCase();
            
            if (!category || groupCategory === category) {
                group.style.display = '';
            } else {
                group.style.display = 'none';
            }
        });
    });

    // Handle join group buttons
    const joinButtons = document.querySelectorAll('button:contains("Join Group")');
    joinButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const groupId = e.target.closest('div').dataset.groupId;
            
            fetch(`/groups/${groupId}/join`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    e.target.textContent = 'Joined';
                    e.target.disabled = true;
                    e.target.classList.remove('bg-amber-600', 'hover:bg-amber-700');
                    e.target.classList.add('bg-gray-400', 'cursor-not-allowed');
                }
            });
        });
    });
});
</script>
@endpush 