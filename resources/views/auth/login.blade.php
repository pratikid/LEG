<x-guest-layout>
    <div class="min-h-screen bg-[#181d22] flex flex-col">
        <!-- Top Bar -->
        <nav class="w-full h-14 flex items-center px-6 border-b border-gray-700 bg-[#181d22]">
            <div class="flex items-center space-x-2">
                <span class="text-2xl">🦷</span>
                <span class="text-lg font-semibold text-white">Family History</span>
            </div>
        </nav>
        <!-- Centered Login Form -->
        <div class="flex-1 flex flex-col items-center justify-center">
            <div class="w-full max-w-md px-4">
                <h2 class="text-3xl font-bold text-center text-white mb-10 mt-4">Sign in to Family History</h2>
                <form method="POST" action="{{ route('login') }}" class="space-y-8">
                    @csrf
                    <div>
                        <x-input-label for="username">
                            <span class="text-gray-200 text-base font-medium mb-2 block">Username</span>
                        </x-input-label>
                        <x-text-input id="username" name="username" type="text" required autofocus placeholder="Enter your username" class="w-full bg-[#232a32] text-gray-100 placeholder-gray-500 rounded-xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-700 shadow-sm" value="{{ old('username', '') }}" />
                        <x-input-error :messages="$errors?->get('username')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password">
                            <span class="text-gray-200 text-base font-medium mb-2 block">Password</span>
                        </x-input-label>
                        <x-text-input id="password" name="password" type="password" required placeholder="Enter your password" class="w-full bg-[#232a32] text-gray-100 placeholder-gray-500 rounded-xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-700 shadow-sm" />
                        <x-input-error :messages="$errors?->get('password')" class="mt-2" />
                    </div>
                    <div class="flex justify-center mt-2">
                        <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full px-8 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition text-lg shadow-md">
                            Sign in
                        </x-primary-button>
                    </div>
                </form>
                <div class="text-center mt-8">
                    <span class="text-gray-400">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="text-blue-400 hover:underline ml-1">Sign up</a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout> 