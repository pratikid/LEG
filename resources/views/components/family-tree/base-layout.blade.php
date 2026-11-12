@push('scripts')
<script>
// Base Layout Class
class BaseLayout {
    constructor(core) {
        this.core = core;
    }
    
    createSingleNodeLayout(node, x, y) {
        return {
            descendants: () => [{
                id: node.id,
                x: x,
                y: y,
                data: node
            }]
        };
    }
    
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
    
    getRootNodes() {
        return this.core.treeData.nodes.filter(node => !this.core.parentById.has(node.id));
    }
    
    createTreeStructure() {
        const rootNodes = this.getRootNodes();
        
        if (rootNodes.length === 0) {
            return this.createSingleNodeLayout(this.core.treeData.nodes[0], 
                this.core.width / 2, this.core.height / 2);
        }
        
        if (rootNodes.length === 1) {
            const root = d3.stratify()
                .id(d => d.id)
                .parentId(d => this.core.parentById.get(d.id) ?? null)(this.core.treeData.nodes);
            
            return root;
        } else {
            // Multiple roots - create a virtual root
            const virtualRoot = {
                id: 'virtual-root',
                name: 'Virtual Root',
                children: rootNodes.map(rootNode => ({
                    id: rootNode.id,
                    name: rootNode.name,
                    data: rootNode
                }))
            };
            
            const allNodes = [virtualRoot, ...this.core.treeData.nodes];
            
            const root = d3.stratify()
                .id(d => d.id)
                .parentId(d => {
                    if (d.id === 'virtual-root') return null;
                    return this.core.parentById.get(d.id) ?? 'virtual-root';
                })(allNodes);
            
            return root;
        }
    }
    
    getBirthYear(node) {
        let birthYear = 1900;
        if (node.birth_date && node.birth_date !== null && node.birth_date !== '') {
            const date = new Date(node.birth_date);
            if (!isNaN(date.getTime())) {
                birthYear = date.getFullYear();
            }
        }
        return birthYear;
    }
}
</script>
@endpush 