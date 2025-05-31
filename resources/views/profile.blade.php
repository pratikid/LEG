@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">My Profile</h2>
        <!-- Profile Info -->
        <div class="flex items-center mb-6">
            <img src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" alt="Profile Photo" class="h-16 w-16 rounded-full object-cover">
            <div class="ml-4">
                <div class="text-lg font-medium text-gray-900">{{ Auth::user()->name }}</div>
                <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
        </div>
        <!-- Edit Profile Form -->
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', Auth::user()->name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', Auth::user()->email) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
            </div>
            <div>
                <label for="profile_photo" class="block text-sm font-medium text-gray-700">Profile Photo</label>
                <input type="file" name="profile_photo" id="profile_photo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Save Changes</button>
            </div>
        </form>
        <!-- Change Password -->
        <div class="mt-10">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Change Password</h3>
            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Update Password</button>
                </div>
            </form>
        </div>
        <!-- Notification Preferences -->
        <div class="mt-10">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Notification Preferences</h3>
            <form action="{{ route('profile.notifications') }}" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-center">
                    <input id="email_notifications" name="email_notifications" type="checkbox" class="h-4 w-4 text-amber-600 border-gray-300 rounded" {{ Auth::user()->email_notifications ? 'checked' : '' }}>
                    <label for="email_notifications" class="ml-2 block text-sm text-gray-700">Email notifications</label>
                </div>
                <div class="flex items-center">
                    <input id="sms_notifications" name="sms_notifications" type="checkbox" class="h-4 w-4 text-amber-600 border-gray-300 rounded" {{ Auth::user()->sms_notifications ? 'checked' : '' }}>
                    <label for="sms_notifications" class="ml-2 block text-sm text-gray-700">SMS notifications</label>
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Save Preferences</button>
                </div>
            </form>
        </div>
        <!-- Account Deletion -->
        <div class="mt-10 border-t pt-6">
            <h3 class="text-lg font-medium text-red-700 mb-2">Delete Account</h3>
            <form action="{{ route('profile.destroy') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete Account</button>
            </form>
        </div>
    </div>
</div>
@endsection 