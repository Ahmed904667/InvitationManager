@php
    // Handle variables that might not be set
    $isDraft = isset($isDraft) ? $isDraft : false;
    $isCompleted = isset($isCompleted) ? $isCompleted : false;
    
    // Get user timezone for date conversion
    $userTimezone = auth()->user()->timezone ?? 'UTC';
    
    $cardClasses = 'rounded-lg shadow-sm border p-6';
    $iconClasses = 'h-10 w-10 rounded-full flex items-center justify-center';
    
    if ($isDraft) {
        $cardClasses .= ' border-yellow-300 bg-yellow-50';
        $iconClasses .= ' bg-yellow-100';
    } elseif ($isCompleted) {
        $cardClasses .= ' border-secondary bg-secondary';
        $iconClasses .= ' bg-tertiary';
    } else {
        $cardClasses .= ' border-primary bg-secondary';
        $iconClasses .= ' bg-tertiary';
    }
@endphp

<div class="{{ $cardClasses }}" data-event-id="{{ $event->id }}">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <div class="{{ $iconClasses }}">
                @if($isDraft)
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                @elseif($isCompleted)
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @else
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                @endif
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-primary">{{ $event->name ?: 'Untitled Event' }}</h3>
                <p class="text-sm text-secondary">{{ Str::limit($event->description, 50) ?: 'No description' }}</p>
            </div>
        </div>
        <span class="badge badge-{{ $event->status }}">{{ ucfirst($event->status) }}</span>
    </div>
    
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <p class="text-sm font-medium text-secondary">Active Guests</p>
            <p class="text-lg font-bold text-primary">
                {{ $event->active_guests_count ?? 0 }}
            </p>
        </div>
        <div>
            <p class="text-sm font-medium text-secondary">{{ $isCompleted ? 'Attended' : 'Active Lists' }}</p>
            <p class="text-lg font-bold text-primary">
                @if($isCompleted)
                    @php
                        $attendedCount = $event->invitations->where('rsvp_status', 'yes')->count();
                    @endphp
                    {{ $attendedCount }}
                @else
                    {{ $event->active_guest_lists_count ?? 0 }}
                @endif
            </p>
        </div>
    </div>
    
    @if($isCompleted)
        <div class="mb-4 p-3 bg-tertiary rounded-md">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-sm font-medium text-secondary">RSVP Yes</p>
                    <p class="text-lg font-bold text-green-600">
                        @php
                            // Use active guests for accurate RSVP counts
                            $rsvpYes = 0;
                            if (isset($event->active_guests)) {
                                foreach ($event->active_guests as $eventGuest) {
                                    $guest = $eventGuest->guest;
                                    $invitation = $guest->invitations->where('event_id', $event->id)->first();
                                    if ($invitation && $invitation->rsvp_status === 'yes') {
                                        $rsvpYes++;
                                    }
                                }
                            }
                        @endphp
                        {{ $rsvpYes }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-secondary">RSVP No</p>
                    <p class="text-lg font-bold text-red-600">
                        @php
                            // Use active guests for accurate RSVP counts
                            $rsvpNo = 0;
                            if (isset($event->active_guests)) {
                                foreach ($event->active_guests as $eventGuest) {
                                    $guest = $eventGuest->guest;
                                    $invitation = $guest->invitations->where('event_id', $event->id)->first();
                                    if ($invitation && $invitation->rsvp_status === 'no') {
                                        $rsvpNo++;
                                    }
                                }
                            }
                        @endphp
                        {{ $rsvpNo }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-secondary">No Response</p>
                    <p class="text-lg font-bold text-tertiary">
                        @php
                            // Use active guests for accurate RSVP counts
                            $rsvpNoResponse = 0;
                            if (isset($event->active_guests)) {
                                foreach ($event->active_guests as $eventGuest) {
                                    $guest = $eventGuest->guest;
                                    $invitation = $guest->invitations->where('event_id', $event->id)->first();
                                    if (!$invitation || !$invitation->rsvp_status || $invitation->rsvp_status === 'none') {
                                        $rsvpNoResponse++;
                                    }
                                }
                            }
                        @endphp
                        {{ $rsvpNoResponse }}
                    </p>
                </div>
            </div>
        </div>
    @endif
    
    <div class="mb-4 p-3 {{ $isDraft ? 'bg-yellow-100' : 'bg-tertiary' }} rounded-md">
        @if($event->start_date)
            <div class="flex items-center text-sm text-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                {{ $event->start_date->setTimezone($userTimezone)->format('D, M j, Y') }}
            </div>
            <div class="flex items-center text-sm text-secondary mt-1">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ $event->start_date->setTimezone($userTimezone)->format('g:i A') }}
            </div>
        @else
            <div class="flex items-center text-sm text-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-yellow-600 font-medium">Date not set</span>
            </div>
        @endif
        
        @if($event->venue_name || $event->venue_address)
            <div class="flex items-center text-sm text-secondary mt-1">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                {{ $event->venue_name ?: Str::limit($event->venue_address, 30) ?: 'Location TBD' }}
            </div>
        @else
            <div class="flex items-center text-sm text-secondary mt-1">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-yellow-600 font-medium">Location not set</span>
            </div>
        @endif
        
        @if($isDraft)
            <div class="flex items-center text-sm text-yellow-600 mt-2 font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Last saved: {{ $event->updated_at->setTimezone($userTimezone)->diffForHumans() }}
            </div>
        @elseif($isCompleted)
            <div class="flex items-center text-sm text-secondary mt-2 font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Completed: {{ $event->updated_at->setTimezone($userTimezone)->diffForHumans() }}
            </div>
        @endif
    </div>
    
    <div class="flex space-x-2">
        @if($isDraft)
            <a href="{{ route('organizer.events.continue', $event) }}" class="flex-1 btn-primary inline-flex items-center justify-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Continue Editing
            </a>
        @elseif($isCompleted)
            <a href="{{ route('organizer.events.show', $event) }}" class="flex-1 btn-secondary inline-flex items-center justify-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                View Results
            </a>
        @else
            <a href="{{ route('organizer.events.show', $event) }}" class="flex-1 btn-primary inline-flex items-center justify-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                View Details
            </a>
        @endif
        
        @if(!$isDraft && !$isCompleted && !in_array($event->status, ['cancelled', 'running']))
            <a href="{{ route('organizer.events.edit', $event) }}" class="btn-secondary" title="Edit Event">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </a>
        @endif
        
        @if($event->canUseScanner())
            <button class="btn-accent" onclick="generateScannerUrl({{ $event->id }})" title="Generate Scanner URL">
            <svg class="w-6 h-6 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="8" height="8" />
                                <path d="M6 6h.01" />
                                <rect x="14" y="2" width="8" height="8" />
                                <path d="M18 6h.01" />
                                <rect x="2" y="14" width="8" height="8" />
                                <path d="M6 18h.01" />
                                <path d="M14 14h.01" />
                                <path d="M18 18h.01" />
                                <path d="M18 22h4v-4" />
                                <path d="M14 18v4" />
                                <path d="M22 14h-4" />
                            </svg>
            </button>
        @endif
        
        @php
            $canDelete = in_array($event->status, ['draft', 'completed', 'cancelled']);
            $canCancel = !in_array($event->status, ['running', 'completed', 'cancelled', 'draft']);
        @endphp
        
        @if($canDelete)
            <button class="btn-danger" onclick="confirmDeleteEvent({{ $event->id }}, '{{ $event->name ?: 'Untitled Event' }}')" title="Delete Event">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        @elseif($canCancel)
            <button class="btn-warning" onclick="showCancelEventModal({{ $event->id }}, '{{ $event->name ?: 'Untitled Event' }}')" title="Cancel Event">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        @endif
    </div>
</div>
