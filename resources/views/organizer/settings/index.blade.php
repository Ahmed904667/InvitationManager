@extends('layouts.organizer')

@section('title', 'Organizer Settings')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-primary">Organizer Settings</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage your preferences and notification settings</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        ⚙️ Settings
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Settings Navigation -->
        <div class="bg-white shadow rounded-lg">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button onclick="showTab('notifications')" 
                            id="notifications-tab" 
                            class="tab-button active border-primary text-primary whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        🔔 Notifications
                    </button>
                    <button onclick="showTab('preferred-list')" 
                            id="preferred-list-tab" 
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        📋 Preferred List Settings
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Notifications Tab -->
                <div id="notifications-content" class="tab-content active">
                    <form action="{{ route('organizer.settings.notifications') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <h3 class="text-lg font-medium text-primary mb-4">Notification Preferences</h3>
                            
                            <!-- Mute Notifications -->
                            <div class="flex items-center mb-6">
                                <input type="checkbox" 
                                       id="mute_notifications" 
                                       name="mute_notifications" 
                                       value="1"
                                       {{ $notificationSettings['mute_notifications'] ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="mute_notifications" class="ml-3 block text-sm font-medium text-gray-700">
                                    Mute all notifications
                                </label>
                                <p class="ml-2 text-sm text-gray-500">When enabled, you won't receive any notifications</p>
                            </div>

                            <!-- Preferred Platform -->
                            <div class="space-y-4">
                                <label class="block text-sm font-medium text-gray-700">Preferred Notification Platform</label>
                                
                                <div class="space-y-3">
                                    <div class="flex items-center">
                                        <input type="radio" 
                                               id="platform_email" 
                                               name="preferred_platform" 
                                               value="email"
                                               {{ $notificationSettings['preferred_platform'] === 'email' ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                        <label for="platform_email" class="ml-3 block text-sm font-medium text-gray-700">
                                            📧 Email Notifications
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input type="radio" 
                                               id="platform_whatsapp" 
                                               name="preferred_platform" 
                                               value="whatsapp"
                                               {{ $notificationSettings['preferred_platform'] === 'whatsapp' ? 'checked' : '' }}
                                               {{ empty($notificationSettings['phone']) ? 'disabled' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 disabled:opacity-50">
                                        <label for="platform_whatsapp" class="ml-3 block text-sm font-medium text-gray-700">
                                            💬 WhatsApp Notifications
                                        </label>
                                        @if(empty($notificationSettings['phone']))
                                            <span class="ml-2 text-sm text-red-500">(Phone number required)</span>
                                        @endif
                                    </div>
                                </div>

                                @if(empty($notificationSettings['phone']))
                                    <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-yellow-800">Phone Number Required</h3>
                                                <div class="mt-2 text-sm text-yellow-700">
                                                    <p>To use WhatsApp notifications, you need to add a phone number to your profile. 
                                                    <a href="{{ route('organizer.profile.edit') }}" class="font-medium underline hover:text-yellow-600">Update your profile</a> to add a phone number.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Notification Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Preferred List Settings Tab -->
                <div id="preferred-list-content" class="tab-content hidden">
                    <form action="{{ route('organizer.settings.preferred-list') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <h3 class="text-lg font-medium text-primary mb-4">Guest List Preferences</h3>
                            
                            <!-- Enable Guest Fields -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Enable Guest Fields</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               id="field_email" 
                                               name="enable_guest_fields[email]" 
                                               value="1"
                                               {{ ($preferredListSettings['enable_guest_fields']['email'] ?? false) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="field_email" class="ml-2 block text-sm text-gray-700">Email</label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               id="field_phone" 
                                               name="enable_guest_fields[phone]" 
                                               value="1"
                                               {{ ($preferredListSettings['enable_guest_fields']['phone'] ?? false) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="field_phone" class="ml-2 block text-sm text-gray-700">Phone</label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               id="field_group" 
                                               name="enable_guest_fields[group]" 
                                               value="1"
                                               {{ ($preferredListSettings['enable_guest_fields']['group'] ?? false) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="field_group" class="ml-2 block text-sm text-gray-700">Group</label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               id="field_preferred_language" 
                                               name="enable_guest_fields[preferred_language]" 
                                               value="1"
                                               {{ ($preferredListSettings['enable_guest_fields']['preferred_language'] ?? false) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="field_preferred_language" class="ml-2 block text-sm text-gray-700">Preferred Language</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Defaults -->
                            <div class="mb-6">
                                <h4 class="text-md font-medium text-gray-700 mb-3">Defaults</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="default_country_code" class="block text-sm font-medium text-gray-700">Default Country Code</label>
                                        <select id="default_country_code" 
                                                name="defaults[country_code]" 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="+1" {{ ($preferredListSettings['defaults']['country_code'] ?? '+1') === '+1' ? 'selected' : '' }}>+1 (USA)</option>
                                            <option value="+44" {{ ($preferredListSettings['defaults']['country_code'] ?? '+1') === '+44' ? 'selected' : '' }}>+44 (UK)</option>
                                            <option value="+33" {{ ($preferredListSettings['defaults']['country_code'] ?? '+1') === '+33' ? 'selected' : '' }}>+33 (France)</option>
                                            <option value="+49" {{ ($preferredListSettings['defaults']['country_code'] ?? '+1') === '+49' ? 'selected' : '' }}>+49 (Germany)</option>
                                            <option value="+81" {{ ($preferredListSettings['defaults']['country_code'] ?? '+1') === '+81' ? 'selected' : '' }}>+81 (Japan)</option>
                                            <option value="+86" {{ ($preferredListSettings['defaults']['country_code'] ?? '+1') === '+86' ? 'selected' : '' }}>+86 (China)</option>
                                            <option value="+91" {{ ($preferredListSettings['defaults']['country_code'] ?? '+1') === '+91' ? 'selected' : '' }}>+91 (India)</option>
                                            <option value="+971" {{ ($preferredListSettings['defaults']['country_code'] ?? '+1') === '+971' ? 'selected' : '' }}>+971 (UAE)</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="default_language" class="block text-sm font-medium text-gray-700">Default Language</label>
                                        <select id="default_language" 
                                                name="defaults[language]" 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="en" {{ ($preferredListSettings['defaults']['language'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                                            <option value="es" {{ ($preferredListSettings['defaults']['language'] ?? 'en') === 'es' ? 'selected' : '' }}>Spanish</option>
                                            <option value="fr" {{ ($preferredListSettings['defaults']['language'] ?? 'en') === 'fr' ? 'selected' : '' }}>French</option>
                                            <option value="de" {{ ($preferredListSettings['defaults']['language'] ?? 'en') === 'de' ? 'selected' : '' }}>German</option>
                                            <option value="it" {{ ($preferredListSettings['defaults']['language'] ?? 'en') === 'it' ? 'selected' : '' }}>Italian</option>
                                            <option value="pt" {{ ($preferredListSettings['defaults']['language'] ?? 'en') === 'pt' ? 'selected' : '' }}>Portuguese</option>
                                            <option value="ar" {{ ($preferredListSettings['defaults']['language'] ?? 'en') === 'ar' ? 'selected' : '' }}>Arabic</option>
                                            <option value="zh" {{ ($preferredListSettings['defaults']['language'] ?? 'en') === 'zh' ? 'selected' : '' }}>Chinese</option>
                                            <option value="ja" {{ ($preferredListSettings['defaults']['language'] ?? 'en') === 'ja' ? 'selected' : '' }}>Japanese</option>
                                            <option value="ko" {{ ($preferredListSettings['defaults']['language'] ?? 'en') === 'ko' ? 'selected' : '' }}>Korean</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Auto-archive Settings -->
                            <div class="mb-6">
                                <div class="flex items-center mb-3">
                                    <input type="checkbox" 
                                           id="auto_archive_events" 
                                           name="auto_archive_events" 
                                           value="1"
                                           {{ ($preferredListSettings['auto_archive_events'] ?? false) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="auto_archive_events" class="ml-2 block text-sm font-medium text-gray-700">
                                        Auto-archive completed events
                                    </label>
                                </div>
                                
                                <div class="ml-6">
                                    <label for="auto_archive_days" class="block text-sm text-gray-600">Archive after (days)</label>
                                    <input type="number" 
                                           id="auto_archive_days" 
                                           name="auto_archive_days" 
                                           value="{{ $preferredListSettings['auto_archive_days'] ?? 30 }}"
                                           min="1" 
                                           max="365"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>

                            <!-- Guest Limits -->
                            <div class="mb-6">
                                <label for="max_guests_per_list" class="block text-sm font-medium text-gray-700">Maximum Guests per List</label>
                                <input type="number" 
                                       id="max_guests_per_list" 
                                       name="max_guests_per_list" 
                                       value="{{ $preferredListSettings['max_guests_per_list'] ?? 1000 }}"
                                       min="1" 
                                       max="10000"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-sm text-gray-500">Maximum number of guests allowed per guest list</p>
                            </div>


                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Preferred List Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.tab-button.active {
    border-color: #3B82F6;
    color: #3B82F6;
}

.tab-button {
    transition: all 0.2s ease-in-out;
}

.tab-button:hover {
    border-color: #9CA3AF;
    color: #374151;
}
</style>

<script>
function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });

    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.classList.remove('active');
        button.classList.remove('border-primary', 'text-primary');
        button.classList.add('border-transparent', 'text-gray-500');
    });

    // Show selected tab content
    const selectedContent = document.getElementById(tabName + '-content');
    if (selectedContent) {
        selectedContent.classList.add('active');
    }

    // Activate selected tab button
    const selectedButton = document.getElementById(tabName + '-tab');
    if (selectedButton) {
        selectedButton.classList.add('active', 'border-primary', 'text-primary');
        selectedButton.classList.remove('border-transparent', 'text-gray-500');
    }
}

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
