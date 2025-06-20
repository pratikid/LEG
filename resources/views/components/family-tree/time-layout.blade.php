@push('scripts')
<script>
// Time-based Layout
class TimeBasedLayout extends BaseLayout {
    constructor(core) {
        super(core);
    }
    
    generate() {
        // Group by birth year or generation
        const timeGroups = new Map();
        
        this.core.treeData.nodes.forEach(node => {
            const birthYear = this.getBirthYear(node);
            const decade = Math.floor(birthYear / 10) * 10;
            if (!timeGroups.has(decade)) timeGroups.set(decade, []);
            timeGroups.get(decade).push(node);
        });
        
        const decades = Array.from(timeGroups.keys()).sort((a, b) => a - b);
        const timeWidth = (this.core.width - this.core.margin.left - this.core.margin.right) / Math.max(decades.length, 1);
        const nodeHeight = 80;
        
        const positionedNodes = this.core.treeData.nodes.map(node => {
            const birthYear = this.getBirthYear(node);
            const decade = Math.floor(birthYear / 10) * 10;
            const decadeIndex = decades.indexOf(decade);
            const decadeNodes = timeGroups.get(decade) || [];
            const nodeIndex = decadeNodes.findIndex(n => n.id === node.id);
            
            return {
                id: node.id,
                x: this.core.margin.left + decadeIndex * timeWidth + timeWidth / 2,
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