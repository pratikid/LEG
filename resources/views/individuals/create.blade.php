@extends('layouts.app')
@section('content')
<div class="container mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-4">Create Individual</h1>
    <form method="POST" action="#">
        @csrf
        <!-- Add form fields here -->
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Create</button>
    </form>
</div>
@endsection 