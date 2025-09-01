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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                    {{ $guest->name }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                    {{ $guest->email ?: 'Not provided' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                    {{ $guest->phone ?: 'Not provided' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Guest List</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                    {{ $guestList->name }}
                                </div>
                            </div>
                            @if(isset($group) && $group)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Group</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                    {{ $group->name }}
                                </div>
                            </div>
                            @endif
                            @if(isset($guest) && $guest->notes)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 min-h-[80px]">
                                    {{ $guest->notes }}
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="mt-8">
                            <h4 class="text-lg leading-6 font-medium text-primary mb-4">Event Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Event Name</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                        {{ $event->name }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date & Time</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                        {{ $event->start_date->format('M j, Y \a\t g:i A') }}
                                    </div>
                                </div>
                                @if(isset($event) && ($event->venue_name || $event->venue_address))
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                        {{ $event->venue_name ? $event->venue_name . ($event->venue_address ? ' - ' . $event->venue_address : '') : $event->venue_address }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-8">
                            <h4 class="text-lg leading-6 font-medium text-primary mb-4">RSVP & Check-in Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">RSVP Status</label>
                                    <div id="rsvp-status-display" class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                        @switch(isset($rsvpHistory) ? ($rsvpHistory['status'] ?? 'no_response') : 'no_response')
                                            @case('yes')
                                                <span class="inline-flex items-center text-green-700">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Confirmed
                                                </span>
                                                @break
                                            @case('no')
                                                <span class="inline-flex items-center text-red-700">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Declined
                                                </span>
                                                @break
                                            @case('maybe')
                                                <span class="inline-flex items-center text-yellow-700">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Maybe
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center text-gray-500">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    No Response
                                                </span>
                                        @endswitch
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Status</label>
                                    <div id="checkin-status-display" class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                        @if(isset($checkInInfo) && $checkInInfo['checked_in'])
                                            <span class="inline-flex items-center text-green-700">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Checked In
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-gray-500">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Not Checked In
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Response Date</label>
                                    <div id="rsvp-date-display" class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                        @if(isset($rsvpHistory) && ($rsvpHistory['submitted_at'] ?? false))
                                            {{ \Carbon\Carbon::parse($rsvpHistory['submitted_at'])->format('M j, Y \a\t g:i A') }}
                                        @else
                                            <span class="text-gray-500 italic">Not submitted yet</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Time</label>
                                    <div id="checkin-time-display" class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                        @if(isset($checkInInfo) && $checkInInfo['checked_in_at'])
                                            {{ \Carbon\Carbon::parse($checkInInfo['checked_in_at'])->format('M j, Y \a\t g:i A') }}
                                        @else
                                            <span class="text-gray-500 italic">Not checked in yet</span>
                                        @endif
                                    </div>
                                </div>
                                @if(isset($rsvpHistory) && ($rsvpHistory['note'] ?? false))
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">RSVP Note</label>
                                    <div id="rsvp-note-display" class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 min-h-[60px]">
                                        {{ $rsvpHistory['note'] }}
                                    </div>
                                </div>
                                @endif
                                @if(isset($checkInInfo) && $checkInInfo['checked_in_by'])
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Checked in by</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                        {{ $checkInInfo['checked_in_by']->name ?? $checkInInfo['checked_in_by']->scanner_name ?? 'Unknown' }}
                                    </div>
                                </div>
                                @endif
                                @if(isset($invitation) && $invitation)
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Invitation Status</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                                        @switch($invitation->status)
                                            @case('sent')
                                                <span class="inline-flex items-center text-green-700">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                                    </svg>
                                                    Sent
                                                    @if($invitation->sent_at)
                                                        - {{ $invitation->sent_at->format('M j, Y \a\t g:i A') }}
                                                    @endif
                                                </span>
                                                @break
                                            @case('pending')
                                                <span class="inline-flex items-center text-yellow-700">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Pending
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center text-gray-500">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Not Sent
                                                </span>
                                        @endswitch
                                    </div>
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
                            @if(isset($group) && $group)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Group</span>
                                <span class="text-sm font-medium text-primary">{{ $group->name }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">RSVP Status</span>
                                <span class="text-sm font-medium text-primary">
                                    @switch($rsvpHistory['status'] ?? 'no_response')
                                        @case('yes')
                                            ✓ Confirmed
                                            @break
                                        @case('no')
                                            ✗ Declined
                                            @break
                                        @case('maybe')
                                            ? Maybe
                                            @break
                                        @default
                                            ○ No Response
                                    @endswitch
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Check-in Status</span>
                                <span class="text-sm font-medium text-primary">
                                    @if(isset($checkInInfo) && $checkInInfo['checked_in'])
                                        ✓ Checked In
                                    @else
                                        ○ Not Checked In
                                    @endif
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
                            @if(isset($invitation) && $invitation)
                            <a href="{{ route('public.invite.show', 'preview-' . $invitation->id) }}" target="_blank" class="w-full btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Preview Invitation
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
                     
                     statusDisplay.innerHTML = `<span class="inline-flex items-center ${statusText.color}">
                         <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                             ${statusText.icon}
                         </svg>
                         ${statusText.text}
                     </span>`;
                    
                                         // Update RSVP date
                     const dateDisplay = document.getElementById('rsvp-date-display');
                     if (history.submitted_at) {
                         const date = new Date(history.submitted_at);
                         dateDisplay.textContent = date.toLocaleDateString('en-US', { 
                             month: 'short', 
                             day: 'numeric', 
                             year: 'numeric' 
                         }) + ' at ' + date.toLocaleTimeString('en-US', { 
                             hour: 'numeric', 
                             minute: '2-digit' 
                         });
                     } else {
                         dateDisplay.innerHTML = '<span class="text-gray-500 italic">Not submitted yet</span>';
                     }
                    
                                         // Update RSVP note
                     const noteDisplay = document.getElementById('rsvp-note-display');
                     if (noteDisplay) {
                         if (history.note) {
                             noteDisplay.textContent = history.note;
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
                         statusDisplay.innerHTML = '<span class="inline-flex items-center text-green-700"><svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Checked In</span>';
                     } else {
                         statusDisplay.innerHTML = '<span class="inline-flex items-center text-gray-500"><svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>Not Checked In</span>';
                     }
                    
                                         // Update check-in time
                     const timeDisplay = document.getElementById('checkin-time-display');
                     if (checkIn.checked_in_at) {
                         const date = new Date(checkIn.checked_in_at);
                         timeDisplay.textContent = date.toLocaleDateString('en-US', { 
                             month: 'short', 
                             day: 'numeric', 
                             year: 'numeric' 
                         }) + ' at ' + date.toLocaleTimeString('en-US', { 
                             hour: 'numeric', 
                             minute: '2-digit' 
                         });
                     } else {
                         timeDisplay.innerHTML = '<span class="text-gray-500 italic">Not checked in yet</span>';
                     }
                }
            })
            .catch(error => {
                console.error('Error updating check-in info:', error);
            });
    }

    function getStatusText(status) {
        switch (status) {
            case 'yes': return { 
                text: 'Confirmed', 
                color: 'text-green-700', 
                icon: '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>' 
            };
            case 'no': return { 
                text: 'Declined', 
                color: 'text-red-700', 
                icon: '<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>' 
            };
            case 'maybe': return { 
                text: 'Maybe', 
                color: 'text-yellow-700', 
                icon: '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>' 
            };
            default: return { 
                text: 'No Response', 
                color: 'text-gray-500', 
                icon: '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>' 
            };
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
