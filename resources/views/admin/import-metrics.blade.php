@extends('layouts.app')

@section('content')
<x-admin-nav />
<div class="container mx-auto px-4">
    <div class="bg-gray-800 rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold text-white mb-6">Import Performance Metrics</h1>
        
        <!-- Performance Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-blue-400 mb-2">Standard Imports</h3>
                <div class="text-2xl font-bold text-white" id="standard-total">-</div>
                <div class="text-sm text-gray-400" id="standard-success-rate">Success Rate: -</div>
            </div>
            
            <div class="bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-green-400 mb-2">Optimized Imports</h3>
                <div class="text-2xl font-bold text-white" id="optimized-total">-</div>
                <div class="text-sm text-gray-400" id="optimized-success-rate">Success Rate: -</div>
            </div>
            
            <div class="bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-yellow-400 mb-2">Duration Improvement</h3>
                <div class="text-2xl font-bold text-white" id="duration-improvement">-</div>
                <div class="text-sm text-gray-400">vs Standard Method</div>
            </div>
            
            <div class="bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-purple-400 mb-2">Throughput Improvement</h3>
                <div class="text-2xl font-bold text-white" id="throughput-improvement">-</div>
                <div class="text-sm text-gray-400">vs Standard Method</div>
            </div>
        </div>
        
        <!-- Method Comparison -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-700 rounded-lg p-6">
                <h3 class="text-xl font-semibold text-blue-400 mb-4">Standard Import Metrics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-300">Average Duration:</span>
                        <span class="text-white font-semibold" id="standard-avg-duration">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Average Throughput:</span>
                        <span class="text-white font-semibold" id="standard-avg-throughput">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Average Memory:</span>
                        <span class="text-white font-semibold" id="standard-avg-memory">-</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-700 rounded-lg p-6">
                <h3 class="text-xl font-semibold text-green-400 mb-4">Optimized Import Metrics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-300">Average Duration:</span>
                        <span class="text-white font-semibold" id="optimized-avg-duration">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Average Throughput:</span>
                        <span class="text-white font-semibold" id="optimized-avg-throughput">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Average Memory:</span>
                        <span class="text-white font-semibold" id="optimized-avg-memory">-</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Imports Table -->
        <div class="bg-gray-700 rounded-lg p-6">
            <h3 class="text-xl font-semibold text-white mb-4">Recent Imports</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-300">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-600">
                        <tr>
                            <th class="px-6 py-3">Method</th>
                            <th class="px-6 py-3">Duration</th>
                            <th class="px-6 py-3">Records</th>
                            <th class="px-6 py-3">Throughput</th>
                            <th class="px-6 py-3">Memory</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Time</th>
                        </tr>
                    </thead>
                    <tbody id="recent-imports-tbody">
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-400">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Refresh Button -->
        <div class="mt-6 text-center">
            <button onclick="loadMetrics()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                Refresh Metrics
            </button>
        </div>
    </div>
</div>

<script>
async function loadMetrics() {
    try {
        // Load summary metrics
        const summaryResponse = await fetch('/api/v1/import-metrics/summary');
        const summaryData = await summaryResponse.json();
        
        if (summaryData.success) {
            updateSummaryMetrics(summaryData.data);
        }
        
        // Load recent metrics
        const recentResponse = await fetch('/api/v1/import-metrics/recent?hours=24');
        const recentData = await recentResponse.json();
        
        if (recentData.success) {
            updateRecentImportsTable(recentData.data);
        }
        
    } catch (error) {
        console.error('Error loading metrics:', error);
    }
}

function updateSummaryMetrics(data) {
    // Update summary cards
    document.getElementById('standard-total').textContent = data.total_imports.standard;
    document.getElementById('optimized-total').textContent = data.total_imports.optimized;
    document.getElementById('standard-success-rate').textContent = `Success Rate: ${data.success_rates.standard}%`;
    document.getElementById('optimized-success-rate').textContent = `Success Rate: ${data.success_rates.optimized}%`;
    document.getElementById('duration-improvement').textContent = `${data.performance_improvements.duration}%`;
    document.getElementById('throughput-improvement').textContent = `${data.performance_improvements.throughput}%`;
    
    // Update method comparison
    document.getElementById('standard-avg-duration').textContent = `${data.average_metrics.standard.duration}s`;
    document.getElementById('standard-avg-throughput').textContent = `${data.average_metrics.standard.throughput} rec/s`;
    document.getElementById('standard-avg-memory').textContent = `${data.average_metrics.standard.memory} MB`;
    
    document.getElementById('optimized-avg-duration').textContent = `${data.average_metrics.optimized.duration}s`;
    document.getElementById('optimized-avg-throughput').textContent = `${data.average_metrics.optimized.throughput} rec/s`;
    document.getElementById('optimized-avg-memory').textContent = `${data.average_metrics.optimized.memory} MB`;
}

function updateRecentImportsTable(data) {
    const tbody = document.getElementById('recent-imports-tbody');
    const allImports = [];
    
    // Combine imports from both methods
    if (data.standard) {
        allImports.push(...data.standard.map(import => ({ ...import, method: 'Standard' })));
    }
    if (data.optimized) {
        allImports.push(...data.optimized.map(import => ({ ...import, method: 'Optimized' })));
    }
    
    // Sort by timestamp (newest first)
    allImports.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
    
    // Take only the last 10 imports
    const recentImports = allImports.slice(0, 10);
    
    if (recentImports.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-400">No recent imports found</td></tr>';
        return;
    }
    
    tbody.innerHTML = recentImports.map(function(importItem) {
        return '<tr class="border-b border-gray-600">' +
            '<td class="px-6 py-4">' +
                '<span class="px-2 py-1 text-xs rounded-full ' + (importItem.method === 'Standard' ? 'bg-blue-600 text-blue-100' : 'bg-green-600 text-green-100') + '">' +
                    importItem.method +
                '</span>' +
            '</td>' +
            '<td class="px-6 py-4">' + importItem.duration_seconds + 's</td>' +
            '<td class="px-6 py-4">' + importItem.total_records + '</td>' +
            '<td class="px-6 py-4">' + importItem.records_per_second + ' rec/s</td>' +
            '<td class="px-6 py-4">' + importItem.memory_used_mb + ' MB</td>' +
            '<td class="px-6 py-4">' +
                '<span class="px-2 py-1 text-xs rounded-full ' + (importItem.success ? 'bg-green-600 text-green-100' : 'bg-red-600 text-red-100') + '">' +
                    (importItem.success ? 'Success' : 'Failed') +
                '</span>' +
            '</td>' +
            '<td class="px-6 py-4 text-sm">' + new Date(importItem.timestamp).toLocaleString() + '</td>' +
        '</tr>';
    }).join('');
}

// Load metrics on page load
document.addEventListener('DOMContentLoaded', loadMetrics);

// Auto-refresh every 30 seconds
setInterval(loadMetrics, 30000);
</script>
@endsection 