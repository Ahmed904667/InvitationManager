@extends('layouts.organizer')

@section('title', 'Delete Account')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900 mb-4">
                <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Delete Account</h1>
            <p class="text-gray-600 dark:text-gray-400">This action cannot be undone</p>
        </div>

        <!-- Warning Box -->
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Warning: This action is permanent</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <p>Deleting your account will permanently remove:</p>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li>All your event data and guest lists</li>
                            <li>Your profile information and settings</li>
                            <li>All associated data and history</li>
                            <li>Access to all Invaro services</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form id="deleteAccountForm" class="space-y-6">
            @csrf
            
            <!-- Password Confirmation -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Confirm your password
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-colors duration-200"
                       placeholder="Enter your current password">
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    We need to verify your identity before proceeding.
                </p>
            </div>

            <!-- Confirmation Checkbox -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="confirm-deletion" 
                           name="confirm_deletion" 
                           type="checkbox" 
                           required
                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="confirm-deletion" class="text-gray-700 dark:text-gray-300">
                        I understand that this action is <strong>permanent and cannot be undone</strong>. I want to delete my account and all associated data.
                    </label>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6">
                <button type="submit" 
                        id="deleteButton"
                        class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="deleteButtonText">Request Account Deletion</span>
                    <svg id="deleteButtonSpinner" class="hidden animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
                
                <a href="{{ route('organizer.settings') }}" 
                   class="flex-1 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-lg font-medium hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200 text-center">
                    Cancel
                </a>
            </div>
        </form>

        <!-- Success Message -->
        <div id="successMessage" class="hidden mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200" id="successText"></p>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div id="errorMessage" class="hidden mt-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200" id="errorText"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('deleteAccountForm');
    const deleteButton = document.getElementById('deleteButton');
    const deleteButtonText = document.getElementById('deleteButtonText');
    const deleteButtonSpinner = document.getElementById('deleteButtonSpinner');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    const successText = document.getElementById('successText');
    const errorText = document.getElementById('errorText');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Hide previous messages
        successMessage.classList.add('hidden');
        errorMessage.classList.add('hidden');
        
        // Show loading state
        deleteButton.disabled = true;
        deleteButtonText.textContent = 'Sending Request...';
        deleteButtonSpinner.classList.remove('hidden');

        try {
            const formData = new FormData(form);
            const response = await fetch('{{ route("account.delete.submit") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                successText.textContent = data.message;
                successMessage.classList.remove('hidden');
                form.reset();
            } else {
                errorText.textContent = data.message || 'An error occurred. Please try again.';
                errorMessage.classList.remove('hidden');
            }
        } catch (error) {
            errorText.textContent = 'An error occurred. Please try again.';
            errorMessage.classList.remove('hidden');
        } finally {
            // Reset button state
            deleteButton.disabled = false;
            deleteButtonText.textContent = 'Request Account Deletion';
            deleteButtonSpinner.classList.add('hidden');
        }
    });
});
</script>
@endsection
