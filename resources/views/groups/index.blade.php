@extends('layouts.app')
@section('content')
<div class="container mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-4">Groups</h1>
    @if(isset($groups) && $groups && count($groups))
        <ul>
            @foreach($groups as $group)
                <li>{{ $group->name ?? '' }}</li>
            @endforeach
        </ul>
    @else
        <p class="text-gray-400">No groups found.</p>
    @endif
</div>
@endsection 