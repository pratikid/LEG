@extends('layouts.app')
@section('content')
<div class="container mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-4">Edit Individual (ID: {{ $id ?? '-' }})</h1>
    <form method="POST" action="#">
        @csrf
        @method('PUT')
        <!-- Add form fields here, use old('field', '') for safe defaults -->
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
    </form>
</div>
@endsection 