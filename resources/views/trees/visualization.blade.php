@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-8">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('trees.show', $tree->id) }}" class="text-blue-400 hover:text-blue-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Tree Visualization - {{ $tree->name }}</h1>
        </div>
        
        <!-- Layout Dropdown -->
        <div class="relative">
            <select id="layoutSelect" class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="force">Force-Directed Graph</option>
                <option value="vertical">Vertical Tree</option>
                <option value="horizontal">Horizontal Tree</option>
                <option value="radial">Radial Tree</option>
                <option value="fan">Fan Chart</option>
                <option value="pedigree">Pedigree Chart</option>
                <option value="descendant">Descendant Chart</option>
                <option value="adjacency">Adjacency Matrix</option>
                <option value="bowtie">Bowtie Chart</option>
                <option value="time">Time-based Layout</option>
                <option value="geographical">Geographical Layout</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="relative" id="tree-visualization-container">
        <!-- Left Side Controls -->
        <div class="absolute left-4 top-4 z-10 flex flex-col space-y-2">
            <!-- Zoom In -->
            <button id="zoomIn" class="control-btn" title="Zoom In">
                <span class="text-lg font-bold">+</span>
            </button>
            
            <!-- Zoom Out -->
            <button id="zoomOut" class="control-btn" title="Zoom Out">
                <span class="text-lg font-bold">−</span>
            </button>
            
            <!-- Fit to Screen Toggle -->
            <button id="fitToScreen" class="control-btn" title="Fit to Screen">
                <span class="text-lg">⤢</span>
            </button>
            
            <!-- Layout Toggle -->
            <button id="toggleLayout" class="control-btn" title="Toggle Layout">
                <span class="text-lg">⇄</span>
            </button>
            
            <!-- Highlight Direct Line Toggle -->
            <button id="highlightDirectLine" class="control-btn" title="Highlight Direct Line">
                <span class="text-lg">⭘</span>
            </button>
            
            <!-- Reset Positions -->
            <button id="resetPositions" class="control-btn" title="Reset Positions">
                <span class="text-lg">↺</span>
            </button>
        </div>

        <!-- Right Side Controls -->
        <div class="absolute right-4 top-4 z-10 flex flex-col space-y-2">
            <!-- Fullscreen Toggle -->
            <button id="fullscreenToggle" class="control-btn" title="Toggle Fullscreen">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
            </button>
            
            <!-- Add Person -->
            <div class="relative">
                <button id="showAddMenu" class="control-btn" title="Add Person">
                    <span class="text-lg">＋</span>
                </button>
                <div id="addMenu" class="hidden absolute right-0 top-full mt-2 bg-white border border-gray-300 rounded-lg shadow-lg p-2 min-w-48">
                    <button class="menu-item" data-action="addIndividual">
                        <span class="mr-2">👤</span> Add Individual
                    </button>
                    <button class="menu-item" data-action="addPartner">
                        <span class="mr-2">💑</span> Add Partner
                    </button>
                    <button class="menu-item" data-action="addChild">
                        <span class="mr-2">👶</span> Add Child
                    </button>
                    <button class="menu-item" data-action="addParents">
                        <span class="mr-2">👨‍👩‍👧</span> Add Parents
                    </button>
                </div>
            </div>
            
            <!-- Export Toggle -->
            <div class="relative">
                <button id="showExportMenu" class="control-btn" title="Export">
                    <span class="text-lg">⤓</span>
                </button>
                <div id="exportMenu" class="hidden absolute right-0 top-full mt-2 bg-white border border-gray-300 rounded-lg shadow-lg p-2 min-w-48">
                    <button class="menu-item" data-action="saveAsImage">
                        <span class="mr-2">🖼️</span> Save as Image
                    </button>
                    <button class="menu-item" data-action="generatePDF">
                        <span class="mr-2">📄</span> Generate PDF
                    </button>
                    <button class="menu-item" data-action="copyShareLink">
                        <span class="mr-2">📋</span> Copy Share Link
                    </button>
                </div>
            </div>
            
            <!-- Navigation Reset -->
            <button id="resetView" class="control-btn" title="Reset View">
                <span class="text-lg">⌂</span>
            </button>
        </div>

        <x-family-tree :treeData="$treeData" />
    </div>
