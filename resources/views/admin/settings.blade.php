@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-primary">System Settings</h1>
            <p class="text-gray-600 mt-2">Manage system configuration and user permissions</p>
        </div>

        <!-- Settings Tabs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button class="tab-button active" onclick="showTab('general')" data-tab="general">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        General
                    </button>
                    <button class="tab-button" onclick="showTab('users')" data-tab="users">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Users
                    </button>
                    <button class="tab-button" onclick="showTab('security')" data-tab="security">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Security
                    </button>
                    <button class="tab-button" onclick="showTab('notifications')" data-tab="notifications">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.19 4.19A2 2 0 004 6v10a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-1.81 1.19z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 9h2v2H7V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 9h2v2h-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h2v2h-2V9z"></path>
                        </svg>
                        Notifications
                    </button>
                    <button class="tab-button" onclick="showTab('urls')" data-tab="urls">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        URL Management
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- General Settings -->
                <div id="general-tab" class="tab-content active">
                    <form id="generalSettingsForm" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-primary mb-4">General Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Application Name</label>
                                    <input type="text" name="app_name" value="{{ config('app.name') }}" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Application URL</label>
                                    <input type="url" name="app_url" value="{{ config('app.url') }}" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Default Timezone</label>
                                    <select name="timezone" class="form-select">
                                        <option value="UTC" {{ config('app.timezone') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                        <option value="America/New_York" {{ config('app.timezone') === 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                        <option value="America/Chicago" {{ config('app.timezone') === 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                        <option value="America/Denver" {{ config('app.timezone') === 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                        <option value="America/Los_Angeles" {{ config('app.timezone') === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Default Language</label>
                                    <select name="locale" class="form-select">
                                        <option value="en" {{ config('app.locale') === 'en' ? 'selected' : '' }}>English</option>
                                        <option value="es" {{ config('app.locale') === 'es' ? 'selected' : '' }}>Spanish</option>
                                        <option value="fr" {{ config('app.locale') === 'fr' ? 'selected' : '' }}>French</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-primary mb-4">Guest List Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Maximum Guests per List</label>
                                    <input type="number" name="max_guests_per_list" value="1000" class="form-input" min="1" max="10000">
                                </div>
                                <div>
                                    <label class="form-label">Auto-archive After (Days)</label>
                                    <input type="number" name="auto_archive_days" value="30" class="form-input" min="1" max="365">
                                </div>
                                <div>
                                    <label class="form-label">Enable QR Code Generation</label>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="enable_qr_codes" class="form-checkbox" checked>
                                        <span class="ml-2 text-sm text-gray-700">Automatically generate QR codes for guests</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Enable Email Notifications</label>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="enable_email_notifications" class="form-checkbox" checked>
                                        <span class="ml-2 text-sm text-gray-700">Send email notifications for check-ins</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">Save General Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Users Management -->
                <div id="users-tab" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-primary">User Management</h3>
                        <button class="btn-primary" onclick="showAddUserModal()">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add User
                        </button>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="usersTable">
                                <!-- Will be populated via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Security Settings -->
                <div id="security-tab" class="tab-content hidden">
                    <form id="securitySettingsForm" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-primary mb-4">Authentication Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Session Timeout (Minutes)</label>
                                    <input type="number" name="session_timeout" value="120" class="form-input" min="15" max="1440">
                                </div>
                                <div>
                                    <label class="form-label">Password Minimum Length</label>
                                    <input type="number" name="password_min_length" value="8" class="form-input" min="6" max="20">
                                </div>
                                <div>
                                    <label class="form-label">Require Password Complexity</label>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="require_password_complexity" class="form-checkbox" checked>
                                        <span class="ml-2 text-sm text-gray-700">Require uppercase, lowercase, numbers, and symbols</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Enable Two-Factor Authentication</label>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="enable_2fa" class="form-checkbox">
                                        <span class="ml-2 text-sm text-gray-700">Allow users to enable 2FA</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-primary mb-4">Access Control</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Maximum Login Attempts</label>
                                    <input type="number" name="max_login_attempts" value="5" class="form-input" min="3" max="10">
                                </div>
                                <div>
                                    <label class="form-label">Lockout Duration (Minutes)</label>
                                    <input type="number" name="lockout_duration" value="15" class="form-input" min="5" max="60">
                                </div>
                                <div>
                                    <label class="form-label">Enable IP Whitelist</label>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="enable_ip_whitelist" class="form-checkbox">
                                        <span class="ml-2 text-sm text-gray-700">Restrict access to specific IP addresses</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Require HTTPS</label>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="require_https" class="form-checkbox" checked>
                                        <span class="ml-2 text-sm text-gray-700">Force HTTPS connections</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">Save Security Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Notification Settings -->
                <div id="notifications-tab" class="tab-content hidden">
                    <form id="notificationSettingsForm" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-primary mb-4">Email Notifications</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">SMTP Host</label>
                                    <input type="text" name="smtp_host" value="{{ config('mail.mailers.smtp.host') }}" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">SMTP Port</label>
                                    <input type="number" name="smtp_port" value="{{ config('mail.mailers.smtp.port') }}" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">SMTP Username</label>
                                    <input type="text" name="smtp_username" value="{{ config('mail.mailers.smtp.username') }}" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" name="smtp_password" class="form-input" placeholder="••••••••">
                                </div>
                                <div>
                                    <label class="form-label">From Email Address</label>
                                    <input type="email" name="from_email" value="{{ config('mail.from.address') }}" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">From Name</label>
                                    <input type="text" name="from_name" value="{{ config('mail.from.name') }}" class="form-input">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-primary mb-4">Notification Preferences</h3>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox" name="email_notifications" class="form-checkbox" checked>
                                    <span class="ml-2 text-sm text-gray-700">Enable email notifications</span>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="sms_notifications" class="form-checkbox">
                                    <span class="ml-2 text-sm text-gray-700">Enable SMS notifications</span>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="whatsapp_notifications" class="form-checkbox" checked>
                                    <span class="ml-2 text-sm text-gray-700">Enable WhatsApp notifications</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">Save Notification Settings</button>
                        </div>
                    </form>
                </div>

                <!-- URL Management -->
                <div id="urls-tab" class="tab-content hidden">
                    <div class="space-y-6">
                        <!-- Current Status -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-blue-800">Current Active URL</h4>
                                    <p class="text-sm text-blue-600" id="currentActiveUrl">Loading...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Switch Buttons -->
                        <div>
                            <h3 class="text-lg font-medium text-primary mb-4">Quick URL Switch</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <button onclick="switchToLocalhost()" class="btn-secondary flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                    </svg>
                                    Switch to Localhost
                                </button>
                                <button onclick="switchToNgrok()" class="btn-secondary flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                    </svg>
                                    Switch to ngrok
                                </button>
                            </div>
                        </div>

                        <!-- URL Configuration -->
                        <div>
                            <h3 class="text-lg font-medium text-primary mb-4">URL Configuration</h3>
                            <form id="urlSettingsForm" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="form-label">Localhost URL</label>
                                        <input type="url" value="http://localhost:8000" class="form-input" readonly>
                                        <p class="text-xs text-gray-500 mt-1">Fixed localhost URL for development</p>
                                    </div>
                                    <div>
                                        <label class="form-label">ngrok URL</label>
                                        <input type="url" name="ngrok_url" id="ngrokUrl" class="form-input" placeholder="https://your-ngrok-url.ngrok-free.app">
                                        <p class="text-xs text-gray-500 mt-1">Your current ngrok public URL</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Active URL Preference</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="radio" name="use_localhost" value="true" id="useLocalhost" class="form-radio">
                                            <label for="useLocalhost" class="ml-2 text-sm text-gray-700">Use localhost (for local development)</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" name="use_localhost" value="false" id="useNgrok" class="form-radio">
                                            <label for="useNgrok" class="ml-2 text-sm text-gray-700">Use ngrok (for external access)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="btn-primary">Save URL Settings</button>
                                </div>
                            </form>
                        </div>

                        <!-- URL Testing -->
                        <div>
                            <h3 class="text-lg font-medium text-primary mb-4">Test URLs</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-gray-800 mb-2">Localhost</h4>
                                    <p class="text-sm text-gray-600 mb-2">http://localhost:8000</p>
                                    <a href="http://localhost:8000" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">Open in new tab →</a>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-gray-800 mb-2">ngrok</h4>
                                    <p class="text-sm text-gray-600 mb-2" id="ngrokTestUrl">Loading...</p>
                                    <a href="#" id="ngrokTestLink" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">Open in new tab →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-primary mb-4">Add New User</h3>
            <form id="addUserForm" class="space-y-4">
                <div>
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="organizer">Organizer</option>
                        <option value="scanner">Scanner</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideAddUserModal()" class="btn-secondary">Cancel</button>
                    <button type="button" onclick="addUser()" class="btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    setupEventListeners();
    loadUrlSettings(); // Load URL settings when the page loads
});

function setupEventListeners() {
    // Form submissions
    document.getElementById('generalSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveGeneralSettings();
    });

    document.getElementById('securitySettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveSecuritySettings();
    });

    document.getElementById('notificationSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveNotificationSettings();
    });

    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        addUser();
    });
}

function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('active');
    });

    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });

    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    document.getElementById(tabName + '-tab').classList.add('active');

    // Add active class to selected tab button
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
}

