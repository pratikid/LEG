@props(['user'])

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Account Settings</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage your account preferences and settings.</p>
            </div>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Information</h3>
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        <img class="h-16 w-16 rounded-full" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                    </div>
                    <div>
                        <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                            Change Photo
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First name</label>
                        <input type="text" name="first_name" id="first_name" value="{{ $user->first_name }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                    </div>

                    <div class="sm:col-span-3">
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last name</label>
                        <input type="text" name="last_name" id="last_name" value="{{ $user->last_name }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                    </div>

                    <div class="sm:col-span-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <input type="email" name="email" id="email" value="{{ $user->email }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                    </div>

                    <div class="sm:col-span-6">
                        <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                        <textarea name="bio" id="bio" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">{{ $user->bio }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Privacy Settings -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Privacy Settings</h3>
            <form action="{{ route('profile.privacy') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="profile_visibility" id="profile_visibility" value="public" {{ $user->profile_visibility === 'public' ? 'checked' : '' }} class="focus:ring-amber-500 h-4 w-4 text-amber-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="profile_visibility" class="font-medium text-gray-700">Public Profile</label>
                            <p class="text-gray-500">Allow others to view your profile and family tree information.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="show_email" id="show_email" value="true" {{ $user->show_email ? 'checked' : '' }} class="focus:ring-amber-500 h-4 w-4 text-amber-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="show_email" class="font-medium text-gray-700">Show Email</label>
                            <p class="text-gray-500">Display your email address on your public profile.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="allow_messages" id="allow_messages" value="true" {{ $user->allow_messages ? 'checked' : '' }} class="focus:ring-amber-500 h-4 w-4 text-amber-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="allow_messages" class="font-medium text-gray-700">Allow Messages</label>
                            <p class="text-gray-500">Let other users send you messages through the platform.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        Save Privacy Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Preferences -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Preferences</h3>
            <form action="{{ route('profile.notifications') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="email_notifications" id="email_notifications" value="true" {{ $user->email_notifications ? 'checked' : '' }} class="focus:ring-amber-500 h-4 w-4 text-amber-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="email_notifications" class="font-medium text-gray-700">Email Notifications</label>
                            <p class="text-gray-500">Receive email notifications for important updates and activities.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="tree_updates" id="tree_updates" value="true" {{ $user->tree_updates ? 'checked' : '' }} class="focus:ring-amber-500 h-4 w-4 text-amber-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="tree_updates" class="font-medium text-gray-700">Tree Updates</label>
                            <p class="text-gray-500">Get notified when changes are made to your family trees.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="message_notifications" id="message_notifications" value="true" {{ $user->message_notifications ? 'checked' : '' }} class="focus:ring-amber-500 h-4 w-4 text-amber-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="message_notifications" class="font-medium text-gray-700">Message Notifications</label>
                            <p class="text-gray-500">Receive notifications when you get new messages.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        Save Notification Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Account Deletion -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Delete Account</h3>
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Warning</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>Once you delete your account, there is no going back. Please be certain.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle profile photo upload
    const photoButton = document.querySelector('button:contains("Change Photo")');
    const photoInput = document.createElement('input');
    photoInput.type = 'file';
    photoInput.accept = 'image/*';

    photoButton.addEventListener('click', () => {
        photoInput.click();
    });

    photoInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            const formData = new FormData();
            formData.append('photo', e.target.files[0]);
            
            fetch('{{ route("profile.photo") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }
    });

    // Handle account deletion
    const deleteButton = document.querySelector('button:contains("Delete Account")');
    deleteButton.addEventListener('click', () => {
        if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
            fetch('{{ route("profile.delete") }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route("home") }}';
                }
            });
        }
    });
});
</script>
@endpush 