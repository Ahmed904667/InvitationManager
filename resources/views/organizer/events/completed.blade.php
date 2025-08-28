@extends('layouts.organizer')

@section('title', 'Completed Events')

@section('content')

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-primary">Completed Events</h1>
            <p class="text-gray-600 mt-2">View your past events and their results</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('organizer.events.index') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Events
            </a>
            <button class="btn-secondary" onclick="exportCompletedEvents()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export All
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--gray-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--gray-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Completed</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $completedEvents->total() }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--green-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--green-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Guests</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $completedEvents->sum('active_guests_count') }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--blue-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--blue-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Avg. Attendance</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $completedEvents->count() > 0 ? round($completedEvents->sum(function($event) { return $event->invitations->where('rsvp_status', 'yes')->count(); }) / $completedEvents->count(), 1) : 0 }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--purple-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--purple-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Response Rate</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $completedEvents->count() > 0 ? round(($completedEvents->sum(function($event) { return $event->invitations->whereIn('rsvp_status', ['yes', 'no', 'maybe'])->count(); }) / $completedEvents->sum(function($event) { return $event->invitations->count(); })) * 100, 1) : 0 }}%</p>
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
                <label class="block text-sm font-medium text-primary mb-2">Date Range</label>
                <select id="dateFilter" class="form-select">
                    <option value="">All Dates</option>
                    <option value="week">Last Week</option>
                    <option value="month">Last Month</option>
                    <option value="quarter">Last 3 Months</option>
                    <option value="year">Last Year</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Sort By</label>
                <select id="sortFilter" class="form-select">
                    <option value="start_date">Event Date</option>
                    <option value="name">Name</option>
                    <option value="guests_count">Guests</option>
                    <option value="rsvp_rate">RSVP Rate</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Results Per Page</label>
                <select id="perPageFilter" class="form-select">
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Completed Events List -->
    @if($completedEvents->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($completedEvents as $event)
                @include('organizer.events.partials.event-card', ['event' => $event, 'isCompleted' => true])
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $completedEvents->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-lg font-medium">No completed events found</p>
                <p class="text-sm">Events will appear here once they are completed</p>
                <a href="{{ route('organizer.events.index') }}" class="inline-block mt-4 btn-primary">
                    View Active Events
                </a>
            </div>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function exportCompletedEvents() {
    // Implementation for exporting completed events
    alert('Export functionality will be implemented here');
}

// Add any additional JavaScript for filtering and searching
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search and filter functionality
    const searchInput = document.getElementById('searchInput');
    const dateFilter = document.getElementById('dateFilter');
    const sortFilter = document.getElementById('sortFilter');
    const perPageFilter = document.getElementById('perPageFilter');

    // Add event listeners for filtering
    [searchInput, dateFilter, sortFilter, perPageFilter].forEach(element => {
        if (element) {
            element.addEventListener('change', function() {
                // Implement filter logic here
                console.log('Filter changed:', this.value);
            });
        }
    });
});
</script>
@endpush

