@extends('layouts.organizer')

@section('title', $event->name)

@section('content')

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
</div>

@if($event->rsvp_enabled)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventId = {{ $event->id }};
    let refreshInterval;

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
});
</script>
@endif

@endsection 