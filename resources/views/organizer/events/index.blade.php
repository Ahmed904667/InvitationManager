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
            <a href="{{ route('organizer.events.create.new') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New Event
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--primary-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Events</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="totalEvents">{{ $activeEvents->count() + $cancelledEvents->count() + $completedEvents->count() }}</p>
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
                <div class="p-2 rounded-lg" style="background: var(--red-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--red-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Cancelled Events</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="cancelledEvents">{{ $cancelledEvents->count() }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--purple-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--purple-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Active Guests</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="totalGuests">{{ $activeEvents->sum('active_guests_count') + $cancelledEvents->sum('active_guests_count') + $completedEvents->sum('active_guests_count') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="rounded-lg shadow-sm border p-6 mb-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium" style="color: var(--text-primary);">Search & Filter Events</h3>
            <button id="clearFiltersBtn" class="btn-secondary text-sm" onclick="clearFilters()">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Clear Filters
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Search Events</label>
                <input type="text" id="searchInput" placeholder="Search by name, description..." class="form-input">
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
        <div id="filterResults" class="mt-4 text-sm" style="color: var(--text-secondary); display: none;">
            <span id="filterResultsText"></span>
        </div>
    </div>

    <!-- Draft Events Section -->
    @php
        $draftEvents = $activeEvents->where('status', 'draft');
        $otherActiveEvents = $activeEvents->where('status', '!=', 'draft');
    @endphp
    
    @if($draftEvents->count() > 0)
        <div class="mb-8" id="draft-events-section">
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
        <div class="mb-8" id="active-events-section">
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

    <!-- Cancelled Events Section -->
    @if($cancelledEvents->count() > 0)
        <div class="mb-8" id="cancelled-events-section">
            <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancelled Events ({{ $cancelledEvents->count() }})
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($cancelledEvents as $event)
                    @include('organizer.events.partials.event-card', ['event' => $event, 'isDraft' => false])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Completed Events Section -->
    @if($completedEvents->count() > 0)
        <div class="mb-8" id="completed-events-section">
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
        <div id="noEventsMessage" class="col-span-3 text-center py-12">
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

    <!-- No events found after filtering -->
    <div id="noFilteredEventsMessage" class="col-span-3 text-center py-12" style="display: none;">
        <div class="text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <p class="text-lg font-medium">No events match your search criteria</p>
            <p class="text-sm">Try adjusting your filters or search terms</p>
            <button onclick="clearFilters()" class="inline-block mt-4 btn-secondary">
                Clear Filters
            </button>
        </div>
    </div>
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
let allEvents = [];
let filteredEvents = [];

// Load events on page load
document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('Initializing events page...');
        initializeEvents();
        setupEventListeners();
        applyFilters();
        console.log('Events page initialized successfully');
    } catch (error) {
        console.error('Error initializing events page:', error);
        // Fallback: show all events if filtering fails
        const allEventCards = document.querySelectorAll('[data-event-id]');
        allEventCards.forEach(card => {
            card.style.display = 'block';
        });
    }
});

function initializeEvents() {
    // Collect all event cards from the page
    const eventCards = document.querySelectorAll('[data-event-id]');
    allEvents = Array.from(eventCards).map(card => {
        const eventId = card.getAttribute('data-event-id');
        const eventName = card.querySelector('h3').textContent.trim();
        const eventStatus = card.querySelector('.badge').textContent.trim().toLowerCase();
        const eventDate = card.querySelector('.flex.items-center.text-sm.text-gray-600 svg + *')?.textContent?.trim() || '';
        const eventTime = card.querySelector('.flex.items-center.text-sm.text-gray-600.mt-1 svg + *')?.textContent?.trim() || '';
        const eventDescription = card.querySelector('p.text-sm.text-gray-500')?.textContent?.trim() || '';
        
        return {
            id: eventId,
            element: card,
            name: eventName,
            status: eventStatus,
            date: eventDate,
            time: eventTime,
            description: eventDescription,
            fullText: `${eventName} ${eventDescription} ${eventDate} ${eventTime}`.toLowerCase()
        };
    });
    
    filteredEvents = [...allEvents];
}

function setupEventListeners() {
    try {
        // Search input
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', debounce(function() {
                applyFilters();
            }, 300));
        }

        // Filters
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                applyFilters();
            });
        }

        const dateFilter = document.getElementById('dateFilter');
        if (dateFilter) {
            dateFilter.addEventListener('change', function() {
                applyFilters();
            });
        }

        const sortFilter = document.getElementById('sortFilter');
        if (sortFilter) {
            sortFilter.addEventListener('change', function() {
                applyFilters();
            });
        }
    } catch (error) {
        console.error('Error setting up event listeners:', error);
    }
}

