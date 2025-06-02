@extends('layouts.app')
@section('content')
<div class="container mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-4">Create Tree</h1>
    <form method="POST" action="{{ route('trees.store') }}" class="bg-gray-900 p-6 rounded shadow max-w-lg mx-auto">
        @csrf
        <div class="mb-4">
            <label for="name" class="block mb-1 font-semibold">Tree Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600">
            @error('name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="description" class="block mb-1 font-semibold">Description</label>
            <textarea name="description" id="description" rows="3" class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600">{{ old('description') }}</textarea>
            @error('description')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Create Tree</button>
    </form>
</div>
@endsection 