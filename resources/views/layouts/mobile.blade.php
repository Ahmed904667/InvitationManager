<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Event Scanner')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Logo.jpg') }}">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Mobile optimizations -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- QR Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    
    <!-- Chart.js for performance graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Check HTTPS for camera access -->
    <script>
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            console.warn('Camera access requires HTTPS. Consider serving this page over HTTPS for full functionality.');
        }
    </script>
    
    @stack('styles')
    
    <style>
        /* Mobile-first styling with web app theme */
        :root {
            --primary: #8b2bfa;
            --primary-50: #f5f3ff;
            --primary-100: #ede9fe;
            --primary-200: #ddd6fe;
            --primary-300: #c4b5fd;
            --primary-400: #a78bfa;
            --primary-500: #8b2bfa;
            --primary-600: #7c3aed;
            --primary-700: #6d28d9;
            --primary-800: #5b21b6;
            --primary-900: #4c1d95;
            --accent: #3b82f6;
            --accent-50: #eff6ff;
            --accent-100: #dbeafe;
            --accent-600: #2563eb;
            --accent-700: #1d4ed8;
            --success: #10b981;
            --success-50: #ecfdf5;
            --success-100: #d1fae5;
            --success-200: #a7f3d0;
            --success-300: #6ee7b7;
            --success-400: #34d399;
            --success-500: #10b981;
            --success-600: #059669;
            --success-700: #047857;
            --success-800: #065f46;
            --success-900: #064e3b;
            --info: #3b82f6;
            --info-50: #eff6ff;
            --info-100: #dbeafe;
            --info-200: #bfdbfe;
            --info-300: #93c5fd;
            --info-400: #60a5fa;
            --info-500: #3b82f6;
            --info-600: #2563eb;
            --info-700: #1d4ed8;
            --info-800: #1e40af;
            --info-900: #1e3a8a;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-800);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        .mobile-container {
            max-width: 100vw;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Regular page layout for desktop */
        @media (min-width: 1024px) {
            .mobile-container {
                max-width: 100%;
                min-height: auto;
                display: block;
                padding: 0 2rem;
            }
            
            body {
                background: var(--primary-50);
            }
        }

        /* Responsive breakpoints */
        @media (min-width: 640px) {
            .mobile-container {
                max-width: 100%;
            }
            
            .mobile-header {
                padding: 1.25rem;
            }
            
            .mobile-toolbar {
                padding: 1rem 1.5rem;
            }
        }
        
        @media (min-width: 768px) {
            .mobile-container {
                max-width: 100%;
            }
            
            .mobile-header {
                padding: 1.5rem;
            }
            
            .mobile-toolbar {
                padding: 1.25rem 2rem;
            }
        }
        
        /* Desktop adjustments */
        @media (min-width: 1024px) {
            .mobile-container {
                max-width: 100%;
                margin: 0;
                box-shadow: none;
                border-radius: 0;
                overflow: visible;
            }
            
            .mobile-header {
                border-radius: 0;
                padding: 1.75rem;
            }
            
            .mobile-toolbar {
                position: relative;
                box-shadow: 0 2px 20px rgba(0,0,0,0.1);
                border-radius: 1rem;
                padding: 1.5rem 2rem;
                margin-bottom: 2rem;
            }
            
            .mobile-content {
                padding-bottom: 0;
            }
        }
        
        @media (min-width: 1280px) {
            .mobile-container {
                max-width: 1000px;
            }
        }
        
        @media (min-width: 1536px) {
            .mobile-container {
                max-width: 1100px;
            }
        }

        .mobile-header {
            background: linear-gradient(135deg, var(--primary-800), var(--primary-700));
            color: white;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        /* Regular header for desktop */
        @media (min-width: 1024px) {
            .mobile-header {
                position: relative;
                border-radius: 0;
                margin-bottom: 2rem;
            }
        }

        .mobile-content {
            flex: 1;
            padding: 0;
            overflow-y: auto;
        }

        .mobile-toolbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(229, 231, 235, 0.5);
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-around;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 40;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Regular toolbar for desktop */
        @media (min-width: 1024px) {
            .mobile-toolbar {
                position: relative;
                border-top: none;
                border-bottom: 1px solid rgba(229, 231, 235, 0.5);
                box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
                margin-bottom: 2rem;
                border-radius: 1rem;
            }
        }

        .toolbar-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.75rem 0.5rem;
            color: var(--gray-500);
            text-decoration: none;
            border-radius: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 70px;
            position: relative;
            overflow: hidden;
        }
        
        @media (min-width: 640px) {
            .toolbar-btn {
                min-width: 80px;
                padding: 0.875rem 0.75rem;
            }
        }
        
        @media (min-width: 768px) {
            .toolbar-btn {
                min-width: 90px;
                padding: 1rem 1rem;
            }
        }

        .toolbar-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 1rem;
        }

        .toolbar-btn.active {
            color: white;
            transform: translateY(-2px);
        }

        .toolbar-btn.active::before {
            opacity: 1;
        }

        .toolbar-btn:hover {
            transform: translateY(-1px);
            color: var(--primary-600);
        }

        .toolbar-btn.active:hover {
            color: white;
        }

        .toolbar-btn svg {
            width: 24px;
            height: 24px;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }

        .toolbar-btn.active svg {
            transform: scale(1.1);
        }

        .toolbar-btn span {
            font-size: 0.75rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        @media (min-width: 640px) {
            .toolbar-btn svg {
                width: 26px;
                height: 26px;
                margin-bottom: 0.625rem;
            }
            
            .toolbar-btn span {
                font-size: 0.8rem;
            }
        }
        
        @media (min-width: 768px) {
            .toolbar-btn svg {
                width: 28px;
                height: 28px;
                margin-bottom: 0.75rem;
            }
            
            .toolbar-btn span {
                font-size: 0.875rem;
            }
        }

        .btn-primary {
            background-color: var(--accent-600);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .btn-primary:hover {
            background-color: var(--accent-700);
        }

        .btn-secondary {
            background-color: white;
            color: var(--gray-700);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid var(--gray-300);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background-color: var(--gray-50);
            border-color: var(--gray-400);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-200);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-600);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        /* Scanner specific styles */
        .scanner-container {
            background: black;
            border-radius: 1rem;
            overflow: hidden;
            position: relative;
            aspect-ratio: 1;
            max-width: 300px;
            margin: 0 auto;
        }

        .scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .scanner-target {
            width: 200px;
            height: 200px;
            border: 2px solid white;
            border-radius: 1rem;
            position: relative;
        }

        .scanner-target::before,
        .scanner-target::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid var(--accent-600);
        }

        .scanner-target::before {
            top: -3px;
            left: -3px;
            border-right: none;
            border-bottom: none;
        }

        .scanner-target::after {
            top: -3px;
            right: -3px;
            border-left: none;
            border-bottom: none;
        }

        /* Mobile responsive adjustments */
        @media (max-width: 640px) {
            .mobile-content {
                padding-bottom: 80px; /* Space for toolbar */
            }
            
            .scanner-container {
                max-width: 280px;
            }
        }

        /* Loading spinner */
        .loading-spinner {
            border: 3px solid var(--gray-300);
            border-top: 3px solid var(--accent-600);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Hide scrollbars but keep scrolling */
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body>
    <div class="mobile-container">
        <!-- Header -->
        <div class="mobile-header">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <div>
                        <h1 class="text-lg font-bold">@yield('header-title', 'Event Scanner')</h1>
                        <p class="text-sm opacity-90">@yield('header-subtitle', '')</p>
                    </div>
                </div>
                @yield('header-actions')
            </div>
        </div>

        <!-- Content -->
        <div class="mobile-content">
            @yield('content')
        </div>

        <!-- Bottom Toolbar -->
        @yield('toolbar')
    </div>

    <!-- Global JavaScript -->
    <script>
        // Set CSRF token for AJAX requests
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Mobile utility functions
        function showAlert(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} fixed top-4 left-4 right-4 z-50`;
            alertDiv.textContent = message;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        function showLoading(element) {
            const spinner = document.createElement('div');
            spinner.className = 'loading-spinner mx-auto';
            element.innerHTML = '';
            element.appendChild(spinner);
        }

        // Prevent zoom on double tap (iOS Safari)
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);

        // Add to home screen prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });
    </script>

    @stack('scripts')
</body>
</html>
