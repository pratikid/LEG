@extends('layouts.app')
@section('content')
<div class="container mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-4">Individual Details (ID: {{ $individual->id ?? '-' }})</h1>
    <p>{{ $individual->first_name ?? '' }} {{ $individual->last_name ?? '' }}</p>
    <p class="text-gray-700 mb-2">Sex: <span class="font-semibold">{{ $individual->sex === 'M' ? 'Male' : ($individual->sex === 'F' ? 'Female' : 'Unknown') }}</span></p>

    @if(isset($error) && $error)
        <div class="text-red-500 text-xs mb-4">{{ $error }}</div>
    @endif

    <!-- Parent-Child Relationship Form -->
    <form id="parent-child-form" class="mb-4" @if(isset($error) && $error) style="pointer-events:none;opacity:0.5;" @endif>
        @csrf
        <input type="hidden" name="child_id" value="{{ $individual->id }}">
        <label for="parent_id" class="block mb-2">Add Parent:</label>
        <select name="parent_id" id="parent_id" class="bg-gray-800 text-white rounded p-2 w-full mb-2">
            <option value="">Select a parent</option>
            @foreach($allIndividuals as $person)
                @if($person->id !== $individual->id)
                    <option value="{{ $person->id }}">{{ $person->first_name }} {{ $person->last_name }}</option>
                @endif
            @endforeach
        </select>
        <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded">Add Parent</button>
    </form>

    <!-- Add Child Relationship Form -->
    <form id="add-child-form" class="mb-4" @if(isset($error) && $error) style="pointer-events:none;opacity:0.5;" @endif>
        @csrf
        <input type="hidden" name="parent_id" value="{{ $individual->id }}">
        <label for="child_id" class="block mb-2">Add Child:</label>
        <select name="child_id" id="child_id" class="bg-gray-800 text-white rounded p-2 w-full mb-2">
            <option value="">Select a child</option>
            @foreach($allIndividuals as $person)
                @if($person->id !== $individual->id)
                    <option value="{{ $person->id }}">{{ $person->first_name }} {{ $person->last_name }}</option>
                @endif
            @endforeach
        </select>
        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded">Add Child</button>
    </form>

    <!-- Spouse Relationship Form -->
    <form id="spouse-form" class="mb-4" @if(isset($error) && $error) style="pointer-events:none;opacity:0.5;" @endif>
        @csrf
        <input type="hidden" name="spouse_a_id" value="{{ $individual->id }}">
        <label for="spouse_b_id" class="block mb-2">Add Spouse:</label>
        <select name="spouse_b_id" id="spouse_b_id" class="bg-gray-800 text-white rounded p-2 w-full mb-2">
            <option value="">Select a spouse</option>
            @foreach($allIndividuals as $person)
                @if($person->id !== $individual->id)
                    <option value="{{ $person->id }}">{{ $person->first_name }} {{ $person->last_name }}</option>
                @endif
            @endforeach
        </select>
        <button type="submit" class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded">Add Spouse</button>
    </form>

    <!-- Add Sibling Relationship Form -->
    <form id="sibling-form" class="mb-4" @if(isset($error) && $error) style="pointer-events:none;opacity:0.5;" @endif>
        @csrf
        <input type="hidden" name="sibling_a_id" value="{{ $individual->id }}">
        <label for="sibling_b_id" class="block mb-2">Add Sibling:</label>
        <select name="sibling_b_id" id="sibling_b_id" class="bg-gray-800 text-white rounded p-2 w-full mb-2">
            <option value="">Select a sibling</option>
            @foreach($allIndividuals as $person)
                @if($person->id !== $individual->id)
                    <option value="{{ $person->id }}">{{ $person->first_name }} {{ $person->last_name }}</option>
                @endif
            @endforeach
        </select>
        <button type="submit" class="bg-yellow-700 hover:bg-yellow-800 text-white px-4 py-2 rounded">Add Sibling</button>
    </form>

    <!-- Shortest Path Query Form -->
    <div class="mb-4">
        <form id="shortest-path-form" class="mb-2 flex flex-col md:flex-row items-start md:items-end gap-2" @if(isset($error) && $error) style="pointer-events:none;opacity:0.5;" @endif>
            <div>
                <label for="target_individual_id" class="block mb-1">Find shortest path to:</label>
                <select id="target_individual_id" class="bg-gray-800 text-white rounded p-2 w-full">
                    <option value="">Select an individual</option>
                    @foreach($allIndividuals as $person)
                        @if($person->id !== $individual->id)
                            <option value="{{ $person->id }}">{{ $person->first_name }} {{ $person->last_name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label for="path_max_depth" class="block mb-1">Max Depth:</label>
                <input type="number" id="path_max_depth" class="bg-gray-800 text-white rounded p-2 w-24" value="10" min="1" max="20">
            </div>
            <button type="submit" class="bg-cyan-700 hover:bg-cyan-800 text-white px-4 py-2 rounded">Find Path</button>
        </form>
        <div id="shortest-path-loading" class="text-gray-400 hidden">Loading...</div>
        <div id="shortest-path-result" class="mt-2"></div>
    </div>

    <!-- Display Relationships -->
    <div class="mb-4">
        <h3 class="font-bold mb-2">Parents:</h3>
        <ul id="parents-list"></ul>
        <template id="parent-item-template">
            <li class="flex items-center justify-between">
                <span class="parent-name"></span>
                <form method="POST" class="ml-2 remove-parent-form">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="parent_id" class="parent-id-input">
                    <input type="hidden" name="child_id" value="{{ $individual->id }}">
                    <button type="submit" class="text-red-500 hover:underline text-xs">Remove</button>
                </form>
            </li>
        </template>
    </div>
    <div class="mb-4">
        <h3 class="font-bold mb-2">Children:</h3>
        <ul id="children-list"></ul>
        <template id="child-item-template">
            <li class="flex items-center justify-between">
                <span class="child-name"></span>
                <form method="POST" class="ml-2 remove-child-form">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="parent_id" value="{{ $individual->id }}">
                    <input type="hidden" name="child_id" class="child-id-input">
                    <button type="submit" class="text-red-500 hover:underline text-xs">Remove</button>
                </form>
            </li>
        </template>
    </div>
    <div class="mb-4">
        <h3 class="font-bold mb-2">Spouses:</h3>
        <ul id="spouses-list"></ul>
        <template id="spouse-item-template">
            <li class="flex items-center justify-between">
                <span class="spouse-name"></span>
                <form method="POST" class="ml-2 remove-spouse-form">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="spouse_a_id" value="{{ $individual->id }}">
                    <input type="hidden" name="spouse_b_id" class="spouse-b-id-input">
                    <button type="submit" class="text-red-500 hover:underline text-xs">Remove</button>
                </form>
            </li>
        </template>
    </div>
    <div class="mb-4">
        <h3 class="font-bold mb-2">Ancestors:</h3>
        <label for="ancestors-limit" class="text-sm mr-2">Show:</label>
        <select id="ancestors-limit" class="bg-gray-800 text-white rounded p-1 mb-2">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
        </select>
        <div id="ancestors-loading" class="text-gray-400">Loading...</div>
        <ul id="ancestors-list"></ul>
        <div id="ancestors-truncated" class="text-xs text-yellow-400"></div>
    </div>
    <div class="mb-4">
        <h3 class="font-bold mb-2">Descendants:</h3>
        <label for="descendants-limit" class="text-sm mr-2">Show:</label>
        <select id="descendants-limit" class="bg-gray-800 text-white rounded p-1 mb-2">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
        </select>
        <div id="descendants-loading" class="text-gray-400">Loading...</div>
        <ul id="descendants-list"></ul>
        <div id="descendants-truncated" class="text-xs text-yellow-400"></div>
    </div>
    <div class="mb-4">
        <h3 class="font-bold mb-2">Siblings:</h3>
        <label for="siblings-limit" class="text-sm mr-2">Show:</label>
        <select id="siblings-limit" class="bg-gray-800 text-white rounded p-1 mb-2">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
        </select>
        <div id="siblings-loading" class="text-gray-400">Loading...</div>
        <ul id="siblings-list"></ul>
        <div id="siblings-truncated" class="text-xs text-yellow-400"></div>
        <template id="sibling-item-template">
            <li class="flex items-center justify-between">
                <span class="sibling-name"></span>
                <form method="POST" class="ml-2 remove-sibling-form">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="sibling_a_id" value="{{ $individual->id }}">
                    <input type="hidden" name="sibling_b_id" class="sibling-b-id-input">
                    <button type="submit" class="text-red-500 hover:underline text-xs">Remove</button>
                </form>
            </li>
        </template>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Function to handle form submissions
        function handleFormSubmit(formId, endpoint, successCallback) {
            const form = document.getElementById(formId);
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                const submitButton = form.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                
                try {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Adding...';
                    
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        successCallback(data);
                        form.reset();
                        // Show success message
                        const successMessage = document.createElement('div');
                        successMessage.className = 'text-green-500 text-sm mt-2';
                        successMessage.textContent = 'Relationship added successfully!';
                        form.appendChild(successMessage);
                        setTimeout(() => successMessage.remove(), 3000);
                    } else {
                        throw new Error(data.message || 'Failed to add relationship');
                    }
                } catch (error) {
                    // Show error message
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'text-red-500 text-sm mt-2';
                    errorMessage.textContent = error.message;
                    form.appendChild(errorMessage);
                    setTimeout(() => errorMessage.remove(), 3000);
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            });
        }

        // Function to refresh relationship lists
        function refreshRelationshipList(endpoint, listId, templateId, removeEndpoint) {
            fetch(endpoint)
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    const list = document.getElementById(listId);
                    list.innerHTML = '';
                    const template = document.getElementById(templateId);

                    if (!data || data.length === 0) {
                        list.innerHTML = '<li class="text-gray-400">None found.</li>';
                        return;
                    }

                    data.forEach(item => {
                        if (!item || !item.properties) {
                            console.warn('Invalid item data:', item);
                            return;
                        }

                        const li = template.content.cloneNode(true);
                        const classMap = {
                            'parents-list': 'parent',
                            'children-list': 'child',
                            'spouses-list': 'spouse',
                            'siblings-list': 'sibling'
                        };
                        const classPrefix = classMap[listId];

                        let idInputClass = `${classPrefix}-id-input`;
                        if (listId === 'spouses-list') idInputClass = 'spouse-b-id-input';
                        if (listId === 'siblings-list') idInputClass = 'sibling-b-id-input';

                        const nameSpan = li.querySelector(`.${classPrefix}-name`);
                        const idInput = li.querySelector(`.${idInputClass}`);
                        const removeForm = li.querySelector(`.remove-${classPrefix}-form`);
                        
                        nameSpan.textContent = `${item.properties.first_name || ''} ${item.properties.last_name || ''}`;
                        idInput.value = item.properties.id;
                        removeForm.action = removeEndpoint;
                        list.appendChild(li);
                    });
                })
                .catch(error => {
                    console.error('Error loading relationships:', error);
                    document.getElementById(listId).innerHTML = `<li class="text-red-500">Failed to load ${listId.replace('-list', '')}. Please try again.</li>`;
                });
        }

        // Set up form handlers
        handleFormSubmit('parent-child-form', "{{ route('relationships.parent-child') }}", () => {
            refreshRelationshipList(
                "{{ route('relationships.parents', $individual->id) }}",
                'parents-list',
                'parent-item-template',
                "{{ route('relationships.remove-parent-child') }}"
            );
        });

        handleFormSubmit('add-child-form', "{{ route('relationships.parent-child') }}", () => {
            refreshRelationshipList(
                "{{ route('relationships.children', $individual->id) }}",
                'children-list',
                'child-item-template',
                "{{ route('relationships.remove-parent-child') }}"
            );
        });

        handleFormSubmit('spouse-form', "{{ route('relationships.spouse') }}", () => {
            refreshRelationshipList(
                "{{ route('relationships.spouses', $individual->id) }}",
                'spouses-list',
                'spouse-item-template',
                "{{ route('relationships.remove-spouse') }}"
            );
        });

        handleFormSubmit('sibling-form', "{{ route('relationships.sibling') }}", () => {
            refreshRelationshipList(
                "{{ route('relationships.siblings', $individual->id) }}",
                'siblings-list',
                'sibling-item-template',
                "{{ route('relationships.remove-sibling') }}"
            );
        });

        // Initial load of relationship lists
        refreshRelationshipList(
            "{{ route('relationships.parents', $individual->id) }}",
            'parents-list',
            'parent-item-template',
            "{{ route('relationships.remove-parent-child') }}"
        );
        refreshRelationshipList(
            "{{ route('relationships.children', $individual->id) }}",
            'children-list',
            'child-item-template',
            "{{ route('relationships.remove-parent-child') }}"
        );
        refreshRelationshipList(
            "{{ route('relationships.spouses', $individual->id) }}",
            'spouses-list',
            'spouse-item-template',
            "{{ route('relationships.remove-spouse') }}"
        );
        refreshRelationshipList(
            "{{ route('relationships.siblings', $individual->id) }}",
            'siblings-list',
            'sibling-item-template',
            "{{ route('relationships.remove-sibling') }}"
        );

        // Helper for fetching with limit
        function fetchWithLimit(endpoint, limit, loadingId, listId, truncatedId, label) {
            document.getElementById(loadingId).style.display = '';
            document.getElementById(listId).innerHTML = '';
            document.getElementById(truncatedId).innerHTML = '';
            
            fetch(endpoint + '?limit=' + limit)
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    document.getElementById(loadingId).style.display = 'none';
                    
                    if (!data || data.length === 0) {
                        document.getElementById(listId).innerHTML = '<li class="text-gray-400">None found.</li>';
                        return;
                    }

                    const items = data
                        .filter(x => x && x.properties)
                        .map(x => `<li>${x.properties.first_name || ''} ${x.properties.last_name || ''}</li>`)
                        .join('');
                    
                    document.getElementById(listId).innerHTML = items || '<li class="text-gray-400">None found.</li>';
                    
                    if (data.length == limit) {
                        document.getElementById(truncatedId).innerHTML = `Showing only the first ${limit} ${label.toLowerCase()} (may be truncated).`;
                    }
                })
                .catch(error => {
                    console.error('Error loading relationships:', error);
                    document.getElementById(loadingId).style.display = 'none';
                    document.getElementById(listId).innerHTML = `<li class="text-red-500">Failed to load ${label.toLowerCase()}. Please try again.</li>`;
                });
        }
        // Ancestors
        let ancestorsLimit = document.getElementById('ancestors-limit').value;
        fetchWithLimit("{{ route('relationships.ancestors', $individual->id) }}", ancestorsLimit, 'ancestors-loading', 'ancestors-list', 'ancestors-truncated', 'Ancestors');
        document.getElementById('ancestors-limit').addEventListener('change', function() {
            ancestorsLimit = this.value;
            fetchWithLimit("{{ route('relationships.ancestors', $individual->id) }}", ancestorsLimit, 'ancestors-loading', 'ancestors-list', 'ancestors-truncated', 'Ancestors');
        });
        // Descendants
        let descendantsLimit = document.getElementById('descendants-limit').value;
        fetchWithLimit("{{ route('relationships.descendants', $individual->id) }}", descendantsLimit, 'descendants-loading', 'descendants-list', 'descendants-truncated', 'Descendants');
        document.getElementById('descendants-limit').addEventListener('change', function() {
            descendantsLimit = this.value;
            fetchWithLimit("{{ route('relationships.descendants', $individual->id) }}", descendantsLimit, 'descendants-loading', 'descendants-list', 'descendants-truncated', 'Descendants');
        });
        // Siblings (with Remove button)
        function fetchSiblingsWithRemove(limit) {
            document.getElementById('siblings-loading').style.display = '';
            document.getElementById('siblings-list').innerHTML = '';
            document.getElementById('siblings-truncated').innerHTML = '';
            fetch("{{ route('relationships.siblings', $individual->id) }}?limit=" + limit)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('siblings-loading').style.display = 'none';
                    const list = document.getElementById('siblings-list');
                    list.innerHTML = '';
                    const template = document.getElementById('sibling-item-template');
                    if (data.length) {
                        data.forEach(s => {
                            const li = template.content.cloneNode(true);
                            li.querySelector('.sibling-name').textContent = `${s.properties.first_name} ${s.properties.last_name}`;
                            li.querySelector('.sibling-b-id-input').value = s.properties.id;
                            li.querySelector('.remove-sibling-form').action = "{{ route('relationships.remove-sibling') }}";
                            list.appendChild(li);
                        });
                        if (data.length == limit) {
                            document.getElementById('siblings-truncated').innerHTML = `Showing only the first ${limit} siblings (may be truncated).`;
                        }
                    } else {
                        list.innerHTML = '<li class="text-gray-400">None found.</li>';
                    }
                })
                .catch(() => {
                    document.getElementById('siblings-loading').style.display = 'none';
                    document.getElementById('siblings-list').innerHTML = '<li class="text-red-500">Failed to load siblings.</li>';
                });
        }
        let siblingsLimit = document.getElementById('siblings-limit').value;
        fetchSiblingsWithRemove(siblingsLimit);
        document.getElementById('siblings-limit').addEventListener('change', function() {
            siblingsLimit = this.value;
            fetchSiblingsWithRemove(siblingsLimit);
        });
        // Shortest Path Query
        document.getElementById('shortest-path-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const toId = document.getElementById('target_individual_id').value;
            const maxDepth = document.getElementById('path_max_depth').value;
            const loading = document.getElementById('shortest-path-loading');
            const resultDiv = document.getElementById('shortest-path-result');
            loading.classList.remove('hidden');
            resultDiv.innerHTML = '';
            fetch(`{{ url('/relationships') }}/{{ $individual->id }}/shortest-path/${toId}?maxDepth=${maxDepth}`)
                .then(res => res.json())
                .then(data => {
                    loading.classList.add('hidden');
                    if (!data.length) {
                        resultDiv.innerHTML = '<span class="text-gray-400">No path found.</span>';
                        return;
                    }
                    // Show the path as a sequence of names
                    const nodes = data[0].segments
                        ? [data[0].start, ...data[0].segments.map(seg => seg.end)]
                        : [];
                    if (nodes.length) {
                        resultDiv.innerHTML = nodes.map(n => `<span class='inline-block bg-gray-700 text-white px-2 py-1 rounded m-1'>${n.properties.first_name} ${n.properties.last_name}</span>`).join('<span class="mx-1">→</span>');
                    } else {
                        resultDiv.innerHTML = '<span class="text-gray-400">Path found, but could not parse nodes.</span>';
                    }
                })
                .catch(() => {
                    loading.classList.add('hidden');
                    resultDiv.innerHTML = '<span class="text-red-500">Failed to fetch path.</span>';
                });
        });
    });
    </script>
</div>
@endsection 