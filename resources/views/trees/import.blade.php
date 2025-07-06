@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-8">
    <div class="bg-gray-800 rounded-lg shadow-lg max-w-xl mx-auto p-8">
        <h1 class="text-2xl font-bold text-white mb-6">Import Tree</h1>
        
        <!-- Progress tracking for recent imports -->
        @if(auth()->user())
            @php
                $recentImports = \App\Models\ImportProgress::where('user_id', auth()->id())
                    ->whereIn('status', ['pending', 'processing'])
                    ->with('tree')
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get();
            @endphp
            
            @if($recentImports->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white mb-3">Recent Imports</h3>
                    @foreach($recentImports as $import)
                        <x-import-progress :treeId="$import->tree_id" />
                    @endforeach
                </div>
            @endif
        @endif
        
        <form method="POST" action="{{ route('trees.import') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2" for="tree_id">Select Empty Tree (optional)</label>
                <select name="tree_id" id="tree_id" class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Create New Tree --</option>
                    @foreach($emptyTrees as $tree)
                        <option value="{{ $tree->id }}">{{ $tree->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">If you select a tree, the GEDCOM will be imported into it. Otherwise, a new tree will be created.</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2" for="import_method">Import Method</label>
                <select name="import_method" id="import_method" class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="standard">Standard Import (Multi-Database)</option>
                    <option value="optimized">Optimized Import (Parallel Processing)</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">
                    <strong>Standard:</strong> Traditional multi-database import with transaction safety.<br>
                    <strong>Optimized:</strong> Parallel processing with memory optimization for large files.
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2" for="gedcom">GEDCOM File</label>
                <input type="file" name="gedcom" id="gedcom" accept=".ged,.gedcom" required class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">Maximum file size: 10MB. Supported formats: .ged, .gedcom</p>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition w-full">
                Start Import
            </button>
        </form>
        
        @if(isset($importStatus))
            <p class="mt-4 text-green-400">{{ $importStatus }}</p>
        @endif
        
        <!-- Import Information -->
        <div class="mt-6 p-4 bg-gray-700 rounded-lg">
            <h4 class="text-sm font-semibold text-white mb-2">Import Process</h4>
            <ul class="text-xs text-gray-300 space-y-1">
                <li>• Your file will be processed in the background</li>
                <li>• You'll receive a notification when complete</li>
                <li>• Large files may take several minutes to process</li>
                <li>• You can continue using the application while importing</li>
                <li>• Performance metrics will be tracked for comparison</li>
            </ul>
        </div>
        
        <!-- Method Comparison -->
        <div class="mt-4 p-4 bg-gray-700 rounded-lg">
            <h4 class="text-sm font-semibold text-white mb-2">Method Comparison</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-300">
                <div>
                    <h5 class="font-semibold text-blue-400 mb-1">Standard Import</h5>
                    <ul class="space-y-1">
                        <li>• Transaction-safe</li>
                        <li>• Sequential processing</li>
                        <li>• Better for small files</li>
                        <li>• Consistent performance</li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-semibold text-green-400 mb-1">Optimized Import</h5>
                    <ul class="space-y-1">
                        <li>• Parallel processing</li>
                        <li>• Memory optimization</li>
                        <li>• Better for large files</li>
                        <li>• Faster processing</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 