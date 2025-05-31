@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Import Tree</h1>
    <form method="POST" action="#" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2" for="gedcom">GEDCOM File</label>
            <input type="file" name="gedcom" id="gedcom" accept=".ged,.gedcom" required class="block w-full border border-gray-300 rounded p-2">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Import</button>
    </form>
    @if(isset($importStatus))
        <p class="mt-4 text-green-600">{{ $importStatus }}</p>
    @endif
</div>
@endsection 