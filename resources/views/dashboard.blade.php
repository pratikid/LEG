@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold mb-6">
        Welcome back, {{ optional(Auth::user())->name ?? 'User' }}
    </h1>
    <!-- Recent Activity -->
    <section class="mb-10">
        <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
        <div class="space-y-6">
            @if(isset($activities) && $activities && count($activities))
                @foreach($activities as $activity)
                    <div class="flex items-start space-x-4">
                        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-700">{!! activityIcon($activity->action ?? '') !!}</span>
                        <div>
                            <p>{{ $activity->action ?? 'Activity' }} @if(!empty($activity->model_type)) on {{ class_basename($activity->model_type) }}@endif</p>
                            <span class="text-gray-400 text-sm">{{ optional($activity->created_at)->diffForHumans() ?? '-' }}</span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-gray-400">No recent activity</div>
            @endif
        </div>
    </section>
    <!-- Statistics -->
    <section class="mb-10">
        <h2 class="text-xl font-semibold mb-4">Family Tree Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-800 rounded-xl p-6 flex flex-col items-start">
                <span class="text-gray-400 mb-2">Total Members</span>
                <span class="text-3xl font-bold">{{ $totalMembers ?? 0 }}</span>
            </div>
            <div class="bg-gray-800 rounded-xl p-6 flex flex-col items-start">
                <span class="text-gray-400 mb-2">Generations</span>
                <span class="text-3xl font-bold">{{ $generations ?? '-' }}</span>
            </div>
            <div class="bg-gray-800 rounded-xl p-6 flex flex-col items-start">
                <span class="text-gray-400 mb-2">Photos</span>
                <span class="text-3xl font-bold">{{ $totalPhotos ?? 0 }}</span>
            </div>
        </div>
    </section>
    <!-- User's Trees -->
    <section class="mb-10 mt-10">
        <h2 class="text-xl font-semibold mb-4">Your Trees</h2>
        <ul>
            @if(isset($userTrees) && $userTrees && count($userTrees))
                @foreach($userTrees as $tree)
                    <li><a href="{{ route('trees.show', $tree->id) }}" class="text-blue-400 hover:underline">{{ $tree->name ?? 'Unnamed Tree' }}</a></li>
                @endforeach
            @else
                <li class="text-gray-400">No trees found.</li>
            @endif
        </ul>
    </section>
    <!-- Recent Individuals -->
    <section class="mb-10">
        <h2 class="text-xl font-semibold mb-4">Recently Added Individuals</h2>
        <ul>
            @if(isset($recentIndividuals) && $recentIndividuals && count($recentIndividuals))
                @foreach($recentIndividuals as $individual)
                    <li><a href="{{ route('individuals.show', $individual->id) }}" class="text-blue-400 hover:underline">{{ $individual->first_name ?? '' }} {{ $individual->last_name ?? '' }}</a></li>
                @endforeach
            @else
                <li class="text-gray-400">No recent individuals.</li>
            @endif
        </ul>
    </section>
    <!-- Quick Actions -->
    <section class="flex flex-wrap items-center gap-4 justify-between mt-10">
        <div>
            <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
            <a href="{{ route('trees.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full">View My Tree</a>
            <a href="{{ route('individuals.create') }}" class="ml-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-full">Add New Individual</a>
            <a href="{{ route('trees.create') }}" class="ml-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-2 rounded-full">Start New Tree</a>
        </div>
        <a href="{{ route('timeline.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold px-6 py-2 rounded-full">Start Research</a>
    </section>
@endsection 