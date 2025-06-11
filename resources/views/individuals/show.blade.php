@extends('layouts.app')
@section('content')
<div class="container mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-4">Individual Details (ID: {{ $individual->id ?? '-' }})</h1>
    <p>{{ $individual->first_name ?? '' }} {{ $individual->last_name ?? '' }}</p>

    @if(isset($error) && $error)
        <div class="text-red-500 text-xs mb-4">{{ $error }}</div>
    @endif

    <!-- Parent-Child Relationship Form -->
    <form method="POST" action="{{ route('relationships.parent-child') }}" class="mb-4" @if(isset($error) && $error) style="pointer-events:none;opacity:0.5;" @endif>
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
    <form method="POST" action="{{ route('relationships.parent-child') }}" class="mb-4" @if(isset($error) && $error) style="pointer-events:none;opacity:0.5;" @endif>
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
    <form method="POST" action="{{ route('relationships.spouse') }}" class="mb-4" @if(isset($error) && $error) style="pointer-events:none;opacity:0.5;" @endif>
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
    <form method="POST" action="{{ route('relationships.sibling') }}" class="mb-4" @if(isset($error) && $error) style="pointer-events:none;opacity:0.5;" @endif>
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
        // Parents (with Remove button)
        fetch("{{ route('relationships.parents', $individual->id) }}")
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('parents-list');
                list.innerHTML = '';
                const template = document.getElementById('parent-item-template');
                data.forEach(p => {
                    const li = template.content.cloneNode(true);
                    li.querySelector('.parent-name').textContent = `${p.properties.first_name} ${p.properties.last_name}`;
                    li.querySelector('.parent-id-input').value = p.properties.id;
                    li.querySelector('.remove-parent-form').action = "{{ route('relationships.remove-parent-child') }}";
                    list.appendChild(li);
                });
            })
            .catch(() => {
                document.getElementById('parents-list').innerHTML = '<li class="text-red-500">Failed to load parents.</li>';
            });
        // Children (with Remove button)
        fetch("{{ route('relationships.children', $individual->id) }}")
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('children-list');
                list.innerHTML = '';
                const template = document.getElementById('child-item-template');
                data.forEach(c => {
                    const li = template.content.cloneNode(true);
                    li.querySelector('.child-name').textContent = `${c.properties.first_name} ${c.properties.last_name}`;
                    li.querySelector('.child-id-input').value = c.properties.id;
                    li.querySelector('.remove-child-form').action = "{{ route('relationships.remove-parent-child') }}";
                    list.appendChild(li);
                });
            })
            .catch(() => {
                document.getElementById('children-list').innerHTML = '<li class="text-red-500">Failed to load children.</li>';
            });
        // Spouses (with Remove button)
        fetch("{{ route('relationships.spouses', $individual->id) }}")
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('spouses-list');
                list.innerHTML = '';
                const template = document.getElementById('spouse-item-template');
                data.forEach(s => {
                    const li = template.content.cloneNode(true);
                    li.querySelector('.spouse-name').textContent = `${s.properties.first_name} ${s.properties.last_name}`;
                    li.querySelector('.spouse-b-id-input').value = s.properties.id;
                    li.querySelector('.remove-spouse-form').action = "{{ route('relationships.remove-spouse') }}";
                    list.appendChild(li);
                });
            })
            .catch(() => {
                document.getElementById('spouses-list').innerHTML = '<li class="text-red-500">Failed to load spouses.</li>';
            });
        // Helper for fetching with limit
        function fetchWithLimit(endpoint, limit, loadingId, listId, truncatedId, label) {
            document.getElementById(loadingId).style.display = '';
            document.getElementById(listId).innerHTML = '';
            document.getElementById(truncatedId).innerHTML = '';
            fetch(endpoint + '?limit=' + limit)
                .then(res => res.json())
                .then(data => {
                    document.getElementById(loadingId).style.display = 'none';
                    document.getElementById(listId).innerHTML = data.length ? data.map(x => `<li>${x.properties.first_name} ${x.properties.last_name}</li>`).join('') : `<li class='text-gray-400'>None found.</li>`;
                    if (data.length == limit) {
                        document.getElementById(truncatedId).innerHTML = `Showing only the first ${limit} ${label.toLowerCase()} (may be truncated).`;
                    }
                })
                .catch(() => {
                    document.getElementById(loadingId).style.display = 'none';
                    document.getElementById(listId).innerHTML = `<li class='text-red-500'>Failed to load ${label.toLowerCase()}.</li>`;
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