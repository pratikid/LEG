@props(['treeData'])

<div class="family-tree-container">
    <div id="tree-container" style="width: 100%; height: 1100px;" data-tree-data='{!! json_encode($treeData) !!}'></div>
</div>

{{-- Include all layout components --}}
<x-family-tree.base-layout />
<x-family-tree.vertical-layout />
<x-family-tree.horizontal-layout />
<x-family-tree.radial-layout />
<x-family-tree.fan-layout />
<x-family-tree.pedigree-layout />
<x-family-tree.descendant-layout />
<x-family-tree.adjacency-layout />
<x-family-tree.bowtie-layout />
<x-family-tree.time-layout />
<x-family-tree.geographical-layout />
<x-family-tree.force-layout />

@push('scripts')
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
// Family Tree Core Component
class FamilyTreeCore {
    constructor(containerId, treeData) {
        this.container = document.getElementById(containerId);
        this.treeData = this.processTreeData(treeData);
        this.treeState = {
            currentZoom: 1,
            fitToScreen: false,
            layout: 'force',
            highlightDirectLine: false,
            selectedNode: null,
            currentLayout: 'force'
        };
        
        this.colors = {
            nodeFill: '#ffffff',
            nodeStroke: '#4f46e5', // Indigo
            linkStroke: '#10b981', // Emerald
            textFill: '#1f2937', // Gray-800
            textStroke: '#ffffff',
            nodeHover: '#818cf8', // Indigo-400
            linkHover: '#34d399', // Emerald-400
            directLine: '#ef4444', // Red-500
            highlight: '#fbbf24' // Amber-400
        };
        
        this.width = this.container.clientWidth;
        this.height = this.container.clientHeight;
        this.margin = {top: 20, right: 90, bottom: 30, left: 90};
        
        this.svg = null;
        this.zoom = null;
        // Create nodeMap after data processing to ensure it contains the correct nodes
        this.nodeMap = new Map(this.treeData.nodes.map(node => [node.id, node]));
        this.links = this.processLinks();
        this.currentLayoutData = null;
        this.simulation = null;
        
        this.layouts = {
            vertical: new VerticalTreeLayout(this),
            horizontal: new HorizontalTreeLayout(this),
            radial: new RadialTreeLayout(this),
            fan: new FanTreeLayout(this),
            pedigree: new PedigreeLayout(this),
            descendant: new DescendantLayout(this),
            adjacency: new AdjacencyMatrixLayout(this),
            bowtie: new BowtieLayout(this),
            time: new TimeBasedLayout(this),
            geographical: new GeographicalLayout(this),
            force: new ForceDirectedLayout(this)
        };
        
        this.init();
    }
    
    processTreeData(treeData) {
        // Validate input
        if (!treeData || !treeData.nodes || !Array.isArray(treeData.nodes)) {
            console.error('Invalid tree data structure');
            return { nodes: [], edges: [] };
        }
        
        if (!treeData.edges || !Array.isArray(treeData.edges)) {
            console.warn('No edges found in tree data');
            treeData.edges = [];
        }
        
        // Deduplicate nodes by ID and validate node structure
        const uniqueNodes = new Map();
        treeData.nodes.forEach(node => {
            if (node && node.id !== null && node.id !== undefined) {
                // Convert ID to string for consistent handling
                const nodeId = String(node.id).trim();
                if (nodeId !== '') {
                    if (!uniqueNodes.has(nodeId)) {
                        // Ensure node has required properties
                        const validNode = {
                            id: nodeId,
                            name: node.name || node.first_name || 'Unknown',
                            first_name: node.first_name || '',
                            last_name: node.last_name || '',
                            sex: node.sex || '',
                            birth_date: node.birth_date || null,
                            death_date: node.death_date || null
                        };
                        uniqueNodes.set(nodeId, validNode);
                    }
                }
            }
        });
        treeData.nodes = Array.from(uniqueNodes.values());
        
        // Deduplicate edges by creating a unique key for each edge
        const uniqueEdges = new Map();
        treeData.edges.forEach(edge => {
            if (edge && edge.from !== null && edge.from !== undefined && 
                edge.to !== null && edge.to !== undefined && edge.type) {
                // Convert edge IDs to strings for consistent handling
                const fromId = String(edge.from);
                const toId = String(edge.to);
                const key = `${fromId}-${toId}-${edge.type}`;
                if (!uniqueEdges.has(key)) {
                    uniqueEdges.set(key, {
                        from: fromId,
                        to: toId,
                        type: edge.type
                    });
                }
            }
        });
        treeData.edges = Array.from(uniqueEdges.values());
        
        // Filter out isolated nodes by only keeping nodes that have relationships
        const connectedNodeIds = new Set(treeData.edges.flatMap(edge => [edge.from, edge.to]));
        treeData.nodes = treeData.nodes.filter(node => connectedNodeIds.has(node.id));
        
        // Filter edges to only include those between connected nodes
        treeData.edges = treeData.edges.filter(edge => 
            connectedNodeIds.has(edge.from) && connectedNodeIds.has(edge.to)
        );
        
        return treeData;
    }
    
