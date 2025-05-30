@extends('layouts.app')

@section('content')
    <div class="container mx-auto max-w-md mt-10 p-6 bg-white rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Reset Password</h1>
        @if (session('status'))
            <div class="mb-4 text-green-600">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 text-red-600">
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
                <label for="password" class="block text-sm font-medium">New Password</label>
                <input id="password" type="password" name="password" required class="w-full border rounded px-3 py-2 mt-1">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full border rounded px-3 py-2 mt-1">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Reset Password</button>
        </form>
    </div>
@endsection 