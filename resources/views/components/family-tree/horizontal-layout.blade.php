@push('scripts')
<script>
// Horizontal Tree Layout
class HorizontalTreeLayout extends BaseLayout {
    constructor(core) {
        super(core);
    }
    
    generate() {
        const tree = d3.tree().size([
            this.core.height - this.core.margin.top - this.core.margin.bottom, 
            this.core.width - this.core.margin.left - this.core.margin.right
        ]);
        
        const root = this.createTreeStructure();
        const treeResult = tree(root);
        
        // Swap x and y coordinates for horizontal layout
        treeResult.descendants().forEach(d => {
            [d.x, d.y] = [d.y, d.x];
        });
        
        return this.validateLayoutResult(treeResult, 'horizontal');
    }
}
</script>
@endpush 