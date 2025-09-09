@extends('layouts.organizer')

@section('title', 'Organizer Dashboard')

@section('content')
<div class="min-h-screen bg-primary">

    <!-- Hero Section -->
    <div class="bg-primary min-h-[60vh] sm:min-h-[70vh] lg:h-[80vh] flex items-center py-8 sm:py-12 lg:py-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                <!-- Hero Content -->
                <div class="space-y-4 sm:space-y-6 text-center lg:text-left">
                    <div class="space-y-3 sm:space-y-4">
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-primary leading-tight">
                            Welcome back, 
                            <span class="text-primary-600">{{ Auth::user()->name ?? 'Organizer' }}</span>!
                        </h2>
                        <p class="text-base sm:text-lg lg:text-xl text-secondary leading-relaxed">
                            Ready to create amazing events and manage your guest lists? 
                            Let's make your next event unforgettable.
                        </p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center lg:justify-start">
                        <a href="{{ route('organizer.events.create.new') }}" 
                            class="btn-primary inline-flex items-center justify-center px-4 py-2 sm:px-6 sm:py-3 border border-transparent text-sm sm:text-base font-medium rounded-md shadow-sm">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 sm:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Event
                        </a>
                    </div>
                </div>
                
                <!-- System Image Section -->
                <div class="flex justify-center lg:justify-end order-first lg:order-last">
                    <div class="relative w-full max-w-sm sm:max-w-md lg:max-w-none">
                        <!-- System Image -->
                        <div class="w-full h-64 sm:h-80 lg:w-[35rem] lg:h-[28rem] bg-primary rounded-2xl flex items-center justify-center shadow-2xl overflow-hidden">
                            @if(file_exists(public_path('images/dashboard-hero.jpg')))
                                <img src="{{ asset('images/dashboard-hero.jpg') }}" alt="Dashboard Hero" class="w-full h-full object-cover">
                            @elseif(file_exists(public_path('images/dashboard-hero.png')))
                                <img src="{{ asset('images/dashboard-hero.png') }}" alt="Dashboard Hero" class="w-full h-full object-cover">
                            @else
                                <!-- Placeholder with event-themed icon -->
                                <div class="text-center text-white px-4">
                                    <svg class="w-32 h-32 sm:w-40 sm:h-40 lg:w-60 lg:h-60 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="text-sm sm:text-base lg:text-lg font-medium">Event Management</p>
                                    <p class="text-xs sm:text-sm opacity-80">Add your hero image</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <!-- Total Guest Lists -->
            <div class="card">
                <div class="p-3 sm:p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-primary-500 rounded-md flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-xs sm:text-sm font-medium text-secondary truncate">Guest Lists</dt>
                                <dd class="text-base sm:text-lg font-medium text-primary">{{ $stats['total_guest_lists'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Guests -->
            <div class="card">
                <div class="p-3 sm:p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-xs sm:text-sm font-medium text-secondary truncate">Total Guests</dt>
                                <dd class="text-base sm:text-lg font-medium text-primary">{{ $stats['total_guests'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="card">
                <div class="p-3 sm:p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-xs sm:text-sm font-medium text-secondary truncate">Upcoming Events</dt>
                                <dd class="text-base sm:text-lg font-medium text-primary">{{ $stats['upcoming_events']->count() ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="p-3 sm:p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-xs sm:text-sm font-medium text-secondary truncate">Recent Activity</dt>
                                <dd class="text-base sm:text-lg font-medium text-primary">{{ count($stats['recent_activity'] ?? []) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Guest Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 mb-6 sm:mb-8">
            <div class="card">
                <div class="px-4 py-4 sm:py-5 sm:p-6">
                    <h3 class="text-base sm:text-lg leading-6 font-medium text-primary mb-3 sm:mb-4">Recent Guest Lists</h3>
                    <div class="space-y-3 sm:space-y-4">
                        @forelse($stats['recent_guest_lists'] ?? [] as $guestList)
                        <div class="flex items-center justify-between p-3 bg-secondary rounded-lg">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-primary truncate">{{ $guestList->name }}</h4>
                                <p class="text-xs sm:text-sm text-secondary">{{ $guestList->guests_count ?? 0 }} guests</p>
                            </div>
                            <a href="{{ route('organizer.guest-lists.display', $guestList) }}" class="text-primary-600 hover:text-primary-800 text-xs sm:text-sm font-medium transition-colors duration-200 ml-2 flex-shrink-0">
                                View →
                            </a>
                        </div>
                        @empty
                        <div class="text-center py-4 text-secondary text-sm">
                            No guest lists yet
                        </div>
                        @endforelse
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('organizer.guest-lists.index') }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium transition-colors duration-200">
                            View all guest lists →
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="px-4 py-4 sm:py-5 sm:p-6">
                    <h3 class="text-base sm:text-lg leading-6 font-medium text-primary mb-3 sm:mb-4">Upcoming Events</h3>
                    <div class="space-y-3 sm:space-y-4">
                        @forelse($stats['upcoming_events'] ?? [] as $event)
                        <div class="flex items-center justify-between p-3 bg-secondary rounded-lg">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-primary truncate">{{ $event->name }}</h4>
                                <p class="text-xs sm:text-sm text-secondary">
                                    {{ $event->event_date ? $event->event_date->format('M j, Y') : 'No date set' }}
                                </p>
                            </div>
                            <a href="{{ route('organizer.guest-lists.edit', $event) }}" class="text-primary-600 hover:text-primary-800 text-xs sm:text-sm font-medium transition-colors duration-200 ml-2 flex-shrink-0">
                                Manage →
                            </a>
                        </div>
                        @empty
                        <div class="text-center py-4 text-secondary text-sm">
                            No upcoming events
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="px-4 py-4 sm:py-5 sm:p-6">
                <h3 class="text-base sm:text-lg leading-6 font-medium text-primary mb-3 sm:mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <a href="{{ route('organizer.guest-lists.create') }}" class="flex items-center p-3 sm:p-4 border border-primary rounded-lg hover:bg-secondary transition-colors duration-200">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-primary-500 rounded-md flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4 min-w-0">
                            <h4 class="text-xs sm:text-sm font-medium text-primary truncate">Create Guest List</h4>
                            <p class="text-xs sm:text-sm text-secondary truncate">Start a new event</p>
                        </div>
                    </a>

                    <a href="{{ route('organizer.guest-lists.index') }}" class="flex items-center p-3 sm:p-4 border border-primary rounded-lg hover:bg-secondary transition-colors duration-200">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4 min-w-0">
                            <h4 class="text-xs sm:text-sm font-medium text-primary truncate">Manage Guest Lists</h4>
                            <p class="text-xs sm:text-sm text-secondary truncate">View all lists</p>
                        </div>
                    </a>

                    <a href="{{ route('organizer.reports') }}" class="flex items-center p-3 sm:p-4 border border-primary rounded-lg hover:bg-secondary transition-colors duration-200 sm:col-span-2 lg:col-span-1">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4 min-w-0">
                            <h4 class="text-xs sm:text-sm font-medium text-primary truncate">View Reports</h4>
                            <p class="text-xs sm:text-sm text-secondary truncate">Analytics & insights</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showCreateModal() {
    // Redirect to create page
    window.location.href = "{{ route('organizer.guest-lists.create') }}";
}
</script>
@endpush
@endsection 