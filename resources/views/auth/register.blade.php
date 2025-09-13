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
            <h3 class="text-2xl font-bold mb-2">Join Our Community</h3>
            <p class="text-lg opacity-90">Create your account and start your journey with us</p>
        </div>
    </div>


    <!-- Right side - Register Form -->
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
                    Create Account
                </h2>
                <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400">
                    Join us today and start your journey
                </p>
            </div>
            
            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-3 py-2 lg:px-4 lg:py-3 rounded-lg mb-4 lg:mb-6">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs lg:text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-4 lg:space-y-6" id="registration-form">
                @csrf
                <div class="space-y-3 lg:space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 lg:mb-2">
                            Full name
                        </label>
                        <input id="name" name="name" type="text" required 
                               class="w-full px-3 py-2 lg:px-4 lg:py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white transition-colors duration-200 text-sm lg:text-base" 
                               placeholder="Enter your full name" value="{{ old('name') }}">
                    </div>
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
                               placeholder="Create a strong password">
                        
                        <!-- Password Strength Indicator -->
                        <div id="password-strength" class="mt-2 hidden">
                            <div class="flex space-x-1 mb-2">
                                <div class="h-1 flex-1 bg-gray-200 dark:bg-gray-700 rounded" id="strength-bar-1"></div>
                                <div class="h-1 flex-1 bg-gray-200 dark:bg-gray-700 rounded" id="strength-bar-2"></div>
                                <div class="h-1 flex-1 bg-gray-200 dark:bg-gray-700 rounded" id="strength-bar-3"></div>
                                <div class="h-1 flex-1 bg-gray-200 dark:bg-gray-700 rounded" id="strength-bar-4"></div>
                            </div>
                            <div id="strength-text" class="text-xs text-gray-600 dark:text-gray-400"></div>
                            <div id="strength-requirements" class="mt-2 text-xs space-y-1">
                                <div class="flex items-center" id="req-length">
                                    <span class="w-4 h-4 mr-2 text-gray-400">✗</span>
                                    <span class="text-gray-600 dark:text-gray-400">At least 8 characters</span>
                                </div>
                                <div class="flex items-center" id="req-lowercase">
                                    <span class="w-4 h-4 mr-2 text-gray-400">✗</span>
                                    <span class="text-gray-600 dark:text-gray-400">One lowercase letter</span>
                                </div>
                                <div class="flex items-center" id="req-uppercase">
                                    <span class="w-4 h-4 mr-2 text-gray-400">✗</span>
                                    <span class="text-gray-600 dark:text-gray-400">One uppercase letter</span>
                                </div>
                                <div class="flex items-center" id="req-number">
                                    <span class="w-4 h-4 mr-2 text-gray-400">✗</span>
                                    <span class="text-gray-600 dark:text-gray-400">One number</span>
                                </div>
                                <div class="flex items-center" id="req-special">
                                    <span class="w-4 h-4 mr-2 text-gray-400">✗</span>
                                    <span class="text-gray-600 dark:text-gray-400">One special character</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 lg:mb-2">
                            Confirm Password
                        </label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required 
                               class="w-full px-3 py-2 lg:px-4 lg:py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white transition-colors duration-200 text-sm lg:text-base" 
                               placeholder="Confirm your password">
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms" name="terms" type="checkbox" required
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded">
                    </div>
                    <div class="ml-3 text-xs lg:text-sm">
                        <label for="terms" class="text-gray-700 dark:text-gray-300">
                            I agree to the 
                            <a href="{{ route('terms') }}" target="_blank" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200">
                                Terms and Conditions
                            </a>
                            and 
                            <a href="{{ route('privacy') }}" target="_blank" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200">
                                Privacy Policy
                            </a>
                        </label>
                    </div>
                </div>

                <div>
                    <button type="button" id="create-account-btn"
                            class="w-full flex justify-center py-2 lg:py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                        Create Account
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-xs lg:text-sm text-gray-600 dark:text-gray-400">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200">
                            Sign in here
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- OTP Verification Modal -->
<div id="otp-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900 mb-4">
                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Verify Your Email</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    We've sent a 6-digit verification code to
                </p>
                <p class="text-sm font-medium text-primary-600 dark:text-primary-400 mb-4" id="modal-email">
                    <!-- Email will be populated here -->
                </p>
                <form id="otp-form" class="space-y-4">
                    @csrf
                    <div>
                        <label for="modal-otp" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Verification Code
                        </label>
                        <input 
                            id="modal-otp" 
                            name="otp_code" 
                            type="text" 
                            maxlength="6"
                            required 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-center tracking-widest"
                            placeholder="000000"
                            autocomplete="one-time-code">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Enter the 6-digit code sent to your email
                        </p>
                    </div>
                    
                    <!-- Error Message -->
                    <div id="otp-error" class="hidden px-3 py-2 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 text-xs rounded-md">
                        <!-- Error message will be shown here -->
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" id="cancel-otp" 
                                class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" id="verify-otp-btn"
                                class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Verify & Create Account
                        </button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Didn't receive the code? 
                        <button type="button" id="resend-otp" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200">
                            Resend Code
                        </button>
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

        // Registration form elements
        const createAccountBtn = document.getElementById('create-account-btn');
        const registrationForm = document.getElementById('registration-form');
        const otpModal = document.getElementById('otp-modal');
        const modalEmail = document.getElementById('modal-email');
        const otpForm = document.getElementById('otp-form');
        const cancelOtpBtn = document.getElementById('cancel-otp');
        const verifyOtpBtn = document.getElementById('verify-otp-btn');
        const resendOtpBtn = document.getElementById('resend-otp');
        const otpError = document.getElementById('otp-error');
        const modalOtpInput = document.getElementById('modal-otp');

        // Store form data for later use
        let formData = {};

        // Create Account button click handler
        createAccountBtn.addEventListener('click', function() {
            // Get form data
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            const terms = document.getElementById('terms').checked;

            // Validate form
            if (!name) {
                showFormError('Please enter your full name.');
                return;
            }

            if (!email) {
                showFormError('Please enter your email address.');
                return;
            }

            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showFormError('Please enter a valid email address.');
                return;
            }

            if (!password) {
                showFormError('Please enter a password.');
                return;
            }

            if (password !== passwordConfirmation) {
                showFormError('Passwords do not match.');
                return;
            }

            if (!terms) {
                showFormError('Please accept the terms and conditions.');
                return;
            }

            // Store form data with timezone
            const browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            formData = { 
                name, 
                email, 
                password, 
                password_confirmation: passwordConfirmation, 
                terms,
                browser_timezone: browserTimezone,
                timezone: browserTimezone
            };

            // Send OTP
            sendOTP(email, name);
        });

        // Send OTP function
        function sendOTP(email, name) {
            createAccountBtn.disabled = true;
            createAccountBtn.textContent = 'Sending OTP...';

            fetch('{{ route("register.send-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    email: email,
                    name: name
                })
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response is not JSON');
                }
                
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show modal
                    modalEmail.textContent = email;
                    otpModal.classList.remove('hidden');
                    modalOtpInput.focus();
                    hideFormError();
                } else {
                    showFormError(data.message || 'Failed to send OTP. Please try again.');
                }
            })
            .catch(error => {
                showFormError('Failed to send OTP. Please try again.');
            })
            .finally(() => {
                createAccountBtn.disabled = false;
                createAccountBtn.textContent = 'Create Account';
            });
        }

        // OTP form submission
        otpForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const otpCode = modalOtpInput.value.trim();
            
            if (!otpCode || otpCode.length !== 6) {
                showOtpError('Please enter a valid 6-digit code.');
                return;
            }

            verifyOTP(otpCode);
        });

        // Verify OTP function
        function verifyOTP(otpCode) {
            verifyOtpBtn.disabled = true;
            verifyOtpBtn.textContent = 'Verifying...';
            hideOtpError();

            // Add OTP to form data (timezone is already included in formData)
            const dataToSend = { ...formData, otp_code: otpCode, email: formData.email };

            fetch('{{ route("register.verify-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Timezone': formData.browser_timezone || Intl.DateTimeFormat().resolvedOptions().timeZone
                },
                credentials: 'same-origin',
                body: JSON.stringify(dataToSend)
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response is not JSON');
                }
                
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Redirect to dashboard
                    window.location.href = data.redirect_url || '/organizer';
                } else {
                    showOtpError(data.message || 'Invalid OTP code. Please try again.');
                }
            })
            .catch(error => {
                showOtpError('Verification failed. Please try again.');
            })
            .finally(() => {
                verifyOtpBtn.disabled = false;
                verifyOtpBtn.textContent = 'Verify & Create Account';
            });
        }

        // Cancel OTP modal
        cancelOtpBtn.addEventListener('click', function() {
            otpModal.classList.add('hidden');
            modalOtpInput.value = '';
            hideOtpError();
        });

        // Resend OTP
        resendOtpBtn.addEventListener('click', function() {
            sendOTP(formData.email, formData.name);
        });

        // Auto-submit when 6 digits are entered
        modalOtpInput.addEventListener('input', function(e) {
            const value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            e.target.value = value;
            
            if (value.length === 6) {
                // Auto-submit form
                setTimeout(() => {
                    otpForm.dispatchEvent(new Event('submit'));
                }, 500);
            }
        });

        // Show form error
        function showFormError(message) {
            // Remove existing error messages
            const existingErrors = document.querySelectorAll('.form-error');
            existingErrors.forEach(error => error.remove());

            // Create new error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'form-error bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-3 py-2 lg:px-4 lg:py-3 rounded-lg mb-4 lg:mb-6';
            errorDiv.textContent = message;

            // Insert after the form
            registrationForm.parentNode.insertBefore(errorDiv, registrationForm.nextSibling);
        }

        // Hide form error
        function hideFormError() {
            const existingErrors = document.querySelectorAll('.form-error');
            existingErrors.forEach(error => error.remove());
        }

        // Show OTP error
        function showOtpError(message) {
            otpError.textContent = message;
            otpError.classList.remove('hidden');
        }

        // Hide OTP error
        function hideOtpError() {
            otpError.classList.add('hidden');
        }

        // Password strength checking
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('password-strength');
        const strengthBars = [
            document.getElementById('strength-bar-1'),
            document.getElementById('strength-bar-2'),
            document.getElementById('strength-bar-3'),
            document.getElementById('strength-bar-4')
        ];
        const strengthText = document.getElementById('strength-text');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length > 0) {
                passwordStrength.classList.remove('hidden');
                checkPasswordStrength(password);
            } else {
                passwordStrength.classList.add('hidden');
            }
        });

        function checkPasswordStrength(password) {
            const requirements = {
                length: password.length >= 8,
                lowercase: /[a-z]/.test(password),
                uppercase: /[A-Z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };

            // Update requirement indicators
            updateRequirement('req-length', requirements.length);
            updateRequirement('req-lowercase', requirements.lowercase);
            updateRequirement('req-uppercase', requirements.uppercase);
            updateRequirement('req-number', requirements.number);
            updateRequirement('req-special', requirements.special);

            // Calculate strength score
            const score = Object.values(requirements).filter(Boolean).length;
            updateStrengthBars(score);
            updateStrengthText(score);
        }

        function updateRequirement(elementId, met) {
            const element = document.getElementById(elementId);
            const icon = element.querySelector('span');
            const text = element.querySelector('span:last-child');
            
            if (met) {
                icon.textContent = '✓';
                icon.className = 'w-4 h-4 mr-2 text-green-500';
                text.className = 'text-green-600 dark:text-green-400';
            } else {
                icon.textContent = '✗';
                icon.className = 'w-4 h-4 mr-2 text-gray-400';
                text.className = 'text-gray-600 dark:text-gray-400';
            }
        }

        function updateStrengthBars(score) {
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
            
            strengthBars.forEach((bar, index) => {
                bar.className = 'h-1 flex-1 rounded transition-colors duration-300';
                
                if (index < score) {
                    bar.classList.add(colors[Math.min(score - 1, 3)]);
                } else {
                    bar.classList.add('bg-gray-200', 'dark:bg-gray-700');
                }
            });
        }

        function updateStrengthText(score) {
            const texts = [
                'Very Weak',
                'Weak', 
                'Fair',
                'Good',
                'Strong'
            ];
            
            const colors = [
                'text-red-600 dark:text-red-400',
                'text-orange-600 dark:text-orange-400',
                'text-yellow-600 dark:text-yellow-400',
                'text-blue-600 dark:text-blue-400',
                'text-green-600 dark:text-green-400'
            ];
            
            strengthText.textContent = texts[score];
            strengthText.className = `text-xs font-medium ${colors[score]}`;
        }
    });
</script>
</body>
</html> 