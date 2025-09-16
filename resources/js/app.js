import './bootstrap';
import './theme';

// Invaro Application JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme manager
    if (window.themeManager) {
        // Theme manager initialized
    }
    
    // Initialize timezone detection
    initializeTimezoneDetection();
    
    // Initialize mobile menu toggle
    initializeMobileMenu();
    
    // Initialize flash message auto-hide
    initializeFlashMessages();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize scanner functionality
    initializeScanner();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize offline detection
    initializeOfflineDetection();
});

// Timezone detection functionality
function initializeTimezoneDetection() {
    // Detect browser timezone - FIXED: use timeZone not timezone
    const browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    
    // Browser timezone detected
    
    // Validate timezone before using it
    if (browserTimezone && browserTimezone !== 'undefined' && browserTimezone !== 'null') {
        // Add timezone to all forms
        addTimezoneToForms(browserTimezone);
        
        // Add timezone to AJAX requests
        addTimezoneToAjaxRequests(browserTimezone);
        
        // Re-run timezone detection when new forms are added to the page
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.tagName === 'FORM' || node.querySelector && node.querySelector('form')) {
                                // New form detected, adding timezone
                                addTimezoneToForms(browserTimezone);
                            }
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    } else {
        // Invalid browser timezone detected
    }
}


function addTimezoneToForms(timezone) {
    // Add timezone to all forms
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Remove existing timezone fields first
        const existingTimezone = form.querySelector('input[name="timezone"]');
        const existingBrowserTimezone = form.querySelector('input[name="browser_timezone"]');
        
        if (existingTimezone) {
            existingTimezone.remove();
        }
        if (existingBrowserTimezone) {
            existingBrowserTimezone.remove();
        }
        
        // Add timezone field
        const timezoneInput = document.createElement('input');
        timezoneInput.type = 'hidden';
        timezoneInput.name = 'timezone';
        timezoneInput.value = timezone;
        form.appendChild(timezoneInput);
        
        // Add browser_timezone for explicit detection
        const browserTimezoneInput = document.createElement('input');
        browserTimezoneInput.type = 'hidden';
        browserTimezoneInput.name = 'browser_timezone';
        browserTimezoneInput.value = timezone;
        form.appendChild(browserTimezoneInput);
        
        // Timezone fields added to form
    });
}

function addTimezoneToAjaxRequests(timezone) {
    // Override fetch to include timezone header
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        if (typeof url === 'string' && url.startsWith('/')) {
            options.headers = {
                ...options.headers,
                'X-Timezone': timezone
            };
        }
        return originalFetch(url, options);
    };
}

// Mobile menu functionality
function initializeMobileMenu() {
    const mobileMenuButton = document.querySelector('[aria-controls="mobile-menu"]');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// Flash message functionality
function initializeFlashMessages() {
    const flashMessages = document.querySelectorAll('#flash-message');
    
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.3s ease-out';
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 300);
        }, 3000);
    });
}

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(function(field) {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required.');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    // Email validation
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(function(field) {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address.');
            isValid = false;
        }
    });
    
    return isValid;
}



function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Scanner functionality
function initializeScanner() {
    const scannerViewport = document.querySelector('.scanner-viewport');
    
    if (scannerViewport) {
        // Initialize camera access for QR code scanning
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            const startScanButton = document.querySelector('#start-scan');
            if (startScanButton) {
                startScanButton.addEventListener('click', startCamera);
            }
        }
    }
}

function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(function(stream) {
            const video = document.querySelector('#scanner-video');
            if (video) {
                video.srcObject = stream;
                video.play();
            }
        })
        .catch(function(error) {
            showNotification('Camera access denied. Please enable camera permissions.', 'error');
        });
}

// Search functionality
function initializeSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(function(input) {
        let searchTimeout;
        
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                performSearch(input.value, input.dataset.searchTarget);
            }, 300);
        });
    });
}

function performSearch(query, target) {
    if (query.length < 2) {
        clearSearchResults(target);
        return;
    }
    
    // AJAX search request
    fetch(`/api/search?q=${encodeURIComponent(query)}&target=${target}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        displaySearchResults(data, target);
    })
    .catch(error => {
        // Search error occurred
    });
}

function displaySearchResults(results, target) {
    const resultsContainer = document.querySelector(`#${target}-results`);
    if (!resultsContainer) return;
    
    resultsContainer.innerHTML = '';
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<div class="p-4 text-gray-500">No results found</div>';
        return;
    }
    
    results.forEach(function(result) {
        const resultElement = document.createElement('div');
        resultElement.className = 'p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-200';
        resultElement.innerHTML = `
            <div class="font-medium">${result.name}</div>
            <div class="text-sm text-gray-500">${result.description || ''}</div>
        `;
        resultElement.addEventListener('click', function() {
            selectSearchResult(result, target);
        });
        resultsContainer.appendChild(resultElement);
    });
}

function clearSearchResults(target) {
    const resultsContainer = document.querySelector(`#${target}-results`);
    if (resultsContainer) {
        resultsContainer.innerHTML = '';
    }
}

function selectSearchResult(result, target) {
    const input = document.querySelector(`[data-search-target="${target}"]`);
    if (input) {
        input.value = result.name;
        input.dataset.selectedId = result.id;
    }
    clearSearchResults(target);
}

