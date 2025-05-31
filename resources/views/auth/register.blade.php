<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#181d22] text-gray-100">
    <x-guest-layout>
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#181d22]">
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-[#232a32] shadow-md overflow-hidden sm:rounded-lg">
                <h2 class="text-2xl font-bold text-center mb-6 text-white">Register</h2>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div>
                        <x-input-label for="name">
                            <span class="text-gray-200 text-base font-medium mb-2 block">Name</span>
                        </x-input-label>
                        <x-text-input id="name" class="block mt-1 w-full bg-[#232a32] text-gray-100 placeholder-gray-500 rounded-xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-700 shadow-sm" type="text" name="name" value="{{ old('name', '') }}" required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors?->get('name')" class="mt-2" />
                    </div>

                    <!-- Email Address -->
                    <div class="mt-4">
                        <x-input-label for="email">
                            <span class="text-gray-200 text-base font-medium mb-2 block">Email</span>
                        </x-input-label>
                        <x-text-input id="email" class="block mt-1 w-full bg-[#232a32] text-gray-100 placeholder-gray-500 rounded-xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-700 shadow-sm" type="email" name="email" value="{{ old('email', '') }}" required autocomplete="username" />
                        <x-input-error :messages="$errors?->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="mt-4">
                        <x-input-label for="password">
                            <span class="text-gray-200 text-base font-medium mb-2 block">Password</span>
                        </x-input-label>
                        <x-text-input id="password" class="block mt-1 w-full bg-[#232a32] text-gray-100 placeholder-gray-500 rounded-xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-700 shadow-sm" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors?->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mt-4">
                        <x-input-label for="password_confirmation">
                            <span class="text-gray-200 text-base font-medium mb-2 block">Confirm Password</span>
                        </x-input-label>
                        <x-text-input id="password_confirmation" class="block mt-1 w-full bg-[#232a32] text-gray-100 placeholder-gray-500 rounded-xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-700 shadow-sm" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors?->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a class="underline text-sm text-gray-400 hover:text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                            {{ __('Already registered?') }}
                        </a>

                        <x-primary-button class="ml-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full px-8 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition text-lg shadow-md">
                            {{ __('Register') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </x-guest-layout>
</body>
</html> 