@extends('layouts.app')

@section('content')
    <div class="container mx-auto max-w-md mt-10 p-6 bg-white rounded shadow">
        @if (session('status'))
            <div class="mb-4 text-green-600">
                {{ session('status') }}
            </div>
        @endif
        @if (
            $errors->any())
            <div class="mb-4 text-red-600">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <h1 class="text-2xl font-bold mb-6">Forgot Password</h1>
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input id="email" type="email" name="email" required autofocus class="w-full border rounded px-3 py-2 mt-1">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Send Password Reset Link</button>
        </form>
    </div>
@endsection 