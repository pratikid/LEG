<!-- Node Customization Component -->
<div class="bg-white p-4 rounded-lg shadow-sm mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Node Appearance</h3>
    <form action="{{ route('timeline.preferences.update') }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <!-- Node Color -->
        <div>
            <label for="node_color" class="block text-sm font-medium text-gray-700">Node Color</label>
            <select name="node_color" id="node_color"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="amber" {{ auth()->user()->preferences['node_color'] ?? 'amber' === 'amber' ? 'selected' : '' }}>Amber</option>
                <option value="blue" {{ auth()->user()->preferences['node_color'] ?? 'amber' === 'blue' ? 'selected' : '' }}>Blue</option>
                <option value="green" {{ auth()->user()->preferences['node_color'] ?? 'amber' === 'green' ? 'selected' : '' }}>Green</option>
                <option value="red" {{ auth()->user()->preferences['node_color'] ?? 'amber' === 'red' ? 'selected' : '' }}>Red</option>
                <option value="purple" {{ auth()->user()->preferences['node_color'] ?? 'amber' === 'purple' ? 'selected' : '' }}>Purple</option>
            </select>
        </div>

        <!-- Node Shape -->
        <div>
            <label for="node_shape" class="block text-sm font-medium text-gray-700">Node Shape</label>
            <select name="node_shape" id="node_shape"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="circle" {{ auth()->user()->preferences['node_shape'] ?? 'circle' === 'circle' ? 'selected' : '' }}>Circle</option>
                <option value="square" {{ auth()->user()->preferences['node_shape'] ?? 'circle' === 'square' ? 'selected' : '' }}>Square</option>
                <option value="diamond" {{ auth()->user()->preferences['node_shape'] ?? 'circle' === 'diamond' ? 'selected' : '' }}>Diamond</option>
            </select>
        </div>

        <!-- Display Options -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Display Options</label>
            <div class="space-y-2">
                <div class="flex items-center">
                    <input type="checkbox" name="show_dates" id="show_dates" value="1"
                        {{ (auth()->user()->preferences['show_dates'] ?? true) ? 'checked' : '' }}
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="show_dates" class="ml-2 block text-sm text-gray-900">Show Dates</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="show_location" id="show_location" value="1"
                        {{ (auth()->user()->preferences['show_location'] ?? true) ? 'checked' : '' }}
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="show_location" class="ml-2 block text-sm text-gray-900">Show Location</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="show_description" id="show_description" value="1"
                        {{ (auth()->user()->preferences['show_description'] ?? false) ? 'checked' : '' }}
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="show_description" class="ml-2 block text-sm text-gray-900">Show Description</label>
                </div>
            </div>
        </div>

        <!-- Node Size -->
        <div>
            <label for="node_size" class="block text-sm font-medium text-gray-700">Node Size</label>
            <select name="node_size" id="node_size"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="small" {{ auth()->user()->preferences['node_size'] ?? 'medium' === 'small' ? 'selected' : '' }}>Small</option>
                <option value="medium" {{ auth()->user()->preferences['node_size'] ?? 'medium' === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="large" {{ auth()->user()->preferences['node_size'] ?? 'medium' === 'large' ? 'selected' : '' }}>Large</option>
            </select>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Save Preferences
            </button>
        </div>
    </form>
</div> 