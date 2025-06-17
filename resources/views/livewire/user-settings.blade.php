<div>
    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium">Name</label>
            <input type="text" id="name" name="name" wire:model.defer="name" class="mt-1 block w-full rounded border-gray-300" />
            @error('name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="email" class="block text-sm font-medium">Email</label>
            <input type="email" id="email" name="email" wire:model.defer="email" class="mt-1 block w-full rounded border-gray-300" />
            @error('email') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
    </form>
</div> 