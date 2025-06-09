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
    </div>

    <x-family-tree :treeData="$treeDataJson" />
</div>
@endsection 