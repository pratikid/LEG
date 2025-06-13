@props(['treeData'])

<div class="family-tree-container">
    <div id="tree-container" style="width: 100%; height: 100%;"></div>
</div>

@push('scripts')
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('tree-container');
    const data = @json($treeData);
    
    // Debug data
    console.log('Nodes:', data.nodes);
    console.log('Edges:', data.edges);
    
    // Color scheme
    const colors = {
        nodeFill: '#ffffff',
        nodeStroke: '#4f46e5', // Indigo
        linkStroke: '#10b981', // Emerald
        textFill: '#1f2937', // Gray-800
        textStroke: '#ffffff',
        nodeHover: '#818cf8', // Indigo-400
        linkHover: '#34d399' // Emerald-400
    };

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

    // Create a map of nodes for easy lookup
    const nodeMap = new Map(data.nodes.map(node => [node.id, node]));
    
    // Debug nodeMap
    console.log('NodeMap:', Array.from(nodeMap.entries()));
    
    // Create links array with validation
    const links = data.edges.map(edge => {
        const source = nodeMap.get(edge.from);
        const target = nodeMap.get(edge.to);
        
        // Debug each link creation
        console.log('Creating link:', {
            from: edge.from,
            to: edge.to,
            sourceFound: !!source,
            targetFound: !!target
        });
        
        if (!source || !target) {
            console.warn('Missing node for edge:', edge);
            return null;
        }
        
        return {
            source: source,
            target: target,
            type: edge.type
        };
    }).filter(link => link !== null); // Remove any invalid links

    // Debug final links
    console.log('Final links:', links);

    // Create a D3 force simulation
    const simulation = d3.forceSimulation(data.nodes)
        .force('link', d3.forceLink(links).id(d => d.id).distance(100))
        .force('charge', d3.forceManyBody().strength(-300))
        .force('center', d3.forceCenter(width / 2, height / 2))
        .force('collision', d3.forceCollide().radius(50));

    // Create links
    const link = svg.append('g')
        .selectAll('path')
        .data(links)
        .enter()
        .append('path')
        .attr('class', 'link')
        .attr('fill', 'none')
        .attr('stroke', colors.linkStroke)
        .attr('stroke-width', 2)
        .style('transition', 'stroke 0.3s')
        .on('mouseover', function() {
            d3.select(this)
                .attr('stroke', colors.linkHover)
                .attr('stroke-width', 3);
        })
        .on('mouseout', function() {
            d3.select(this)
                .attr('stroke', colors.linkStroke)
                .attr('stroke-width', 2);
        });

    // Add relationship labels
    const linkLabels = svg.append('g')
        .selectAll('text')
        .data(links)
        .enter()
        .append('text')
        .attr('class', 'link-label')
        .attr('text-anchor', 'middle')
        .attr('fill', colors.textFill)
        .style('font-size', '12px')
        .style('pointer-events', 'none')
        .text(d => d.type);

    // Create nodes
    const node = svg.append('g')
        .selectAll('g')
        .data(data.nodes)
        .enter()
        .append('g')
        .attr('class', 'node')
        .call(d3.drag()
            .on('start', dragstarted)
            .on('drag', dragged)
            .on('end', dragended));

    // Add circles to nodes
    node.append('circle')
        .attr('r', 12)
        .attr('fill', colors.nodeFill)
        .attr('stroke', colors.nodeStroke)
        .attr('stroke-width', 2)
        .style('transition', 'all 0.3s')
        .on('mouseover', function(event, d) {
            d3.select(this)
                .attr('stroke', colors.nodeHover)
                .attr('stroke-width', 3);
        })
        .on('mouseout', function(event, d) {
            d3.select(this)
                .attr('stroke', colors.nodeStroke)
                .attr('stroke-width', 2);
        });

    // Add labels to nodes
    node.append('text')
        .attr('dy', '.31em')
        .attr('x', 15)
        .attr('text-anchor', 'start')
        .attr('fill', colors.textFill)
        .style('font-size', '14px')
        .style('font-weight', '500')
        .text(d => d.name)
        .clone(true).lower()
        .attr('stroke', colors.textStroke)
        .attr('stroke-width', 3);

    // Add tooltips
    node.append('title')
        .text(d => `Birth: ${d.birth_date}${d.death_date ? '\nDeath: ' + d.death_date : ''}`);

    // Update positions on each tick
    simulation.on('tick', () => {
        link.attr('d', d => {
            const dx = d.target.x - d.source.x;
            const dy = d.target.y - d.source.y;
            const dr = Math.sqrt(dx * dx + dy * dy);
            return `M${d.source.x},${d.source.y}A${dr},${dr} 0 0,1 ${d.target.x},${d.target.y}`;
        });

        linkLabels.attr('transform', d => {
            const x = (d.source.x + d.target.x) / 2;
            const y = (d.source.y + d.target.y) / 2;
            const angle = Math.atan2(d.target.y - d.source.y, d.target.x - d.source.x) * 180 / Math.PI;
            return `translate(${x},${y}) rotate(${angle})`;
        });

        node.attr('transform', d => `translate(${d.x},${d.y})`);
    });

    // Drag functions
    function dragstarted(event, d) {
        if (!event.active) simulation.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
    }

    function dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }

    function dragended(event, d) {
        if (!event.active) simulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
    }
});
</script>
@endpush

<style>
.family-tree-container {
    width: 100%;
    height: 100%;
    border: 1px solid #ccc;
    border-radius: 4px;
    overflow: hidden;
    background-color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.family-tree-container svg {
    transition: all 0.3s ease;
}

.node circle {
    cursor: pointer;
}

.link {
    cursor: pointer;
}
</style> 