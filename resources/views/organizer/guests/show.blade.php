@extends('layouts.organizer')

@section('title', $guest->name . ' - ' . $event->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-primary">Guest Details</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $guest->name }} at {{ $event->name }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="refresh-guest-data" class="btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                    <a href="{{ route('organizer.events.show', $event) }}" class="btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Event
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Guest Profile -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center mb-6">
                            <div class="flex-shrink-0 h-16 w-16">
                                <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-xl font-medium text-gray-700">{{ substr($guest->name, 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="ml-6">
                                <h3 class="text-2xl font-bold text-primary">{{ $guest->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $guest->email ?: 'No email provided' }}</p>
                                <div class="flex items-center mt-2 space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Guest
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $guestList->name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Full Name</label>
                                <input type="text" value="{{ $guest->name }}" class="form-input" readonly>
                            </div>
                            <div>
                                <label class="form-label">Email Address</label>
                                <input type="email" value="{{ $guest->email ?: 'Not provided' }}" class="form-input" readonly>
                            </div>
                            <div>
                                <label class="form-label">Phone Number</label>
                                <input type="text" value="{{ $guest->phone ?: 'Not provided' }}" class="form-input" readonly>
                            </div>
                            <div>
                                <label class="form-label">Guest List</label>
                                <input type="text" value="{{ $guestList->name }}" class="form-input" readonly>
                            </div>
                            @if($group)
                            <div>
                                <label class="form-label">Group</label>
                                <input type="text" value="{{ $group->name }}" class="form-input" readonly>
                            </div>
                            @endif
                            @if($guest->notes)
                            <div class="md:col-span-2">
                                <label class="form-label">Notes</label>
                                <textarea class="form-input" rows="3" readonly>{{ $guest->notes }}</textarea>
                            </div>
                            @endif
                        </div>

                        <div class="mt-8">
                            <h4 class="text-lg leading-6 font-medium text-primary mb-4">Event Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Event Name</label>
                                    <input type="text" value="{{ $event->name }}" class="form-input" readonly>
                                </div>
                                <div>
                                    <label class="form-label">Date & Time</label>
                                    <input type="text" value="{{ $event->start_date->format('M j, Y \a\t g:i A') }}" class="form-input" readonly>
                                </div>
                                @if($event->venue_name || $event->venue_address)
                                <div class="md:col-span-2">
                                    <label class="form-label">Location</label>
                                    <input type="text" value="{{ $event->venue_name ? $event->venue_name . ($event->venue_address ? ' - ' . $event->venue_address : '') : $event->venue_address }}" class="form-input" readonly>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-8">
                            <h4 class="text-lg leading-6 font-medium text-primary mb-4">RSVP & Check-in Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">RSVP Status</label>
                                    <input type="text" id="rsvp-status-display" value="@switch($rsvpHistory['status'] ?? 'no_response')@case('yes')✓ Confirmed@break@case('no')✗ Declined@break@case('maybe')? Maybe@break@default○ No Response@endswitch" class="form-input" readonly>
                                </div>
                                <div>
                                    <label class="form-label">Check-in Status</label>
                                    <input type="text" id="checkin-status-display" value="@if($checkInInfo['checked_in'])✓ Checked In@else○ Not Checked In@endif" class="form-input" readonly>
                                </div>
                                <div>
                                    <label class="form-label">Response Date</label>
                                    <input type="text" id="rsvp-date-display" value="@if($rsvpHistory['submitted_at'] ?? false){{ \Carbon\Carbon::parse($rsvpHistory['submitted_at'])->format('M j, Y \a\t g:i A') }}@elseNot submitted yet@endif" class="form-input" readonly>
                                </div>
                                <div>
                                    <label class="form-label">Check-in Time</label>
                                    <input type="text" id="checkin-time-display" value="@if($checkInInfo['checked_in_at']){{ \Carbon\Carbon::parse($checkInInfo['checked_in_at'])->format('M j, Y \a\t g:i A') }}@elseNot checked in yet@endif" class="form-input" readonly>
                                </div>
                                @if($rsvpHistory['note'] ?? false)
                                <div class="md:col-span-2">
                                    <label class="form-label">RSVP Note</label>
                                    <textarea id="rsvp-note-display" class="form-input" rows="2" readonly>{{ $rsvpHistory['note'] }}</textarea>
                                </div>
                                @endif
                                @if($checkInInfo['checked_in_by'])
                                <div class="md:col-span-2">
                                    <label class="form-label">Checked in by</label>
                                    <input type="text" value="{{ $checkInInfo['checked_in_by']->name }}" class="form-input" readonly>
                                </div>
                                @endif
                                @if($invitation)
                                <div class="md:col-span-2">
                                    <label class="form-label">Invitation Status</label>
                                    <input type="text" value="@if($invitation->status === 'sent')✓ Sent@elseif($invitation->status === 'pending')⏳ Pending@else○ Not Sent@endif@if($invitation->status === 'sent' && $invitation->sent_at) - {{ $invitation->sent_at->format('M j, Y \a\t g:i A') }}@endif" class="form-input" readonly>
                                </div>
                                @endif
                            </div>
                        </div>
                                        </div>
                </div>
            </div>

            <!-- Guest Stats -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-primary mb-4">Guest Statistics</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Added to Event</span>
                                <span class="text-sm font-medium text-primary">{{ $guest->created_at->format('M j, Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Last Updated</span>
                                <span class="text-sm font-medium text-primary">{{ $guest->updated_at->format('M j, Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Guest List</span>
                                <span class="text-sm font-medium text-primary">{{ $guestList->name }}</span>
                            </div>
                            @if($group)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Group</span>
                                <span class="text-sm font-medium text-primary">{{ $group->name }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">RSVP Status</span>
                                <span class="text-sm font-medium text-primary">
                                    @switch($rsvpHistory['status'] ?? 'no_response')
                                        @case('yes')✓ Confirmed@break
                                        @case('no')✗ Declined@break
                                        @case('maybe')? Maybe@break
                                        @default○ No Response
                                    @endswitch
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Check-in Status</span>
                                <span class="text-sm font-medium text-primary">
                                    @if($checkInInfo['checked_in'])✓ Checked In@else○ Not Checked In@endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg mt-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-primary mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('organizer.events.show', $event) }}" class="w-full btn-secondary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Event
                            </a>
                            @if($invitation && $invitation->token && !str_starts_with($invitation->token, 'preview-'))
                            <a href="{{ route('public.invite.show', $invitation->token) }}" target="_blank" class="w-full btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Invitation
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Last Updated -->
                <div class="bg-white shadow rounded-lg mt-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-primary mb-4">Real-time Updates</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">RSVP Last Updated</span>
                                <span id="rsvp-last-updated" class="text-sm font-medium text-primary">Just now</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Auto-refresh</span>
                                <span class="text-sm font-medium text-green-600">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventId = {{ $event->id }};
    const guestId = {{ $guest->id }};
    let refreshInterval;

    // Function to update RSVP information
    function updateRsvpInfo() {
        fetch(`/organizer/events/${eventId}/guests/${guestId}/rsvp-history`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const history = data.rsvp_history;
                    
                                         // Update RSVP status
                     const statusDisplay = document.getElementById('rsvp-status-display');
                     const statusText = getStatusText(history.status);
                     
                     statusDisplay.value = `${statusText.symbol} ${statusText.text}`;
                    
                                         // Update RSVP date
                     const dateDisplay = document.getElementById('rsvp-date-display');
                     if (history.submitted_at) {
                         const date = new Date(history.submitted_at);
                         dateDisplay.value = date.toLocaleDateString('en-US', { 
                             month: 'short', 
                             day: 'numeric', 
                             year: 'numeric' 
                         }) + ' at ' + date.toLocaleTimeString('en-US', { 
                             hour: 'numeric', 
                             minute: '2-digit' 
                         });
                     } else {
                         dateDisplay.value = 'Not submitted yet';
                     }
                    
                                         // Update RSVP note
                     const noteDisplay = document.getElementById('rsvp-note-display');
                     if (noteDisplay) {
                         if (history.note) {
                             noteDisplay.value = history.note;
                             noteDisplay.parentElement.style.display = 'block';
                         } else {
                             noteDisplay.parentElement.style.display = 'none';
                         }
                     }
                    
                                         // Update last updated time
                     const lastUpdated = new Date(history.last_updated);
                     document.getElementById('rsvp-last-updated').textContent = lastUpdated.toLocaleTimeString();
                }
            })
            .catch(error => {
                console.error('Error updating RSVP info:', error);
            });
    }

    // Function to update check-in information
    function updateCheckInInfo() {
        fetch(`/organizer/events/${eventId}/guests/${guestId}/check-in-status`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const checkIn = data.check_in_status;
                    
                                         // Update check-in status
                     const statusDisplay = document.getElementById('checkin-status-display');
                     if (checkIn.checked_in) {
                         statusDisplay.value = '✓ Checked In';
                     } else {
                         statusDisplay.value = '○ Not Checked In';
                     }
                    
                                         // Update check-in time
                     const timeDisplay = document.getElementById('checkin-time-display');
                     if (checkIn.checked_in_at) {
                         const date = new Date(checkIn.checked_in_at);
                         timeDisplay.value = date.toLocaleDateString('en-US', { 
                             month: 'short', 
                             day: 'numeric', 
                             year: 'numeric' 
                         }) + ' at ' + date.toLocaleTimeString('en-US', { 
                             hour: 'numeric', 
                             minute: '2-digit' 
                         });
                     } else {
                         timeDisplay.value = 'Not checked in yet';
                     }
                }
            })
            .catch(error => {
                console.error('Error updating check-in info:', error);
            });
    }

    function getStatusText(status) {
        switch (status) {
            case 'yes': return { text: 'Confirmed', color: 'text-green-600', symbol: '✓' };
            case 'no': return { text: 'Declined', color: 'text-red-600', symbol: '✗' };
            case 'maybe': return { text: 'Maybe', color: 'text-yellow-600', symbol: '?' };
            default: return { text: 'No Response', color: 'text-gray-500', symbol: '○' };
        }
    }

    // Manual refresh button
    document.getElementById('refresh-guest-data').addEventListener('click', function() {
        updateRsvpInfo();
        updateCheckInInfo();
    });

    // Set up auto-refresh every 30 seconds
    refreshInterval = setInterval(() => {
        updateRsvpInfo();
        updateCheckInInfo();
    }, 30000);

    // Clean up interval on page unload
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
});
</script>

@endsection
