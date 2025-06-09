@extends('layouts.app')
@section('content')
<div class="container mx-auto mt-8">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('trees.index') }}" class="text-blue-400 hover:text-blue-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">{{ $tree->name }}</h1>
        </div>
        <div class="flex space-x-4">
            <a href="{{ route('trees.visualization', $tree->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg">
                View Tree Visualization
            </a>
            <a href="{{ route('trees.edit', $tree->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                Edit Tree
            </a>
            <form action="{{ route('trees.destroy', $tree->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg" onclick="return confirm('Are you sure you want to delete this tree?')">
                    Delete Tree
                </button>
            </form>
            <a href="{{ route('trees.export-gedcom', $tree->id) }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                Export GEDCOM
            </a>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-6 mb-6">
        <div class="col-span-3">
<div id="tree-container" class="border p-4 bg-white shadow-lg rounded-lg" 
     data-tree='@json($treeDataJson ?? [])'>
    @if(empty($treeDataNeo4j))
        <p class="text-gray-600">No family tree data available to display.</p>
    @else
        <p class="text-gray-600">Family tree visualization will be rendered here.</p>
    @endif
</div>
        </div>
        <div class="col-span-1">
            <div class="bg-gray-800 p-4 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="{{ route('individuals.create', ['tree_id' => $tree->id]) }}" class="block w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-center">
                        Add Individual
                    </a>
                    <a href="{{ route('groups.create', ['tree_id' => $tree->id]) }}" class="block w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-center">
                        Add Group
                    </a>
                    <a href="{{ route('trees.edit', $tree->id) }}#settings" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-center">
                        Tree Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://d3js.org/d3.v7.min.js"></script>

<script>
    try {
        const container = document.getElementById('tree-container');
        if (!container) {
            console.error('Tree container not found');
            return;
        }
        
        const treeDataRaw = container.dataset.tree;
        if (!treeDataRaw) {
            console.warn('No tree data available');
            return;
        }
        
        const treeData = JSON.parse(treeDataRaw);
        if (!treeData || typeof treeData !== 'object') {
            console.error('Invalid tree data format');
            return;
        }
        
         const width = 800;
         const height = 600;
        
        // Clear existing content first
        d3.select('#tree-container').selectAll('*').remove();
        
        const svg = d3.select('#tree-container')
            .append('svg')
            .attr('width', width)
            .attr('height', height);

        const g = svg.append('g')
            .attr('transform', 'translate(40,0)');
    } catch (error) {
        console.error('Error rendering tree visualization:', error);
        document.getElementById('tree-container').innerHTML = 
            '<p class="text-red-600">Error loading tree visualization.</p>';
    }

    // --- layout & drawing --------------------------------------------------
     const tree = d3.tree()

        const links = tree(root).links();
        const linkPathGenerator = d3.linkVertical()
            .x(d => d.x)
            .y(d => d.y);
        g.selectAll('path')
            .data(links)
            .enter()
            .append('path')
            .attr('d', linkPathGenerator);
        const nodes = g.selectAll('g')
            .data(root.descendants())
            .enter()
            .append('g')
            .attr('transform', d => `translate(${d.x},${d.y})`);
        nodes.append('circle')
            .attr('r', 5);
        nodes.append('text')
            .attr('dy', '0.31em')
            .attr('x', d => d.children ? -6 : 6)
            .attr('text-anchor', d => d.children ? 'end' : 'start')
            .text(d => d.data.name);
</script>
</div>
@endsection 