</div>

<!-- Tree Data Script -->
<script type="application/json" id="tree-data">
    {!! json_encode($treeData) !!}
</script>

<style>
.control-btn {
    width: 2.5rem;
    height: 2.5rem;
    background-color: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
    transition: all 0.2s;
}

.control-btn:hover {
    background-color: #f9fafb;
    border-color: #9ca3af;
}

.menu-item {
    width: 100%;
    text-align: left;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    color: #374151;
    border-radius: 0.25rem;
    transition: background-color 0.15s;
}

.menu-item:hover {
    background-color: #f3f4f6;
}

/* Fullscreen styles */
#tree-visualization-container.fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    background-color: white;
    padding: 1rem;
}

#tree-visualization-container.fullscreen .family-tree-container {
    width: 100%;
    height: 100%;
}

#tree-visualization-container.fullscreen #tree-container {
    width: 100% !important;
    height: 100% !important;
}

#tree-visualization-container.fullscreen #tree-container svg {
    width: 100% !important;
    height: 100% !important;
}

/* Fullscreen button icon states */
#fullscreenToggle.fullscreen-active svg {
    transform: rotate(180deg);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fullscreenToggle = document.getElementById('fullscreenToggle');
    const container = document.getElementById('tree-visualization-container');
    const treeContainer = document.getElementById('tree-container');
    
    // Fullscreen functionality
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            // Enter fullscreen
            if (container.requestFullscreen) {
                container.requestFullscreen();
            } else if (container.webkitRequestFullscreen) {
                container.webkitRequestFullscreen();
            } else if (container.msRequestFullscreen) {
                container.msRequestFullscreen();
            }
        } else {
            // Exit fullscreen
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }
    
    // Update fullscreen button state
    function updateFullscreenButton() {
        if (document.fullscreenElement) {
            fullscreenToggle.classList.add('fullscreen-active');
            container.classList.add('fullscreen');
            // Resize tree container for fullscreen
            if (treeContainer) {
                treeContainer.style.width = '100%';
                treeContainer.style.height = '100%';
                // Call resizeSVG method on the family tree instance
                if (window.familyTree && typeof window.familyTree.resizeSVG === 'function') {
                    setTimeout(() => {
                        window.familyTree.resizeSVG();
                    }, 100); // Small delay to ensure DOM updates are complete
                }
            }
        } else {
            fullscreenToggle.classList.remove('fullscreen-active');
            container.classList.remove('fullscreen');
            // Reset tree container size
            if (treeContainer) {
                treeContainer.style.width = '100%';
                treeContainer.style.height = '1100px';
                // Call resizeSVG method on the family tree instance
                if (window.familyTree && typeof window.familyTree.resizeSVG === 'function') {
                    setTimeout(() => {
                        window.familyTree.resizeSVG();
                    }, 100); // Small delay to ensure DOM updates are complete
                }
            }
        }
    }
    
    // Event listeners
    fullscreenToggle.addEventListener('click', toggleFullscreen);
    
    // Listen for fullscreen change events
    document.addEventListener('fullscreenchange', updateFullscreenButton);
    document.addEventListener('webkitfullscreenchange', updateFullscreenButton);
    document.addEventListener('mozfullscreenchange', updateFullscreenButton);
    document.addEventListener('MSFullscreenChange', updateFullscreenButton);
    
    // Handle window resize events for fullscreen mode
    window.addEventListener('resize', function() {
        if (document.fullscreenElement && window.familyTree && typeof window.familyTree.resizeSVG === 'function') {
            setTimeout(() => {
                window.familyTree.resizeSVG();
            }, 100);
        }
    });
    
    // Handle escape key to exit fullscreen
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.fullscreenElement) {
            toggleFullscreen();
        }
    });
    
    // Initialize button state
    updateFullscreenButton();
});
</script>
@endsection 