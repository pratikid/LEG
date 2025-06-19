@props(['treeId'])

<div id="import-progress-{{ $treeId }}" class="hidden bg-gray-800 rounded-lg p-4 mb-4">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-lg font-semibold text-white">Import Progress</h3>
        <span id="progress-status-{{ $treeId }}" class="text-sm text-gray-300">Processing...</span>
    </div>
    
    <div class="w-full bg-gray-700 rounded-full h-2 mb-2">
        <div id="progress-bar-{{ $treeId }}" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
    </div>
    
    <div class="flex justify-between text-sm text-gray-400">
        <span id="progress-text-{{ $treeId }}">0 / 0 records processed</span>
        <span id="progress-percentage-{{ $treeId }}">0%</span>
    </div>
    
    <div id="progress-error-{{ $treeId }}" class="hidden mt-2 p-2 bg-red-900 border border-red-700 rounded text-red-200 text-sm"></div>
    
    <div id="progress-success-{{ $treeId }}" class="hidden mt-2 p-2 bg-green-900 border border-green-700 rounded text-green-200 text-sm">
        Import completed successfully! You can now view your tree.
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const progressContainer = document.getElementById('import-progress-{{ $treeId }}');
    const progressBar = document.getElementById('progress-bar-{{ $treeId }}');
    const progressStatus = document.getElementById('progress-status-{{ $treeId }}');
    const progressText = document.getElementById('progress-text-{{ $treeId }}');
    const progressPercentage = document.getElementById('progress-percentage-{{ $treeId }}');
    const progressError = document.getElementById('progress-error-{{ $treeId }}');
    const progressSuccess = document.getElementById('progress-success-{{ $treeId }}');
    
    let pollInterval;
    
    function updateProgress() {
        fetch(`/import-progress/{{ $treeId }}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Progress error:', data.error);
                    return;
                }
                
                progressContainer.classList.remove('hidden');
                
                const percentage = data.progress_percentage || 0;
                progressBar.style.width = percentage + '%';
                progressPercentage.textContent = percentage + '%';
                progressText.textContent = `${data.processed_records || 0} / ${data.total_records || 0} records processed`;
                
                switch (data.status) {
                    case 'pending':
                        progressStatus.textContent = 'Waiting to start...';
                        progressStatus.className = 'text-sm text-yellow-400';
                        break;
                    case 'processing':
                        progressStatus.textContent = 'Processing...';
                        progressStatus.className = 'text-sm text-blue-400';
                        break;
                    case 'completed':
                        progressStatus.textContent = 'Completed';
                        progressStatus.className = 'text-sm text-green-400';
                        progressSuccess.classList.remove('hidden');
                        clearInterval(pollInterval);
                        break;
                    case 'failed':
                        progressStatus.textContent = 'Failed';
                        progressStatus.className = 'text-sm text-red-400';
                        progressError.textContent = data.error_message || 'Import failed';
                        progressError.classList.remove('hidden');
                        clearInterval(pollInterval);
                        break;
                }
            })
            .catch(error => {
                console.error('Error fetching progress:', error);
            });
    }
    
    // Start polling for progress updates
    updateProgress();
    pollInterval = setInterval(updateProgress, 2000); // Poll every 2 seconds
    
    // Stop polling after 10 minutes to prevent infinite polling
    setTimeout(() => {
        if (pollInterval) {
            clearInterval(pollInterval);
        }
    }, 600000);
});
</script> 