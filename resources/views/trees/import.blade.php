@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Import GEDCOM File</h2>
    <form method="POST" action="{{ route('trees.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2" for="gedcom">GEDCOM File</label>
            <input type="file" name="gedcom" id="gedcom" accept=".ged,.gedcom" required class="block w-full border border-gray-300 rounded p-2">
        </div>
        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded">Import</button>
    </form>
</div>
@endsection 