<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invaro - Professional Event Management</title>
    <meta name="description" content="Streamline your events with our comprehensive guest management platform. Perfect for organizers, scanners, and administrators.">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Logo.jpg') }}">
    
    <!-- Theme initialization script - prevents flickering -->
    <script>
        (function() {
            // Get theme from localStorage or default to light
            const theme = localStorage.getItem('theme') || 'light';
            
            // Apply theme immediately to prevent flickering
            document.documentElement.setAttribute('data-theme', theme);
            
            // Apply Tailwind dark mode class
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/theme.js'])
</head>
<body class="bg-primary text-primary">
    <!-- Navigation -->
    <nav class="nav-bg border-b border-primary sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/" class="text-4xl font-bold gradient-text">
                        Invaro
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-secondary hover:text-primary transition-colors">
                        Features
                    </a>
                    <a href="#how-it-works" class="text-secondary hover:text-primary transition-colors">
                        How It Works
                    </a>
                    @auth
                        <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : (Auth::user()->role === 'organizer' ? route('organizer.dashboard') : route('scanner.dashboard')) }}" class="btn-primary">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-secondary hover:text-primary transition-colors">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="btn-primary">
                            Get Started
                        </a>
                    @endauth
                    
                    <!-- Theme Toggle -->
                    <button class="theme-toggle" type="button" aria-label="Toggle theme">
                        <span class="theme-toggle-thumb"></span>
                    </button>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-secondary hover:text-primary" aria-controls="mobile-menu" aria-expanded="false">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 border-t border-primary">
                <a href="#features" class="block px-3 py-2 text-secondary hover:text-primary transition-colors">
                    Features
                </a>
                <a href="#how-it-works" class="block px-3 py-2 text-secondary hover:text-primary transition-colors">
                    How It Works
                </a>
                @auth
                    <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : (Auth::user()->role === 'organizer' ? route('organizer.dashboard') : route('scanner.dashboard')) }}" class="block px-3 py-2 btn-primary">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-secondary hover:text-primary transition-colors">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="block px-3 py-2 btn-primary">
                        Get Started
                    </a>
                @endauth
                
                <!-- Theme Toggle in Mobile Menu -->
                <div class="px-3 py-2">
                    <button class="theme-toggle" type="button" aria-label="Toggle theme">
                        <span class="theme-toggle-thumb"></span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <div class="hero-grid">
            <!-- Left Column - Content -->
            <div class="hero-content slide-in-left">
                <h1 class="hero-title">
                    Professional
                    <span class="gradient-text">
                        Guest Management
                    </span>
                    Made Simple
                </h1>
                <p class="hero-desc">
                    Transform guest management with Invaro. Import and reuse guest
                    lists in seconds, auto-create personalized invites with InvaroAI,
                    schedule or send instantly, track RSVPs and check-ins, and see
                    real-time reports—all in one platform.
                </p>
                <div class="hero-actions">
                    @auth
                        <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : (Auth::user()->role === 'organizer' ? route('organizer.dashboard') : route('scanner.dashboard')) }}" class="btn-primary">
                            Access Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="hero-btn-primary">
                            Start Now!
                        </a>
                        <a href="#features" class="hero-btn-secondary">
                            Learn More
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Right Column - Trial Form -->
            <div class="trial-card slide-in-right">
                
                <div class="trial-header">
                    <h2 class="trial-title">Send Your First Invitation</h2>
                    <p class="trial-subtitle">
                        Get a sample invitation sent to your WhatsApp or email to see our platform in action. Type any language, invites will follow!
                    </p>
                </div>

                

                <form class="trial-form" id="trialForm">
                    <div class="form-group flex gap-4">
                        <div class="flex-1">
                            <input 
                                type="text" 
                                id="contact" 
                                name="contact"
                                class="form-input w-full" 
                                placeholder="Email or WhatsApp (+1234567890)"
                                required
                            >
                        </div>
                        <input 
                            type="text" 
                            id="name" 
                            name="name"
                            class="form-input flex-1" 
                            placeholder="Your Name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <input 
                            type="text" 
                            id="event_type" 
                            name="event_type"
                            class="form-input" 
                            placeholder="Event Type (e.g., Wedding, Birthday)"
                            required
                        >
                    </div>

                    <button type="submit" class="hero-btn-primary" id="submitBtn">
                        Send Sample Invitation
                    </button>


                    <!-- Message Box for errors/success -->
                    <div id="trialMessageBox" class="trial-message" style="display:none;"></div>
                        
                    </div>
                </form>
            </div>
        </div>

        <!-- Decorative Elements -->
        
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
            <div class="blob blob-3"></div>
        
    </div>

    <!-- Platform Statistics Section -->
    <div class="stats-section bg-secondary py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                    Trusted by Event Organizers Worldwide
                </h2>
                <p class="text-xl text-secondary max-w-2xl mx-auto">
                    Join thousands of successful events powered by our platform
                </p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <!-- Total Events -->
                <div class="stat-card text-center scale-in delay-100">
                    <div class="stat-icon bg-blue-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="stat-number text-3xl font-bold text-primary mb-2" data-stat="total_events">
                        {{ $stats['total_events'] ?? '0' }}
                    </div>
                    <div class="stat-label text-secondary">Events Created</div>
                </div>

                <!-- Total Invitations -->
                <div class="stat-card text-center scale-in delay-200">
                    <div class="stat-icon bg-green-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="stat-number text-3xl font-bold text-primary mb-2" data-stat="total_invitations_sent">
                        {{ $stats['total_invitations_sent'] ?? '0' }}
                    </div>
                    <div class="stat-label text-secondary">Invitations Sent</div>
                </div>

                <!-- Total Guests -->
                <div class="stat-card text-center scale-in delay-300">
                    <div class="stat-icon bg-purple-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-number text-3xl font-bold text-primary mb-2" data-stat="total_guests">
                        {{ $stats['total_guests'] ?? '0' }}
                    </div>
                    <div class="stat-label text-secondary">Guests Managed</div>
                </div>

                <!-- Total Users -->
                <div class="stat-card text-center scale-in delay-400">
                    <div class="stat-icon bg-yellow-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="stat-number text-3xl font-bold text-primary mb-2" data-stat="total_users">
                        {{ $stats['total_users'] ?? '0' }}
                    </div>
                    <div class="stat-label text-secondary">Active Users</div>
                </div>
            </div>

            <!-- Additional Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                <div class="text-center scale-in delay-500">
                    <div class="text-2xl font-bold text-primary mb-2" data-stat="events_this_month">
                        {{ $stats['events_this_month'] ?? '0' }}
                    </div>
                    <div class="text-secondary">Events This Month</div>
                </div>
                <div class="text-center scale-in delay-600">
                    <div class="text-2xl font-bold text-primary mb-2" data-stat="invitations_this_month">
                        {{ $stats['invitations_this_month'] ?? '0' }}
                    </div>
                    <div class="text-secondary">Invitations This Month</div>
                </div>
                <div class="text-center scale-in delay-700">
                    <div class="text-2xl font-bold text-primary mb-2" data-stat="total_trials">
                        {{ $stats['total_trials'] ?? '0' }}
                    </div>
                    <div class="text-secondary">Trial Requests</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="features-section bg-primary">
        <div class="features-container">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                    Everything You Need for Event Success
                </h2>
                <p class="text-xl text-secondary max-w-2xl mx-auto">
                    Our platform provides all the tools you need to manage guests efficiently, 
                    from list creation to real-time check-ins.
                </p>
            </div>
            

            <div class="features-grid">
                <!-- Feature 1: Guest Lists -->
                <div class="feature-card stagger-animation delay-100">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">Smart Guest Lists</h3>
                    <p class="text-secondary">
                        Create and manage guest lists with ease. Import from spreadsheets, 
                        organize by groups, and track RSVPs in real-time.
                    </p>
                </div>

                <!-- Feature 2: QR Code Scanning -->
                <div class="feature-card stagger-animation delay-200">
                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">QR Code Scanning</h3>
                    <p class="text-secondary">
                        Lightning-fast check-ins with QR code scanning. Works offline and 
                        syncs automatically when connection is restored.
                    </p>
                </div>

                <!-- Feature 3: Real-time Analytics -->
                <div class="feature-card stagger-animation delay-300">
                    <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">Real-time Analytics</h3>
                    <p class="text-secondary">
                        Get instant insights into your events. Track attendance, 
                        check-in times, and generate comprehensive reports.
                    </p>
                </div>

                <!-- Feature 4: Smart Reminders -->
                <div class="feature-card stagger-animation delay-400">
                    <div class="w-12 h-12 bg-yellow-600 rounded-lg flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 18a3 3 0 0 0 6 0M5 12v-1a7 7 0 1 1 14 0v1M6 17h12"/>
                    </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">Smart Reminders</h3>
                    <p class="text-secondary">
                        Automated event reminders keep your guests informed. Send timely notifications 
                        before the event starts and ensure everyone arrives on time.
                    </p>
                </div>

                <!-- Feature 5: Import/Export -->
                <div class="feature-card stagger-animation delay-500">
                    <div class="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">Easy Import/Export</h3>
                    <p class="text-secondary">
                        Import guest lists from Excel, CSV, Google Sheets, or even from your Google Contacts. 
                        Export data in multiple formats for reporting and analysis.
                    </p>
                </div>

                <!-- Feature 6: AI Assistant, InvaroAI -->
                <div class="feature-card stagger-animation delay-600">
                    <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">AI Assistant, InvaroAI</h3>
                    <p class="text-secondary">
                    InvaroAI instantly creates personalized, multi-language messages for every guest in under 5 seconds.
                    Delight your guests, save hours of work, and focus on making your event unforgettable.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div id="how-it-works" class="how-it-works-section bg-secondary">
        <div class="how-it-works-container">
            <div class="text-center mb-16 fade-in">
            <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                How It Works
            </h2>
            <p class="text-xl text-secondary">
                Launch in minutes — three simple steps
            </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Step 1 -->
            <div class="text-center scale-in delay-100">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="text-2xl font-bold text-white">1</span>
                </div>
                <h3 class="text-xl font-semibold text-primary mb-4">Create Your Guest List</h3>
                <p class="text-secondary">
                Import from CSV/Sheets, Google Sheets, or even from your Google Contacts. Clean, segment, and you’re ready to go.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="text-center scale-in delay-200">
                <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="text-2xl font-bold text-white">2</span>
                </div>
                <h3 class="text-xl font-semibold text-primary mb-4">Create Your First Event</h3>
                <p class="text-secondary">
                Follow the guided setup details, schedule, and AI invitations published in under 2 minutes.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="text-center scale-in delay-300">
                <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="text-2xl font-bold text-white">3</span>
                </div>
                <h3 class="text-xl font-semibold text-primary mb-4">Track the Event & Reports</h3>
                <p class="text-secondary">
                Monitor RSVPs and attendance live, scan QR at the door, and export instant reports.
                </p>
            </div>
            </div>
        </div>
    </div>


    <!-- CTA Section -->
    <div class="cta-section bg-gradient-to-r from-blue-600 to-purple-600">
        <div class="cta-container">
            <div class="scale-in">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                    Ready to Transform Your Event Management?
                </h2>
                <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                    Join thousands of event organizers who trust our platform 
                    to manage their guests efficiently and professionally.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @auth
                        <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : (Auth::user()->role === 'organizer' ? route('organizer.dashboard') : route('scanner.dashboard')) }}" class="btn-primary text-lg px-8 py-4">
                            Access Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn-primary text-lg px-8 py-4">
                            Ready
                        </a>
                        <a href="#features" class="btn-secondary text-lg px-8 py-4">
                            Learn More
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex flex-col">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <span class="ml-2 text-xl font-bold">Invaro</span>
                    </div>
                    <p class="text-gray-400 flex-grow">
                    From invite until insight. Build reusable guest lists in seconds,
                    send AI-personalized multilingual invites, and track RSVPs and
                    check-ins live. Schedule or send instantly, then turn real-time
                    dashboards into decisions—all in one platform.
                    </p>
                </div>
                
                <div class="flex flex-col justify-center">
                    <h3 class="text-lg font-semibold mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        <!-- Instagram -->
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-gradient-to-r hover:from-purple-500 hover:to-pink-500 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <!-- Facebook -->
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-blue-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <!-- LinkedIn -->
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-blue-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        <!-- Twitter -->
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-blue-400 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Invaro. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    @vite(['resources/js/app.js', 'resources/js/landing.js'])
</body>
</html> 