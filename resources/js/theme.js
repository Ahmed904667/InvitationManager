// Theme management for Guest Manager
class ThemeManager {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        // Set initial theme
        this.setTheme(this.theme);
        
        // Add event listeners to theme toggles
        this.addEventListeners();
        
        // Watch for system theme changes
        this.watchSystemTheme();
        
        // Add tooltips to theme toggles
        this.addTooltips();
    }

    setTheme(theme) {
        this.theme = theme;
        
        // Update data-theme attribute
        document.documentElement.setAttribute('data-theme', theme);
        
        // Update class for Tailwind dark mode
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        
        // Update all theme toggles
        this.updateToggles();
        
        // Save to localStorage
        localStorage.setItem('theme', theme);
        
        // Dispatch custom event
        document.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
        
        // Show notification
        this.showThemeNotification(theme);
    }

    toggleTheme() {
        const newTheme = this.theme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
    }

    updateToggles() {
        const toggles = document.querySelectorAll('.theme-toggle');
        toggles.forEach(toggle => {
            toggle.setAttribute('data-theme', this.theme);
            
            // Update icons if they exist
            const sunIcon = toggle.querySelector('.sun-icon');
            const moonIcon = toggle.querySelector('.moon-icon');
            
            if (sunIcon && moonIcon) {
                if (this.theme === 'dark') {
                    sunIcon.classList.add('hidden');
                    moonIcon.classList.remove('hidden');
                } else {
                    sunIcon.classList.remove('hidden');
                    moonIcon.classList.add('hidden');
                }
            }
        });
    }

    addEventListeners() {
        // Add click listeners to theme toggles
        document.addEventListener('click', (e) => {
            if (e.target.closest('.theme-toggle')) {
                e.preventDefault();
                this.toggleTheme();
            }
        });

        // Add keyboard support
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'j') {
                e.preventDefault();
                this.toggleTheme();
            }
        });
    }

    addTooltips() {
        const toggles = document.querySelectorAll('.theme-toggle');
        toggles.forEach(toggle => {
            toggle.title = 'Toggle theme (Ctrl+J)';
        });
    }

    watchSystemTheme() {
        // Check if user prefers dark mode
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        const handleChange = (e) => {
            if (localStorage.getItem('theme') === null) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        };
        
        mediaQuery.addEventListener('change', handleChange);
    }

    showThemeNotification(theme) {
        // Create a temporary notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 bg-primary-500 text-white px-4 py-2 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full';
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    ${theme === 'dark' 
                        ? '<path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />'
                        : '<path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />'
                    }
                </svg>
                Switched to ${theme} mode
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 2 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 2000);
    }

    // Get current theme
    getCurrentTheme() {
        return this.theme;
    }

    // Check if dark mode is active
    isDarkMode() {
        return this.theme === 'dark';
    }
}

// Initialize theme manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});

// Export for use in other modules
export default ThemeManager; 