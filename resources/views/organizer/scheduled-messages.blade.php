@extends('layouts.organizer')

@section('title', 'Scheduled Messages')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Scheduled Messages</h1>
        <p class="text-gray-600">View and manage all your scheduled event invitations and reminder messages.</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" id="summary-cards">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Scheduled Events</p>
                    <p class="text-2xl font-semibold text-gray-900" id="scheduled-events-count">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending Reminders</p>
                    <p class="text-2xl font-semibold text-gray-900" id="pending-reminders-count">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Next Event</p>
                    <p class="text-sm font-semibold text-gray-900" id="next-event-time">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Next Reminder</p>
                    <p class="text-sm font-semibold text-gray-900" id="next-reminder-time">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <nav class="flex space-x-8" aria-label="Tabs">
            <button class="tab-button active px-3 py-2 font-medium text-sm rounded-md" data-tab="scheduled-events">
                Scheduled Events
            </button>
            <button class="tab-button px-3 py-2 font-medium text-sm rounded-md" data-tab="scheduled-reminders">
                Pending Reminders
            </button>
            <button class="tab-button px-3 py-2 font-medium text-sm rounded-md" data-tab="past-reminders">
                Past Reminders
            </button>
        </nav>
    </div>

    <!-- Content Areas -->
    <div id="scheduled-events" class="tab-content active">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Scheduled Event Invitations</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled For</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platforms</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="scheduled-events-table">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="scheduled-reminders" class="tab-content hidden">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Pending Reminder Messages</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled For</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="scheduled-reminders-table">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="past-reminders" class="tab-content hidden">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Past Reminder Messages</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled For</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="past-reminders-table">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load scheduled messages data
    loadScheduledMessages();
    
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active', 'bg-gray-100', 'text-gray-900'));
            tabContents.forEach(content => content.classList.add('hidden'));
            
            // Add active class to clicked button and show corresponding content
            button.classList.add('active', 'bg-gray-100', 'text-gray-900');
            document.getElementById(tabName).classList.remove('hidden');
        });
    });
});

function loadScheduledMessages() {
    fetch('{{ route("organizer.scheduled-messages.index") }}')
        .then(response => response.json())
        .then(data => {
            updateSummaryCards(data.summary);
            updateScheduledEventsTable(data.scheduled_events);
            updateScheduledRemindersTable(data.scheduled_reminders);
            updatePastRemindersTable(data.past_reminders);
        })
        .catch(error => {
            console.error('Error loading scheduled messages:', error);
        });
}

function updateSummaryCards(summary) {
    document.getElementById('scheduled-events-count').textContent = summary.total_scheduled_events;
    document.getElementById('pending-reminders-count').textContent = summary.total_scheduled_reminders;
    
    if (summary.next_scheduled_event) {
        document.getElementById('next-event-time').textContent = formatDateTime(summary.next_scheduled_event);
    }
    
    if (summary.next_scheduled_reminder) {
        document.getElementById('next-reminder-time').textContent = formatDateTime(summary.next_scheduled_reminder);
    }
}

function updateScheduledEventsTable(events) {
    const tbody = document.getElementById('scheduled-events-table');
    
    if (events.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No scheduled events found</td></tr>';
        return;
    }
    
    tbody.innerHTML = events.map(event => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${event.name}</div>
                <div class="text-sm text-gray-500">${event.description || 'No description'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${formatDateTime(event.scheduled_at)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${formatDateTime(event.event_date)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${event.guest_count} guests
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${event.platforms.map(platform => 
                    `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">${platform}</span>`
                ).join('')}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <a href="/organizer/events/${event.id}" class="text-indigo-600 hover:text-indigo-900">View Event</a>
            </td>
        </tr>
    `).join('');
}

function updateScheduledRemindersTable(reminders) {
    const tbody = document.getElementById('scheduled-reminders-table');
    
    if (reminders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No pending reminders found</td></tr>';
        return;
    }
    
    tbody.innerHTML = reminders.map(reminder => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${reminder.event_name}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${reminder.guest_name}</div>
                <div class="text-sm text-gray-500">${reminder.guest_email || reminder.guest_phone || 'No contact info'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                    reminder.platform === 'email' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'
                }">${reminder.platform}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${formatDateTime(reminder.scheduled_for)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${formatDateTime(reminder.event_date)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <span class="text-yellow-600">Pending</span>
            </td>
        </tr>
    `).join('');
}

function updatePastRemindersTable(reminders) {
    const tbody = document.getElementById('past-reminders-table');
    
    if (reminders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No past reminders found</td></tr>';
        return;
    }
    
    tbody.innerHTML = reminders.map(reminder => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${reminder.event_name}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${reminder.guest_name}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                    reminder.platform === 'email' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'
                }">${reminder.platform}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${formatDateTime(reminder.scheduled_for)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${reminder.sent_at ? formatDateTime(reminder.sent_at) : '-'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                    reminder.status === 'sent' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }">${reminder.status}</span>
            </td>
        </tr>
    `).join('');
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return '-';
    
    const date = new Date(dateTimeString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<style>
.tab-button.active {
    background-color: rgb(243 244 246);
    color: rgb(17 24 39);
}

.tab-button {
    color: rgb(107 114 128);
}

.tab-button:hover {
    color: rgb(17 24 39);
}
</style>
@endsection
