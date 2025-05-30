<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Timeline Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('timeline.update', $timelineEvent) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $timelineEvent->title)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('description', $timelineEvent->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <!-- Event Date -->
                        <div>
                            <x-input-label for="event_date" :value="__('Event Date')" />
                            <x-text-input id="event_date" name="event_date" type="date" class="mt-1 block w-full" :value="old('event_date', $timelineEvent->event_date->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('event_date')" />
                        </div>

                        <!-- Event Type -->
                        <div>
                            <x-input-label for="event_type" :value="__('Event Type')" />
                            <select id="event_type" name="event_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select an event type</option>
                                <option value="birth" {{ old('event_type', $timelineEvent->event_type) == 'birth' ? 'selected' : '' }}>Birth</option>
                                <option value="death" {{ old('event_type', $timelineEvent->event_type) == 'death' ? 'selected' : '' }}>Death</option>
                                <option value="marriage" {{ old('event_type', $timelineEvent->event_type) == 'marriage' ? 'selected' : '' }}>Marriage</option>
                                <option value="divorce" {{ old('event_type', $timelineEvent->event_type) == 'divorce' ? 'selected' : '' }}>Divorce</option>
                                <option value="immigration" {{ old('event_type', $timelineEvent->event_type) == 'immigration' ? 'selected' : '' }}>Immigration</option>
                                <option value="other" {{ old('event_type', $timelineEvent->event_type) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('event_type')" />
                        </div>

                        <!-- Location -->
                        <div>
                            <x-input-label for="location" :value="__('Location')" />
                            <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="old('location', $timelineEvent->location)" />
                            <x-input-error class="mt-2" :messages="$errors->get('location')" />
                        </div>

                        <!-- Public/Private -->
                        <div class="flex items-center">
                            <input id="is_public" name="is_public" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('is_public', $timelineEvent->is_public) ? 'checked' : '' }}>
                            <x-input-label for="is_public" :value="__('Make this event public')" class="ml-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Update Event') }}</x-primary-button>
                            <a href="{{ route('timeline.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 