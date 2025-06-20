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
        
        // Use tree layout for descendants
        const tree = d3.tree().size([
            this.core.width - this.core.margin.left - this.core.margin.right, 
            this.core.height - this.core.margin.top - this.core.margin.bottom
        ]);
        const treeNodes = this.core.treeData.nodes.filter(node => descendants.has(node.id));
        const rootStratify = d3.stratify()
            .id(d => d.id)
            .parentId(d => {
                const parentLink = this.core.links.find(link => link.target.id === d.id && link.type === 'parent-child');
                return parentLink ? parentLink.source.id : null;
            })(treeNodes);
        
        return tree(rootStratify);
    }
}
</script>
@endpush 