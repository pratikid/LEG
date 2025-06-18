@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-8">
    <div class="bg-gray-800 rounded-lg shadow-lg max-w-xl mx-auto p-8">
        <h1 class="text-2xl font-bold text-white mb-6">Import Tree</h1>
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
                <label class="block text-sm font-medium text-gray-300 mb-2" for="gedcom">GEDCOM File</label>
                <input type="file" name="gedcom" id="gedcom" accept=".ged,.gedcom" required class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">Import</button>
        </form>
        @if(isset($importStatus))
            <p class="mt-4 text-green-400">{{ $importStatus }}</p>
        @endif
    </div>
</div>
@endsection 