    processLinks() {
        return this.treeData.edges.map(edge => {
            const source = this.nodeMap.get(edge.from);
            const target = this.nodeMap.get(edge.to);
            
            if (!source || !target) {
                console.warn('Missing node for edge:', edge);
                return null;
            }
            
            // Validate that both source and target have valid coordinates
            if (typeof source.x !== 'number' || isNaN(source.x)) {
                source.x = this.width / 2;
            }
            if (typeof source.y !== 'number' || isNaN(source.y)) {
                source.y = this.height / 2;
            }
            if (typeof target.x !== 'number' || isNaN(target.x)) {
                target.x = this.width / 2;
            }
            if (typeof target.y !== 'number' || isNaN(target.y)) {
                target.y = this.height / 2;
            }
            
            return {
                source: source,
                target: target,
                type: edge.type
            };
        }).filter(link => link !== null);
    }
    
    init() {
        try {
            // Validate container exists
            if (!this.container) {
                console.error('Container element not found');
                return;
            }
            
            // Validate tree data
            if (!this.treeData || !this.treeData.nodes || !Array.isArray(this.treeData.nodes)) {
                console.error('Invalid tree data provided');
                return;
            }
            
            if (this.treeData.nodes.length === 0) {
                console.warn('No valid nodes found in tree data');
                return;
            }
            
            this.createSVG();
            this.createZoom();
            this.createLinkElements();
            this.createNodes();
            this.createArrowMarker();
            this.setupEventListeners();
            this.updateLayout('force');
        } catch (error) {
            console.error('Error initializing family tree:', error);
        }
    }
    
    createSVG() {
        this.svg = d3.select('#tree-container')
            .append('svg')
            .attr('width', this.width)
            .attr('height', this.height)
            .append('g')
            .attr('transform', `translate(${this.margin.left},${this.margin.top})`);
    }
    
    createZoom() {
        this.zoom = d3.zoom()
            .scaleExtent([0.1, 5])
            .on('zoom', (event) => {
                this.treeState.currentZoom = event.transform.k;
                this.svg.attr('transform', event.transform);
            });
        
        d3.select('#tree-container svg').call(this.zoom);
    }
    
    createLinkElements() {
        this.link = this.svg.append('g')
            .selectAll('path')
            .data(this.links)
            .enter()
            .append('path')
            .attr('class', 'link')
            .attr('fill', 'none')
            .attr('stroke', this.colors.linkStroke)
            .attr('stroke-width', 2)
            .style('transition', 'stroke 0.3s')
            .on('mouseover', (event, d) => {
                d3.select(event.currentTarget)
                    .attr('stroke', this.colors.linkHover)
                    .attr('stroke-width', 3);
            })
            .on('mouseout', (event, d) => {
                d3.select(event.currentTarget)
                    .attr('stroke', this.colors.linkStroke)
                    .attr('stroke-width', 2);
            });
        
        this.linkLabels = this.svg.append('g')
            .selectAll('text')
            .data(this.links)
            .enter()
            .append('text')
            .attr('class', 'link-label')
            .attr('text-anchor', 'middle')
            .attr('fill', this.colors.textFill)
            .style('font-size', '12px')
            .style('pointer-events', 'none')
            .text(d => d.type);
    }
    
