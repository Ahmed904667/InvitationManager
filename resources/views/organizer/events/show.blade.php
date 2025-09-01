@extends('layouts.organizer')

@section('title', $event->name)

@section('content')

<style>
.tab-button {
    transition: all 0.2s ease-in-out;
}

.tab-button:hover {
    color: var(--primary-600) !important;
}

.tab-button.active {
    border-color: var(--primary-600) !important;
    color: var(--primary-600) !important;
}

.tab-content {
    transition: opacity 0.2s ease-in-out;
}
</style>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-primary">{{ $event->name }}</h1>
            <p class="text-gray-600 mt-2">{{ $event->description }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('organizer.events.preview', $event) }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Preview
            </a>
            <a href="{{ route('organizer.events.notifications', $event) }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                Notifications
            </a>
            <a href="{{ route('organizer.events.edit', $event) }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Event
            </a>
        </div>
    </div>

    <!-- Event Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--primary-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Guests</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $stats['total_guests'] }}</p>
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
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Confirmed RSVP</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $stats['rsvp_stats']['yes'] }}</p>
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
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Checked In</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $stats['attendance_stats']['checked_in'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--purple-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--purple-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Guest Lists</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $stats['guest_lists_count'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Event Details -->
        <div class="lg:col-span-2">
            <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
                <h2 class="text-xl font-semibold mb-4" style="color: var(--text-primary);">Event Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-medium mb-3" style="color: var(--text-primary);">Basic Information</h3>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium" style="color: var(--text-secondary);">Date & Time:</span>
                                <p class="text-sm" style="color: var(--text-primary);">
                                    {{ $event->start_date->format('l, F j, Y') }} at {{ $event->start_date->format('g:i A') }}
                                    @if($event->end_date)
                                        <br><span class="text-xs text-gray-500">Ends: {{ $event->end_date->format('g:i A') }}</span>
                                    @endif
                                </p>
                            </div>
                            
                            @if($event->venue_name || $event->venue_address)
                            <div>
                                <span class="text-sm font-medium" style="color: var(--text-secondary);">Location:</span>
                                <p class="text-sm" style="color: var(--text-primary);">
                                    @if($event->venue_name){{ $event->venue_name }}<br>@endif
                                    @if($event->venue_address){{ $event->venue_address }}@endif
                                </p>
                            </div>
                            @endif
                            
                            @if($event->parking_info)
                            <div>
                                <span class="text-sm font-medium" style="color: var(--text-secondary);">Parking:</span>
                                <p class="text-sm" style="color: var(--text-primary);">{{ $event->parking_info }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-medium mb-3" style="color: var(--text-primary);">Event Settings</h3>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium" style="color: var(--text-secondary);">Status:</span>
                                <span class="badge badge-{{ $event->status }}">{{ ucfirst($event->status) }}</span>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium" style="color: var(--text-secondary);">RSVP:</span>
                                <span class="text-sm" style="color: var(--text-primary);">
                                    {{ $event->rsvp_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium" style="color: var(--text-secondary);">QR Check-in:</span>
                                <span class="text-sm" style="color: var(--text-primary);">
                                    {{ $event->qr_checkin_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                            
                            @if($event->rsvp_deadline)
                            <div>
                                <span class="text-sm font-medium" style="color: var(--text-secondary);">RSVP Deadline:</span>
                                <p class="text-sm" style="color: var(--text-primary);">{{ $event->rsvp_deadline }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($event->additional_information)
                <div class="mt-6">
                    <h3 class="font-medium mb-3" style="color: var(--text-primary);">Additional Information</h3>
                    <p class="text-sm" style="color: var(--text-primary);">{{ $event->additional_information }}</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- RSVP & Attendance Summary -->
        <div>
            <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
                <h2 class="text-xl font-semibold mb-4" style="color: var(--text-primary);">RSVP Summary</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm" style="color: var(--text-secondary);">Confirmed</span>
                        <div class="flex items-center">
                            <span class="text-sm font-medium mr-2 rsvp-confirmed-count" style="color: var(--text-primary);">{{ $stats['rsvp_stats']['yes'] }}</span>
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full rsvp-confirmed-bar" style="width: {{ $stats['total_guests'] > 0 ? ($stats['rsvp_stats']['yes'] / $stats['total_guests']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm" style="color: var(--text-secondary);">Maybe</span>
                        <div class="flex items-center">
                            <span class="text-sm font-medium mr-2 rsvp-maybe-count" style="color: var(--text-primary);">{{ $stats['rsvp_stats']['maybe'] }}</span>
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full rsvp-maybe-bar" style="width: {{ $stats['total_guests'] > 0 ? ($stats['rsvp_stats']['maybe'] / $stats['total_guests']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm" style="color: var(--text-secondary);">Declined</span>
                        <div class="flex items-center">
                            <span class="text-sm font-medium mr-2 rsvp-declined-count" style="color: var(--text-primary);">{{ $stats['rsvp_stats']['no'] }}</span>
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full rsvp-declined-bar" style="width: {{ $stats['total_guests'] > 0 ? ($stats['rsvp_stats']['no'] / $stats['total_guests']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm" style="color: var(--text-secondary);">No Response</span>
                        <div class="flex items-center">
                            <span class="text-sm font-medium mr-2 rsvp-no-response-count" style="color: var(--text-primary);">{{ $stats['rsvp_stats']['no_response'] }}</span>
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="bg-gray-500 h-2 rounded-full rsvp-no-response-bar" style="width: {{ $stats['total_guests'] > 0 ? ($stats['rsvp_stats']['no_response'] / $stats['total_guests']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 pt-6 border-t" style="border-color: var(--border-primary);">
                    <h3 class="font-medium mb-3" style="color: var(--text-primary);">Attendance</h3>
                    <div class="flex justify-between items-center">
                        <span class="text-sm" style="color: var(--text-secondary);">Checked In</span>
                        <span class="text-sm font-medium" style="color: var(--text-primary);">{{ $stats['attendance_stats']['checked_in'] }} / {{ $stats['total_guests'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Guest Lists and Guests -->
    <div class="rounded-lg shadow-sm border" style="background: var(--bg-primary); border-color: var(--border-primary);">
        <div class="p-6 border-b" style="border-color: var(--border-primary);">
            <h2 class="text-xl font-semibold" style="color: var(--text-primary);">Guest Lists & Guests</h2>
        </div>
        
        <!-- Tabs -->
        <div class="border-b" style="border-color: var(--border-primary);">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button id="guests-tab" class="tab-button active py-4 px-1 border-b-2 font-medium text-sm transition-colors" style="border-color: var(--primary-600); color: var(--primary-600);">
                    Guest Lists
                </button>
                <button id="notifications-tab" class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition-colors" style="border-color: transparent; color: var(--text-secondary);">
                    Notifications
                </button>
            </nav>
        </div>
        
        <!-- Tab Content -->
        <div id="guests-content" class="tab-content">
        
        @php
            // Group active event guests by guest list
            $guestsByList = $activeEventGuests->groupBy(function($eventGuest) {
                return $eventGuest->guest->guest_list_id ?? 'standalone';
            });
        @endphp
        
        @forelse($guestsByList as $guestListId => $eventGuests)
        @php
            $guestList = $guestListId !== 'standalone' ? $event->guestLists->find($guestListId) : null;
            $listName = $guestList ? $guestList->name : 'Standalone Guests';
        @endphp
        <div class="p-6 border-b last:border-b-0" style="border-color: var(--border-primary);">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium" style="color: var(--text-primary);">{{ $listName }}</h3>
                <span class="text-sm" style="color: var(--text-secondary);">{{ $eventGuests->count() }} guests</span>
            </div>
            
            @if($eventGuests->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y" style="border-color: var(--border-primary);">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Guest</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Contact</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">RSVP Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Attendance</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Check-in Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-primary);">
                        @foreach($eventGuests as $eventGuest)
                        @php
                            $guest = $eventGuest->guest;
                            $invitation = $guest->invitations->where('event_id', $event->id)->first();
                            $rsvpStatus = $invitation ? ($invitation->rsvp_status ?? 'no_response') : 'no_response';
                            // Treat 'none' as 'no_response'
                            if ($rsvpStatus === 'none') {
                                $rsvpStatus = 'no_response';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50" data-guest-id="{{ $guest->id }}">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div>
                                    <a href="{{ route('organizer.events.guests.show', ['event' => $event, 'guest' => $guest]) }}" class="text-sm font-medium hover:text-blue-600 transition-colors" style="color: var(--text-primary);">
                                        {{ $guest->name }}
                                    </a>
                                    @if($guest->notes)
                                    <div class="text-xs" style="color: var(--text-secondary);">{{ Str::limit($guest->notes, 50) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm" style="color: var(--text-primary);">
                                    @if($guest->email)
                                    <div>{{ $guest->email }}</div>
                                    @endif
                                    @if($guest->phone)
                                    <div>{{ $guest->phone }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap rsvp-status-cell">
                                @switch($rsvpStatus)
                                    @case('yes')
                                        <span class="badge rsvp-badge badge-success">Confirmed</span>
                                        @break
                                    @case('no')
                                        <span class="badge rsvp-badge badge-danger">Declined</span>
                                        @break
                                    @case('maybe')
                                        <span class="badge rsvp-badge badge-warning">Maybe</span>
                                        @break
                                    @default
                                        <span class="badge rsvp-badge badge-secondary">No Response</span>
                                @endswitch
                                @if($invitation && $invitation->rsvp_at)
                                <div class="text-xs mt-1 rsvp-time" style="color: var(--text-secondary);">
                                    {{ $invitation->rsvp_at->format('M j, g:i A') }}
                                </div>
                                @else
                                <div class="text-xs mt-1 rsvp-time" style="color: var(--text-secondary); display: none;"></div>
                                @endif
                                @if($invitation && $invitation->rsvp_note)
                                <div class="text-xs mt-1 rsvp-note" style="color: var(--text-secondary); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $invitation->rsvp_note }}">
                                    "{{ Str::limit($invitation->rsvp_note, 30) }}"
                                </div>
                                @else
                                <div class="text-xs mt-1 rsvp-note" style="color: var(--text-secondary); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: none;"></div>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($eventGuest->checked_in)
                                    <span class="badge badge-success">Checked In</span>
                                @else
                                    <span class="badge badge-secondary">Not Checked In</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm" style="color: var(--text-primary);">
                                @if($eventGuest->checked_in && $eventGuest->checked_in_at)
                                    {{ $eventGuest->checked_in_at->format('M j, g:i A') }}
                                    @if($eventGuest->scannedByScanner)
                                    <div class="text-xs" style="color: var(--text-secondary);">
                                        by {{ $eventGuest->scanner_name }}
                                    </div>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <p class="text-sm" style="color: var(--text-secondary);">No guests in this list</p>
            </div>
            @endif
        </div>
        @empty
        <div class="p-6 text-center">
            <p class="text-sm" style="color: var(--text-secondary);">No guests associated with this event</p>
        </div>
        @endforelse
        </div>
        
        <!-- Notifications Tab Content -->
        <div id="notifications-content" class="tab-content hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium" style="color: var(--text-primary);">Event Notifications</h3>
                    <div class="flex space-x-2">
                        <button type="button" onclick="openSendNotificationModal()" class="btn-primary text-sm flex items-center" id="send-notification-btn">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg> Send Notification
                        </button>
                        <button type="button" class="btn-secondary text-sm flex items-center" id="refresh-notifications-btn">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>


                        <a href="{{ route('organizer.events.notifications', $event) }}" class="btn-secondary text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View All Notifications
                            <button type="button" onclick="toggleNotificationDetails()" class="ml-2 text-xs text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </a>
                    </div>
                </div>
                
                @php
                    $recentNotifications = $event->notifications()
                        ->with(['guest', 'user'])
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();
                    
                    $stats = [
                        'queued' => $event->notifications()->whereIn('status', ['queued', 'sending', 'pending'])->count(),
                        'delivered' => $event->notifications()->whereIn('status', ['delivered', 'sent'])->count(),
                        'read' => $event->notifications()->where('status', 'read')->count(),
                        'failed' => $event->notifications()->whereIn('status', ['failed', 'undelivered', 'canceled', 'bounced'])->count(),
                    ];
                @endphp
                
                <!-- Notification Statistics -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                    <div class="text-center p-3 rounded-lg" style="background: var(--orange-100);">
                        <div class="text-xl font-bold notification-queued-count" style="color: var(--orange-600);">{{ $stats['queued'] }}</div>
                        <div class="text-xs" style="color: var(--text-secondary);">Queued</div>
                    </div>
                    <div class="text-center p-3 rounded-lg" style="background: var(--green-100);">
                        <div class="text-xl font-bold notification-delivered-count" style="color: var(--green-600);">{{ $stats['delivered'] }}</div>
                        <div class="text-xs" style="color: var(--text-secondary);">Delivered</div>
                    </div>
                    <div class="text-center p-3 rounded-lg" style="background: var(--emerald-100);">
                        <div class="text-xl font-bold notification-read-count" style="color: var(--emerald-600);">{{ $stats['read'] }}</div>
                        <div class="text-xs" style="color: var(--text-secondary);">Read</div>
                    </div>
                    <div class="text-center p-3 rounded-lg" style="background: var(--red-100);">
                        <div class="text-xl font-bold notification-failed-count" style="color: var(--red-600);">{{ $stats['failed'] }}</div>
                        <div class="text-xs" style="color: var(--text-secondary);">Failed</div>
                    </div>
                </div>
                
                <!-- Recent Notifications -->
                @if($recentNotifications->count() > 0)
                <div class="space-y-3">
                    @foreach($recentNotifications as $notification)
                    <div class="flex items-center justify-between p-3 rounded-lg border" style="background: var(--bg-secondary); border-color: var(--border-primary);">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 rounded-full 
                                @if($notification->status === 'read') bg-emerald-500
                                @elseif($notification->status === 'delivered' || $notification->status === 'sent') bg-green-500
                                @elseif($notification->status === 'queued' || $notification->status === 'sending' || $notification->status === 'pending') bg-orange-500
                                @elseif($notification->status === 'failed' || $notification->status === 'undelivered' || $notification->status === 'canceled' || $notification->status === 'bounced') bg-red-500
                                @else bg-orange-500
                                @endif">
                            </div>
                            <div>
                                <div class="text-sm font-medium" style="color: var(--text-primary);">
                                    {{ $notification->guest->name ?? 'Unknown Guest' }}
                                </div>
                                <div class="text-xs" style="color: var(--text-secondary);">
                                    {{ ucfirst($notification->channel) }} • {{ ucfirst($notification->type) }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs" style="color: var(--text-secondary);">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                            <div class="text-xs font-medium 
                                @if($notification->status === 'read') text-emerald-600
                                @elseif($notification->status === 'delivered' || $notification->status === 'sent') text-green-600
                                @elseif($notification->status === 'queued' || $notification->status === 'sending' || $notification->status === 'pending') text-orange-600
                                @elseif($notification->status === 'failed' || $notification->status === 'undelivered' || $notification->status === 'canceled' || $notification->status === 'bounced') text-red-600
                                @else text-orange-600
                                @endif">
                                @if($notification->status === 'read') Read
                                @elseif($notification->status === 'delivered' || $notification->status === 'sent') Delivered
                                @elseif($notification->status === 'queued' || $notification->status === 'sending' || $notification->status === 'pending') Queued
                                @elseif($notification->status === 'failed' || $notification->status === 'undelivered' || $notification->status === 'canceled' || $notification->status === 'bounced') Failed
                                @else Queued
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <p class="text-sm" style="color: var(--text-secondary);">No notifications sent yet</p>
                </div>
                @endif

                <!-- Extended Notification Details (Collapsible) -->
                <div id="notification-details" class="hidden mt-6">
                    <div class="border rounded-lg" style="border-color: var(--border-primary);">
                        <div class="p-4 border-b" style="border-color: var(--border-primary); background: var(--bg-secondary);">
                            <h4 class="text-lg font-medium" style="color: var(--text-primary);">Detailed Notification Status</h4>
                            <p class="text-sm" style="color: var(--text-secondary);">View status for all notifications sent to guests</p>
                        </div>
                        <div class="p-4">
                            @php
                                $allNotifications = $event->notifications()
                                    ->with(['guest', 'user'])
                                    ->orderBy('created_at', 'desc')
                                    ->get()
                                    ->groupBy('channel');
                            @endphp
                            
                            @foreach($allNotifications as $channel => $channelNotifications)
                            <div class="mb-6">
                                <h5 class="text-md font-medium mb-3" style="color: var(--text-primary);">
                                    {{ ucfirst($channel) }} Notifications 
                                    <span class="text-sm font-normal" style="color: var(--text-secondary);">({{ $channelNotifications->count() }})</span>
                                </h5>
                                <div class="space-y-2">
                                    @foreach($channelNotifications as $notification)
                                    <div class="flex items-center justify-between p-3 rounded-lg border" style="background: var(--bg-secondary); border-color: var(--border-primary);">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-3 h-3 rounded-full 
                                                @if($notification->status === 'read') bg-emerald-500
                                                @elseif($notification->status === 'delivered' || $notification->status === 'sent') bg-green-500
                                                @elseif($notification->status === 'queued' || $notification->status === 'sending' || $notification->status === 'pending') bg-orange-500
                                                @elseif($notification->status === 'failed' || $notification->status === 'undelivered' || $notification->status === 'canceled' || $notification->status === 'bounced') bg-red-500
                                                @else bg-orange-500
                                                @endif">
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium" style="color: var(--text-primary);">
                                                    {{ $notification->guest->name ?? 'Unknown Guest' }}
                                                </div>
                                                <div class="text-xs" style="color: var(--text-secondary);">
                                                    {{ ucfirst($notification->type) }} • {{ $notification->created_at->format('M j, Y g:i A') }}
                                                </div>
                                                @if($notification->message)
                                                <div class="text-xs mt-1" style="color: var(--text-secondary);">
                                                    {{ Str::limit($notification->message, 100) }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                                                                 <div class="text-right">
                                             <div class="text-xs font-medium 
                                                 @if($notification->status === 'read') text-emerald-600
                                                 @elseif($notification->status === 'delivered' || $notification->status === 'sent') text-green-600
                                                 @elseif($notification->status === 'queued' || $notification->status === 'sending' || $notification->status === 'pending') text-orange-600
                                                 @elseif($notification->status === 'failed' || $notification->status === 'undelivered' || $notification->status === 'canceled' || $notification->status === 'bounced') text-red-600
                                                 @else text-orange-600
                                                 @endif">
                                                 @if($notification->status === 'read') Read
                                                 @elseif($notification->status === 'delivered' || $notification->status === 'sent') Delivered
                                                 @elseif($notification->status === 'queued' || $notification->status === 'sending' || $notification->status === 'pending') Queued
                                                 @elseif($notification->status === 'failed' || $notification->status === 'undelivered' || $notification->status === 'canceled' || $notification->status === 'bounced') Failed
                                                 @else Queued
                                                 @endif
                                             </div>
                                             @if($notification->external_id)
                                             <div class="text-xs" style="color: var(--text-secondary);">
                                                 ID: {{ $notification->external_id }}
                                             </div>
                                             @endif
                                             

                                         </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Notification Modal -->
<div id="send-notification-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Send Notification to All Guests</h3>
            <button onclick="closeSendNotificationModal()" class="modal-close">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 18"></path>
                </svg>
            </button>
        </div>

                <div class="modal-body">
                    <form id="send-notification-form" action="{{ route('organizer.events.notifications.send', $event) }}" method="POST">
                        <!-- Debug: Show the actual route URL -->
                        <div style="display: none;">
                            Route URL: {{ route('organizer.events.notifications.send', $event) }}
                        </div>
                        @csrf
                        
                        <!-- Platform Selection -->
                        <div class="form-group">
                            <label class="form-label">Notification Platform</label>
                            <div class="space-y-3">
                                @if($event->invitation_platforms && in_array('whatsapp', $event->invitation_platforms))
                                <label class="checkbox-label">
                                    <input type="checkbox" name="platforms[]" value="whatsapp" class="checkbox-input">
                                    <span class="checkbox-text">WhatsApp</span>
                                    <span class="badge badge-success">Available</span>
                                </label>
                                @endif
                                
                                @if($event->invitation_platforms && in_array('email', $event->invitation_platforms))
                                <label class="checkbox-label">
                                    <input type="checkbox" name="platforms[]" value="email" class="checkbox-input">
                                    <span class="checkbox-text">Email</span>
                                    <span class="badge badge-success">Available</span>
                                </label>
                                @endif
                                
                                @if(!$event->invitation_platforms || (empty(array_intersect(['whatsapp', 'email'], $event->invitation_platforms))))
                                <div class="alert alert-warning">
                                    <p>No notification platforms configured for this event. Please update event settings to enable WhatsApp or Email notifications.</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Message Type -->
                        <div class="form-group">
                            <label for="notification_type" class="form-label">Message Type</label>
                            <select id="notification_type" name="type" class="form-select">
                                <option value="event_reminder">Event Reminder</option>
                                <option value="event_update">Event Update</option>
                                <option value="custom">Custom Message</option>
                            </select>
                        </div>

                        <!-- Message Content -->
                        <div class="form-group">
                            <label for="notification_message" class="form-label">Message Content</label>
                            <textarea id="notification_message" name="message" rows="4" class="form-textarea" placeholder="Enter your message here..."></textarea>
                        </div>

                        <!-- Template Suggestions -->
                        <div class="form-group">
                            <label class="form-label">Template Suggestions</label>
                            <div class="template-grid">
                                <button type="button" onclick="useTemplate('reminder')" class="template-option">
                                    <div class="template-title">Event Reminder</div>
                                    <div class="template-description">Remind guests about upcoming event</div>
                                </button>
                                <button type="button" onclick="useTemplate('update')" class="template-option">
                                    <div class="template-title">Event Update</div>
                                    <div class="template-description">Inform about event changes</div>
                                </button>
                                <button type="button" onclick="useTemplate('welcome')" class="template-option">
                                    <div class="template-title">Welcome Message</div>
                                    <div class="template-description">Welcome guests to the event</div>
                                </button>
                                <button type="button" onclick="useTemplate('custom')" class="template-option">
                                    <div class="template-title">Custom</div>
                                    <div class="template-description">Write your own message</div>
                                </button>
                            </div>
                        </div>

                        <!-- Guest Count Info -->
                        <div class="alert alert-info">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p>This notification will be sent to <strong>{{ $event->activeGuests()->count() }}</strong> active guests</p>
                                    <p class="text-sm mt-1">Guests will receive notifications based on their available contact methods</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeSendNotificationModal()" class="modal-btn modal-btn-secondary">
                        Cancel
                    </button>
                    <button type="button" onclick="testRoute()" class="modal-btn modal-btn-info">
                        Test Route
                    </button>
                    <button type="submit" form="send-notification-form" class="modal-btn modal-btn-primary" id="send-notification-submit-btn">
                        Send Notification
                    </button>
                </div>

@if($event->rsvp_enabled)
<style>
.template-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-top: 0.5rem;
}

.template-option {
    text-align: left;
    padding: 1rem;
    border: 1px solid var(--border-primary);
    border-radius: 0.5rem;
    background: var(--bg-secondary);
    transition: all 0.2s ease;
    cursor: pointer;
}

.template-option:hover {
    background: var(--bg-primary);
    border-color: var(--primary-500);
    transform: translateY(-1px);
}

.template-title {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.template-description {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 0.5rem 0;
}

.checkbox-input {
    margin-right: 0.75rem;
    width: 1rem;
    height: 1rem;
    accent-color: var(--primary-600);
}

.checkbox-text {
    margin-right: 0.75rem;
    color: var(--text-primary);
}

@media (max-width: 768px) {
    .template-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventId = {{ $event->id }};
    let refreshInterval;
    
    // Tab functionality
    const guestsTab = document.getElementById('guests-tab');
    const notificationsTab = document.getElementById('notifications-tab');
    const guestsContent = document.getElementById('guests-content');
    const notificationsContent = document.getElementById('notifications-content');
    
    function switchTab(activeTab, activeContent, inactiveTab, inactiveContent) {
        // Update tab buttons
        activeTab.classList.add('active');
        activeTab.style.borderColor = 'var(--primary-600)';
        activeTab.style.color = 'var(--primary-600)';
        
        inactiveTab.classList.remove('active');
        inactiveTab.style.borderColor = 'transparent';
        inactiveTab.style.color = 'var(--text-secondary)';
        
        // Update content
        activeContent.classList.remove('hidden');
        inactiveContent.classList.add('hidden');
    }
    
    guestsTab.addEventListener('click', () => {
        switchTab(guestsTab, guestsContent, notificationsTab, notificationsContent);
    });
    
    notificationsTab.addEventListener('click', () => {
        switchTab(notificationsTab, notificationsContent, guestsTab, guestsContent);
        
        // Auto-refresh notification statuses when notifications tab is clicked
        refreshNotificationStatuses();
    });

    // Function to update RSVP statuses in the table and summary
    function updateRsvpStatuses() {
        // Update detailed RSVP statuses
        fetch(`/organizer/events/${eventId}/rsvp/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const details = data.rsvp_details;
                    
                    details.forEach(guestDetail => {
                        const guestRow = document.querySelector(`[data-guest-id="${guestDetail.guest_id}"]`);
                        if (guestRow) {
                            const rsvpCell = guestRow.querySelector('.rsvp-status-cell');
                            if (rsvpCell) {
                                // Update RSVP status
                                const statusBadge = rsvpCell.querySelector('.rsvp-badge');
                                const statusText = rsvpCell.querySelector('.rsvp-text');
                                const statusTime = rsvpCell.querySelector('.rsvp-time');
                                const statusNote = rsvpCell.querySelector('.rsvp-note');
                                
                                // Update badge
                                if (statusBadge) {
                                    statusBadge.className = `badge rsvp-badge badge-${getStatusClass(guestDetail.rsvp_status)}`;
                                    statusBadge.textContent = getStatusText(guestDetail.rsvp_status);
                                }
                                
                                // Update time
                                if (statusTime) {
                                    if (guestDetail.rsvp_at) {
                                        const rsvpDate = new Date(guestDetail.rsvp_at);
                                        statusTime.textContent = rsvpDate.toLocaleDateString('en-US', { 
                                            month: 'short', 
                                            day: 'numeric' 
                                        }) + ', ' + rsvpDate.toLocaleTimeString('en-US', { 
                                            hour: 'numeric', 
                                            minute: '2-digit' 
                                        });
                                        statusTime.style.display = 'block';
                                    } else {
                                        statusTime.style.display = 'none';
                                    }
                                }
                                
                                // Update note
                                if (statusNote) {
                                    if (guestDetail.rsvp_note) {
                                        statusNote.textContent = `"${guestDetail.rsvp_note.length > 30 ? guestDetail.rsvp_note.substring(0, 30) + '...' : guestDetail.rsvp_note}"`;
                                        statusNote.title = guestDetail.rsvp_note;
                                        statusNote.style.display = 'block';
                                    } else {
                                        statusNote.style.display = 'none';
                                    }
                                }
                            }
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error updating RSVP statuses:', error);
            });

        // Update RSVP summary statistics
        fetch(`/organizer/events/${eventId}/rsvp/stats`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    
                    // Update summary counts
                    const confirmedCount = document.querySelector('.rsvp-confirmed-count');
                    const maybeCount = document.querySelector('.rsvp-maybe-count');
                    const declinedCount = document.querySelector('.rsvp-declined-count');
                    const noResponseCount = document.querySelector('.rsvp-no-response-count');
                    
                    if (confirmedCount) confirmedCount.textContent = stats.rsvp_responses.yes;
                    if (maybeCount) maybeCount.textContent = stats.rsvp_responses.maybe;
                    if (declinedCount) declinedCount.textContent = stats.rsvp_responses.no;
                    if (noResponseCount) noResponseCount.textContent = stats.rsvp_responses.no_response;
                    
                    // Update progress bars
                    const confirmedBar = document.querySelector('.rsvp-confirmed-bar');
                    const maybeBar = document.querySelector('.rsvp-maybe-bar');
                    const declinedBar = document.querySelector('.rsvp-declined-bar');
                    const noResponseBar = document.querySelector('.rsvp-no-response-bar');
                    
                    if (confirmedBar) confirmedBar.style.width = `${stats.total_guests > 0 ? (stats.rsvp_responses.yes / stats.total_guests) * 100 : 0}%`;
                    if (maybeBar) maybeBar.style.width = `${stats.total_guests > 0 ? (stats.rsvp_responses.maybe / stats.total_guests) * 100 : 0}%`;
                    if (declinedBar) declinedBar.style.width = `${stats.total_guests > 0 ? (stats.rsvp_responses.no / stats.total_guests) * 100 : 0}%`;
                    if (noResponseBar) noResponseBar.style.width = `${stats.total_guests > 0 ? (stats.rsvp_responses.no_response / stats.total_guests) * 100 : 0}%`;
                }
            })
            .catch(error => {
                console.error('Error updating RSVP stats:', error);
            });
    
    }

    function getStatusClass(status) {
        switch (status) {
            case 'yes': return 'success';
            case 'no': return 'danger';
            case 'maybe': return 'warning';
            default: return 'secondary';
        }
    }

    function getStatusText(status) {
        switch (status) {
            case 'yes': return 'Confirmed';
            case 'no': return 'Declined';
            case 'maybe': return 'Maybe';
            default: return 'No Response';
        }
    }

    // Set up auto-refresh every 30 seconds
    refreshInterval = setInterval(updateRsvpStatuses, 30000);

    // Clean up interval on page unload
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
    
    // Add event listener to refresh button
    const refreshBtn = document.getElementById('refresh-notifications-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            refreshNotificationStatuses();
        });
    } else {
        console.error('Refresh button not found!');
    }
    
    // Function to refresh notification statuses
    function refreshNotificationStatuses() {
        const refreshUrl = '{{ route("organizer.events.notifications.refresh", $event) }}';
        const submitBtn = document.getElementById('refresh-notifications-btn');
        let originalText = '';
        
        // Show loading state
        if (submitBtn) {
            originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Refreshing...';
            submitBtn.disabled = true;
        }
        
        fetch(refreshUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update notification statuses in the UI without reloading
                updateNotificationStatusesInUI(data);
                
                // Log the update details
                console.log(`Updated ${data.updated_count} notifications, Skipped ${data.missing_external_id} with missing IDs`);
            } else {
                showNotification(data.message || 'Failed to update notification statuses', 'error');
            }
        })
        .catch(error => {
            console.error('Notification status refresh failed:', error);
            showNotification('Failed to refresh notification statuses', 'error');
        })
        .finally(() => {
            // Reset button state
            if (submitBtn && originalText) {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }
    
    // Function to update notification statuses in the UI
    function updateNotificationStatusesInUI(data) {
        // Update notification statistics
        updateNotificationStats();
        
        // Update individual notification statuses
        updateIndividualNotificationStatuses();
        
        // Update notification count badges
        updateNotificationCounts();
    }
    
    // Function to update notification statistics
    function updateNotificationStats() {
        // Fetch updated notification statistics
        const eventId = {{ $event->id }};
        
        fetch(`/organizer/events/${eventId}/notifications/stats`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update the statistics display
                    const stats = data.stats;
                    
                    // Update notification count badges
                    const queuedCount = document.querySelector('.notification-queued-count');
                    const deliveredCount = document.querySelector('.notification-delivered-count');
                    const readCount = document.querySelector('.notification-read-count');
                    const failedCount = document.querySelector('.notification-failed-count');
                    
                    if (queuedCount) queuedCount.textContent = stats.queued;
                    if (deliveredCount) deliveredCount.textContent = stats.delivered;
                    if (readCount) readCount.textContent = stats.read;
                    if (failedCount) failedCount.textContent = stats.failed;
                    
                    console.log('Notification stats updated successfully');
                } else {
                    console.error('Failed to update notification stats:', data.message);
                }
            })
            .catch(error => {
                console.error('Failed to update notification stats:', error);
            });
    }
    
    // Function to update individual notification statuses
    function updateIndividualNotificationStatuses() {
        // Fetch updated notification list
        const eventId = {{ $event->id }};
        
        fetch(`/organizer/events/${eventId}/notifications/list`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update the notifications list
                    updateNotificationsList(data.notifications);
                    console.log('Notifications list updated successfully');
                } else {
                    console.error('Failed to update notifications list:', data.message);
                }
            })
            .catch(error => {
                console.error('Failed to update notifications list:', error);
            });
    }
    
    // Function to update the notifications list in the UI
    function updateNotificationsList(notifications) {
        const notificationsContainer = document.querySelector('#notifications-content .space-y-3');
        if (!notificationsContainer) return;
        
        // Clear existing notifications
        notificationsContainer.innerHTML = '';
        
        if (notifications.length === 0) {
            // Show "no notifications" message
            notificationsContainer.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-sm" style="color: var(--text-secondary);">No notifications sent yet</p>
                </div>
            `;
            return;
        }
        
        // Add updated notifications
        notifications.forEach(notification => {
            const notificationElement = createNotificationElement(notification);
            notificationsContainer.appendChild(notificationElement);
        });
    }
    
    // Function to create a notification element
    function createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between p-3 rounded-lg border';
        div.style.cssText = 'background: var(--bg-secondary); border-color: var(--border-primary);';
        
        const statusColor = getStatusColor(notification.status);
        const statusText = getStatusText(notification.status);
        
        div.innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-2 h-2 rounded-full ${statusColor}"></div>
                <div>
                    <div class="text-sm font-medium" style="color: var(--text-primary);">
                        ${notification.guest_name || 'Unknown Guest'}
                    </div>
                    <div class="text-xs" style="color: var(--text-secondary);">
                        ${notification.channel.charAt(0).toUpperCase() + notification.channel.slice(1)} • ${notification.type.charAt(0).toUpperCase() + notification.type.slice(1)}
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs" style="color: var(--text-secondary);">
                    ${formatTime(notification.created_at)}
                </div>
                <div class="text-xs font-medium ${statusColor.replace('bg-', 'text-')}">
                    ${statusText}
                </div>
            </div>
        `;
        
        return div;
    }
    
    // Function to get status color class
    function getStatusColor(status) {
        switch (status) {
            case 'read': return 'bg-emerald-500';
            case 'delivered':
            case 'sent': return 'bg-green-500';
            case 'queued':
            case 'sending':
            case 'pending': return 'bg-orange-500';
            case 'failed':
            case 'undelivered':
            case 'canceled':
            case 'bounced': return 'bg-red-500';
            default: return 'bg-orange-500';
        }
    }
    
    // Function to get status text
    function getStatusText(status) {
        switch (status) {
            case 'read': return 'Read';
            case 'delivered':
            case 'sent': return 'Delivered';
            case 'queued':
            case 'sending':
            case 'pending': return 'Queued';
            case 'failed':
            case 'undelivered':
            case 'canceled':
            case 'bounced': return 'Failed';
            default: return 'Queued';
        }
    }
    
    // Function to format time
    function formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + 'm ago';
        if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + 'h ago';
        return date.toLocaleDateString();
    }
    
    // Function to update notification counts
    function updateNotificationCounts() {
        // This is handled by updateNotificationStats()
        console.log('Notification counts updated via stats refresh');
    }
    
    // Function to show notifications (if not already defined)
    function showNotification(message, type = 'info') {
        // Check if showNotification function exists, otherwise use alert
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
        } else {
            alert(message);
        }
    }
});

// Notification Modal Functions
function openSendNotificationModal() {
    console.log('Opening notification modal...');
    showModal('send-notification-modal');
}

function closeSendNotificationModal() {
    console.log('Closing notification modal...');
    hideModal('send-notification-modal');
}

function testRoute() {
    console.log('Testing route...');
    const testUrl = '{{ route("organizer.events.notifications.test", $event) }}';
    console.log('Test URL:', testUrl);
    
    fetch(testUrl, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Test route response:', data);
        alert('Test route working! Event ID: ' + data.event_id);
    })
    .catch(error => {
        console.error('Test route error:', error);
        alert('Test route failed: ' + error.message);
    });
}



function toggleNotificationDetails() {
    const details = document.getElementById('notification-details');
    const button = event.target.closest('button');
    const icon = button.querySelector('svg');
    
    if (details.classList.contains('hidden')) {
        details.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        details.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

function useTemplate(templateType) {
    const messageField = document.getElementById('notification_message');
    const typeField = document.getElementById('notification_type');
    
    let message = '';
    let type = '';
    
    switch (templateType) {
        case 'reminder':
            type = 'event_reminder';
            message = `Hi! Just a friendly reminder about our upcoming event. We're looking forward to seeing you there!`;
            break;
        case 'update':
            type = 'event_update';
            message = `Important update about our event. Please check the latest details and let us know if you have any questions.`;
            break;
        case 'welcome':
            type = 'custom';
            message = `Welcome to our event! We're excited to have you join us. If you need any information, feel free to reach out.`;
            break;
        case 'custom':
            type = 'custom';
            message = '';
            break;
    }
    
    if (typeField) typeField.value = type;
    if (messageField) messageField.value = message;
    
    // Focus on message field if it's custom
    if (templateType === 'custom' && messageField) {
        messageField.focus();
    }
}



// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    // Debug button click
    const sendBtn = document.getElementById('send-notification-btn');
    if (sendBtn) {
        sendBtn.addEventListener('click', function(e) {
            console.log('Send notification button clicked!');
        });
    }
    
    const form = document.getElementById('send-notification-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submission started...');
            
            const formData = new FormData(this);
            const platforms = formData.getAll('platforms[]');
            const message = formData.get('message');
            const type = formData.get('type');
            
            console.log('Form data:', { platforms, message, type });
            console.log('Form action:', this.action);
            
            // Debug: Log all form data
            console.log('=== FORM DATA DEBUG ===');
            for (let [key, value] of formData.entries()) {
                console.log(`Form field ${key}:`, value);
            }
            console.log('=== END FORM DATA DEBUG ===');
            
            // Also log the raw form element
            console.log('Form element:', this);
            console.log('Form action attribute:', this.action);
            
            if (platforms.length === 0) {
                alert('Please select at least one notification platform.');
                return;
            }
            
            if (!message.trim()) {
                alert('Please enter a message.');
                return;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('send-notification-submit-btn');
            if (!submitBtn) {
                console.error('Submit button not found!');
                alert('Error: Submit button not found');
                return;
            }
            
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;
            
            console.log('Submitting to:', this.action);
            
            // Submit the form
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            console.log('CSRF Token:', csrfToken);
            console.log('Form action URL:', this.action);
            
            // Log the actual request being made
            const requestData = {
                method: 'POST',
                url: this.action,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            };
            console.log('Request data:', requestData);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                console.log('Response URL:', response.url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    closeSendNotificationModal();
                    // Show success message
                    if (typeof showNotification === 'function') {
                        showNotification('Notifications sent successfully!', 'success');
                    } else {
                        alert('Notifications sent successfully!');
                    }
                    // Refresh the page to show new notifications
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to send notifications');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                console.error('Error details:', {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
                
                if (typeof showNotification === 'function') {
                    showNotification(error.message || 'Failed to send notifications', 'error');
                } else {
                    alert('Error: ' + error.message || 'Failed to send notifications');
                }
            })
            .finally(() => {
                // Reset button state
                if (submitBtn) {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            });
        });
    }
});
</script>
@endif

@endsection 