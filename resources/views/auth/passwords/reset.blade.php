@extends('layouts.app')

@section('content')
    <div class="container mx-auto max-w-md mt-10 p-6 bg-[#232a32] rounded shadow">
        <h1 class="text-2xl font-bold mb-6 text-white">Reset Password</h1>
        @if (session('status'))
            <div class="mb-4 text-green-400">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 text-red-400">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-200 mb-2">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email ?? '') }}" required class="w-full bg-[#181d22] text-gray-100 placeholder-gray-500 border border-gray-700 rounded px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if($errors && $errors->has('email'))
                    <span class="text-red-400 text-sm">{{ $errors->first('email') }}</span>
                @endif
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-200 mb-2">New Password</label>
                <input id="password" type="password" name="password" required class="w-full bg-[#181d22] text-gray-100 placeholder-gray-500 border border-gray-700 rounded px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if($errors && $errors->has('password'))
                    <span class="text-red-400 text-sm">{{ $errors->first('password') }}</span>
                @endif
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-200 mb-2">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full bg-[#181d22] text-gray-100 placeholder-gray-500 border border-gray-700 rounded px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if($errors && $errors->has('password_confirmation'))
                    <span class="text-red-400 text-sm">{{ $errors->first('password_confirmation') }}</span>
                @endif
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">Reset Password</button>
        </form>
    </div>
@endsection 