    createNodes() {
        this.node = this.svg.append('g')
            .selectAll('g')
            .data(this.treeData.nodes)
            .enter()
            .append('g')
            .attr('class', 'node')
            .call(d3.drag()
                .on('start', (event, d) => this.dragstarted(event, d))
                .on('drag', (event, d) => this.dragged(event, d))
                .on('end', (event, d) => this.dragended(event, d)));
        
        // Add circles to nodes
        this.node.append('circle')
            .attr('r', 32)
            .attr('fill', d => {
                if (d.sex === 'F' || d.sex === 'female') return '#f9a8d4'; // Pink for female
                if (d.sex === 'M' || d.sex === 'male') return '#60a5fa'; // Blue for male
                return this.colors.nodeFill; // Default
            })
            .attr('stroke', this.colors.nodeStroke)
            .attr('stroke-width', 2)
            .style('transition', 'all 0.3s')
            .on('mouseover', (event, d) => {
                d3.select(event.currentTarget)
                    .attr('stroke', this.colors.nodeHover)
                    .attr('stroke-width', 3);
            })
            .on('mouseout', (event, d) => {
                d3.select(event.currentTarget)
                    .attr('stroke', this.colors.nodeStroke)
                    .attr('stroke-width', 2);
            })
            .on('click', (event, d) => {
                this.showNodeDetails(d);
            })
            .on('dblclick', (event, d) => {
                this.editNode(d);
            });
        
        // Add first_name inside the node
        this.node.append('text')
            .attr('text-anchor', 'middle')
            .attr('dy', '.35em')
            .attr('fill', this.colors.textFill)
            .style('font-size', '12px')
            .style('font-weight', '600')
            .text(d => d.first_name);
        
        // Add tooltips
        this.node.append('title')
            .text(d => {
                let birth = '';
                if (d.birth_date && d.birth_date !== null && d.birth_date !== '') {
                    const date = new Date(d.birth_date);
                    if (!isNaN(date.getTime())) {
                        birth = date.getFullYear();
                    }
                }
                let death = '';
                if (d.death_date && d.death_date !== null && d.death_date !== '') {
                    const date = new Date(d.death_date);
                    if (!isNaN(date.getTime())) {
                        death = date.getFullYear();
                    }
                }
                return `${d.name}\nBirth: ${birth || 'Unknown'}${death ? '\nDeath: ' + death : ''}`;
            });
    }
    
    createArrowMarker() {
        this.svg.append('defs').append('marker')
            .attr('id', 'arrowhead')
            .attr('viewBox', '-0 -5 10 10')
            .attr('refX', 28)
            .attr('refY', 0)
            .attr('orient', 'auto')
            .attr('markerWidth', 8)
            .attr('markerHeight', 8)
            .attr('xoverflow', 'visible')
            .append('svg:path')
            .attr('d', 'M 0,-5 L 10,0 L 0,5')
            .attr('fill', this.colors.linkStroke)
            .style('stroke','none');
        
        this.link.attr('marker-end', 'url(#arrowhead)');
    }
    