function applyFilters() {
    try {
        const searchTerm = document.getElementById('searchInput')?.value?.toLowerCase() || '';
        const statusFilter = document.getElementById('statusFilter')?.value?.toLowerCase() || '';
        const dateFilter = document.getElementById('dateFilter')?.value || '';
        const sortBy = document.getElementById('sortFilter')?.value || 'start_date';

    // Filter events
    filteredEvents = allEvents.filter(event => {
        // Search filter
        const matchesSearch = !searchTerm || event.fullText.includes(searchTerm);
        
        // Status filter
        const matchesStatus = !statusFilter || event.status === statusFilter;
        
        // Date filter
        const matchesDate = !dateFilter || matchesDateFilter(event, dateFilter);
        
        return matchesSearch && matchesStatus && matchesDate;
    });

    // Sort events
    sortEvents(filteredEvents, sortBy);

        // Update display
        updateEventDisplay();
        updateEventCounts();
    } catch (error) {
        console.error('Error applying filters:', error);
    }
}

function matchesDateFilter(event, dateFilter) {
    if (!event.date) return false;
    
    const now = new Date();
    const eventDate = new Date(event.date);
    
    switch (dateFilter) {
        case 'today':
            return eventDate.toDateString() === now.toDateString();
        case 'week':
            const weekFromNow = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
            return eventDate >= now && eventDate <= weekFromNow;
        case 'month':
            const monthFromNow = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000);
            return eventDate >= now && eventDate <= monthFromNow;
        case 'upcoming':
            return eventDate >= now;
        case 'past':
            return eventDate < now;
        default:
            return true;
    }
}

function sortEvents(events, sortBy) {
    events.sort((a, b) => {
        switch (sortBy) {
            case 'name':
                return a.name.localeCompare(b.name);
            case 'created_at':
                // For now, we'll use the order they appear on the page
                return 0;
            case 'guests_count':
                // We'd need to extract guest count from the DOM
                return 0;
            case 'start_date':
            default:
                // Sort by date (assuming events are already in date order)
                return 0;
        }
    });
}

function updateEventDisplay() {
    // Hide all events first
    allEvents.forEach(event => {
        event.element.style.display = 'none';
    });
    
    // Show filtered events
    filteredEvents.forEach(event => {
        event.element.style.display = 'block';
    });
    
    // Update section visibility
    updateSectionVisibility();
    
    // Show/hide "no filtered events" message
    const noFilteredEventsMessage = document.getElementById('noFilteredEventsMessage');
    const noEventsMessage = document.getElementById('noEventsMessage');
    
    if (filteredEvents.length === 0 && allEvents.length > 0) {
        // Show "no filtered events" message when filters return no results
        if (noFilteredEventsMessage) noFilteredEventsMessage.style.display = 'block';
        if (noEventsMessage) noEventsMessage.style.display = 'none';
    } else {
        // Hide "no filtered events" message
        if (noFilteredEventsMessage) noFilteredEventsMessage.style.display = 'none';
        // Show original "no events" message only if there are truly no events
        if (noEventsMessage) noEventsMessage.style.display = allEvents.length === 0 ? 'block' : 'none';
    }
}

function updateSectionVisibility() {
    // Find sections by their unique IDs
    const sections = [
        { id: 'draft-events-section', type: 'draft' },
        { id: 'active-events-section', type: 'active' },
        { id: 'cancelled-events-section', type: 'cancelled' },
        { id: 'completed-events-section', type: 'completed' }
    ];
    
    sections.forEach(section => {
        const sectionElement = document.getElementById(section.id);
        if (sectionElement) {
            const visibleEvents = sectionElement.querySelectorAll('[data-event-id]');
            const hasVisibleEvents = Array.from(visibleEvents).some(event => 
                event.style.display !== 'none'
            );
            
            sectionElement.style.display = hasVisibleEvents ? 'block' : 'none';
        }
    });
}

function updateEventCounts() {
    const totalEvents = filteredEvents.length;
    const activeEvents = filteredEvents.filter(e => e.status !== 'draft' && e.status !== 'completed' && e.status !== 'cancelled').length;
    const completedEvents = filteredEvents.filter(e => e.status === 'completed').length;
    const cancelledEvents = filteredEvents.filter(e => e.status === 'cancelled').length;
    const draftEvents = filteredEvents.filter(e => e.status === 'draft').length;
    
    // Update the stats cards
    const totalElement = document.getElementById('totalEvents');
    const activeElement = document.getElementById('activeEvents');
    const completedElement = document.getElementById('completedEvents');
    const cancelledElement = document.getElementById('cancelledEvents');
    
    if (totalElement) totalElement.textContent = totalEvents;
    if (activeElement) activeElement.textContent = activeEvents;
    if (completedElement) completedElement.textContent = completedEvents;
    if (cancelledElement) cancelledElement.textContent = cancelledEvents;
    
    // Update filter results text
    updateFilterResultsText();
}

