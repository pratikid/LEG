@push('scripts')
<script>
// Vertical Tree Layout
class VerticalTreeLayout extends BaseLayout {
    constructor(core) {
        super(core);
    }
    
    generate() {
        const tree = d3.tree().size([
            this.core.width - this.core.margin.left - this.core.margin.right, 
            this.core.height - this.core.margin.top - this.core.margin.bottom
        ]);
        
        const root = this.createTreeStructure();
        const treeResult = tree(root);
        
        return this.validateLayoutResult(treeResult, 'vertical');
    }
}
</script>
@endpush 