    updateLayout(layoutType) {
        console.log(`Updating layout to: ${layoutType}`);
        this.treeState.currentLayout = layoutType;
        
        // Stop current simulation if running
        if (this.simulation) {
            this.simulation.stop();
        }
        
        // Get new layout data
        const layout = this.layouts[layoutType];
        if (!layout) {
            console.warn(`Layout '${layoutType}' not found`);
            return;
        }
        
        this.currentLayoutData = layout.generate();
        
        // Store simulation reference for force-directed layout
        if (layoutType === 'force' && this.currentLayoutData.simulation) {
            this.simulation = this.currentLayoutData.simulation;
        } else {
            this.simulation = null;
        }
        
        // Update node positions
        this.node.transition().duration(500)
            .attr('transform', d => {
                let x, y;
                
                if (d.fx !== null && d.fy !== null && !isNaN(d.fx) && !isNaN(d.fy)) {
                    x = d.fx;
                    y = d.fy;
                    d.x = x;
                    d.y = y;
                } else {
                    const nodeData = this.currentLayoutData.descendants ? 
                        this.currentLayoutData.descendants().find(n => n.id === d.id) : null;
                    
                    if (nodeData && typeof nodeData.x === 'number' && typeof nodeData.y === 'number' && 
                        !isNaN(nodeData.x) && !isNaN(nodeData.y)) {
                        x = nodeData.x;
                        y = nodeData.y;
                        d.x = x;
                        d.y = y;
                    } else {
                        x = (typeof d.x === 'number' && !isNaN(d.x)) ? d.x : this.width / 2;
                        y = (typeof d.y === 'number' && !isNaN(d.y)) ? d.y : this.height / 2;
                        d.x = x;
                        d.y = y;
                    }
                }
                
                x = typeof x === 'number' && !isNaN(x) ? x : this.width / 2;
                y = typeof y === 'number' && !isNaN(y) ? y : this.height / 2;
                
                return `translate(${x},${y})`;
            });
        
        // Update link positions
        this.updateLinkPositions();
        
        // For force-directed layout, start continuous updates
        if (layoutType === 'force' && this.simulation) {
            this.startForceSimulation();
        }
        
        // Fit to screen after layout change - call immediately for non-force layouts
        if (layoutType !== 'force') {
            this.fitToScreen();
        } else {
            // For force-directed layout, wait a bit for the simulation to settle
            setTimeout(() => {
                this.fitToScreen();
            }, 1000);
        }
    }
    
    startForceSimulation() {
        if (this.simulation) {
            this.simulation.on("tick", () => {
                // Update node positions during simulation
                this.node.attr('transform', d => `translate(${d.x},${d.y})`);
                
                // Update link positions during simulation
                this.link.attr('d', d => {
                    return `M${d.source.x},${d.source.y}L${d.target.x},${d.target.y}`;
                });
                
                // Update link labels during simulation
                this.linkLabels.attr('transform', d => {
                    const x = (d.source.x + d.target.x) / 2;
                    const y = (d.source.y + d.target.y) / 2;
                    return `translate(${x},${y})`;
                });
            });
        }
    }
    
    updateLinkPositions() {
        this.link.transition().duration(500)
            .attr('d', d => {
                const sourceX = (d.source.fx !== null && !isNaN(d.source.fx)) ? d.source.fx : 
                               (typeof d.source.x === 'number' && !isNaN(d.source.x)) ? d.source.x : this.width / 2;
                const sourceY = (d.source.fy !== null && !isNaN(d.source.fy)) ? d.source.fy : 
                               (typeof d.source.y === 'number' && !isNaN(d.source.y)) ? d.source.y : this.height / 2;
                const targetX = (d.target.fx !== null && !isNaN(d.target.fx)) ? d.target.fx : 
                               (typeof d.target.x === 'number' && !isNaN(d.target.x)) ? d.target.x : this.width / 2;
                const targetY = (d.target.fy !== null && !isNaN(d.target.fy)) ? d.target.fy : 
                               (typeof d.target.y === 'number' && !isNaN(d.target.y)) ? d.target.y : this.height / 2;
                
                if (this.treeState.currentLayout === 'radial' || this.treeState.currentLayout === 'fan') {
                    const dx = targetX - sourceX;
                    const dy = targetY - sourceY;
                    const dr = Math.sqrt(dx * dx + dy * dy);
                    return `M${sourceX},${sourceY}A${dr},${dr} 0 0,1 ${targetX},${targetY}`;
                } else {
                    return `M${sourceX},${sourceY}L${targetX},${targetY}`;
                }
            });
        
        this.linkLabels.transition().duration(500)
            .attr('transform', d => {
                const sourceX = (d.source.fx !== null && !isNaN(d.source.fx)) ? d.source.fx : 
                               (typeof d.source.x === 'number' && !isNaN(d.source.x)) ? d.source.x : this.width / 2;
                const sourceY = (d.source.fy !== null && !isNaN(d.source.fy)) ? d.source.fy : 
                               (typeof d.source.y === 'number' && !isNaN(d.source.y)) ? d.source.y : this.height / 2;
                const targetX = (d.target.fx !== null && !isNaN(d.target.fx)) ? d.target.fx : 
                               (typeof d.target.x === 'number' && !isNaN(d.target.x)) ? d.target.x : this.width / 2;
                const targetY = (d.target.fy !== null && !isNaN(d.target.fy)) ? d.target.fy : 
                               (typeof d.target.y === 'number' && !isNaN(d.target.y)) ? d.target.y : this.height / 2;
                
                const x = (sourceX + targetX) / 2;
                const y = (sourceY + targetY) / 2;
                return `translate(${x},${y})`;
            });
    }
    
