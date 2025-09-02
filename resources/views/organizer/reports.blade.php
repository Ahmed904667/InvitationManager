@extends('layouts.organizer')

@section('title', 'Reports & Analytics')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary mb-2">Reports & Analytics</h1>
        <p class="text-secondary">Comprehensive insights into your events, guest lists, and engagement metrics</p>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-8">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button id="general-tab" class="tab-button active border-b-2 border-primary py-2 px-1 text-sm font-medium text-primary" data-tab="general">
                General Reports
            </button>
            <button id="event-tab" class="tab-button border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="event">
                Event-Specific Reports
            </button>
        </nav>
    </div>

    <!-- General Reports Tab -->
    <div id="general-content" class="tab-content active">
        <!-- Account Overview -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-primary mb-6">Account Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Events -->
                <div class="card">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-primary">{{ $stats['overview']['total_events'] ?? 0 }}</div>
                                <div class="text-sm text-secondary">Total Events</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Events -->
                <div class="card">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-green-600">{{ $stats['overview']['active_events'] ?? 0 }}</div>
                                <div class="text-sm text-secondary">Active Events</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Events -->
                <div class="card">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-blue-600">{{ count($stats['completed_events'] ?? []) }}</div>
                                <div class="text-sm text-secondary">Completed Events</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Guest Lists -->
                <div class="card">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-purple-600">{{ $stats['overview']['total_guest_lists'] ?? 0 }}</div>
                                <div class="text-sm text-secondary">Guest Lists</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guest List Health Overview -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-primary mb-6">Guest List Health Overview</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Health Distribution -->
                <div class="card">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-primary mb-4">Health Distribution</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary">Excellent</span>
                                <span class="text-sm font-medium text-green-600">{{ $stats['guest_list_health']['excellent'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary">Good</span>
                                <span class="text-sm font-medium text-blue-600">{{ $stats['guest_list_health']['good'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary">Needs Attention</span>
                                <span class="text-sm font-medium text-yellow-600">{{ $stats['guest_list_health']['needs_attention'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary">Critical</span>
                                <span class="text-sm font-medium text-red-600">{{ $stats['guest_list_health']['critical'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Health Score -->
                <div class="card">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-primary mb-4">Average Health Score</h3>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-primary mb-2">{{ $stats['guest_list_health']['average_score'] ?? 0 }}/100</div>
                            <div class="text-sm text-secondary">Overall Health</div>
                        </div>
                    </div>
                </div>

                <!-- Total Guests -->
                <div class="card">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-primary mb-4">Total Guests</h3>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-primary mb-2">{{ $stats['guest_list_health']['total_guests'] ?? 0 }}</div>
                            <div class="text-sm text-secondary">Across All Lists</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-primary mb-6">Recent Events</h2>
            <div class="card">
                <div class="px-4 py-5 sm:p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guests</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-ins</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($stats['event_performance'] ?? [] as $event)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">{{ $event['event_name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">{{ $event['start_date'] ? $event['start_date']->format('M j, Y') : 'No date' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ ($event['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($event['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">{{ $event['total_guests'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">{{ $event['checked_in_guests'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-secondary">No recent events found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Check-in Analytics -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-primary mb-6">Check-in Analytics</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Check-in Summary -->
                <div class="card">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-primary mb-4">Check-in Summary</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary">Total Guests:</span>
                                <span class="text-sm font-medium text-primary">{{ $stats['checkin_analytics']['total_guests'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary">Total Check-ins:</span>
                                <span class="text-sm font-medium text-primary">{{ $stats['checkin_analytics']['total_checkins'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary">Overall Check-in Rate:</span>
                                <span class="text-sm font-medium text-primary">{{ $stats['checkin_analytics']['overall_checkin_rate'] ?? 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check-ins by Event -->
                <div class="card">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-primary mb-4">Check-ins by Event</h3>
                        <div class="space-y-3 max-h-48 overflow-y-auto">
                            @forelse($stats['checkin_analytics']['checkins_by_event'] ?? [] as $event)
                            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-primary">{{ $event['event_name'] }}</div>
                                    <div class="text-xs text-secondary">{{ $event['checked_in'] }}/{{ $event['total_guests'] }} guests</div>
                                </div>
                                <div class="text-sm font-medium text-primary">{{ $event['checkin_rate'] }}%</div>
                            </div>
                            @empty
                            <div class="text-center py-4 text-secondary text-sm">No check-in data available</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event-Specific Reports Tab -->
    <div id="event-content" class="tab-content hidden">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-primary mb-6">Event-Specific Reports</h2>
            
            <!-- Event Selection -->
            <div class="card mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <label for="event-select" class="block text-sm font-medium text-primary mb-2">Select Completed Event</label>
                            <select id="event-select" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Choose an event...</option>
                                @foreach($stats['completed_events'] ?? [] as $event)
                                <option value="{{ $event['id'] }}">
                                    {{ $event['name'] }} - {{ $event['start_date'] ? $event['start_date']->format('M j, Y') : 'No date' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <button id="generate-report-btn" class="btn-primary px-6 py-2" disabled>
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Event Report Content -->
            <div id="event-report-content" class="hidden">
                <!-- Event Information -->
                <div class="card mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-xl font-bold text-primary mb-4">Event Information</h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-lg font-semibold text-primary mb-3">Basic Details</h4>
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-sm text-secondary">Event Name:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-name">-</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-secondary">Description:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-description">-</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-secondary">Start Date:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-start-date">-</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-secondary">End Date:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-end-date">-</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-secondary">Location:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-location">-</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-secondary">Venue Name:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-venue-name">-</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-secondary">Venue Address:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-venue-address">-</div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-primary mb-3">Event Settings</h4>
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-sm text-secondary">RSVP Enabled:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-rsvp-enabled">-</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-secondary">Check-in Enabled:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-checkin-enabled">-</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-secondary">Invitation Platforms:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-invitation-platforms">-</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-secondary">Guest Lists:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-guest-lists">-</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-secondary">Scanners:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-scanners">-</div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-secondary">Created:</span>
                                        <div class="text-sm font-medium text-primary mt-1" id="event-created">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event Overview -->
                <div class="card mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-xl font-bold text-primary mb-4">Event Overview</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-primary" id="event-total-guests">-</div>
                                <div class="text-sm text-secondary">Total Guests</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600" id="event-checked-in">-</div>
                                <div class="text-sm text-secondary">Checked In</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600" id="event-checkin-rate">-</div>
                                <div class="text-sm text-secondary">Check-in Rate</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600" id="event-rsvp-yes">-</div>
                                <div class="text-sm text-secondary">RSVP Yes</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-yellow-600" id="event-rsvp-maybe">-</div>
                                <div class="text-sm text-secondary">RSVP Maybe</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600" id="event-rsvp-no">-</div>
                                <div class="text-sm text-secondary">RSVP No</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Statistics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Invitation Statistics -->
                    <div class="card">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-primary mb-4">Invitation Statistics</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-secondary">Total Invitations:</span>
                                    <span class="text-sm font-medium text-primary" id="inv-total">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-secondary">Unique Guests Invited:</span>
                                    <span class="text-sm font-medium text-blue-600" id="inv-unique-guests">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-secondary">Sent Successfully:</span>
                                    <span class="text-sm font-medium text-green-600" id="inv-sent">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-secondary">Delivery Rate:</span>
                                    <span class="text-sm font-medium text-blue-600" id="inv-delivery-rate">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-secondary">RSVP Response Rate:</span>
                                    <span class="text-sm font-medium text-purple-600" id="inv-rsvp-rate">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-secondary">Pending Responses:</span>
                                    <span class="text-sm font-medium text-yellow-600" id="inv-pending">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Check-in Statistics -->
                    <div class="card">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-primary mb-4">Check-in Statistics</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-secondary">First Check-in:</span>
                                    <span class="text-sm font-medium text-primary" id="checkin-first">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-secondary">Last Check-in:</span>
                                    <span class="text-sm font-medium text-primary" id="checkin-last">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-secondary">Average Time:</span>
                                    <span class="text-sm font-medium text-blue-600" id="checkin-avg-time">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-secondary">Not Checked In:</span>
                                    <span class="text-sm font-medium text-red-600" id="checkin-not-checked">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Engagement Metrics -->
                <div class="card mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-primary mb-4">Guest Engagement Metrics</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600" id="engagement-overall">-</div>
                                <div class="text-sm text-secondary">Overall Engagement</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600" id="engagement-rsvp">-</div>
                                <div class="text-sm text-secondary">RSVP Engagement</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600" id="engagement-checkin">-</div>
                                <div class="text-sm text-secondary">Check-in Engagement</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scanner Usage -->
                <div class="card mb-6" id="scanner-usage-container">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-primary mb-4">Scanner Usage</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scanner Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-ins</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="scanner-usage-tbody">
                                    <!-- Scanner data will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Check-in Time Chart -->
                    <div class="card" id="checkin-chart-container">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-primary mb-4">Check-in Time Distribution</h3>
                            <div class="h-64 flex items-center justify-center">
                                <canvas id="checkin-time-chart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- RSVP Time Chart -->
                    <div class="card" id="rsvp-chart-container">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-primary mb-4">RSVP Response Time Distribution</h3>
                            <div class="h-64 flex items-center justify-center">
                                <canvas id="rsvp-time-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guest List Details -->
                <div class="card mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-primary mb-4">Guest List Details</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RSVP Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RSVP Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Method</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="guest-list-tbody">
                                    <!-- Guest data will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="card">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-primary mb-4">Export Report</h3>
                        <div class="flex space-x-4">
                            <button id="export-pdf-btn" class="btn-secondary px-4 py-2" onclick="downloadEventReportPdf()">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Reports page loaded');
    
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Update active tab button
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.add('active', 'border-primary', 'text-primary');
            this.classList.remove('border-transparent', 'text-gray-500');
            
            // Show target tab content
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            document.getElementById(targetTab + '-content').classList.remove('hidden');
            document.getElementById(targetTab + '-content').classList.add('active');
        });
    });
    
    // Event selection and report generation (only for event tab)
    const eventSelect = document.getElementById('event-select');
    const generateReportBtn = document.getElementById('generate-report-btn');
    const eventReportContent = document.getElementById('event-report-content');
    
    let checkinChart = null;
    let rsvpChart = null;
    
    // Enable/disable generate button based on selection
    if (eventSelect) {
        eventSelect.addEventListener('change', function() {
            generateReportBtn.disabled = !this.value;
        });
    }
    
    // Generate report when button is clicked
    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', function() {
            const selectedOption = eventSelect.options[eventSelect.selectedIndex];
            if (selectedOption.value) {
                const eventId = selectedOption.value;
                fetchEventReport(eventId);
            }
        });
    }
    
    function fetchEventReport(eventId) {
        // Show loading state
        generateReportBtn.disabled = true;
        generateReportBtn.textContent = 'Loading...';
        
        // Fetch detailed event data from backend
        fetch(`/organizer/event-report/${eventId}`)
            .then(response => response.json())
            .then(eventData => {
                if (eventData.error) {
                    alert('Error loading event data: ' + eventData.error);
                    return;
                }
                generateEventReport(eventData);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading event data. Please try again.');
            })
            .finally(() => {
                // Reset button state
                generateReportBtn.disabled = false;
                generateReportBtn.textContent = 'Generate Report';
            });
    }
    
    function generateEventReport(eventData) {
        // Show report content
        eventReportContent.classList.remove('hidden');
        
        // Update event overview stats
        updateEventOverview(eventData);
        
        // Generate charts
        generateCharts(eventData);
        
        // Populate guest list
        populateGuestList(eventData);
        
        // Scroll to report content
        eventReportContent.scrollIntoView({ behavior: 'smooth' });
    }
    
    function updateEventOverview(eventData) {
        try {
            // Basic event info
            document.getElementById('event-name').textContent = eventData.event_name || 'N/A';
            document.getElementById('event-description').textContent = eventData.description || 'No description';
            document.getElementById('event-start-date').textContent = eventData.start_date ? new Date(eventData.start_date).toLocaleString() : 'N/A';
            document.getElementById('event-end-date').textContent = eventData.end_date ? new Date(eventData.end_date).toLocaleString() : 'N/A';
            document.getElementById('event-location').textContent = eventData.location || 'No location specified';
            document.getElementById('event-venue-name').textContent = eventData.venue_name || 'No venue specified';
            document.getElementById('event-venue-address').textContent = eventData.venue_address || 'No address specified';
            
            // Event settings
            document.getElementById('event-rsvp-enabled').textContent = eventData.rsvp_enabled ? 'Yes' : 'No';
            document.getElementById('event-checkin-enabled').textContent = eventData.checkin_enabled ? 'Yes' : 'No';
            
            // Get invitation platforms from channel breakdown
            let platforms = 'None';
            if (eventData.invitation_stats?.channel_breakdown) {
                const channelNames = Object.keys(eventData.invitation_stats.channel_breakdown);
                if (channelNames.length > 0) {
                    platforms = channelNames.map(channel => 
                        channel.charAt(0).toUpperCase() + channel.slice(1)
                    ).join(', ');
                }
            }
            document.getElementById('event-invitation-platforms').textContent = platforms;
            
            document.getElementById('event-guest-lists').textContent = eventData.event_info?.guest_list_count || 0;
            document.getElementById('event-scanners').textContent = eventData.event_info?.scanner_count || 0;
            document.getElementById('event-created').textContent = eventData.created_at ? new Date(eventData.created_at).toLocaleString() : 'N/A';
            
            // Overview stats
            document.getElementById('event-total-guests').textContent = eventData.total_guests || 0;
            document.getElementById('event-checked-in').textContent = eventData.checked_in_guests || 0;
            document.getElementById('event-checkin-rate').textContent = (eventData.checkin_rate || 0) + '%';
            document.getElementById('event-rsvp-yes').textContent = eventData.rsvp_yes || 0;
            document.getElementById('event-rsvp-maybe').textContent = eventData.rsvp_maybe || 0;
            document.getElementById('event-rsvp-no').textContent = eventData.rsvp_no || 0;
            
            // Update detailed statistics
            updateDetailedStatistics(eventData);
            
            // Update engagement metrics
            updateEngagementMetrics(eventData);
            
            // Update scanner usage
            updateScannerUsage(eventData);
        } catch (error) {
            console.error('Error updating event overview:', error);
            alert('Error updating event overview. Please try again.');
        }
    }
    
    function generateCharts(eventData) {
        // Check-in time chart
        if (eventData.checkin_enabled && eventData.checkin_data) {
            generateCheckinTimeChart(eventData.checkin_data);
        } else {
            document.getElementById('checkin-chart-container').classList.add('hidden');
        }
        
        // RSVP time chart
        if (eventData.rsvp_enabled && eventData.rsvp_data) {
            generateRSVPTimeChart(eventData.rsvp_data);
        } else {
            document.getElementById('rsvp-chart-container').classList.add('hidden');
        }
    }
    
    function generateCheckinTimeChart(checkinData) {
        const ctx = document.getElementById('checkin-time-chart').getContext('2d');
        
        if (checkinChart) {
            checkinChart.destroy();
        }
        
        checkinChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: checkinData.time_labels || ['9 AM', '10 AM', '11 AM', '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM'],
                datasets: [{
                    label: 'Check-ins',
                    data: checkinData.time_data || [5, 12, 18, 25, 20, 15, 10, 8, 3],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    
    function generateRSVPTimeChart(rsvpData) {
        const ctx = document.getElementById('rsvp-time-chart').getContext('2d');
        
        if (rsvpChart) {
            rsvpChart.destroy();
        }
        
        rsvpChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: rsvpData.time_labels || ['Same Day', '1 Day', '2 Days', '3 Days', '1 Week', '2 Weeks', '1 Month+'],
                datasets: [{
                    label: 'RSVP Responses',
                    data: rsvpData.time_data || [15, 25, 30, 20, 15, 10, 5],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(107, 114, 128, 0.8)',
                        'rgba(75, 85, 99, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    
    function populateGuestList(eventData) {
        const tbody = document.getElementById('guest-list-tbody');
        tbody.innerHTML = '';
        
        if (eventData.guests && eventData.guests.length > 0) {
            eventData.guests.forEach(guest => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">${guest.name || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">${guest.email || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            ${guest.rsvp_status === 'yes' ? 'bg-green-100 text-green-800' : 
                              (guest.rsvp_status === 'maybe' ? 'bg-yellow-100 text-yellow-800' : 
                               (guest.rsvp_status === 'no' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'))}">
                            ${guest.rsvp_status ? guest.rsvp_status.toUpperCase() : 'NO RESPONSE'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">${guest.rsvp_date || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">${guest.checkin_time || 'Not checked in'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">${guest.checkin_method || 'N/A'}</td>
                `;
                tbody.appendChild(row);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-sm text-secondary">No guest data available</td></tr>';
        }
    }
    
    function updateDetailedStatistics(eventData) {
        try {
            // Invitation statistics
            if (eventData.invitation_stats) {
                document.getElementById('inv-total').textContent = eventData.invitation_stats.total_invitations || 0;
                document.getElementById('inv-unique-guests').textContent = eventData.invitation_stats.total_unique_guests_invited || 0;
                document.getElementById('inv-sent').textContent = eventData.invitation_stats.sent_invitations || 0;
                document.getElementById('inv-delivery-rate').textContent = (eventData.invitation_stats.delivery_rate || 0) + '%';
                document.getElementById('inv-rsvp-rate').textContent = (eventData.invitation_stats.rsvp_response_rate || 0) + '%';
                document.getElementById('inv-pending').textContent = eventData.invitation_stats.rsvp_breakdown?.pending || 0;
            }
            
            // Check-in statistics
            if (eventData.checkin_stats) {
                document.getElementById('checkin-first').textContent = eventData.checkin_stats.first_checkin ? 
                    new Date(eventData.checkin_stats.first_checkin).toLocaleString() : 'No check-ins';
                document.getElementById('checkin-last').textContent = eventData.checkin_stats.last_checkin ? 
                    new Date(eventData.checkin_stats.last_checkin).toLocaleString() : 'No check-ins';
                document.getElementById('checkin-avg-time').textContent = eventData.checkin_stats.average_checkin_time || 'N/A';
                document.getElementById('checkin-not-checked').textContent = eventData.checkin_stats.not_checked_in || 0;
            }
        } catch (error) {
            console.error('Error updating detailed statistics:', error);
        }
    }
    
    function updateEngagementMetrics(eventData) {
        try {
            if (eventData.engagement_metrics) {
                document.getElementById('engagement-overall').textContent = (eventData.engagement_metrics.overall_engagement_rate || 0) + '%';
                document.getElementById('engagement-rsvp').textContent = (eventData.engagement_metrics.rsvp_engagement_rate || 0) + '%';
                document.getElementById('engagement-checkin').textContent = (eventData.engagement_metrics.checkin_engagement_rate || 0) + '%';
            }
        } catch (error) {
            console.error('Error updating engagement metrics:', error);
        }
    }
    
    function updateScannerUsage(eventData) {
        try {
            const tbody = document.getElementById('scanner-usage-tbody');
            const container = document.getElementById('scanner-usage-container');
            
            if (!eventData.checkin_stats?.scanner_usage || eventData.checkin_stats.scanner_usage.length === 0) {
                container.classList.add('hidden');
                return;
            }
            
            container.classList.remove('hidden');
            tbody.innerHTML = '';
            
            eventData.checkin_stats.scanner_usage.forEach(scanner => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">${scanner.scanner_name || 'Unknown Scanner'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">${scanner.checkins_count || 0}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            ${scanner.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                            ${scanner.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                `;
                tbody.appendChild(row);
            });
        } catch (error) {
            console.error('Error updating scanner usage:', error);
        }
    }
    
    // PDF Download functionality
    window.downloadEventReportPdf = function() {
        const eventSelect = document.getElementById('event-select');
        if (!eventSelect || !eventSelect.value) {
            alert('Please select an event first to generate a report.');
            return;
        }
        
        const eventId = eventSelect.value;
        const eventName = eventSelect.options[eventSelect.selectedIndex].text;
        
        // Show loading state
        const exportBtn = document.getElementById('export-pdf-btn');
        const originalText = exportBtn.innerHTML;
        exportBtn.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Generating PDF...';
        exportBtn.disabled = true;
        
        // Create a temporary form to download the PDF
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = `/organizer/event-report/${eventId}/pdf`;
        form.target = '_blank';
        
        // Add CSRF token if needed
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        // Submit the form
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        // Reset button state after a delay
        setTimeout(() => {
            exportBtn.innerHTML = originalText;
            exportBtn.disabled = false;
        }, 2000);
    };
});
</script>
@endpush

<style>
.tab-button.active {
    @apply border-primary text-primary;
}

.tab-button:not(.active) {
    @apply border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300;
}

.tab-content {
    @apply transition-all duration-200;
}
</style>
@endsection
