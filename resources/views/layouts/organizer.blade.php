<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Organizer Dashboard') - {{ config('app.name', 'Guest Manager') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-primary">
    <div class="min-h-screen bg-primary">
        <!-- Navigation -->
        <nav class="nav-bg shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('organizer.dashboard') }}" class="text-4xl font-bold text-primary-600">
                                Invaro
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('organizer.dashboard') }}" class="border-primary-500 text-primary inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="{{ route('organizer.guest-lists.index') }}" class="border-transparent text-secondary hover:border-primary hover:text-primary inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                                Guest Lists
                            </a>
                            <a href="{{ route('organizer.events.index') }}" class="border-transparent text-secondary hover:border-primary hover:text-primary inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                                Events
                            </a>
                            <a href="{{ route('organizer.reports') }}" class="border-transparent text-secondary hover:border-primary hover:text-primary inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                                Reports
                            </a>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <div class="ml-3 relative">
                            <div class="flex items-center space-x-4">
                                                                 <!-- Profile Picture with Dropdown -->
                                 <div id="user-dropdown">
                                     <button type="button" class="flex items-center space-x-2 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" id="user-menu-button">
                                         <div class="h-10 w-10 rounded-full bg-primary-500 flex items-center justify-center overflow-hidden">
                                             @if(Auth::user()->profile_photo_url ?? false)
                                                 <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
                                             @else
                                                 <span class="text-white font-medium text-lg">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                             @endif
                                         </div>
                                         <span class="text-sm text-secondary">{{ Auth::user()->name }}</span>
                                         <svg class="h-4 w-4 text-secondary" fill="currentColor" viewBox="0 0 20 20">
                                             <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                         </svg>
                                     </button>
                                 </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button type="button" class="bg-primary inline-flex items-center justify-center p-2 rounded-md text-secondary hover:text-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 transition-colors duration-200" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="sm:hidden" id="mobile-menu">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('organizer.dashboard') }}" class="bg-primary-50 border-primary-500 text-primary-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Dashboard
                    </a>
                    <a href="{{ route('organizer.guest-lists.index') }}" class="border-transparent text-secondary hover:bg-secondary hover:border-primary hover:text-primary block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200">
                        Guest Lists
                    </a>
                    <a href="{{ route('organizer.events.index') }}" class="border-transparent text-secondary hover:bg-secondary hover:border-primary hover:text-primary block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200">
                        Events
                    </a>
                    <a href="{{ route('organizer.reports') }}" class="border-transparent text-secondary hover:bg-secondary hover:border-primary hover:text-primary block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200">
                        Reports
                    </a>
                </div>
                <div class="pt-4 pb-3 border-t border-primary">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-primary-500 flex items-center justify-center">
                                <span class="text-white font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-primary">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium text-secondary">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-secondary hover:text-primary hover:bg-secondary transition-colors duration-200">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Profile Dropdown Menu (Portal) -->
    <div id="dropdown-menu" class="hidden fixed z-50 w-56 origin-top-right rounded-xl bg-secondary py-2 shadow-2xl ring-1 ring-black/10 focus:outline-none border border-white/20 transform transition-all duration-200 ease-out scale-95 opacity-0">
        <!-- User Info Section -->
        <div class="px-4 py-4 border-b border-gray-100/50">
            <div class="flex items-center space-x-3">
                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg">
                    @if(Auth::user()->profile_photo_url ?? false)
                        <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="h-11 w-11 rounded-full object-cover border-2 border-white">
                    @else
                        <span class="text-white font-bold text-lg">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-primary truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-secondary truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>
        
        <!-- Theme Toggle -->
        <div class="px-4 py-3 border-b border-gray-100/50">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-primary">Theme</span>
                <button class="theme-toggle relative inline-flex h-6 w-11 items-center rounded-full bg-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" type="button" aria-label="Toggle theme">
                    <span class="theme-toggle-thumb inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 ease-in-out translate-x-1"></span>
                    <svg class="sun-icon absolute left-1 h-3 w-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                    </svg>
                    <svg class="moon-icon absolute right-1 h-3 w-3 text-blue-400 hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Menu Items -->
        <div class="py-2">
            <a href="{{ route('organizer.settings') }}" class="group flex items-center px-4 py-3 text-sm text-primary hover:bg-primary transition-all duration-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 mr-3">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <span class="font-medium">Settings</span>
                <svg class="ml-auto w-4 h-4 text-primary/60 dark:text-primary/40 group-hover:text-primary dark:group-hover:text-primary-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            
            <a href="{{ route('organizer.profile.show') }}" class="group flex items-center px-4 py-3 text-sm text-primary hover:bg-primary transition-all duration-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 mr-3">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <span class="font-medium">Profile</span>
                <svg class="ml-auto w-4 h-4 text-primary/60 dark:text-primary/40 group-hover:text-primary dark:group-hover:text-primary-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            
            <a href="#" class="group flex items-center px-4 py-3 text-sm text-primary hover:bg-primary transition-all duration-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 mr-3">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="font-medium">Help & Support</span>
                <svg class="ml-auto w-4 h-4 text-primary/60 dark:text-primary/40 group-hover:text-primary dark:group-hover:text-primary-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        
        <!-- Divider -->
        <div class="border-t border-gray-100/50 my-2"></div>
        
        <!-- Logout Section -->
        <div class="px-2">
            <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button type="submit" class="group w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-primary-to-r hover:from-red-50 hover:to-transparent rounded-lg transition-all duration-200">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 group-hover:bg-red-200 transition-colors duration-200 mr-3">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <span class="font-medium">Sign Out</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div id="flash-message" class="fixed top-4 right-4 z-50">
        <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div id="flash-message" class="fixed top-4 right-4 z-50">
        <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    </div>
    @endif

    <script>
        // Auto-hide flash messages
        setTimeout(function() {
            const flashMessage = document.getElementById('flash-message');
            if (flashMessage) {
                flashMessage.style.opacity = '0';
                setTimeout(function() {
                    flashMessage.remove();
                }, 300);
            }
        }, 3000);

        // Profile dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownButton = document.getElementById('user-menu-button');
            const dropdownMenu = document.getElementById('dropdown-menu');
            
            if (dropdownButton && dropdownMenu) {
                // Toggle dropdown on button click
                dropdownButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    if (dropdownMenu.classList.contains('hidden')) {
                        // Position the dropdown properly
                        const buttonRect = dropdownButton.getBoundingClientRect();
                        dropdownMenu.style.top = (buttonRect.bottom + 8) + 'px';
                        dropdownMenu.style.right = (window.innerWidth - buttonRect.right) + 'px';
                        
                        // Show dropdown with animation
                        dropdownMenu.classList.remove('hidden');
                        setTimeout(() => {
                            dropdownMenu.classList.remove('scale-95', 'opacity-0');
                            dropdownMenu.classList.add('scale-100', 'opacity-100');
                        }, 10);
                    } else {
                        // Hide dropdown with animation
                        dropdownMenu.classList.add('scale-95', 'opacity-0');
                        dropdownMenu.classList.remove('scale-100', 'opacity-100');
                        setTimeout(() => {
                            dropdownMenu.classList.add('hidden');
                        }, 200);
                    }
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.add('scale-95', 'opacity-0');
                        dropdownMenu.classList.remove('scale-100', 'opacity-100');
                        setTimeout(() => {
                            dropdownMenu.classList.add('hidden');
                        }, 200);
                    }
                });
                
                // Close dropdown on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        dropdownMenu.classList.add('scale-95', 'opacity-0');
                        dropdownMenu.classList.remove('scale-100', 'opacity-100');
                        setTimeout(() => {
                            dropdownMenu.classList.add('hidden');
                        }, 200);
                    }
                });
                
                // Close dropdown on window resize
                window.addEventListener('resize', function() {
                    dropdownMenu.classList.add('scale-95', 'opacity-0');
                    dropdownMenu.classList.remove('scale-100', 'opacity-100');
                    setTimeout(() => {
                        dropdownMenu.classList.add('hidden');
                    }, 200);
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html> 