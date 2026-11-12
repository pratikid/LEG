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
            let queueIndex = 0;
            while (queueIndex < queue.length) {
                const node = queue[queueIndex++];
                descendants.add(node.id);
                
                (this.core.childrenById.get(node.id) || []).forEach(child => {
                    if (!descendants.has(child.id)) queue.push(child);
                });
            }
            
            // Find ancestors
            const ancestorQueue = [root];
            let ancestorIndex = 0;
            while (ancestorIndex < ancestorQueue.length) {
                const node = ancestorQueue[ancestorIndex++];
                ancestors.add(node.id);
                
                (this.core.parentsById.get(node.id) || []).forEach(parent => {
                    if (!ancestors.has(parent.id)) ancestorQueue.push(parent);
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