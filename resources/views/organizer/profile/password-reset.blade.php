@extends('layouts.organizer')

@section('title', 'Reset Password')

@section('content')
<div class="min-h-screen bg-primary py-8">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-primary-600">Reset Password</h1>
            <p class="mt-2 text-secondary">Enter your new password below</p>
        </div>

        <!-- Password Reset Form -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20 p-6">
            @if ($errors->any())
                <div class="mb-6 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-400">
                                There were some errors with your submission
                            </h3>
                            <div class="mt-2 text-sm text-red-300">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('organizer.profile.password.reset.submit') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-primary mb-2">
                        New Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            minlength="8"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-primary dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200"
                            placeholder="Enter your new password"
                        >
                        <button 
                            type="button" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            onclick="togglePassword('password')"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
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

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-primary mb-2">
                        Confirm New Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            required
                            minlength="8"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-primary dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200"
                            placeholder="Confirm your new password"
                        >
                        <button 
                            type="button" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            onclick="togglePassword('password_confirmation')"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button 
                        type="submit" 
                        class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200"
                    >
                        Reset Password
                    </button>
                </div>

                <!-- Back Link -->
                <div class="text-center">
                    <a 
                        href="{{ route('organizer.profile.edit') }}" 
                        class="text-primary-400 hover:text-primary-300 text-sm font-medium transition-colors duration-200"
                    >
                        ← Back to Profile
                    </a>
                </div>
            </form>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-400">
                        Security Notice
                    </h3>
                    <div class="mt-2 text-sm text-blue-300">
                        <p>This password reset link is valid for 24 hours and can only be used once. After resetting your password, this link will expire automatically.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('svg');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
        `;
    } else {
        input.type = 'password';
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        `;
    }
}

// Password strength checking
document.addEventListener('DOMContentLoaded', function() {
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
@endsection
