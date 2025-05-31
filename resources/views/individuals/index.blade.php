@extends('layouts.app')
@section('content')
<div class="container mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-4">Individuals</h1>
    @if(isset($individuals) && $individuals && count($individuals))
        <ul>
            @foreach($individuals as $individual)
                <li>{{ $individual->first_name ?? '' }} {{ $individual->last_name ?? '' }}</li>
            @endforeach
        </ul>
    @else
        <p class="text-gray-400">No individuals found.</p>
    @endif
</div>
@endsection 