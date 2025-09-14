@extends('layouts.organizer')

@section('title', "Notification Status - {$event->name}")

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Notification Status</h1>
                <p class="text-gray-600">{{ $event->name }}</p>
                <a href="{{ route('organizer.events.show', $event) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    ← Back to Event
                </a>
            </div>
            <div class="flex space-x-3">
                <button type="button" id="refreshBtn" class="btn-secondary flex items-center" onclick="refreshNotificationStatuses()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span id="refreshBtnText">Refresh Status</span>
                </button>

            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-xl font-bold text-orange-600" id="queued-count">{{ $stats['queued'] }}</div>
            <div class="text-xs text-gray-600">Queued</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-xl font-bold text-green-600" id="delivered-count">{{ $stats['delivered'] }}</div>
            <div class="text-xs text-gray-600">Delivered</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-xl font-bold text-emerald-600" id="read-count">{{ $stats['read'] }}</div>
            <div class="text-xs text-gray-600">Read</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-xl font-bold text-red-600" id="failed-count">{{ $stats['failed'] }}</div>
            <div class="text-xs text-gray-600">Failed</div>
        </div>
    </div>

    <!-- Notifications by Channel -->
    <div id="notifications-container">
        @foreach($notifications as $channel => $channelNotifications)
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ ucfirst($channel) }} Notifications 
                    <span class="text-sm font-normal text-gray-500">({{ $channelNotifications->count() }})</span>
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($channelNotifications as $notification)
                        <tr data-notification-id="{{ $notification->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $notification->guest->name }}</div>
                                <div class="text-sm text-gray-500">
                                    @if($notification->channel === 'email')
                                        {{ $notification->guest->email }}
                                    @else
                                        {{ $notification->guest->phone }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="notification-status inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" data-status="{{ $notification->status }}">
                                    @switch($notification->status)
                                        @case('delivered')
                                        @case('sent')
                                            <span class="bg-green-100 text-green-800">Delivered</span>
                                            @break
                                        @case('read')
                                            <span class="bg-emerald-100 text-emerald-800">Read</span>
                                            @break
                                        @case('failed')
                                        @case('undelivered')
                                        @case('canceled')
                                        @case('bounced')
                                            <span class="bg-red-100 text-red-800">Failed</span>
                                            @break
                                        @default
                                            <span class="bg-orange-100 text-orange-800">Queued</span>
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $notification->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($notification->sent_at)
                                    {{ $notification->sent_at->setTimezone($userTimezone)->format('M j, Y g:i A') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($notification->external_id)
                                    <div class="text-xs">
                                        <strong>ID:</strong> {{ $notification->external_id }}
                                    </div>
                                @endif
                                @if($notification->error_message)
                                    <div class="text-xs text-red-600 mt-1">
                                        <strong>Error:</strong> {{ $notification->error_message }}
                                    </div>
                                @endif
                                @if($notification->delivery_details)
                                    <div class="text-xs text-gray-600 mt-1">
                                        <details>
                                            <summary class="cursor-pointer hover:text-gray-800">View Details</summary>
                                            <pre class="mt-2 text-xs bg-gray-100 p-2 rounded">{{ json_encode($notification->delivery_details, JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

        @if($notifications->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-gray-500 text-lg mb-2">No notifications found</div>
            <div class="text-gray-400 text-sm">Notifications will appear here when you send messages to guests</div>
        </div>
        @endif
    </div>
</div>

<script>
function refreshNotificationStatuses() {
    const refreshBtn = document.getElementById('refreshBtn');
    const refreshBtnText = document.getElementById('refreshBtnText');
    const originalText = refreshBtnText.textContent;
    
    // Disable button and show loading state
    refreshBtn.disabled = true;
    refreshBtnText.textContent = 'Refreshing...';
    
    fetch(`{{ route('organizer.events.notifications.refresh', $event) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update statistics
            updateNotificationStats();
            // Update individual notification statuses
            updateIndividualNotificationStatuses();
            // Show success message
            showNotification(data.message, 'success');
        } else {
            showNotification('Failed to refresh notifications: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error refreshing notifications:', error);
        showNotification('Error refreshing notifications', 'error');
    })
    .finally(() => {
        // Re-enable button and restore text
        refreshBtn.disabled = false;
        refreshBtnText.textContent = originalText;
    });
}

function updateNotificationStats() {
    fetch(`{{ route('organizer.events.notifications.stats', $event) }}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('queued-count').textContent = data.stats.queued;
            document.getElementById('delivered-count').textContent = data.stats.delivered;
            document.getElementById('read-count').textContent = data.stats.read;
            document.getElementById('failed-count').textContent = data.stats.failed;
        }
    })
    .catch(error => {
        console.error('Error updating stats:', error);
    });
}

function updateIndividualNotificationStatuses() {
    fetch(`{{ route('organizer.events.notifications.list', $event) }}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.notifications.forEach(notification => {
                const row = document.querySelector(`tr[data-notification-id="${notification.id}"]`);
                if (row) {
                    const statusCell = row.querySelector('.notification-status');
                    if (statusCell) {
                        statusCell.innerHTML = createStatusBadge(notification.status);
                    }
                }
            });
        }
    })
    .catch(error => {
        console.error('Error updating notification statuses:', error);
    });
}

function createStatusBadge(status) {
    const statusConfig = {
        'delivered': { bg: 'bg-green-100', text: 'text-green-800', label: 'Delivered' },
        'sent': { bg: 'bg-green-100', text: 'text-green-800', label: 'Delivered' },
        'read': { bg: 'bg-emerald-100', text: 'text-emerald-800', label: 'Read' },
        'failed': { bg: 'bg-red-100', text: 'text-red-800', label: 'Failed' },
        'undelivered': { bg: 'bg-red-100', text: 'text-red-800', label: 'Failed' },
        'canceled': { bg: 'bg-red-100', text: 'text-red-800', label: 'Failed' },
        'bounced': { bg: 'bg-red-100', text: 'text-red-800', label: 'Failed' },
        'default': { bg: 'bg-orange-100', text: 'text-orange-800', label: 'Queued' }
    };
    
    const config = statusConfig[status] || statusConfig.default;
    return `<span class="${config.bg} ${config.text}">${config.label}</span>`;
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
    
    // Set colors based on type
    switch(type) {
        case 'success':
            notification.className += ' bg-green-500 text-white';
            break;
        case 'error':
            notification.className += ' bg-red-500 text-white';
            break;
        case 'warning':
            notification.className += ' bg-yellow-500 text-white';
            break;
        default:
            notification.className += ' bg-blue-500 text-white';
    }
    
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
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
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
</script>
@endsection


