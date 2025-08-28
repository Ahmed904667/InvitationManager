<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    @auth
        <!-- Authenticated user - redirect to appropriate dashboard -->
        <div class="min-h-screen bg-gray-100 flex items-center justify-center">
            <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-primary mb-2">Welcome back, {{ Auth::user()->name }}!</h2>
                    <p class="text-gray-600 mb-6">Redirecting you to your dashboard...</p>
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                </div>
            </div>
        </div>
    @else
        <!-- Non-authenticated user - redirect to landing page -->
        <div class="min-h-screen bg-gray-100 flex items-center justify-center">
            <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-primary mb-2">Welcome to Guest Manager</h2>
                    <p class="text-gray-600 mb-6">Redirecting you to our landing page...</p>
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                </div>
            </div>
        </div>
    @endauth

    <script>
        // Auto-redirect based on authentication status
        @auth
            // Redirect authenticated users to their dashboard
            window.location.href = '{{ Auth::user()->role === "admin" ? route("admin.dashboard") : (Auth::user()->role === "organizer" ? route("organizer.dashboard") : route("scanner.dashboard")) }}';
        @else
            // Redirect non-authenticated users to landing page
            window.location.href = '{{ route("home") }}';
        @endauth
    </script>
</body>
</html>
