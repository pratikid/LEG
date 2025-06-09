@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-8">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('trees.show', $tree->id) }}" class="text-blue-400 hover:text-blue-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Tree Visualization - {{ $tree->name }}</h1>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <!-- Controls Toolbar -->
            <div class="mb-4 flex flex-wrap justify-between items-center gap-2">
                <div class="flex space-x-2">
                    <button id="zoom-in" class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </button>
                    <button id="zoom-out" class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                    </button>
                    <button id="reset-zoom" class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
                <div class="flex space-x-2">
                    <select id="tree-style" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm rounded-md">
                        <option value="traditional">Traditional</option>
                        <option value="fan">Fan Chart</option>
                        <option value="radial">Radial</option>
                        <option value="descendant">Descendant</option>
                    </select>
                </div>
            </div>

            <!-- Tree Visualization Container -->
            <div id="tree-container" class="w-full h-[600px] border border-gray-200 rounded-lg" data-tree='@json($treeDataJson)'></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const initialTreeData = JSON.parse(document.getElementById('tree-container').dataset.tree);
    const container = document.getElementById('tree-container');
    const width = container.clientWidth;
    const height = container.clientHeight;
    const margin = {top: 20, right: 90, bottom: 30, left: 90};

    // Create SVG
    const svg = d3.select('#tree-container')
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .append('g')
        .attr('transform', `translate(${margin.left},${margin.top})`);

    // Create zoom behavior
    const zoom = d3.zoom()
        .scaleExtent([0.5, 3])
        .on('zoom', (event) => {
            svg.attr('transform', event.transform);
        });

    d3.select('#tree-container svg').call(zoom);

    // Tree layout
    const treeLayout = d3.tree()
        .size([height - margin.top - margin.bottom, width - margin.left - margin.right]);

    // Create hierarchy
    const root = d3.hierarchy(initialTreeData);
    const treeNodes = treeLayout(root);

    // Create links
    const link = svg.selectAll('.link')
        .data(treeNodes.links())
        .enter()
        .append('path')
        .attr('class', 'link')
        .attr('fill', 'none')
        .attr('stroke', '#ccc')
        .attr('stroke-width', 1.5)
        .attr('d', d3.linkHorizontal()
            .x(d => d.y)
            .y(d => d.x));

    // Create nodes
    const node = svg.selectAll('.node')
        .data(treeNodes.descendants())
        .enter()
        .append('g')
        .attr('class', 'node')
        .attr('transform', d => `translate(${d.y},${d.x})`);

    // Add circles to nodes
    node.append('circle')
        .attr('r', 10)
        .attr('fill', '#fff')
        .attr('stroke', '#amber-500')
        .attr('stroke-width', 2);

    // Add labels to nodes
    node.append('text')
        .attr('dy', '.31em')
        .attr('x', d => d.children ? -13 : 13)
        .attr('text-anchor', d => d.children ? 'end' : 'start')
        .text(d => d.data.name)
        .clone(true).lower()
        .attr('stroke', 'white')
        .attr('stroke-width', 3);

    // Zoom controls
    document.getElementById('zoom-in').addEventListener('click', () => {
        d3.select('#tree-container svg').transition()
            .duration(750)
            .call(zoom.scaleBy, 1.3);
    });

    document.getElementById('zoom-out').addEventListener('click', () => {
        d3.select('#tree-container svg').transition()
            .duration(750)
            .call(zoom.scaleBy, 0.7);
    });

    document.getElementById('reset-zoom').addEventListener('click', () => {
        d3.select('#tree-container svg').transition()
            .duration(750)
            .call(zoom.transform, d3.zoomIdentity);
    });

    // Tree style change handler
    document.getElementById('tree-style').addEventListener('change', function(e) {
        const style = e.target.value;
        // Implement different tree layouts based on style
        // This would require additional logic to transform the tree data
        // and update the visualization accordingly
    });
});
</script>
@endpush
@endsection 