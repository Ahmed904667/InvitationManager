<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Invaro') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Logo.jpg') }}">
    
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
<div class="min-h-screen flex w-full">
    <!-- Left side - Image -->
    <div class="hidden lg:flex lg:w-1/2 relative">
        <img src="{{ asset('images/pexels-karolina-grabowska-4219890.jpg') }}" 
             alt="Beautiful red envelope with carnations" 
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-30"></div>
        <div class="absolute bottom-8 left-8 text-white">
            <h3 class="text-2xl font-bold mb-2">Welcome Back</h3>
            <p class="text-lg opacity-90">Sign in to continue your journey with us</p>
        </div>
    </div>


    <!-- Right side - Login Form -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center bg-white dark:bg-gray-900 min-h-screen lg:min-h-0">
        <!-- Back Button -->
        <div class="absolute top-4 left-4 lg:top-6 lg:left-6 z-10">
            <a href="{{ route('home') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:hover:text-white lg:text-white lg:hover:text-gray-200 transition-colors duration-200">
                <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-1 lg:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span class="text-sm lg:text-base">Back to Home</span>
            </a>
        </div>

        <!-- Theme Toggle -->
        <div class="absolute top-4 right-4 lg:top-6 lg:right-6 z-10">
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
        
        <div class="max-w-md mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 lg:py-0">
            <div class="text-center mb-6 lg:mb-8">
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    Welcome Back
                </h2>
                <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400">
                    Sign in to your account to continue
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
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-3 py-2 lg:px-4 lg:py-3 rounded-lg mb-4 lg:mb-6">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs lg:text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-4 lg:space-y-6" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="space-y-3 lg:space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 lg:mb-2">
                            Email address
                        </label>
                        <input id="email" name="email" type="email" required 
                               class="w-full px-3 py-2 lg:px-4 lg:py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white transition-colors duration-200 text-sm lg:text-base" 
                               placeholder="Enter your email" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 lg:mb-2">
                            Password
                        </label>
                        <input id="password" name="password" type="password" required 
                               class="w-full px-3 py-2 lg:px-4 lg:py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white transition-colors duration-200 text-sm lg:text-base" 
                               placeholder="Enter your password">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded">
                        <label for="remember" class="ml-2 block text-xs lg:text-sm text-gray-700 dark:text-gray-300">
                            Remember me
                        </label>
                    </div>
                    <div class="text-xs lg:text-sm">
                        <a href="{{ route('password.request') }}" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 lg:py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                        Sign in
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-xs lg:text-sm text-gray-600 dark:text-gray-400">
                        Don't have an account? 
                        <a href="{{ route('register') }}" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200">
                            Sign up here
                        </a>
                    </p>
                </div>
            </form>

            <div class="mt-6 lg:mt-8">
                <!-- Label -->
                <div class="relative mb-4">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-xs lg:text-sm">
                        <span class="px-2 bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                            Or continue with
                        </span>
                    </div>
                </div>

                <!-- Button -->
                <div class="flex justify-center">
                    <div id="g_id_onload"
                        data-client_id="{{ config('services.google.client_id') }}"
                        data-callback="handleCredentialResponse"
                        data-auto_prompt="false"
                        data-cancel_on_tap_outside="false"
                        data-context="signin"
                        data-ux_mode="popup"
                        data-itp_support="true">
                    </div>
                    <div class="g_id_signin"
                        data-type="standard" 
                        data-shape="rectangular" 
                        data-theme="outline" 
                        data-text="signin_with" 
                        data-size="large" 
                        data-logo_alignment="left"
                        data-width="300">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let isGoogleSignInInProgress = false;

function handleCredentialResponse(response) {
    // Prevent multiple simultaneous requests
    if (isGoogleSignInInProgress) {
        console.log('Google sign-in already in progress, ignoring duplicate request');
        return;
    }
    
    isGoogleSignInInProgress = true;
    
    // Show loading state
    const googleButton = document.querySelector('.g_id_signin');
    if (googleButton) {
        googleButton.style.opacity = '0.5';
        googleButton.style.pointerEvents = 'none';
    }
    
    // Use fetch for more reliable POST request
    fetch('{{ route("google.login") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'X-Timezone': Intl.DateTimeFormat().resolvedOptions().timeZone
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            credential: response.credential,
            browser_timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        })
    })
    .then(response => {
        if (response.ok) {
            // If successful, redirect to the intended page
            return response.json().then(data => {
                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    window.location.href = '/organizer';
                }
            });
        } else {
            // Handle error response
            return response.json().then(data => {
                throw new Error(data.message || 'Google sign-in failed');
            });
        }
    })
    .catch(error => {
        console.error('Google sign-in error:', error);
        // Show error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-3 py-2 lg:px-4 lg:py-3 rounded-lg mb-4 lg:mb-6';
        errorDiv.innerHTML = '<p class="text-xs lg:text-sm">Google sign-in failed. Please try again.</p>';
        
        // Insert error message before the form
        const form = document.querySelector('form');
        form.parentNode.insertBefore(errorDiv, form);
        
        // Remove error message after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 5000);
    })
    .finally(() => {
        // Reset the flag and restore button state
        isGoogleSignInInProgress = false;
        if (googleButton) {
            googleButton.style.opacity = '1';
            googleButton.style.pointerEvents = 'auto';
        }
    });
}

// Initialize Google Identity Services with proper configuration
window.onload = function() {
    if (typeof google !== 'undefined' && google.accounts) {
        google.accounts.id.initialize({
            client_id: '{{ config("services.google.client_id") }}',
            callback: handleCredentialResponse,
            auto_select: false,
            cancel_on_tap_outside: false,
            context: 'signin',
            ux_mode: 'popup',
            itp_support: true
        });
    }
};
</script>
</body>
</html> 