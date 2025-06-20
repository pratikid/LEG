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
        const visited = new Set();
        
        // Find root nodes (no parents)
        const rootNodes = this.getRootNodes();
        
        // BFS to assign generations
        const queue = rootNodes.map(node => ({node, gen: 0}));
        while (queue.length > 0) {
            const {node, gen} = queue.shift();
            if (visited.has(node.id)) continue;
            visited.add(node.id);
            
            if (!generations.has(gen)) generations.set(gen, []);
            generations.get(gen).push(node);
            
            // Add children to queue
            this.core.links.filter(link => link.source.id === node.id && link.type === 'parent-child')
                .forEach(link => {
                    queue.push({node: link.target, gen: gen + 1});
                });
        }
        
        // Position nodes by generation
        const genWidth = (this.core.width - this.core.margin.left - this.core.margin.right) / Math.max(generations.size, 1);
        const nodeHeight = 80;
        
        const positionedNodes = this.core.treeData.nodes.map(node => {
            const gen = Array.from(generations.entries())
                .find(([g, nodes]) => nodes.some(n => n.id === node.id))?.[0] || 0;
            const genNodes = generations.get(gen) || [];
            const nodeIndex = genNodes.findIndex(n => n.id === node.id);
            
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