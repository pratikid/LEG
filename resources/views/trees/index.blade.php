@extends('layouts.app')

@section('content')
    <div class="container mx-auto max-w-4xl mt-10 p-6 bg-white dark:bg-gray-800 dark:text-white rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Trees</h1>
        @if(isset($trees) && $trees && count($trees))
            <ul>
                @foreach($trees as $tree)
                    <li class="py-2 border-b border-gray-200 dark:border-gray-700">{{ $tree->name ?? '' }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-400">No trees found.</p>
        @endif
    </div>
@endsection 