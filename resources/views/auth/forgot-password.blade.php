<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Invaro</title>
    <meta name="description" content="Reset your password for Invaro guest management platform">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Logo.jpg') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    <div class="min-h-full flex">
        <!-- Left side - Image (Desktop only) -->
        <div class="hidden lg:flex lg:w-1/2 relative">
            <img src="{{ asset('images/pexels-karolina-grabowska-4219890.jpg') }}" 
                 alt="Beautiful red envelope with carnations" 
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black bg-opacity-30"></div>
            <div class="absolute bottom-8 left-8 text-white">
                <h3 class="text-2xl font-bold mb-2">Forgot Your Password?</h3>
                <p class="text-lg opacity-90">No worries, we'll help you reset it</p>
            </div>
        </div>

        <!-- Right side - Forgot Password Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center bg-white dark:bg-gray-900 min-h-screen lg:min-h-0">
            <!-- Back Button -->
            <div class="absolute top-4 left-4 lg:top-6 lg:left-6 z-10">
                <a href="{{ route('login') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:hover:text-white lg:text-white lg:hover:text-gray-200 transition-colors duration-200">
                    <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-1 lg:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span class="text-sm lg:text-base">Back to Login</span>
                </a>
            </div>

            <!-- Theme Toggle -->
            <div class="absolute top-4 right-4 lg:top-6 lg:right-6 z-10">
                <button class="theme-toggle" type="button" aria-label="Toggle theme">
                    <span class="theme-toggle-thumb"></span>
                </button>
            </div>

            <!-- Form Content -->
            <div class="px-4 sm:px-6 lg:px-8 py-6 lg:py-0">
                <div class="max-w-md mx-auto">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-2">Forgot Password?</h2>
                        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400">
                            Enter your email address and we'll send you a link to reset your password.
                        </p>
                    </div>

                    <!-- Success Message -->
                    @if (session('status'))
                        <div class="mb-6 px-3 py-2 lg:px-4 lg:py-3 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 text-xs lg:text-sm rounded-md">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="mb-6 px-3 py-2 lg:px-4 lg:py-3 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 text-xs lg:text-sm rounded-md">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-4 lg:space-y-6">
                        @csrf
                        
                        <div class="space-y-3 lg:space-y-4">
                            <div>
                                <label for="email" class="block text-sm lg:text-base font-medium text-gray-700 dark:text-gray-300 mb-1 lg:mb-2">
                                    Email Address
                                </label>
                                <input 
                                    id="email" 
                                    name="email" 
                                    type="email" 
                                    autocomplete="email" 
                                    required 
                                    value="{{ old('email') }}"
                                    class="w-full px-3 py-2 lg:px-4 lg:py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white text-sm lg:text-base @error('email') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                                    placeholder="Enter your email address">
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full flex justify-center py-2 lg:py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm lg:text-base font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                                Send Reset Link
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 lg:mt-8 text-center">
                        <p class="text-xs lg:text-sm text-gray-600 dark:text-gray-400">
                            Remember your password? 
                            <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.querySelector('.theme-toggle');
            const themeToggleThumb = document.querySelector('.theme-toggle-thumb');
            
            // Check for saved theme preference or default to 'light'
            const currentTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.classList.toggle('dark', currentTheme === 'dark');
            themeToggleThumb.classList.toggle('translate-x-6', currentTheme === 'dark');
            
            themeToggle.addEventListener('click', function() {
                const isDark = document.documentElement.classList.toggle('dark');
                themeToggleThumb.classList.toggle('translate-x-6', isDark);
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
        });
    </script>
</body>
</html>