function updateFilterResultsText() {
    const filterResults = document.getElementById('filterResults');
    const filterResultsText = document.getElementById('filterResultsText');
    const searchTerm = document.getElementById('searchInput').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    
    let resultText = '';
    const totalEvents = allEvents.length;
    const filteredCount = filteredEvents.length;
    
    if (filteredCount === 0) {
        resultText = `No events found matching your criteria.`;
        filterResults.style.color = 'var(--red-600)';
    } else if (filteredCount < totalEvents) {
        resultText = `Showing ${filteredCount} of ${totalEvents} events`;
        
        const filters = [];
        if (searchTerm) filters.push(`"${searchTerm}"`);
        if (statusFilter) filters.push(`status: ${statusFilter}`);
        if (dateFilter) filters.push(`date: ${dateFilter}`);
        
        if (filters.length > 0) {
            resultText += ` (filtered by ${filters.join(', ')})`;
        }
        
        filterResults.style.color = 'var(--text-secondary)';
    } else {
        resultText = `Showing all ${totalEvents} events`;
        filterResults.style.color = 'var(--text-secondary)';
    }
    
    filterResultsText.textContent = resultText;
    filterResults.style.display = filteredCount < totalEvents || filteredCount === 0 ? 'block' : 'none';
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateFilter').value = '';
    document.getElementById('sortFilter').value = 'start_date';
    
    applyFilters();
    
    // Show success message
    if (window.GuestManager?.showNotification) {
        window.GuestManager.showNotification('Filters cleared', 'success');
    }
}

function loadMoreEvents() {
    // This function is kept for compatibility but not needed with client-side filtering
    console.log('Load more events - not implemented for client-side filtering');
}

function updateLoadMoreButton() {
    // Hide load more button since we're doing client-side filtering
    const container = document.getElementById('loadMoreContainer');
    if (container) {
        container.style.display = 'none';
    }
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

// Cancel Event functionality
function showCancelEventModal(eventId, eventName) {
    const modalHtml = `
        <div id="cancelEventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Cancel Event</h3>
                        <button onclick="closeCancelEventModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="mb-4">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4 bg-yellow-100">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium mb-2 text-gray-900">Cancel Event</h3>
                        <p class="text-sm mb-4 text-gray-600">
                            Are you sure you want to cancel the event <span class="font-medium">"${eventName}"</span>?
                        </p>
                        <div class="rounded-md p-4 bg-yellow-50 border border-yellow-200 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>This will cancel the event, send an apology message to all guests, and remove all scheduled messages.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="apologyMessage" class="block text-sm font-medium text-gray-700 mb-2">
                                Apology Message for Guests
                            </label>
                            <textarea id="apologyMessage" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your apology message to send to all guests...">We sincerely apologize, but we need to cancel this event. We will notify you of any future events. Thank you for your understanding.</textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button onclick="closeCancelEventModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button onclick="confirmCancelEvent(${eventId})" class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            Cancel Event
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeCancelEventModal() {
    const modal = document.getElementById('cancelEventModal');
    if (modal) {
        modal.remove();
    }
}

function confirmCancelEvent(eventId) {
    const apologyMessage = document.getElementById('apologyMessage').value.trim();
    
    if (!apologyMessage) {
        window.GuestManager.showNotification('Please enter an apology message', 'error');
        return;
    }
    
    // Show loading state
    const eventCard = document.querySelector(`[data-event-id="${eventId}"]`);
    if (eventCard) {
        eventCard.style.opacity = '0.6';
        eventCard.style.pointerEvents = 'none';
    }
    
    fetch(`/organizer/events/${eventId}/cancel`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            apology_message: apologyMessage
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success notification
            window.GuestManager.showNotification('Event cancelled successfully', 'success');
            
            // Close modal
            closeCancelEventModal();
            
            // Remove the event card from the DOM for immediate visual feedback
            const eventCard = document.querySelector(`[data-event-id="${eventId}"]`);
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
            // Restore card if cancellation failed
            if (eventCard) {
                eventCard.style.opacity = '1';
                eventCard.style.pointerEvents = 'auto';
            }
            window.GuestManager.showNotification(data.message || 'Error cancelling event', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Restore card if cancellation failed
        if (eventCard) {
            eventCard.style.opacity = '1';
            eventCard.style.pointerEvents = 'auto';
        }
        window.GuestManager.showNotification('Error cancelling event', 'error');
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('cancelEventModal');
    if (modal && event.target === modal) {
        closeCancelEventModal();
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