    // Control Functions
    zoomIn() {
        const newScale = this.treeState.currentZoom * 1.2;
        d3.select('#tree-container svg').transition().duration(300)
            .call(this.zoom.scaleTo, newScale);
    }
    
    zoomOut() {
        const newScale = this.treeState.currentZoom * 0.8;
        d3.select('#tree-container svg').transition().duration(300)
            .call(this.zoom.scaleTo, newScale);
    }
    
    fitToScreen() {
        this.treeState.fitToScreen = !this.treeState.fitToScreen;
        const btn = document.getElementById('fitToScreen');
        if (btn) btn.classList.toggle('bg-blue-100', this.treeState.fitToScreen);
        
        if (this.treeState.fitToScreen) {
            const bounds = this.svg.node().getBBox();
            const fullWidth = this.container.clientWidth;
            const fullHeight = this.container.clientHeight;
            const width = bounds.width;
            const height = bounds.height;
            const midX = bounds.x + width / 2;
            const midY = bounds.y + height / 2;
            
            const scale = 0.9 / Math.max(width / fullWidth, height / fullHeight);
            const translate = [fullWidth / 2 - scale * midX, fullHeight / 2 - scale * midY];
            
            d3.select('#tree-container svg').transition().duration(500)
                .call(this.zoom.transform, d3.zoomIdentity.translate(translate[0], translate[1]).scale(scale));
        }
    }
    
    highlightDirectLine() {
        this.treeState.highlightDirectLine = !this.treeState.highlightDirectLine;
        const btn = document.getElementById('highlightDirectLine');
        if (btn) btn.classList.toggle('bg-blue-100', this.treeState.highlightDirectLine);
        
        if (this.treeState.highlightDirectLine) {
            this.link.style('opacity', 0.3);
            this.link.filter(d => d.type === 'parent-child')
                .style('opacity', 1)
                .attr('stroke', this.colors.directLine)
                .attr('stroke-width', 3);
        } else {
            this.link.style('opacity', 1)
                .attr('stroke', this.colors.linkStroke)
                .attr('stroke-width', 2);
        }
    }
    
    resetView() {
        this.treeState.fitToScreen = false;
        this.treeState.highlightDirectLine = false;
        this.treeState.layout = 'force';
        this.treeState.currentLayout = 'force';
        
        // Reset button states
        const fitToScreenBtn = document.getElementById('fitToScreen');
        const highlightDirectLineBtn = document.getElementById('highlightDirectLine');
        const toggleLayoutBtn = document.getElementById('toggleLayout');
        const layoutSelect = document.getElementById('layoutSelect');
        
        if (fitToScreenBtn) fitToScreenBtn.classList.remove('bg-blue-100');
        if (highlightDirectLineBtn) highlightDirectLineBtn.classList.remove('bg-blue-100');
        if (toggleLayoutBtn) toggleLayoutBtn.classList.remove('bg-blue-100');
        if (layoutSelect) layoutSelect.value = 'force';
        
        // Reset view
        d3.select('#tree-container svg').transition().duration(500)
            .call(this.zoom.transform, d3.zoomIdentity);
        
        // Reset link styles
        this.link.style('opacity', 1)
            .attr('stroke', this.colors.linkStroke)
            .attr('stroke-width', 2);
        
        // Reset fixed positions
        this.resetFixedPositions();
    }
    
    resetFixedPositions() {
        this.treeData.nodes.forEach(node => {
            node.fx = null;
            node.fy = null;
        });
        
        // For force-directed layout, restart the simulation
        if (this.treeState.currentLayout === 'force' && this.simulation) {
            this.simulation.alpha(1).restart();
        } else {
            this.updateLayout(this.treeState.currentLayout);
        }
    }
    
    // Drag functions
    dragstarted(event, d) {
        if (!event.active) {
            if (this.simulation) {
                this.simulation.alphaTarget(0.3).restart();
            }
        }
        d.fx = d.x;
        d.fy = d.y;
    }
    
    dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
        d.x = event.x;
        d.y = event.y;
        
        // Use event.currentTarget instead of this to ensure proper DOM element reference
        if (event.currentTarget && event.currentTarget.setAttribute) {
            d3.select(event.currentTarget).attr('transform', `translate(${event.x},${event.y})`);
        }
        
        // For force-directed layout, update connected links immediately
        if (this.treeState.currentLayout === 'force') {
            this.updateConnectedLinks(d);
        }
    }
    
    dragended(event, d) {
        if (!event.active) {
            if (this.simulation) {
                this.simulation.alphaTarget(0);
            }
        }
        // Keep the fixed position for this node
        // Don't set fx/fy to null - let the user decide when to reset
    }
    
    updateConnectedLinks(draggedNode) {
        this.link.filter(d => d.source.id === draggedNode.id || d.target.id === draggedNode.id)
            .attr('d', d => {
                const sourceX = d.source.id === draggedNode.id ? draggedNode.x : d.source.x;
                const sourceY = d.source.id === draggedNode.id ? draggedNode.y : d.source.y;
                const targetX = d.target.id === draggedNode.id ? draggedNode.x : d.target.x;
                const targetY = d.target.id === draggedNode.id ? draggedNode.y : d.target.y;
                
                if (this.treeState.currentLayout === 'radial' || this.treeState.currentLayout === 'fan') {
                    const dx = targetX - sourceX;
                    const dy = targetY - sourceY;
                    const dr = Math.sqrt(dx * dx + dy * dy);
                    return `M${sourceX},${sourceY}A${dr},${dr} 0 0,1 ${targetX},${targetY}`;
                } else {
                    return `M${sourceX},${sourceY}L${targetX},${targetY}`;
                }
            });
        
        this.linkLabels.filter(d => d.source.id === draggedNode.id || d.target.id === draggedNode.id)
            .attr('transform', d => {
                const sourceX = d.source.id === draggedNode.id ? draggedNode.x : d.source.x;
                const sourceY = d.source.id === draggedNode.id ? draggedNode.y : d.source.y;
                const targetX = d.target.id === draggedNode.id ? draggedNode.x : d.target.x;
                const targetY = d.target.id === draggedNode.id ? draggedNode.y : d.target.y;
                
                const x = (sourceX + targetX) / 2;
                const y = (sourceY + targetY) / 2;
                return `translate(${x},${y})`;
            });
    }
    
    // Node interaction functions
    showNodeDetails(node) {
        this.treeState.selectedNode = node;
        console.log('Node details:', node);
    }
    
    editNode(node) {
        console.log('Edit node:', node);
    }
    
    /**
     * Resize SVG method for fullscreen functionality
     * 
     * This method is called when the visualization enters or exits fullscreen mode.
     * It updates the SVG dimensions to match the container size and recalculates
     * the layout to ensure proper display in the new dimensions.
     * 
     * Features:
     * - Updates SVG width and height to match container
     * - Recalculates layout for optimal node positioning
     * - Handles edge cases where SVG might not exist
     * - Maintains zoom and pan functionality
     */
    resizeSVG() {
        if (!this.container) {
            console.warn('Container not found for SVG resize');
            return;
        }
        
        // Update dimensions
        this.width = this.container.clientWidth;
        this.height = this.container.clientHeight;
        
        // Find the main SVG element
        const mainSVG = d3.select('#tree-container svg');
        if (mainSVG.empty()) {
            console.warn('SVG element not found for resize');
            return;
        }
        
        // Update the main SVG element dimensions
        mainSVG
            .attr('width', this.width)
            .attr('height', this.height);
        
        // Update the inner group transform if it exists
        if (this.svg && !this.svg.empty()) {
            this.svg.attr('transform', `translate(${this.margin.left},${this.margin.top})`);
        }
        
        // Recalculate layout if needed
        if (this.treeState.currentLayout) {
            this.updateLayout(this.treeState.currentLayout);
        }
        
        console.log('SVG resized to:', this.width, 'x', this.height);
    }
    
    setupEventListeners() {
        // Control button event listeners
        const addEventListenerSafely = (elementId, event, handler, maxRetries = 5) => {
            let retries = 0;
            
            function tryAddListener() {
                const element = document.getElementById(elementId);
                if (element) {
                    element.addEventListener(event, handler);
                    return true;
                } else if (retries < maxRetries) {
                    retries++;
                    setTimeout(tryAddListener, 100);
                    return false;
                } else {
                    console.warn(`Element with id '${elementId}' not found after ${maxRetries} attempts`);
                    return false;
                }
            }
            
            return tryAddListener();
        };
        
        addEventListenerSafely('zoomIn', 'click', () => this.zoomIn());
        addEventListenerSafely('zoomOut', 'click', () => this.zoomOut());
        addEventListenerSafely('fitToScreen', 'click', () => this.fitToScreen());
        addEventListenerSafely('highlightDirectLine', 'click', () => this.highlightDirectLine());
        addEventListenerSafely('resetView', 'click', () => this.resetView());
        addEventListenerSafely('resetPositions', 'click', () => this.resetFixedPositions());
        
        // Layout dropdown event listener
        const layoutSelect = document.getElementById('layoutSelect');
        if (layoutSelect) {
            layoutSelect.addEventListener('change', (e) => {
                this.updateLayout(e.target.value);
            });
        }
        
        // Menu event listeners
        document.querySelectorAll('[data-action]').forEach(item => {
            item.addEventListener('click', (e) => {
                const action = e.target.closest('[data-action]').dataset.action;
                switch(action) {
                    case 'addIndividual': this.addIndividual(); break;
                    case 'addPartner': this.addPartner(); break;
                    case 'addChild': this.addChild(); break;
                    case 'addParents': this.addParents(); break;
                    case 'saveAsImage': this.saveAsImage(); break;
                    case 'generatePDF': this.generatePDF(); break;
                    case 'copyShareLink': this.copyShareLink(); break;
                }
                
                // Hide menus after action
                const addMenu = document.getElementById('addMenu');
                const exportMenu = document.getElementById('exportMenu');
                if (addMenu) addMenu.classList.add('hidden');
                if (exportMenu) exportMenu.classList.add('hidden');
            });
        });
        
        // Close menus when clicking outside
        document.addEventListener('click', (e) => {
            const addMenu = document.getElementById('addMenu');
            const exportMenu = document.getElementById('exportMenu');
            
            if (!e.target.closest('#showAddMenu') && !e.target.closest('#addMenu')) {
                if (addMenu) addMenu.classList.add('hidden');
            }
            if (!e.target.closest('#showExportMenu') && !e.target.closest('#exportMenu')) {
                if (exportMenu) exportMenu.classList.add('hidden');
            }
        });
    }
    
    // Menu functions
    addIndividual() { console.log('Add individual'); }
    addPartner() { console.log('Add partner'); }
    addChild() { console.log('Add child'); }
    addParents() { console.log('Add parents'); }
    saveAsImage() { console.log('Save as image'); }
    generatePDF() { console.log('Generate PDF'); }
    copyShareLink() { console.log('Copy share link'); }
}

// Initialize the family tree when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    try {
        const treeDataElement = document.getElementById('tree-data');
        let treeData;
        
        if (treeDataElement) {
            treeData = JSON.parse(treeDataElement.textContent);
        } else {
            // Fallback to reading from data attribute
            const container = document.getElementById('tree-container');
            if (container && container.dataset.treeData) {
                treeData = JSON.parse(container.dataset.treeData);
            } else {
                console.error('No tree data available');
                return;
            }
        }
        
        // Validate tree data before creating the visualization
        if (treeData && treeData.nodes && Array.isArray(treeData.nodes) && treeData.nodes.length > 0) {
            window.familyTree = new FamilyTreeCore('tree-container', treeData);
        } else {
            console.error('Invalid or empty tree data provided');
            // Show a user-friendly error message
            const container = document.getElementById('tree-container');
            if (container) {
                container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No family tree data available to display.</div>';
            }
        }
    } catch (error) {
        console.error('Error initializing family tree:', error);
        // Show a user-friendly error message
        const container = document.getElementById('tree-container');
        if (container) {
            container.innerHTML = '<div class="flex items-center justify-center h-full text-red-500">Error loading family tree visualization.</div>';
        }
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