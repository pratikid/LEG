@push('scripts')
<script>
// Bowtie Chart Layout
class BowtieLayout extends BaseLayout {
    constructor(core) {
        super(core);
    }
    
    generate() {
        const centerX = this.core.width / 2;
        const centerY = this.core.height / 2;
        const radius = Math.min(this.core.width, this.core.height) / 3;
        
        // Find ancestors and descendants
        const ancestors = new Set();
        const descendants = new Set();
        const rootNodes = this.getRootNodes();
        
        if (rootNodes.length > 0) {
            const root = rootNodes[0];
            
            // Find descendants
            const queue = [root];
            while (queue.length > 0) {
                const node = queue.shift();
                descendants.add(node.id);
                
                this.core.links.filter(link => link.source.id === node.id && link.type === 'parent-child')
                    .forEach(link => {
                        if (!descendants.has(link.target.id)) {
                            queue.push(link.target);
                        }
                    });
            }
            
            // Find ancestors
            const ancestorQueue = [root];
            while (ancestorQueue.length > 0) {
                const node = ancestorQueue.shift();
                ancestors.add(node.id);
                
                this.core.links.filter(link => link.target.id === node.id && link.type === 'parent-child')
                    .forEach(link => {
                        if (!ancestors.has(link.source.id)) {
                            ancestorQueue.push(link.source);
                        }
                    });
            }
        }
        
        // Position nodes
        const positionedNodes = this.core.treeData.nodes.map((node, index) => {
            let x, y;
            if (ancestors.has(node.id)) {
                // Position ancestors in left half-circle
                const angle = (index / ancestors.size) * Math.PI - Math.PI / 2;
                x = centerX - radius * Math.cos(angle);
                y = centerY + radius * Math.sin(angle);
            } else if (descendants.has(node.id)) {
                // Position descendants in right half-circle
                const angle = (index / descendants.size) * Math.PI + Math.PI / 2;
                x = centerX + radius * Math.cos(angle);
                y = centerY + radius * Math.sin(angle);
            } else {
                // Position others in center
                x = centerX;
                y = centerY;
            }
            
            return {
                id: node.id,
                x: x,
                y: y,
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