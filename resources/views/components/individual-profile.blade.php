@props(['individual'])

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <!-- Profile Header -->
    <div class="px-4 py-5 sm:px-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 h-20 w-20">
                <img class="h-20 w-20 rounded-full" src="{{ $individual->profile_photo_url }}" alt="{{ $individual->name }}">
            </div>
            <div class="ml-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $individual->name }}</h3>
                <p class="text-sm text-gray-500">{{ $individual->birth_date }} - {{ $individual->death_date ?? 'Present' }}</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex" aria-label="Tabs">
            <button class="tab-button active w-1/4 py-4 px-1 text-center border-b-2 border-amber-500 font-medium text-sm text-amber-600" data-tab="overview">
                Overview
            </button>
            <button class="tab-button w-1/4 py-4 px-1 text-center border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="timeline">
                Timeline
            </button>
            <button class="tab-button w-1/4 py-4 px-1 text-center border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="facts">
                Facts
            </button>
            <button class="tab-button w-1/4 py-4 px-1 text-center border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="sources">
                Sources
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Overview Tab -->
        <div class="tab-pane active" id="overview">
            <div class="px-4 py-5 sm:p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Full name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $individual->full_name }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $individual->gender }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Birth date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $individual->birth_date }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Birth place</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $individual->birth_place }}</dd>
                    </div>
                    @if($individual->death_date)
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Death date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $individual->death_date }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Death place</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $individual->death_place }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Timeline Tab -->
        <div class="tab-pane hidden" id="timeline">
            <div class="px-4 py-5 sm:p-6">
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($individual->events as $event)
                        <li>
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-amber-500 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">{{ $event->description }}</p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time datetime="{{ $event->date }}">{{ $event->date }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Facts Tab -->
        <div class="tab-pane hidden" id="facts">
            <div class="px-4 py-5 sm:p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                    @foreach($individual->facts as $fact)
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">{{ $fact->type }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $fact->value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
        </div>

        <!-- Sources Tab -->
        <div class="tab-pane hidden" id="sources">
            <div class="px-4 py-5 sm:p-6">
                <ul role="list" class="divide-y divide-gray-200">
                    @foreach($individual->sources as $source)
                    <li class="py-4">
                        <div class="flex space-x-3">
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium">{{ $source->title }}</h3>
                                    <p class="text-sm text-gray-500">{{ $source->date }}</p>
                                </div>
                                <p class="text-sm text-gray-500">{{ $source->description }}</p>
                                <div class="text-sm text-gray-500">
                                    <a href="{{ $source->url }}" class="text-amber-600 hover:text-amber-500">View Source</a>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active classes
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-amber-500', 'text-amber-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            tabPanes.forEach(pane => pane.classList.add('hidden'));

            // Add active classes
            button.classList.remove('border-transparent', 'text-gray-500');
            button.classList.add('active', 'border-amber-500', 'text-amber-600');
            document.getElementById(button.dataset.tab).classList.remove('hidden');
        });
    });
});
</script>
@endpush 