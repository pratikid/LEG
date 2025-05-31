@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#181d22] flex flex-col">
    <!-- Top Bar -->
    <nav class="w-full h-14 flex items-center px-6 border-b border-gray-600 bg-[#181d22]">
        <div class="flex items-center space-x-2">
            <span class="text-2xl">🦷</span>
            <span class="text-lg font-semibold text-white">Family History</span>
        </div>
    </nav>
    <!-- Centered Login Form -->
    <div class="flex-1 flex flex-col items-center justify-center">
        <div class="w-full max-w-xl px-4">
            <h2 class="text-3xl font-bold text-center text-white mb-8 mt-4">Sign in to Family History</h2>
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="username" class="block text-white text-base font-medium mb-1">Username</label>
                    <input id="username" name="username" type="text" required autofocus placeholder="Enter your username" class="w-full bg-[#232a32] text-white placeholder-gray-400 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('username', '') }}">
                    @if($errors && $errors->has('username'))
                        <span class="text-red-400 text-sm">{{ $errors->first('username') }}</span>
                    @endif
                </div>
                <div>
                    <label for="password" class="block text-white text-base font-medium mb-1">Password</label>
                    <input id="password" name="password" type="password" required placeholder="Enter your password" class="w-full bg-[#232a32] text-white placeholder-gray-400 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @if($errors && $errors->has('password'))
                        <span class="text-red-400 text-sm">{{ $errors->first('password') }}</span>
                    @endif
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full px-8 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">Sign in</button>
                </div>
            </form>
            <div class="text-center mt-6">
                <span class="text-gray-400">Don't have an account?</span>
                <a href="{{ route('register') }}" class="text-blue-400 hover:underline ml-1">Sign up</a>
            </div>
        </div>
    </div>
</div>
@endsection 