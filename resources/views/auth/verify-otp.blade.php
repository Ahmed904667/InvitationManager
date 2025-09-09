<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Invaro</title>
    <meta name="description" content="Verify your email address to complete registration">
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
                <h3 class="text-2xl font-bold mb-2">Verify Your Email</h3>
                <p class="text-lg opacity-90">Enter the code sent to your email</p>
            </div>
        </div>

        <!-- Right side - OTP Verification Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center bg-white dark:bg-gray-900 min-h-screen lg:min-h-0">
            <!-- Back Button -->
            <div class="absolute top-4 left-4 lg:top-6 lg:left-6 z-10">
                <a href="{{ route('register') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:hover:text-white lg:text-white lg:hover:text-gray-200 transition-colors duration-200">
                    <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-1 lg:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span class="text-sm lg:text-base">Back to Register</span>
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
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900 mb-4">
                            <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-2">Verify Your Email</h2>
                        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400">
                            We've sent a 6-digit verification code to
                        </p>
                        <p class="text-sm lg:text-base font-medium text-primary-600 dark:text-primary-400 mt-1">
                            {{ $email }}
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

                    <form method="POST" action="{{ route('register.verify-otp') }}" class="space-y-4 lg:space-y-6">
                        @csrf
                        
                        <input type="hidden" name="email" value="{{ $email }}">
                        
                        <div class="space-y-3 lg:space-y-4">
                            <div>
                                <label for="otp_code" class="block text-sm lg:text-base font-medium text-gray-700 dark:text-gray-300 mb-1 lg:mb-2">
                                    Verification Code
                                </label>
                                <input 
                                    id="otp_code" 
                                    name="otp_code" 
                                    type="text" 
                                    maxlength="6"
                                    required 
                                    class="w-full px-3 py-2 lg:px-4 lg:py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white text-sm lg:text-base text-center tracking-widest @error('otp_code') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                                    placeholder="000000"
                                    autocomplete="one-time-code">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Enter the 6-digit code sent to your email
                                </p>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full flex justify-center py-2 lg:py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm lg:text-base font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                                Verify Email & Create Account
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 lg:mt-8 text-center">
                        <p class="text-xs lg:text-sm text-gray-600 dark:text-gray-400">
                            Didn't receive the code? 
                            <button type="button" onclick="resendOTP()" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200">
                                Resend Code
                            </button>
                        </p>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="text-xs lg:text-sm text-gray-600 dark:text-gray-400">
                            Wrong email? 
                            <a href="{{ route('register') }}" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200">
                                Go back to registration
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

            // Auto-focus on OTP input
            document.getElementById('otp_code').focus();

            // Auto-submit when 6 digits are entered
            document.getElementById('otp_code').addEventListener('input', function(e) {
                const value = e.target.value.replace(/\D/g, ''); // Remove non-digits
                e.target.value = value;
                
                if (value.length === 6) {
                    // Auto-submit form
                    setTimeout(() => {
                        e.target.form.submit();
                    }, 500);
                }
            });
        });

        // Resend OTP function
        function resendOTP() {
            const email = '{{ $email }}';
            const name = '{{ session("registration_name") ?? session("registration_data.name") }}';
            
            fetch('{{ route("register.send-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    email: email,
                    name: name
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const statusDiv = document.createElement('div');
                    statusDiv.className = 'mb-6 px-3 py-2 lg:px-4 lg:py-3 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 text-xs lg:text-sm rounded-md';
                    statusDiv.textContent = 'New verification code sent!';
                    
                    const form = document.querySelector('form');
                    form.parentNode.insertBefore(statusDiv, form);
                    
                    // Remove message after 5 seconds
                    setTimeout(() => {
                        statusDiv.remove();
                    }, 5000);
                } else {
                    alert('Failed to resend code. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to resend code. Please try again.');
            });
        }
    </script>
</body>
</html>
