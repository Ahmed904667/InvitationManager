<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Manager - Professional Event Management</title>
    <meta name="description" content="Streamline your events with our comprehensive guest management platform. Perfect for organizers, scanners, and administrators.">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/css/landing.css'])
</head>
<body class="bg-primary text-primary">
    <!-- Navigation -->
    <nav class="nav-bg border-b border-primary sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold gradient-text">
                        Guest Manager
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
                    Streamline your events with our comprehensive guest management platform. 
                    Perfect for organizers, scanners, and administrators who need reliable, 
                    efficient tools to manage guest lists and check-ins.
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
                    <h2 class="trial-title">Try It Free</h2>
                    <p class="trial-subtitle">
                        Get a sample invitation sent to your WhatsApp or email to see our platform in action
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

                <!-- Feature 4: Role-based Access -->
                <div class="feature-card stagger-animation delay-400">
                    <div class="w-12 h-12 bg-yellow-600 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">Secure Access Control</h3>
                    <p class="text-secondary">
                        Role-based permissions ensure everyone has the right access. 
                        Admins, organizers, and scanners each have tailored interfaces.
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
                        Import guest lists from Excel, CSV, or Google Sheets. 
                        Export data in multiple formats for reporting and analysis.
                    </p>
                </div>

                <!-- Feature 6: Mobile Optimized -->
                <div class="feature-card stagger-animation delay-600">
                    <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">Mobile Optimized</h3>
                    <p class="text-secondary">
                        Perfect for on-the-go event management. Responsive design 
                        works seamlessly on phones, tablets, and desktops.
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
                    Get started in minutes with our simple three-step process
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center scale-in delay-100">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-white">1</span>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">Create Your Event</h3>
                    <p class="text-secondary">
                        Set up your event details and create guest lists. 
                        Import existing data or start from scratch.
                    </p>
                </div>

                <div class="text-center scale-in delay-200">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-white">2</span>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">Invite Your Team</h3>
                    <p class="text-secondary">
                        Add organizers and scanners to your team. 
                        Each role gets access to the tools they need.
                    </p>
                </div>

                <div class="text-center scale-in delay-300">
                    <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-white">3</span>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">Manage Your Event</h3>
                    <p class="text-secondary">
                        Track check-ins in real-time, generate reports, 
                        and ensure your event runs smoothly.
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
                            Start Free Trial
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <span class="ml-2 text-xl font-bold">Guest Manager</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Professional guest management solution for events of all sizes. 
                        Streamline your check-ins and manage guest lists with ease.
                    </p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Product</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">API</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Documentation</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Status</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Guest Manager. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    @vite(['resources/js/app.js', 'resources/js/landing.js'])
</body>
</html> 