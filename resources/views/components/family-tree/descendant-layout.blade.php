@push('scripts')
<script>
// Descendant Chart Layout
class DescendantLayout extends BaseLayout {
    constructor(core) {
        super(core);
    }
    
    generate() {
        const rootNodes = this.getRootNodes();
        
        if (rootNodes.length === 0) {
            return this.createSingleNodeLayout(this.core.treeData.nodes[0], 
                this.core.width / 2, this.core.height / 2);
        }
        
        const root = rootNodes[0]; // Use first root
        const descendants = new Set();
        const queue = [root];
        let queueIndex = 0;
        
        while (queueIndex < queue.length) {
            const node = queue[queueIndex++];
            descendants.add(node.id);
            
            (this.core.childrenById.get(node.id) || []).forEach(child => {
                if (!descendants.has(child.id)) queue.push(child);
            });
        }
        
        // Use tree layout for descendants
        const tree = d3.tree().size([
            this.core.width - this.core.margin.left - this.core.margin.right, 
            this.core.height - this.core.margin.top - this.core.margin.bottom
        ]);
        const treeNodes = this.core.treeData.nodes.filter(node => descendants.has(node.id));
        const rootStratify = d3.stratify()
            .id(d => d.id)
            .parentId(d => {
                const parentId = this.core.parentById.get(d.id);
                return parentId && descendants.has(parentId) ? parentId : null;
            })(treeNodes);
        
        return tree(rootStratify);
    }
}
</script>
@endpush 