@push('scripts')
<script>
// Fan Chart Layout
class FanTreeLayout extends BaseLayout {
    constructor(core) {
        super(core);
    }
    
    generate() {
        const radius = Math.min(this.core.width, this.core.height) / 2 - 100;
        const tree = d3.tree().size([180, radius]);
        
        const root = this.createTreeStructure();
        const treeResult = tree(root);
        
        // Convert to fan layout (limited angle range)
        treeResult.descendants().forEach(d => {
            const angle = (d.x - 90) / 180 * Math.PI;
            const radius = d.y;
            d.x = radius * Math.cos(angle) + this.core.width / 2;
            d.y = radius * Math.sin(angle) + this.core.height / 2;
        });
        
        return this.validateLayoutResult(treeResult, 'fan');
    }
}
</script>
@endpush 