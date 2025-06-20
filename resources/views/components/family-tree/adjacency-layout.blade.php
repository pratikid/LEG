@push('scripts')
<script>
// Adjacency Matrix Layout
class AdjacencyMatrixLayout extends BaseLayout {
    constructor(core) {
        super(core);
    }
    
    generate() {
        const nodeSize = 60;
        const matrixSize = Math.ceil(Math.sqrt(this.core.treeData.nodes.length));
        const matrixWidth = matrixSize * nodeSize;
        const matrixHeight = matrixSize * nodeSize;
        const startX = (this.core.width - matrixWidth) / 2;
        const startY = (this.core.height - matrixHeight) / 2;
        
        const positionedNodes = this.core.treeData.nodes.map((node, index) => {
            const row = Math.floor(index / matrixSize);
            const col = index % matrixSize;
            return {
                id: node.id,
                x: startX + col * nodeSize + nodeSize / 2,
                y: startY + row * nodeSize + nodeSize / 2,
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