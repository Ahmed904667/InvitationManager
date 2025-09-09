@extends('layouts.organizer')

@section('title', 'Organizer Settings')

@section('content')
<div class="min-h-screen bg-secondray">
    <!-- Header -->
    <div class="bg-primary shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-primary">Organizer Settings</h1>
                    <p class="mt-1 text-sm text-secondray-500">Manage your preferences and notification settings</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profile Settings -->
            <div class="bg-primary shadow rounded-lg">
                <div class="px-6 py-4 border-b border-primary">
                    <h3 class="text-lg font-medium text-primary-600">Profile Settings</h3>
                    <p class="mt-1 text-sm text-secondray-500">Manage your personal information and contact details</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-secondary rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-primary">Personal Information</h4>
                                    <p class="text-sm text-secondray">Update your name, email, and contact details</p>
                                </div>
                            </div>
                            <a href="{{ route('organizer.profile.edit') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Profile
                            </a>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-secondary rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-primary">Security</h4>
                                    <p class="text-sm text-secondray">Change your password and security settings</p>
                                </div>
                            </div>
                            <a href="{{ route('organizer.profile.edit') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-primary shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-primary-600">Notification Preferences</h3>
                    <p class="mt-1 text-sm text-secondray-500">Configure how you receive notifications</p>
                </div>
                <div class="p-6">
                    <form action="{{ route('organizer.settings.notifications') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <!-- Mute Notifications -->
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="mute_notifications" 
                                   name="mute_notifications" 
                                   value="1"
                                   {{ $notificationSettings['mute_notifications'] ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="mute_notifications" class="ml-3 block text-sm font-medium text-primary">
                                Mute all notifications
                            </label>
                        </div>
                        <p class="text-sm text-secondray">When enabled, you won't receive any notifications</p>

                        <!-- Preferred Platform -->
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-primary">Preferred Notification Platform</label>
                            
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input type="radio" 
                                           id="platform_email" 
                                           name="preferred_platform" 
                                           value="email"
                                           {{ $notificationSettings['preferred_platform'] === 'email' ? 'checked' : '' }}
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300">
                                    <label for="platform_email" class="ml-3 block text-sm font-medium text-secondray">
                                        Email Notifications
                                    </label>
                                </div>
                                
                                <div class="flex items-center">
                                    <input type="radio" 
                                           id="platform_whatsapp" 
                                           name="preferred_platform" 
                                           value="whatsapp"
                                           {{ $notificationSettings['preferred_platform'] === 'whatsapp' ? 'checked' : '' }}
                                           {{ empty($notificationSettings['phone']) ? 'disabled' : '' }}
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 disabled:opacity-50">
                                    <label for="platform_whatsapp" class="ml-3 block text-sm font-medium text-secondray">
                                        WhatsApp Notifications
                                    </label>
                                    @if(empty($notificationSettings['phone']))
                                        <span class="ml-2 text-sm text-danger-500">(Phone number required)</span>
                                    @endif
                                </div>
                            </div>

                            @if(empty($notificationSettings['phone']))
                                <div class="mt-4 p-4 bg-warning-50 border border-warning-200 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-warning-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-warning-800">Phone Number Required</h3>
                                            <div class="mt-2 text-sm text-warning-700">
                                                <p>To use WhatsApp notifications, you need to add a phone number to your profile. 
                                                <a href="{{ route('organizer.profile.edit') }}" class="font-medium underline hover:text-warning-600">Update your profile</a> to add a phone number.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Notification Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Management -->
            <div class="bg-primary shadow rounded-lg lg:col-span-2">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-primary-600">Account Management</h3>
                    <p class="mt-1 text-sm text-secondray-500">Manage your account and data</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Delete Account -->
                        <div class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-red-800 dark:text-red-200">Delete Account</h4>
                                    <p class="text-sm text-red-600 dark:text-red-300">Permanently delete your account and all data</p>
                                </div>
                            </div>
                            <a href="{{ route('account.delete.request') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Account
                            </a>
                        </div>
                        
                        <!-- Warning Notice -->
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Warning</h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p>Deleting your account is <strong>permanent and cannot be undone</strong>. This will remove:</p>
                                        <ul class="list-disc list-inside mt-2 space-y-1">
                                            <li>All your event data and guest lists</li>
                                            <li>Your profile information and settings</li>
                                            <li>All associated data and history</li>
                                            <li>Access to all Invaro services</li>
                                        </ul>
                                        <p class="mt-2">You will receive a confirmation email before the deletion is processed.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show success/error messages
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        // You can implement a toast notification here
        console.log('Success: {{ session('success') }}');
    @endif

    @if($errors->any())
        // You can implement error display here
        console.log('Errors: {{ $errors->first() }}');
    @endif
});
</script>
@endsection
