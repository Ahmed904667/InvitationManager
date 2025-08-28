<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Theme Test - Guest Manager</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-primary">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="nav-bg shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-primary">Theme Test Page</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Theme Toggle -->
                        <button class="theme-toggle" type="button" aria-label="Toggle theme">
                            <span class="theme-toggle-thumb"></span>
                            <svg class="sun-icon absolute left-1 h-3 w-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                            </svg>
                            <svg class="moon-icon absolute right-1 h-3 w-3 text-blue-400 hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                            </svg>
                        </button>
                        <a href="/" class="text-secondary hover:text-primary transition-colors duration-200">Back to Home</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="space-y-8">
                <!-- Current Theme Display -->
                <div class="card p-6">
                    <h2 class="text-2xl font-bold text-primary mb-4">Current Theme Status</h2>
                    <div class="space-y-2">
                        <p class="text-secondary">Current theme: <span id="current-theme" class="font-semibold text-primary">Loading...</span></p>
                        <p class="text-secondary">Data attribute: <span id="data-theme" class="font-semibold text-primary">Loading...</span></p>
                        <p class="text-secondary">Dark class: <span id="dark-class" class="font-semibold text-primary">Loading...</span></p>
                    </div>
                </div>

                <!-- Test Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="card p-6">
                        <h3 class="text-lg font-semibold text-primary mb-3">Primary Card</h3>
                        <p class="text-secondary mb-4">This card uses theme-aware classes and should change with the theme.</p>
                        <button class="btn-primary">Primary Button</button>
                    </div>

                    <div class="card p-6">
                        <h3 class="text-lg font-semibold text-primary mb-3">Secondary Card</h3>
                        <p class="text-secondary mb-4">Another card to test theme consistency across components.</p>
                        <button class="btn-secondary">Secondary Button</button>
                    </div>
                </div>

                <!-- Form Test -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-primary mb-4">Form Test</h3>
                    <form class="space-y-4">
                        <div>
                            <label class="form-label">Test Input</label>
                            <input type="text" class="form-input" placeholder="Type something...">
                        </div>
                        <div class="flex space-x-4">
                            <button type="button" class="btn-primary">Submit</button>
                            <button type="button" class="btn-secondary">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Color Palette -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-primary mb-4">Color Palette Test</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-primary p-4 rounded text-white text-center">
                            <div class="font-semibold">Primary</div>
                            <div class="text-sm opacity-90">Background</div>
                        </div>
                        <div class="bg-secondary p-4 rounded text-primary text-center">
                            <div class="font-semibold">Secondary</div>
                            <div class="text-sm opacity-90">Background</div>
                        </div>
                        <div class="bg-tertiary p-4 rounded text-primary text-center">
                            <div class="font-semibold">Tertiary</div>
                            <div class="text-sm opacity-90">Background</div>
                        </div>
                        <div class="border-2 border-primary p-4 rounded text-primary text-center">
                            <div class="font-semibold">Border</div>
                            <div class="text-sm opacity-90">Primary</div>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-primary mb-4">How to Test</h3>
                    <div class="space-y-2 text-secondary">
                        <p>1. Click the theme toggle button in the navigation</p>
                        <p>2. Use keyboard shortcut: <kbd class="px-2 py-1 bg-secondary rounded text-sm">Ctrl+J</kbd></p>
                        <p>3. Check if colors change throughout the page</p>
                        <p>4. Open browser console to see debug messages</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update theme display
        function updateThemeDisplay() {
            const currentTheme = window.themeManager ? window.themeManager.getCurrentTheme() : 'unknown';
            const dataTheme = document.documentElement.getAttribute('data-theme');
            const darkClass = document.documentElement.classList.contains('dark') ? 'Yes' : 'No';
            
            document.getElementById('current-theme').textContent = currentTheme;
            document.getElementById('data-theme').textContent = dataTheme || 'none';
            document.getElementById('dark-class').textContent = darkClass;
        }

        // Update display when theme changes
        document.addEventListener('themeChanged', updateThemeDisplay);
        
        // Initial update
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(updateThemeDisplay, 100);
        });
    </script>
</body>
</html> 