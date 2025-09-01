@extends('layouts.organizer')

@section('title', 'My Events')

@section('content')

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-primary">My Events</h1>
            <p class="text-gray-600 mt-2">Manage your events and invitations</p>
        </div>
        <div class="flex space-x-3">
            <button class="btn-secondary" onclick="exportAllEvents()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export All
            </button>
            <a href="{{ route('organizer.events.create.new') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New Event
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--primary-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Events</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="totalEvents">{{ $activeEvents->count() + $completedEvents->count() }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--green-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--green-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Active Events</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="activeEvents">{{ $activeEvents->count() }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--yellow-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--yellow-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Completed Events</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="completedEvents">{{ $completedEvents->count() }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--purple-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--purple-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Guests</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="totalGuests">{{ $activeEvents->sum(function($event) { return $event->guestLists->sum(function($guestList) { return $guestList->guests->count(); }); }) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="rounded-lg shadow-sm border p-6 mb-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Search Events</label>
                <input type="text" id="searchInput" placeholder="Search by name..." class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="sent">Sent</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Date Range</label>
                <select id="dateFilter" class="form-select">
                    <option value="">All Dates</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="past">Past</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Sort By</label>
                <select id="sortFilter" class="form-select">
                    <option value="start_date">Date</option>
                    <option value="name">Name</option>
                    <option value="created_at">Created</option>
                    <option value="guests_count">Guests</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Draft Events Section -->
    @php
        $draftEvents = $activeEvents->where('status', 'draft');
        $otherActiveEvents = $activeEvents->where('status', '!=', 'draft');
    @endphp
    
    @if($draftEvents->count() > 0)
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-primary flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Draft Events ({{ $draftEvents->count() }})
                </h2>
                <p class="text-sm text-gray-600">
                    <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Click "Continue Editing" to resume your work
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($draftEvents as $event)
                    @include('organizer.events.partials.event-card', ['event' => $event, 'isDraft' => true])
                @endforeach
            </div>
        </div>
    @endif
    
    <!-- Other Active Events Section -->
    @if($otherActiveEvents->count() > 0)
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Active Events ({{ $otherActiveEvents->count() }})
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($otherActiveEvents as $event)
                    @include('organizer.events.partials.event-card', ['event' => $event, 'isDraft' => false])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Completed Events Section -->
    @if($completedEvents->count() > 0)
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-primary flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Completed Events ({{ $completedEvents->count() }})
                </h2>
                <a href="{{ route('organizer.events.completed') }}" class="text-sm text-primary hover:underline">
                    View All Completed Events →
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($completedEvents->take(6) as $event)
                    @include('organizer.events.partials.event-card', ['event' => $event, 'isCompleted' => true])
                @endforeach
            </div>
            @if($completedEvents->count() > 6)
                <div class="text-center mt-6">
                    <a href="{{ route('organizer.events.completed') }}" class="btn-secondary">
                        View All {{ $completedEvents->count() }} Completed Events
                    </a>
                </div>
            @endif
        </div>
    @endif
    
    @if($activeEvents->count() == 0 && $completedEvents->count() == 0)
        <div class="col-span-3 text-center py-12">
            <div class="text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-lg font-medium">No events found</p>
                <p class="text-sm">Create your first event to get started</p>
                <a href="{{ route('organizer.events.create') }}" class="inline-block mt-4 btn-primary">
                    Create Event
                </a>
            </div>
        </div>
    @endif
</div>

    <!-- Load More Button -->
    <div class="text-center mt-8" id="loadMoreContainer" style="display: none;">
        <button class="btn-secondary" onclick="loadMoreEvents()">
            Load More Events
        </button>
    </div>
</div>

<!-- Reusable Confirmation Modal -->
<x-confirmation-modal 
    id="deleteEventModal"
    title="Delete Event"
    message="You are about to delete the event"
    warningMessage="This action cannot be undone and will permanently remove all invitations and data associated with this event."
    confirmText="Delete Event"
    confirmClass="modal-btn-danger"
    :danger="true"
    :showWarningBox="true"
    warningBoxText="This will delete all invitations, guest list associations, and any data associated with this event."
/>

@endsection

@push('scripts')
<script>
let currentPage = 1;
let hasMorePages = true;

// Load events on page load
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
});

function setupEventListeners() {
    // Search input
    document.getElementById('searchInput').addEventListener('input', debounce(function() {
        currentPage = 1;
        loadEvents();
    }, 300));

    // Filters
    document.getElementById('statusFilter').addEventListener('change', function() {
        currentPage = 1;
        loadEvents();
    });

    document.getElementById('dateFilter').addEventListener('change', function() {
        currentPage = 1;
        loadEvents();
    });

    document.getElementById('sortFilter').addEventListener('change', function() {
        currentPage = 1;
        loadEvents();
    });
}

function loadEvents(append = false) {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const date = document.getElementById('dateFilter').value;
    const sort = document.getElementById('sortFilter').value;

    // For now, we'll just reload the page with filters
    // In a real implementation, you'd have an AJAX endpoint
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (date) params.append('date', date);
    if (sort) params.append('sort', sort);
    
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
}

function loadMoreEvents() {
    currentPage++;
    loadEvents(true);
}

function updateLoadMoreButton() {
    const container = document.getElementById('loadMoreContainer');
    container.style.display = hasMorePages ? 'block' : 'none';
}

function exportAllEvents() {
    // Implementation for exporting all events
    window.GuestManager.showNotification('Export functionality coming soon!', 'info');
}

// Delete functionality
function confirmDeleteEvent(eventId, eventName) {
    const deleteFunction = (id) => {
        // Show loading state
        const eventCard = document.querySelector(`[data-event-id="${id}"]`);
        if (eventCard) {
            eventCard.style.opacity = '0.6';
            eventCard.style.pointerEvents = 'none';
        }
        
        fetch(`/organizer/events/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success notification first
                window.GuestManager.showNotification('Event deleted successfully', 'success');
                
                // Remove the event card from the DOM for immediate visual feedback
                const eventCard = document.querySelector(`[data-event-id="${id}"]`);
                if (eventCard) {
                    eventCard.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                    eventCard.style.opacity = '0';
                    eventCard.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        eventCard.remove();
                        
                        // Update stats if they exist
                        updateEventStats();
                        
                        // Check if no events left and show empty state
                        const remainingEvents = document.querySelectorAll('[data-event-id]');
                        if (remainingEvents.length === 0) {
                            showEmptyState();
                        }
                    }, 300);
                } else {
                    // Fallback: reload the page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } else {
                // Restore card if deletion failed
                if (eventCard) {
                    eventCard.style.opacity = '1';
                    eventCard.style.pointerEvents = 'auto';
                }
                window.GuestManager.showNotification(data.message || 'Error deleting event', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Restore card if deletion failed
            if (eventCard) {
                eventCard.style.opacity = '1';
                eventCard.style.pointerEvents = 'auto';
            }
            window.GuestManager.showNotification('Error deleting event', 'error');
        });
    };
    
    confirmDelete(eventId, eventName, deleteFunction, 'deleteEventModal');
}

// Update event statistics after deletion
function updateEventStats() {
    const totalEventsElement = document.getElementById('totalEvents');
    const activeEventsElement = document.getElementById('activeEvents');
    const upcomingEventsElement = document.getElementById('upcomingEvents');
    const totalGuestsElement = document.getElementById('totalGuests');
    
    if (totalEventsElement) {
        const currentTotal = parseInt(totalEventsElement.textContent) || 0;
        totalEventsElement.textContent = Math.max(0, currentTotal - 1);
    }
    
    // Note: For more accurate stats, you'd need to recalculate based on the actual deleted event
    // This is a simplified version that just decrements the total
}

// Show empty state when all events are deleted
function showEmptyState() {
    const eventsGrid = document.getElementById('eventsGrid');
    if (eventsGrid) {
        eventsGrid.innerHTML = `
            <div class="col-span-3 text-center py-12">
                <div class="text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-lg font-medium">No events found</p>
                    <p class="text-sm">Create your first event to get started</p>
                    <a href="{{ route('organizer.events.create') }}" class="inline-block mt-4 btn-primary">
                        Create Event
                    </a>
                </div>
            </div>
        `;
    }
}

// Scanner URL generation
function generateScannerUrl(eventId) {
    // Show modal to create or manage scanners
    showScannerModal(eventId);
}

function showScannerModal(eventId) {
    // Create modal HTML
    const modalHtml = `
        <div id="scannerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Scanner URLs</h3>
                        <button onclick="closeScannerModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-4">Generate scanner URLs for your event. Each scanner profile can track check-ins separately.</p>
                        <div id="scannersList">
                            <div class="flex justify-center py-4">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            </div>
                        </div>
                    </div>
                    <div class="border-t pt-4">
                        <div class="flex items-center space-x-2 mb-3">
                            <input type="text" id="newScannerName" placeholder="Scanner name (e.g., Main Gate)" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <button onclick="createNewScanner(${eventId})" class="btn-primary px-4 py-2 text-sm">
                                Create
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Load existing scanners
    loadEventScanners(eventId);
}

function loadEventScanners(eventId) {
    fetch(`/organizer/events/${eventId}/scanners`)
        .then(response => response.json())
        .then(data => {
            const scannersList = document.getElementById('scannersList');
            if (data.scanners.length === 0) {
                scannersList.innerHTML = `
                    <div class="text-center text-gray-500 py-4">
                        <p class="text-sm">No scanners created yet</p>
                        <p class="text-xs">Create your first scanner to get started</p>
                    </div>
                `;
            } else {
                scannersList.innerHTML = data.scanners.map(scanner => `
                    <div class="flex items-center justify-between p-3 border rounded-lg mb-2">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">${scanner.name}</h4>
                            <p class="text-xs text-gray-500">Created ${scanner.created_at}</p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="copyScannerUrl('${scanner.url}')" 
                                    class="btn-secondary text-xs px-3 py-1">
                                Copy URL
                            </button>
                            <button onclick="shareScannerUrl('${scanner.url}', '${scanner.name}')" 
                                    class="btn-accent text-xs px-3 py-1">
                                Share
                            </button>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Error loading scanners:', error);
            document.getElementById('scannersList').innerHTML = `
                <div class="text-center text-red-500 py-4">
                    <p class="text-sm">Error loading scanners</p>
                </div>
            `;
        });
}

function createNewScanner(eventId) {
    const nameInput = document.getElementById('newScannerName');
    const name = nameInput.value.trim();
    
    if (!name) {
        alert('Please enter a scanner name');
        return;
    }
    
    fetch(`/organizer/events/${eventId}/scanners`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name: name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            nameInput.value = '';
            loadEventScanners(eventId);
        } else {
            alert(data.message || 'Error creating scanner');
        }
    })
    .catch(error => {
        console.error('Error creating scanner:', error);
        alert('Error creating scanner');
    });
}

function copyScannerUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        // Show success message
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.add('bg-green-600');
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('bg-green-600');
        }, 2000);
    }).catch(err => {
        console.error('Error copying URL:', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('URL copied to clipboard');
    });
}

function shareScannerUrl(url, scannerName) {
    if (navigator.share) {
        navigator.share({
            title: `Scanner: ${scannerName}`,
            text: `Use this link to access the event scanner: ${scannerName}`,
            url: url
        });
    } else {
        // Fallback: copy to clipboard and show message
        copyScannerUrl(url);
        alert(`Scanner URL copied! Share this link with the scanner operator for ${scannerName}`);
    }
}

function closeScannerModal() {
    const modal = document.getElementById('scannerModal');
    if (modal) {
        modal.remove();
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('scannerModal');
    if (modal && event.target === modal) {
        closeScannerModal();
    }
});

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
@endpush 