@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-primary">System Settings</h1>
                    <p class="mt-1 text-sm text-gray-500">Configure system-wide settings and preferences</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="saveAllSettings()" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save All Settings
                    </button>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        👑 Admin
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Settings Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-primary mb-4">Settings Categories</h3>
                        <nav class="space-y-2">
                            <button onclick="showTab('general')" class="settings-tab active w-full text-left px-3 py-2 rounded-md text-sm font-medium text-blue-700 bg-blue-50">
                                General Settings
                            </button>
                            <button onclick="showTab('security')" class="settings-tab w-full text-left px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary hover:bg-gray-50">
                                Security & Privacy
                            </button>
                            <button onclick="showTab('notifications')" class="settings-tab w-full text-left px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary hover:bg-gray-50">
                                Notifications
                            </button>
                            <button onclick="showTab('integrations')" class="settings-tab w-full text-left px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary hover:bg-gray-50">
                                Integrations
                            </button>
                            <button onclick="showTab('advanced')" class="settings-tab w-full text-left px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary hover:bg-gray-50">
                                Advanced
                            </button>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Settings Content -->
            <div class="lg:col-span-2">
                <!-- General Settings -->
                <div id="general" class="settings-content">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-primary mb-4">General Settings</h3>
                            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                                @csrf
                                @method('PUT')
                                
                                <div>
                                    <label class="form-label">Application Name</label>
                                    <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'Guest Manager' }}" class="form-input">
                                    <p class="form-error">The name of your application as it appears to users.</p>
                                </div>

                                <div>
                                    <label class="form-label">Maximum Guests per List</label>
                                    <input type="number" name="max_guests_per_list" value="{{ $settings['max_guests_per_list'] ?? 1000 }}" class="form-input" min="1" max="10000">
                                    <p class="form-error">Maximum number of guests allowed per guest list.</p>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="allow_guest_import" value="1" {{ ($settings['allow_guest_import'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-primary">Allow Guest Import</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="require_guest_approval" value="1" {{ ($settings['require_guest_approval'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-primary">Require Guest Approval</label>
                                </div>

                                <div>
                                    <label class="form-label">Default Timezone</label>
                                    <select name="timezone" class="form-input">
                                        <option value="UTC" {{ ($settings['timezone'] ?? 'UTC') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                        <option value="America/New_York" {{ ($settings['timezone'] ?? 'UTC') === 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                        <option value="America/Chicago" {{ ($settings['timezone'] ?? 'UTC') === 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                        <option value="America/Denver" {{ ($settings['timezone'] ?? 'UTC') === 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                        <option value="America/Los_Angeles" {{ ($settings['timezone'] ?? 'UTC') === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div id="security" class="settings-content hidden">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-primary mb-4">Security & Privacy</h3>
                            <div class="space-y-6">
                                <div>
                                    <label class="form-label">Session Timeout (minutes)</label>
                                    <input type="number" name="session_timeout" value="{{ $settings['session_timeout'] ?? 120 }}" class="form-input" min="15" max="1440">
                                    <p class="form-error">How long before users are automatically logged out.</p>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="require_2fa" value="1" {{ ($settings['require_2fa'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-primary">Require Two-Factor Authentication</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="log_user_activity" value="1" {{ ($settings['log_user_activity'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-primary">Log User Activity</label>
                                </div>

                                <div>
                                    <label class="form-label">Password Policy</label>
                                    <select name="password_policy" class="form-input">
                                        <option value="basic" {{ ($settings['password_policy'] ?? 'basic') === 'basic' ? 'selected' : '' }}>Basic (8+ characters)</option>
                                        <option value="medium" {{ ($settings['password_policy'] ?? 'basic') === 'medium' ? 'selected' : '' }}>Medium (8+ chars, mixed case)</option>
                                        <option value="strong" {{ ($settings['password_policy'] ?? 'basic') === 'strong' ? 'selected' : '' }}>Strong (8+ chars, mixed case, numbers, symbols)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div id="notifications" class="settings-content hidden">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-primary mb-4">Notifications</h3>
                            <div class="space-y-6">
                                <div class="flex items-center">
                                    <input type="checkbox" name="email_notifications" value="1" {{ ($settings['email_notifications'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-primary">Enable Email Notifications</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="sms_notifications" value="1" {{ ($settings['sms_notifications'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-primary">Enable SMS Notifications</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="checkin_notifications" value="1" {{ ($settings['checkin_notifications'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-primary">Notify on Guest Check-ins</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="system_alerts" value="1" {{ ($settings['system_alerts'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-primary">System Alert Notifications</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Integration Settings -->
                <div id="integrations" class="settings-content hidden">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-primary mb-4">Integrations</h3>
                            <div class="space-y-6">
                                <div>
                                    <label class="form-label">Google Sheets API Key</label>
                                    <input type="password" name="google_sheets_api_key" value="{{ $settings['google_sheets_api_key'] ?? '' }}" class="form-input">
                                    <p class="form-error">API key for Google Sheets integration.</p>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="enable_google_sheets" value="1" {{ ($settings['enable_google_sheets'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-primary">Enable Google Sheets Integration</label>
                                </div>

                                <div>
                                    <label class="form-label">Slack Webhook URL</label>
                                    <input type="url" name="slack_webhook_url" value="{{ $settings['slack_webhook_url'] ?? '' }}" class="form-input">
                                    <p class="form-error">Webhook URL for Slack notifications.</p>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="enable_slack" value="1" {{ ($settings['enable_slack'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-primary">Enable Slack Integration</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Settings -->
                <div id="advanced" class="settings-content hidden">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-primary mb-4">Advanced Settings</h3>
                            <div class="space-y-6">
                                <div>
                                    <label class="form-label">Cache TTL (seconds)</label>
                                    <input type="number" name="cache_ttl" value="{{ $settings['cache_ttl'] ?? 3600 }}" class="form-input" min="60" max="86400">
                                    <p class="form-error">Time to live for cached data in seconds.</p>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="debug_mode" value="1" {{ ($settings['debug_mode'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-primary">Enable Debug Mode</label>
                                </div>

                                <div>
                                    <label class="form-label">Log Level</label>
                                    <select name="log_level" class="form-input">
                                        <option value="error" {{ ($settings['log_level'] ?? 'error') === 'error' ? 'selected' : '' }}>Error</option>
                                        <option value="warning" {{ ($settings['log_level'] ?? 'error') === 'warning' ? 'selected' : '' }}>Warning</option>
                                        <option value="info" {{ ($settings['log_level'] ?? 'error') === 'info' ? 'selected' : '' }}>Info</option>
                                        <option value="debug" {{ ($settings['log_level'] ?? 'error') === 'debug' ? 'selected' : '' }}>Debug</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.settings-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.remove('active', 'text-blue-700', 'bg-blue-50');
        tab.classList.add('text-gray-700', 'hover:text-primary', 'hover:bg-gray-50');
    });
    
    // Show selected content
    document.getElementById(tabName).classList.remove('hidden');
    
    // Add active class to selected tab
    event.target.classList.add('active', 'text-blue-700', 'bg-blue-50');
    event.target.classList.remove('text-gray-700', 'hover:text-primary', 'hover:bg-gray-50');
}

function saveAllSettings() {
    // Collect all form data
    const formData = new FormData();
    
    // Add CSRF token
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('_method', 'PUT');
    
    // Collect all form inputs
    document.querySelectorAll('input, select, textarea').forEach(input => {
        if (input.name) {
            if (input.type === 'checkbox') {
                formData.append(input.name, input.checked ? '1' : '0');
            } else {
                formData.append(input.name, input.value);
            }
        }
    });
    
    // Submit form
    fetch('{{ route("admin.settings.update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }).then(response => {
        if (response.ok) {
            alert('Settings saved successfully!');
        } else {
            alert('Error saving settings. Please try again.');
        }
    });
}
</script>
@endpush 