function loadUsers() {
    fetch('/admin/users')
        .then(response => response.json())
        .then(data => {
            renderUsers(data);
        })
        .catch(error => {
            console.error('Error loading users:', error);
            showNotification('Error loading users', 'error');
        });
}

function renderUsers(users) {
    const tbody = document.getElementById('usersTable');
    tbody.innerHTML = '';

    users.forEach(user => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-700">
                                ${user.first_name.charAt(0)}${user.last_name.charAt(0)}
                            </span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-primary">${user.first_name} ${user.last_name}</div>
                        <div class="text-sm text-gray-500">${user.email}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="badge badge-${getRoleColor(user.role)}">${user.role}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="badge badge-${user.is_active ? 'success' : 'danger'}">
                    ${user.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${user.last_login_at ? new Date(user.last_login_at).toLocaleDateString() : 'Never'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900" onclick="editUser(${user.id})">Edit</button>
                    <button class="text-red-600 hover:text-red-900" onclick="deleteUser(${user.id})">Delete</button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function getRoleColor(role) {
    switch (role) {
        case 'admin': return 'danger';
        case 'organizer': return 'primary';
        case 'scanner': return 'warning';
        default: return 'secondary';
    }
}

function showAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
}

function hideAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
    document.getElementById('addUserForm').reset();
}

function addUser() {
    const formData = new FormData(document.getElementById('addUserForm'));
    
    fetch('/admin/users', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideAddUserModal();
            loadUsers();
            showNotification('User added successfully', 'success');
        } else {
            showNotification(data.message || 'Error adding user', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding user', 'error');
    });
}

function editUser(userId) {
    // Redirect to edit page or show edit modal
    window.location.href = `/admin/users/${userId}/edit`;
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadUsers();
                showNotification('User deleted successfully', 'success');
            } else {
                showNotification(data.message || 'Error deleting user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting user', 'error');
        });
    }
}

