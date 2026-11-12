@push('scripts')
<script>
// Pedigree Chart Layout
class PedigreeLayout extends BaseLayout {
    constructor(core) {
        super(core);
    }
    
    generate() {
        // Group by generation
        const generations = new Map();
        const generationById = new Map();
        const nodeIndexByGeneration = new Map();
        const visited = new Set();
        
        // Find root nodes (no parents)
        const rootNodes = this.getRootNodes();
        
        // BFS to assign generations
        const queue = rootNodes.map(node => ({node, gen: 0}));
        let queueIndex = 0;
        while (queueIndex < queue.length) {
            const {node, gen} = queue[queueIndex++];
            if (visited.has(node.id)) continue;
            visited.add(node.id);
            
            if (!generations.has(gen)) generations.set(gen, []);
            generations.get(gen).push(node);
            generationById.set(node.id, gen);
            nodeIndexByGeneration.set(node.id, generations.get(gen).length - 1);
            
            // Add children to queue
            (this.core.childrenById.get(node.id) || []).forEach(child => {
                if (!visited.has(child.id)) queue.push({node: child, gen: gen + 1});
            });
        }
        
        // Position nodes by generation
        const genWidth = (this.core.width - this.core.margin.left - this.core.margin.right) / Math.max(generations.size, 1);
        const nodeHeight = 80;
        
        const positionedNodes = this.core.treeData.nodes.map(node => {
            const gen = generationById.get(node.id) || 0;
            const nodeIndex = nodeIndexByGeneration.get(node.id) ?? 0;
            
            return {
                id: node.id,
                x: this.core.margin.left + gen * genWidth + genWidth / 2,
                y: this.core.margin.top + (nodeIndex + 1) * nodeHeight,
                data: node
            };
        });
        
        return {
            descendants: () => positionedNodes
        };
    }
}
</script>
@endpush 