@extends('layouts.app')
@section('content')
<div class="container mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-4">Create Group</h1>
    <form method="POST" action="{{ route('groups.store') }}" class="bg-gray-900 p-6 rounded shadow max-w-lg mx-auto">
        @csrf
        <div class="mb-4">
            <label for="name" class="block mb-1 font-semibold">Group Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600">
            @error('name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="description" class="block mb-1 font-semibold">Description</label>
            <textarea name="description" id="description" rows="3" class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600">{{ old('description') }}</textarea>
            @error('description')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="tree_id" class="block mb-1 font-semibold">Tree <span class="text-red-500">*</span></label>
            @if(isset($error) && $error)
                <div class="text-red-500 text-xs mb-2">{{ $error }}</div>
            @endif
            <select name="tree_id" id="tree_id" required class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600" @if(isset($error) && $error) disabled @endif>
                <option value="">Select a tree</option>
                @foreach($trees as $tree)
                    <option value="{{ $tree->id }}" @if(old('tree_id') == $tree->id) selected @endif>{{ $tree->name }}</option>
                @endforeach
            </select>
            @error('tree_id')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Create Group</button>
    </form>
</div>
@endsection 