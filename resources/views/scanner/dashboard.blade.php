@extends('layouts.scanner')

@section('title', 'Scanner Dashboard')

@section('content')
<div class="min-h-screen bg-primary">
    <!-- Header -->
    <div class="scanner-dashboard-header bg-gradient-to-r from-primary-600 to-primary-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-8">
                <div>
                    <h1 class="text-4xl font-bold">Event Scanner Dashboard</h1>
                    <p class="mt-2 text-lg text-primary-100">Check-in guests and manage events efficiently</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('scanner.events.index') }}" class="scanner-main-button bg-white text-primary-600 hover:bg-primary-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                        </svg>
                        Start Scanning
                    </a>
                    <div class="flex items-center space-x-2 px-4 py-2 bg-white bg-opacity-20 rounded-lg backdrop-blur-sm">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium">Scanner Ready</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Available Events -->
            <div class="card">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-secondary truncate">Available Events</dt>
                                <dd class="text-lg font-medium text-primary">{{ $stats['available_events'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Check-ins -->
            <div class="card">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-secondary truncate">Today's Check-ins</dt>
                                <dd class="text-lg font-medium text-primary">{{ $stats['todays_checkins'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Check-ins -->
            <div class="card">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-secondary truncate">Total Check-ins</dt>
                                <dd class="text-lg font-medium text-primary">{{ $stats['total_checkins'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Offline Mode -->
            <div class="card">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-secondary truncate">Offline Mode</dt>
                                <dd class="text-lg font-medium text-primary">{{ $stats['offline_mode'] ?? false ? 'Active' : 'Inactive' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Events -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="card">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-primary mb-4">Available Events</h3>
                    <div class="space-y-4">
                        @forelse($stats['events'] ?? [] as $event)
                        <div class="flex items-center justify-between p-4 border border-primary rounded-lg hover:bg-secondary transition-colors duration-200">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-primary">{{ $event->name }}</h4>
                                <p class="text-sm text-secondary">
                                    {{ $event->event_date ? $event->event_date->format('M j, Y g:i A') : 'No date set' }}
                                </p>
                                <p class="text-xs text-tertiary mt-1">
                                    {{ $event->guests_count ?? 0 }} guests • 
                                    {{ $event->checked_in_count ?? 0 }} checked in
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('scanner.events.scan', $event) }}" class="btn-success inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md">
                                    Scan
                                </a>
                                <a href="{{ route('scanner.events.history', $event) }}" class="btn-secondary inline-flex items-center px-3 py-1 border text-xs font-medium rounded-md">
                                    History
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-secondary">
                            <svg class="mx-auto h-12 w-12 text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-primary">No events available</h3>
                            <p class="mt-1 text-sm text-secondary">No events are currently available for scanning.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-primary mb-4">Recent Check-ins</h3>
                    <div class="space-y-4">
                        @forelse($stats['recent_checkins'] ?? [] as $checkin)
                        <div class="flex items-center justify-between p-3 bg-secondary rounded-lg">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-primary">{{ $checkin->guest->name ?? 'Unknown Guest' }}</h4>
                                <p class="text-sm text-secondary">{{ $checkin->guest->guestList->name ?? 'Unknown Event' }}</p>
                                <p class="text-xs text-tertiary">{{ $checkin->checked_in_at ? $checkin->checked_in_at->diffForHumans() : 'Recently' }}</p>
                            </div>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Checked In
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-secondary">
                            No recent check-ins
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-primary mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('scanner.events.index') }}" class="flex items-center p-4 border border-primary rounded-lg hover:bg-secondary transition-colors duration-200">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-medium text-primary">Scan Events</h4>
                            <p class="text-sm text-secondary">Check-in guests</p>
                        </div>
                    </a>

                    <a href="{{ route('scanner.offline') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-medium text-primary">Offline Mode</h4>
                            <p class="text-sm text-gray-500">Work without internet</p>
                        </div>
                    </a>

                    <a href="{{ route('scanner.settings') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-medium text-primary">Scanner Settings</h4>
                            <p class="text-sm text-gray-500">Configure scanner</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 