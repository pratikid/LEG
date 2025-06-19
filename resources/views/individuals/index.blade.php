@extends('layouts.app')
@section('content')
<div class="container mx-auto mt-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Individuals</h1>
        <a href="{{ route('individuals.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            Add New Individual
        </a>
    </div>

    @if(isset($individuals) && $individuals && count($individuals))
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <ul class="divide-y divide-gray-200">
                @foreach($individuals as $individual)
                    <li class="hover:bg-gray-50 transition-colors duration-150">
                        <a href="{{ route('individuals.show', $individual->id) }}" class="block px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500 font-medium">
                                                {{ substr($individual->first_name ?? '', 0, 1) }}{{ substr($individual->last_name ?? '', 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $individual->first_name ?? '' }} {{ $individual->last_name ?? '' }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ ucfirst($individual->sex) }}
                                        </p>
                                        @if($individual->birth_date || $individual->death_date)
                                            <p class="text-sm text-gray-500">
                                                @php
                                                    $birthYear = $individual->birth_date ? (is_string($individual->birth_date) ? substr($individual->birth_date, 0, 4) : $individual->birth_date->format('Y')) : '?';
                                                    $deathYear = $individual->death_date ? (is_string($individual->death_date) ? substr($individual->death_date, 0, 4) : $individual->death_date->format('Y')) : 'Present';
                                                @endphp
                                                {{ $birthYear }} - {{ $deathYear }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <a href="{{ route('individuals.edit', $individual->id) }}" class="ml-3 bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m2-2l-6 6m2-2l6-6" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="bg-white shadow-sm rounded-lg p-6 text-center">
            <p class="text-gray-500">No individuals found.</p>
            <a href="{{ route('individuals.create') }}" class="inline-block mt-4 text-blue-500 hover:text-blue-600">
                Add your first individual
            </a>
        </div>
    @endif
</div>
@endsection 