// Offline detection
function initializeOfflineDetection() {
    window.addEventListener('online', function() {
        showNotification('Connection restored. Syncing data...', 'success');
        syncOfflineData();
    });
    
    window.addEventListener('offline', function() {
        showNotification('You are now offline. Some features may be limited.', 'warning');
    });
}

function syncOfflineData() {
    // Sync any offline data when connection is restored
    const offlineData = localStorage.getItem('offlineCheckins');
    if (offlineData) {
        const checkins = JSON.parse(offlineData);
        
        checkins.forEach(function(checkin) {
            fetch('/scanner/checkin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(checkin)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Checkin synced successfully
                }
            })
            .catch(error => {
                // Sync error occurred
            });
        });
        
        localStorage.removeItem('offlineCheckins');
        showNotification('Offline data synced successfully!', 'success');
    }
}

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // Ensure it appears above everything including modals
    notification.style.zIndex = '9999';
    notification.style.position = 'fixed';
    notification.style.top = '1rem';
    notification.style.right = '1rem';
    
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="mr-3 text-lg">${getNotificationIcon(type)}</span>
                <span class="font-medium">${message}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white/80 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        if (notification.parentElement) {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(function() {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        success: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        error: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
        warning: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>',
        info: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
    };
    return icons[type] || icons.info;
}

window.showModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Disable page scroll
    }
};

window.hideModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        modal.classList.add('hidden');
        // Only re-enable scroll if no other modals are open
        if (document.querySelectorAll('.modal.show').length === 0) {
            document.body.style.overflow = '';
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal').forEach(function(modal) {
        modal.addEventListener('mousedown', function(e) {
            // Only close if the click is directly on the modal background, not inside modal-content
            if (e.target === modal) {
                modal.classList.remove('show');
                modal.classList.add('hidden');
                if (document.querySelectorAll('.modal.show').length === 0) {
                    document.body.style.overflow = '';
                }
            }
        });
    });
});

// Event Creation Form Functionality
function initializeEventCreationForm() {
    // Initialize collapsible sections
    const sectionHeaders = document.querySelectorAll('.section-header');
    
    sectionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const section = this.closest('.editor-section');
            const content = section.querySelector('.section-content');
            const icon = this.querySelector('.toggle-icon');
            
            // Toggle active state
            this.classList.toggle('active');
            content.classList.toggle('active');
            
            // Update icon rotation
            if (content.classList.contains('active')) {
                icon.style.transform = 'rotate(180deg)';
                icon.style.color = 'var(--primary-600)';
            } else {
                icon.style.transform = 'rotate(0deg)';
                icon.style.color = 'var(--text-secondary)';
            }
        });
    });
    
    // Initialize color picker interactions
    const colorPickers = document.querySelectorAll('input[type="color"]');
    colorPickers.forEach(picker => {
        picker.addEventListener('change', function() {
            // Update corresponding text input if it exists
            const textInput = document.getElementById(this.id.replace('_color', 'ColorText'));
            if (textInput) {
                textInput.value = this.value;
            }
            
            // Update preview if updateColors function exists
            if (typeof updateColors === 'function') {
                updateColors();
            }
        });
    });
    
    // Initialize form validation for event creation
    const eventForm = document.getElementById('eventForm');
    if (eventForm) {
        eventForm.addEventListener('submit', function(e) {
            if (!validateEventForm(this)) {
                e.preventDefault();
                showNotification('Please fix the errors in the form before submitting.', 'error');
            }
        });
    }
    
    // Initialize real-time preview updates
    const previewInputs = document.querySelectorAll('input[oninput*="updatePreview"], textarea[oninput*="updatePreview"]');
    previewInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (typeof updatePreview === 'function') {
                updatePreview();
            }
        });
    });
}

function validateEventForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    // Clear previous errors
    form.querySelectorAll('.field-error').forEach(error => error.remove());
    form.querySelectorAll('.border-red-500').forEach(field => {
        field.classList.remove('border-red-500');
        field.classList.add('border-gray-300');
    });
    
    requiredFields.forEach(function(field) {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required.');
            isValid = false;
        }
    });
    
    // Validate date fields
    const startDate = form.querySelector('#start_date');
    const endDate = form.querySelector('#end_date');
    
    if (startDate && endDate && startDate.value && endDate.value) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        const minDuration = 15; // minutes
        
        if (end <= start) {
            showFieldError(endDate, 'End time must be after start time.');
            isValid = false;
        } else {
            const diffMinutes = (end - start) / (1000 * 60);
            if (diffMinutes < minDuration) {
                showFieldError(endDate, `Event must be at least ${minDuration} minutes long.`);
                isValid = false;
            }
        }
    }
    
    return isValid;
}

function showFieldError(field, message) {
    // Remove existing error
    clearFieldError(field);
    
    // Add error styling
    field.classList.remove('border-gray-300');
    field.classList.add('border-red-500');
    
    // Create error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error text-red-500 text-sm mt-1';
    errorDiv.textContent = message;
    
    // Insert after the field
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('border-red-500');
    field.classList.add('border-gray-300');
    
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Initialize event creation form when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the event creation page
    if (document.querySelector('.editor-container')) {
        initializeEventCreationForm();
    }
});

// Export functions for global use
window.GuestManager = {
    showNotification,
    validateForm,
    performSearch,
    startCamera,
    initializeEventCreationForm,
    validateEventForm
};
