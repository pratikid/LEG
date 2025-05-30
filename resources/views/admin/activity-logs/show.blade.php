<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Activity Log Details') }}
            </h2>
            <a href="{{ route('admin.activity-logs.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">User</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $log->user->name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Action</label>
                                <p class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $log->action === 'create' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $log->action === 'update' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $log->action === 'delete' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Model</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date & Time</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $log->created_at->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>

                        <!-- Technical Details -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Technical Details</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">IP Address</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $log->ip_address }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">User Agent</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $log->user_agent }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Changes -->
                    @if($log->action === 'update' && !empty($log->old_values) && !empty($log->new_values))
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Changes</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Old Value</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($log->new_values as $field => $newValue)
                                            @if(isset($log->old_values[$field]) && $log->old_values[$field] !== $newValue)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ ucfirst(str_replace('_', ' ', $field)) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ is_array($log->old_values[$field]) ? json_encode($log->old_values[$field]) : $log->old_values[$field] }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ is_array($newValue) ? json_encode($newValue) : $newValue }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Created/Deleted Data -->
                    @if(in_array($log->action, ['create', 'delete']))
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                {{ $log->action === 'create' ? 'Created Data' : 'Deleted Data' }}
                            </h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <pre class="text-sm text-gray-900 overflow-x-auto">{{ json_encode($log->action === 'create' ? $log->new_values : $log->old_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 