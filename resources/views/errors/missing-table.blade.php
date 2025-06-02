@extends('layouts.app')
@section('title', 'Missing Database Table')
@section('content')
<div class="container mx-auto mt-16 max-w-xl bg-white p-8 rounded shadow text-center">
    <h1 class="text-3xl font-bold text-red-600 mb-4">Database Table Missing</h1>
    <p class="mb-4 text-gray-700">{{ $error }}</p>
    <pre class="bg-gray-100 text-xs text-gray-600 p-4 rounded mb-4 overflow-x-auto">{{ $details }}</pre>
    <p class="text-gray-500">If you are a developer, run <code>php artisan migrate</code> to create the missing tables.</p>
</div>
@endsection 