@extends('layouts.app')
@section('content')
<div class="container mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-4">Create Individual</h1>
    <form method="POST" action="{{ route('individuals.store') }}" class="bg-gray-900 p-6 rounded shadow max-w-lg mx-auto">
        @csrf
        <div class="mb-4">
            <label for="first_name" class="block mb-1 font-semibold">First Name <span class="text-red-500">*</span></label>
            <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600">
            @error('first_name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="last_name" class="block mb-1 font-semibold">Last Name <span class="text-red-500">*</span></label>
            <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600">
            @error('last_name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="sex" class="block mb-1 font-semibold">Sex <span class="text-red-500">*</span></label>
            <select name="sex" id="sex" required class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="M" @if(old('sex') == 'M') selected @endif>Male</option>
                <option value="F" @if(old('sex') == 'F') selected @endif>Female</option>
            </select>
            @error('sex')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="birth_date" class="block mb-1 font-semibold">Birth Date</label>
            <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}" class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600">
            @error('birth_date')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="death_date" class="block mb-1 font-semibold">Death Date</label>
            <input type="date" name="death_date" id="death_date" value="{{ old('death_date') }}" class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600">
            @error('death_date')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
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
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Create Individual</button>
    </form>
</div>
@endsection 