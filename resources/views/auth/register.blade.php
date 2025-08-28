@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-primary">
    <div class="max-w-md w-full space-y-8">
        <!-- Theme Toggle -->
        <div class="flex justify-end">
            <button class="theme-toggle" type="button" aria-label="Toggle theme">
                <span class="theme-toggle-thumb"></span>
                <svg class="sun-icon absolute left-1 h-3 w-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                </svg>
                <svg class="moon-icon absolute right-1 h-3 w-3 text-blue-400 hidden" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                </svg>
            </button>
        </div>
        
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-primary">
                Create your account
            </h2>
        </div>
        
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="form-label">Full name</label>
                    <input id="name" name="name" type="text" required 
                           class="form-input" 
                           placeholder="Full name" value="{{ old('name') }}">
                </div>
                <div>
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" name="email" type="email" required 
                           class="form-input" 
                           placeholder="Email address" value="{{ old('email') }}">
                </div>
                <div>
                    <label for="password" class="form-label">Password</label>
                    <input id="password" name="password" type="password" required 
                           class="form-input" 
                           placeholder="Password">
                </div>
                <div>
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required 
                           class="form-input" 
                           placeholder="Confirm password">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="btn-primary w-full">
                    Create Account
                </button>
            </div>

            <div class="flex items-center justify-center">
                <div class="text-sm">
                    <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:text-primary-500 transition-colors duration-200">
                        Already have an account? Sign in
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 