function saveGeneralSettings() {
    const formData = new FormData(document.getElementById('generalSettingsForm'));
    
    fetch('/admin/settings/general', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('General settings saved successfully', 'success');
        } else {
            showNotification(data.message || 'Error saving settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving settings', 'error');
    });
}

function saveSecuritySettings() {
    const formData = new FormData(document.getElementById('securitySettingsForm'));
    
    fetch('/admin/settings/security', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Security settings saved successfully', 'success');
        } else {
            showNotification(data.message || 'Error saving settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving settings', 'error');
    });
}

function saveNotificationSettings() {
    const formData = new FormData(document.getElementById('notificationSettingsForm'));
    
    fetch('/admin/settings/notifications', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Notification settings saved successfully', 'success');
        } else {
            showNotification(data.message || 'Error saving settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving settings', 'error');
    });
}

// URL Management Functions
function loadUrlSettings() {
    fetch('/admin/url-settings')
        .then(response => response.json())
        .then(data => {
            document.getElementById('currentActiveUrl').textContent = data.current_active_url;
            document.getElementById('ngrokUrl').value = data.ngrok_url || '';
            document.getElementById('ngrokTestUrl').textContent = data.app_url || 'Not configured';
            document.getElementById('ngrokTestLink').href = data.app_url || '#';
            
            if (data.use_localhost) {
                document.getElementById('useLocalhost').checked = true;
            } else {
                document.getElementById('useNgrok').checked = true;
            }
        })
        .catch(error => {
            console.error('Error loading URL settings:', error);
            showNotification('Error loading URL settings', 'error');
        });
}

function switchToLocalhost() {
    fetch('/admin/switch-to-localhost', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('currentActiveUrl').textContent = data.active_url;
            document.getElementById('useLocalhost').checked = true;
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Error switching to localhost', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error switching to localhost', 'error');
    });
}

function switchToNgrok() {
    fetch('/admin/switch-to-ngrok', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('currentActiveUrl').textContent = data.active_url;
            document.getElementById('useNgrok').checked = true;
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Error switching to ngrok', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error switching to ngrok', 'error');
    });
}

function saveUrlSettings() {
    const formData = new FormData(document.getElementById('urlSettingsForm'));
    
    fetch('/admin/url-settings', {
        method: 'PUT',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('currentActiveUrl').textContent = data.current_active_url;
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Error saving URL settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving URL settings', 'error');
    });
}

// Initialize URL settings when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Load URL settings when URL tab is shown
    const urlTabButton = document.querySelector('[data-tab="urls"]');
    if (urlTabButton) {
        urlTabButton.addEventListener('click', function() {
            setTimeout(loadUrlSettings, 100);
        });
    }
    
    // Handle URL settings form submission
    const urlSettingsForm = document.getElementById('urlSettingsForm');
    if (urlSettingsForm) {
        urlSettingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveUrlSettings();
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.tab-button {
    @apply py-2 px-1 border-b-2 font-medium text-sm;
    @apply text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300;
}

.tab-button.active {
    @apply text-blue-600 border-blue-500;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}
</style>
@endpush 