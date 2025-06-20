@push('scripts')
<script>
// Force-Directed Graph Layout
class ForceDirectedLayout extends BaseLayout {
    constructor(core) {
        super(core);
        this.simulation = null;
    }
    
    generate() {
        // Create force simulation
        this.simulation = d3.forceSimulation(this.core.treeData.nodes)
            .force("link", d3.forceLink(this.core.links).id(d => d.id).distance(100))
            .force("charge", d3.forceManyBody().strength(-300))
            .force("center", d3.forceCenter(this.core.width / 2, this.core.height / 2))
            .force("collision", d3.forceCollide().radius(40))
            .force("x", d3.forceX(this.core.width / 2).strength(0.1))
            .force("y", d3.forceY(this.core.height / 2).strength(0.1));
        
        // Add relationship-based forces
        this.addRelationshipForces();
        
        // Run simulation for a few ticks to get initial positions
        for (let i = 0; i < 100; i++) {
            this.simulation.tick();
        }
        
        // Create positioned nodes from simulation results
        const positionedNodes = this.core.treeData.nodes.map(node => ({
            id: node.id,
            x: node.x || this.core.width / 2,
            y: node.y || this.core.height / 2,
            data: node
        }));
        
        return {
            descendants: () => positionedNodes,
            simulation: this.simulation
        };
    }
    
    addRelationshipForces() {
        // Add different forces based on relationship types
        const parentChildLinks = this.core.links.filter(link => link.type === 'parent-child');
        const spouseLinks = this.core.links.filter(link => link.type === 'SPOUSE_OF');
        const siblingLinks = this.core.links.filter(link => link.type === 'SIBLING_OF');
        
        // Parent-child relationships: stronger attraction, shorter distance
        if (parentChildLinks.length > 0) {
            this.simulation.force("parent-child", d3.forceLink(parentChildLinks)
                .id(d => d.id)
                .distance(80)
                .strength(0.8));
        }
        
        // Spouse relationships: medium attraction, medium distance
        if (spouseLinks.length > 0) {
            this.simulation.force("spouse", d3.forceLink(spouseLinks)
                .id(d => d.id)
                .distance(60)
                .strength(0.6));
        }
        
        // Sibling relationships: weaker attraction, longer distance
        if (siblingLinks.length > 0) {
            this.simulation.force("sibling", d3.forceLink(siblingLinks)
                .id(d => d.id)
                .distance(120)
                .strength(0.4));
        }
        
        // Add generational forces based on birth dates
        this.addGenerationalForces();
    }
    
    addGenerationalForces() {
        // Group nodes by generation (birth decade)
        const generations = new Map();
        this.core.treeData.nodes.forEach(node => {
            const birthYear = this.getBirthYear(node);
            const decade = Math.floor(birthYear / 10) * 10;
            if (!generations.has(decade)) generations.set(decade, []);
            generations.get(decade).push(node);
        });
        
        // Add forces to keep generations roughly aligned
        const decades = Array.from(generations.keys()).sort((a, b) => a - b);
        decades.forEach((decade, index) => {
            const nodes = generations.get(decade);
            const yPosition = this.core.margin.top + (index + 1) * 100;
            
            nodes.forEach(node => {
                // Add a weak force to pull nodes toward their generational line
                this.simulation.force(`generation-${decade}`, d3.forceY(yPosition).strength(0.05));
            });
        });
    }
    
    // Method to update simulation when nodes are dragged
    updateSimulation() {
        if (this.simulation) {
            this.simulation.alpha(0.3).restart();
        }
    }
    
    // Method to stop simulation
    stopSimulation() {
        if (this.simulation) {
            this.simulation.stop();
        }
    }
    
    // Method to restart simulation
    restartSimulation() {
        if (this.simulation) {
            this.simulation.alpha(1).restart();
        }
    }
    
    // Override validateLayoutResult to handle simulation
    validateLayoutResult(layoutResult, layoutName) {
        if (!layoutResult || !layoutResult.descendants) {
            console.warn(`Layout '${layoutName}' returned invalid result, using fallback`);
            return this.createSingleNodeLayout(this.core.treeData.nodes[0] || {id: 'default', name: 'Default'}, 
                this.core.width / 2, this.core.height / 2);
        }
        
        const descendants = layoutResult.descendants();
        if (!Array.isArray(descendants) || descendants.length === 0) {
            console.warn(`Layout '${layoutName}' returned empty descendants, using fallback`);
            return this.createSingleNodeLayout(this.core.treeData.nodes[0] || {id: 'default', name: 'Default'}, 
                this.core.width / 2, this.core.height / 2);
        }
        
        // Ensure all descendants have valid coordinates
        descendants.forEach(d => {
            if (typeof d.x !== 'number' || isNaN(d.x)) {
                console.warn(`Invalid x coordinate for node ${d.id} in layout '${layoutName}', using fallback`);
                d.x = this.core.width / 2;
            }
            if (typeof d.y !== 'number' || isNaN(d.y)) {
                console.warn(`Invalid y coordinate for node ${d.id} in layout '${layoutName}', using fallback`);
                d.y = this.core.height / 2;
            }
        });
        
        return layoutResult;
    }
}
</script>
@endpush 