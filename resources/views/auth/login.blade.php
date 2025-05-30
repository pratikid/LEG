@extends('layouts.app')

@section('content')
    <div class="container mx-auto max-w-md mt-10 p-6 bg-white rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Login</h1>
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input id="email" type="email" name="email" required autofocus class="w-full border rounded px-3 py-2 mt-1">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium">Password</label>
                <input id="password" type="password" name="password" required class="w-full border rounded px-3 py-2 mt-1">
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember" class="text-sm">Remember me</label>
                </div>
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Login</button>
        </form>
    </div>
@endsection 