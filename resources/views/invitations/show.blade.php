@extends('layouts.guest-invite')

@section('content')
<div class="min-h-screen invite-page-bg">
    @if((isset($isPreview) && $isPreview))
    <!-- Preview Banner -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4 shadow-lg">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" clip-rule="evenodd"></path>
                    <path fill-rule="evenodd" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-medium">Preview Mode - This is how your invitation will appear to guests</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('organizer.events.show', $event) }}" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                </a>
                <a href="{{ route('organizer.events.edit', $event) }}" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
    @endif
    @php $currentRsvp = $invitation->rsvp_status ?? 'none'; @endphp
    @if($currentRsvp !== 'none')
    <!-- Floating RSVP Status Banner -->
    <div class="fixed {{ (isset($isPreview) && $isPreview) ? 'top-16' : 'top-0' }} left-0 right-0 z-40 bg-gradient-to-r from-emerald-600 to-green-600 text-white p-4 shadow-lg">
        <div class="max-w-4xl mx-auto flex items-start justify-between">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <div class="font-semibold">Your current RSVP is {{ strtoupper($currentRsvp) }}.</div>
                    @if($invitation->rsvp_at)
                    <div class="text-white/90 text-sm">Submitted on {{ \Carbon\Carbon::parse($invitation->rsvp_at)->format('M j, Y \a\t g:i A') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    @php
        // Get organizer's timezone for calendar events
        $organizerTimezone = $event->user->timezone ?? 'UTC';
        
        // Get guest's timezone for reminders
        $guestTimezone = $guest->timezone ?? 'UTC';
        
        // Convert event times to organizer's timezone for calendar
        $start = \Carbon\Carbon::parse($event->start_date)->setTimezone($organizerTimezone);
        $end = !empty($event->end_date) ? \Carbon\Carbon::parse($event->end_date)->setTimezone($organizerTimezone) : (clone $start)->addHour();
        
        // Convert event times to guest's timezone for reminders
        $startGuestTime = \Carbon\Carbon::parse($event->start_date)->setTimezone($guestTimezone);
        $endGuestTime = !empty($event->end_date) ? \Carbon\Carbon::parse($event->end_date)->setTimezone($guestTimezone) : (clone $startGuestTime)->addHour();
        
        // Convert to UTC for calendar API (Google Calendar expects UTC)
        $startUtc = $start->utc();
        $endUtc = $end->utc();
        $datesParam = $startUtc->format('Ymd\\THis\\Z') . '/' . $endUtc->format('Ymd\\THis\\Z');
        $summary = urlencode($event->name ?? 'Event');
        $details = urlencode(($event->description ?? '') . "\n\nMore info: " . ($inviteUrl ?? ''));
        $locationParam = urlencode($event->venue_address ?: ($event->location ?: ''));
        $googleCalUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$summary}&dates={$datesParam}&details={$details}&location={$locationParam}";
        $rsvpEnabled = (bool) ($event->rsvp_enabled ?? false);
        $currentRsvp = $invitation->rsvp_status ?? 'none';
    @endphp
    
    <!-- Hero Section with Event Details -->
    <div class="relative overflow-hidden invite-hero @if(((isset($isPreview) && $isPreview)) && $currentRsvp !== 'none') pt-36 @elseif(((isset($isPreview) && $isPreview)) || $currentRsvp !== 'none') pt-20 @endif">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative max-w-4xl mx-auto px-6 py-16 text-center">
            <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium mb-6">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                You're Invited
            </div>
            <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">{{ $event->name }}</h1>
            @php
                // Get organizer's timezone
                $organizerTimezone = $event->user->timezone ?? 'UTC';
                
                // Convert event times to organizer's timezone
                $startTime = \Carbon\Carbon::parse($event->start_date)->setTimezone($organizerTimezone);
                $endTime = !empty($event->end_date) ? \Carbon\Carbon::parse($event->end_date)->setTimezone($organizerTimezone) : null;
                
                // Format timezone abbreviation
                $timezoneAbbr = $startTime->format('T');
            @endphp
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6 text-xl">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $startTime->format('l, F j, Y') }}
                </div>
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $startTime->format('g:i A') }}
            @if($endTime)
                @if($startTime->format('Y-m-d') !== $endTime->format('Y-m-d'))
                        - {{ $endTime->format('M j, g:i A') }}
                @else
                        - {{ $endTime->format('g:i A') }}
                @endif
            @endif
                    <span class="ml-2 text-lg font-medium text-white/90">({{ $timezoneAbbr }})</span>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-16 hero-fade"></div>
    </div>

    <div class="max-w-4xl mx-auto px-6 -mt-8 relative z-10">

    @if(session('success'))
            <div class="mb-8 p-6 rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 shadow-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-green-800 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
        </div>
    @endif

    <div class="space-y-8">
        @if(!empty($event->description))
                <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-gray-100 shadow-xl hover:shadow-2xl transition-all duration-300">
                    <div class="flex items-center mb-6">
                        <div class="p-3 bg-blue-100 rounded-2xl mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">About the Event</h2>
                    </div>
                    <p class="text-gray-700 text-lg leading-relaxed">{{ $event->description }}</p>
            </div>
        @endif

        <!-- QR and Calendar Cards - Show only for "yes" or "maybe" RSVPs -->
        @if($currentRsvp === 'yes' || $currentRsvp === 'maybe')
            <!-- QR Check-in Card -->
            @if($event->qr_checkin_enabled)
            <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-gray-100 shadow-xl hover:shadow-2xl transition-all duration-300 text-center">
                <div class="flex items-center justify-center mb-6">
                    <div class="p-3 bg-indigo-100 rounded-2xl mr-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zM13 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V4zM9 4a1 1 0 000 2v1a1 1 0 001 1h1a1 1 0 100-2V6a1 1 0 00-1-1H9zM9 13a1 1 0 100 2h1a1 1 0 001 1v1a1 1 0 102 0v-1a1 1 0 001-1h1a1 1 0 100-2h-1a1 1 0 00-1-1v-1a1 1 0 10-2 0v1a1 1 0 00-1 1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">QR Check-in</h2>
                </div>
                @php
                    $qrData = trim(($event->qr_code_url ?: $inviteUrl) ?? '');
                    $qrPrimary = $qrData !== '' ? ('https://chart.googleapis.com/chart?cht=qr&chld=L|0&chs=280x280&chl=' . rawurlencode($qrData)) : '';
                    $qrFallback = $qrData !== '' ? ('https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . rawurlencode($qrData)) : '';
                @endphp
                @if($qrData !== '')
                    <div class="inline-block p-6 bg-white rounded-2xl shadow-lg border-4 border-indigo-100 mb-6">
                        <img src="{{ $qrPrimary }}" alt="QR Code" class="mx-auto rounded-xl" onerror="this.onerror=null;this.src='{{ $qrFallback }}';">
                    </div>
                @else
                    <div class="p-8 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-300 mb-6">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zM13 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V4z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-gray-500 font-medium">QR code not available</p>
                    </div>
                @endif
                @if(!empty($event->qr_description))
                    <p class="text-gray-600 text-lg leading-relaxed">{{ $event->qr_description }}</p>
                @else
                    <p class="text-gray-600 text-lg leading-relaxed">Show this QR code at the entrance for quick check-in.</p>
                @endif
            </div>
            @endif

            <!-- Calendar Card -->
            <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-gray-100 shadow-xl hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center mb-6">
                    <div class="p-3 bg-green-100 rounded-2xl mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Add to Calendar</h2>
                </div>
                <p class="text-gray-600 mb-6">Save this event to your calendar so you don't forget!</p>
                
                <!-- Calendar Options -->
                <div class="space-y-4">
                    <!-- Primary Calendar Options -->
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ $googleCalUrl }}" target="_blank" class="inline-flex items-center px-6 py-3 btn-gradient-success font-medium rounded-2xl transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            Add to Google Calendar
                        </a>
                        <a href="{{ route('public.invite.ics', ['token' => $invitation->token]) }}" class="inline-flex items-center px-6 py-3 btn-gradient-neutral font-medium rounded-2xl transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            Download Calendar File
                        </a>
                    </div>
                    
                    <!-- Additional Calendar Services -->
                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-sm text-gray-600 mb-3">Other calendar options:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @php
                                $outlookUrl = "https://outlook.live.com/calendar/0/deeplink/compose?subject=" . urlencode($event->name) . "&startdt=" . $start->format('Y-m-d\TH:i:s') . "&enddt=" . $end->format('Y-m-d\TH:i:s') . "&body=" . urlencode(($event->description ?? '') . "\n\nLocation: " . ($event->venue_address ?: ($event->location ?: '')) . "\n\nMore info: " . $inviteUrl);
                                $yahooUrl = "https://calendar.yahoo.com/?v=60&view=d&type=20&title=" . urlencode($event->name) . "&st=" . $startUtc->format('Ymd\THis\Z') . "&et=" . $endUtc->format('Ymd\THis\Z') . "&desc=" . urlencode(($event->description ?? '') . "\n\nLocation: " . ($event->venue_address ?: ($event->location ?: '')) . "\n\nMore info: " . $inviteUrl) . "&in_loc=" . urlencode($event->venue_address ?: ($event->location ?: ''));
                            @endphp
                            
                            <a href="{{ $outlookUrl }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 12a2 2 0 104 0 2 2 0 00-4 0z"/>
                                </svg>
                                Outlook
                            </a>
                            
                            <a href="{{ $yahooUrl }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-xl hover:bg-purple-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 12a2 2 0 104 0 2 2 0 00-4 0z"/>
                                </svg>
                                Yahoo
                            </a>
                            
                            @if((isset($isPreview) && $isPreview))
                                <button disabled class="inline-flex items-center justify-center px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-xl cursor-not-allowed">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
                                        <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
                                    </svg>
                                    Copy Details (Preview)
                                </button>
                            @else
                                <button onclick="copyEventDetails()" class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-xl hover:bg-gray-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
                                        <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
                                    </svg>
                                    Copy Details
                                </button>
                            @endif
                            
                            @if((isset($isPreview) && $isPreview))
                                <button disabled class="inline-flex items-center justify-center px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-xl cursor-not-allowed">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Set Reminder (Preview)
                                </button>
                            @else
                                <button onclick="openReminderModal()" class="inline-flex items-center justify-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-xl hover:bg-orange-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Set Reminder
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if(!empty($reminders))
                @php $remindersCount = is_countable($reminders) ? $reminders->count() : 0; @endphp
                @if($remindersCount > 0)
                <!-- Guest Reminders (shown under Add to Calendar) -->
                <div id="guestRemindersSection" class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-gray-100 shadow-xl hover:shadow-2xl transition-all duration-300 mt-8">
                    <div class="flex items-center mb-6">
                        <div class="p-3 bg-orange-100 rounded-2xl mr-4">
                            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Your Reminders</h2>
                            <p class="text-gray-600">Reminders you've set for this event</p>
                        </div>
                    </div>

                    <div id="guestRemindersList" class="space-y-4">
                        @foreach($reminders as $reminder)
                            @php
                                $reminderTime = \Carbon\Carbon::parse($reminder->scheduled_for)->setTimezone($reminder->guest_timezone ?? $guestTimezone);
                                $platformText = $reminder->platform === 'email' ? 'Email' : 'WhatsApp';
                            @endphp
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-200 hover:bg-gray-100 transition-colors" data-reminder-id="{{ $reminder->id }}">
                                <div class="flex items-center">
                                    @if($reminder->platform === 'email')
                                        <div class="p-2 bg-blue-100 rounded-xl mr-4">
                                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="p-2 bg-green-100 rounded-xl mr-4">
                                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-gray-900">
                                            {{ $reminderTime->format('l, F j, Y \a\t g:i A') }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $platformText }} reminder • {{ $reminderTime->format('T') }} timezone
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        Scheduled
                                    </span>
                                    <button 
                                        onclick="deleteReminder({{ $reminder->id }}, this)" 
                                        class="p-2 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-lg transition-colors"
                                        title="Delete reminder"
                                    >
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif
        @endif

            <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-gray-100 shadow-xl hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center mb-6">
                    <div class="p-3 bg-red-100 rounded-2xl mr-4">
                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Location</h2>
                </div>
                <div class="space-y-3 mb-6">
                @if(!empty($event->venue_name))
                        <div class="text-xl font-semibold text-gray-800">{{ $event->venue_name }}</div>
                @endif
                @if(!empty($event->venue_address))
                        <div class="text-gray-600 text-lg flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mr-2 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $event->venue_address }}
                        </div>
                @elseif(!empty($event->location))
                        <div class="text-gray-600 text-lg flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mr-2 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $event->location }}
                        </div>
                @endif
            </div>
            @if(!empty($mapLink))
                    <div class="mt-6 rounded-2xl overflow-hidden border border-gray-200 shadow-lg">
                    <iframe
                        src="https://www.google.com/maps?q={{ urlencode($event->venue_address ?: ($event->location ?: '')) }}&output=embed"
                        width="100%"
                        height="300"
                        style="border:0;"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                    ></iframe>
                </div>
                @php
                    $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($event->venue_address ?: ($event->location ?: '')) . '&travelmode=driving';
                @endphp
                    <div class="flex flex-wrap gap-4 mt-6">
                        <a href="{{ $mapLink }}" target="_blank" class="inline-flex items-center px-6 py-3 btn-gradient-info font-medium rounded-2xl transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"></path>
                            </svg>
                            Open in Google Maps
                        </a>
                        <a href="{{ $directionsUrl }}" target="_blank" class="inline-flex items-center px-6 py-3 btn-gradient-success font-medium rounded-2xl transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            Get Directions
                    </a>
                </div>
            @endif
        </div>

            <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-gray-100 shadow-xl hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center mb-6">
                    <div class="p-3 bg-purple-100 rounded-2xl mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">RSVP</h2>
                        <p class="text-gray-600">Invitation for <span class="font-semibold text-gray-800">{{ $invitation->guest->name ?? $guest->name ?? 'Guest' }}</span></p>
                    </div>
                </div>

            @if(!$rsvpEnabled)
                    <div class="p-6 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-blue-800 font-medium">RSVP is not required for this event.</p>
                                <p class="text-blue-700 text-sm mt-1">Just show up and enjoy!</p>
                            </div>
                        </div>
                </div>
            @else
                @if((isset($isPreview) && $isPreview))
                    <!-- Preview Mode - RSVP Form Disabled -->
                    <div class="p-6 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" clip-rule="evenodd"></path>
                                    <path fill-rule="evenodd" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-blue-800 font-medium">Preview Mode - RSVP functionality is disabled</p>
                                <p class="text-blue-700 text-sm mt-1">This is how the RSVP form will appear to guests.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Show RSVP form as preview (non-functional) -->
                    <div class="space-y-6 opacity-75">
                        <div class="space-y-4">
                            <label class="text-lg font-semibold text-gray-900 block">Will you be attending?</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="cursor-not-allowed p-6 text-center rounded-2xl border-2 border-gray-200 bg-white">
                                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="font-semibold text-gray-900">Yes, I'll be there!</div>
                                    <div class="text-sm text-gray-600 mt-1">Count me in</div>
                                </div>
                                <div class="cursor-not-allowed p-6 text-center rounded-2xl border-2 border-gray-200 bg-white">
                                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-yellow-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="font-semibold text-gray-900">Maybe</div>
                                    <div class="text-sm text-gray-600 mt-1">I'll try to make it</div>
                                </div>
                                <div class="cursor-not-allowed p-6 text-center rounded-2xl border-2 border-gray-200 bg-white">
                                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-red-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="font-semibold text-gray-900">Can't make it</div>
                                    <div class="text-sm text-gray-600 mt-1">Sorry, I can't attend</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-lg font-semibold text-gray-900 mb-3">Additional Notes (Optional)</label>
                            <textarea 
                                rows="4" 
                                class="w-full border-2 border-gray-200 rounded-2xl p-4 resize-none cursor-not-allowed" 
                                placeholder="Let us know about dietary restrictions, plus ones, or any special requests..."
                                disabled
                            ></textarea>
                        </div>
                        <div class="pt-4">
                            <button type="button" disabled class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-gray-400 text-white font-semibold rounded-2xl cursor-not-allowed">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Submit My RSVP (Preview)
                            </button>
                        </div>
                    </div>
                @else
                    @if($currentRsvp !== 'none')
                        <div class="p-6 rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 shadow-sm mb-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-green-800 font-medium">Your current RSVP is <span class="font-bold">{{ strtoupper($currentRsvp) }}</span>.</p>
                                    <p class="text-green-700 text-sm mt-1">
                                        @if($invitation->rsvp_at)
                                            Submitted on {{ \Carbon\Carbon::parse($invitation->rsvp_at)->format('M j, Y \a\t g:i A') }}
                                        @else
                                            You can update your response below if needed.
                                        @endif
                                    </p>
                                    @if($invitation->rsvp_note)
                                        <div class="mt-2 p-3 bg-white rounded-lg border border-green-200">
                                            <p class="text-sm text-gray-700"><strong>Your note:</strong> {{ $invitation->rsvp_note }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('public.invite.rsvp', ['token' => $invitation->token]) }}" method="POST" class="space-y-6">
                    @csrf
                        <div class="space-y-4">
                            <label class="text-lg font-semibold text-gray-900 block">Will you be attending?</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <label class="group relative">
                                    <input type="radio" name="rsvp_status" value="yes" @checked(old('rsvp_status', $invitation->rsvp_status) === 'yes') required class="sr-only peer">
                                    <div class="cursor-pointer p-6 text-center rounded-2xl border-2 border-gray-200 bg-white transition-all duration-200 peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-lg hover:border-green-300 hover:shadow-md">
                                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-green-100 flex items-center justify-center peer-checked:bg-green-200">
                                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="font-semibold text-gray-900 peer-checked:text-green-800">Yes, I'll be there!</div>
                                        <div class="text-sm text-gray-600 mt-1">Count me in</div>
                                    </div>
                        </label>
                                <label class="group relative">
                                    <input type="radio" name="rsvp_status" value="maybe" @checked(old('rsvp_status', $invitation->rsvp_status) === 'maybe') class="sr-only peer">
                                    <div class="cursor-pointer p-6 text-center rounded-2xl border-2 border-gray-200 bg-white transition-all duration-200 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 peer-checked:shadow-lg hover:border-yellow-300 hover:shadow-md">
                                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-yellow-100 flex items-center justify-center peer-checked:bg-yellow-200">
                                            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="font-semibold text-gray-900 peer-checked:text-yellow-800">Maybe</div>
                                        <div class="text-sm text-gray-600 mt-1">I'll try to make it</div>
                                    </div>
                        </label>
                                <label class="group relative">
                                    <input type="radio" name="rsvp_status" value="no" @checked(old('rsvp_status', $invitation->rsvp_status) === 'no') class="sr-only peer">
                                    <div class="cursor-pointer p-6 text-center rounded-2xl border-2 border-gray-200 bg-white transition-all duration-200 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:shadow-lg hover:border-red-300 hover:shadow-md">
                                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-red-100 flex items-center justify-center peer-checked:bg-red-200">
                                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="font-semibold text-gray-900 peer-checked:text-red-800">Can't make it</div>
                                        <div class="text-sm text-gray-600 mt-1">Sorry, I can't attend</div>
                                    </div>
                        </label>
                            </div>
                    </div>
                    <div>
                            <label class="block text-lg font-semibold text-gray-900 mb-3">Additional Notes (Optional)</label>
                            <textarea 
                                name="rsvp_note" 
                                rows="4" 
                                class="w-full border-2 border-gray-200 rounded-2xl p-4 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 resize-none" 
                                placeholder="Let us know about dietary restrictions, plus ones, or any special requests..."
                            >{{ old('rsvp_note', $invitation->rsvp_note) }}</textarea>
                    </div>
                    @error('rsvp_status')
                            <div class="flex items-center p-4 rounded-2xl bg-red-50 border border-red-200">
                                <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-red-700 font-medium">{{ $message }}</span>
                            </div>
                    @enderror
                    @error('rsvp_note')
                            <div class="flex items-center p-4 rounded-2xl bg-red-50 border border-red-200">
                                <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-red-700 font-medium">{{ $message }}</span>
                            </div>
                    @enderror
                        <div class="pt-4">
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 btn-gradient-primary font-semibold rounded-2xl transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                        @if($currentRsvp === 'none')
                                        Submit My RSVP
                        @else
                                        Update My RSVP
                        @endif
                                </button>
                    </div>
                </form>
                @endif
            @endif
        </div>

        <!-- Old QR and Calendar sections removed - now moved to top between About Event and Location -->

        @if($rsvpEnabled && $currentRsvp === 'none' && $event->qr_checkin_enabled)
                <div id="qr-preview" class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl border border-gray-100 shadow-xl hover:shadow-2xl transition-all duration-300 text-center hidden">
                    <div class="flex items-center justify-center mb-6">
                        <div class="p-3 bg-indigo-100 rounded-2xl mr-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zM13 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V4zM9 4a1 1 0 000 2v1a1 1 0 001 1h1a1 1 0 100-2V6a1 1 0 00-1-1H9zM9 13a1 1 0 100 2h1a1 1 0 001 1v1a1 1 0 102 0v-1a1 1 0 001-1h1a1 1 0 100-2h-1a1 1 0 00-1-1v-1a1 1 0 10-2 0v1a1 1 0 00-1 1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">QR Check-in Preview</h2>
                    </div>
                @php
                    $qrData = trim(($event->qr_code_url ?: $inviteUrl) ?? '');
                        $qrPrimary = $qrData !== '' ? ('https://chart.googleapis.com/chart?cht=qr&chld=L|0&chs=280x280&chl=' . rawurlencode($qrData)) : '';
                        $qrFallback = $qrData !== '' ? ('https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . rawurlencode($qrData)) : '';
                @endphp
                @if($qrData !== '')
                        <div class="inline-block p-6 bg-white rounded-2xl shadow-lg border-4 border-indigo-100 mb-6">
                            <img src="{{ $qrPrimary }}" alt="QR Code" class="mx-auto rounded-xl" onerror="this.onerror=null;this.src='{{ $qrFallback }}';">
                        </div>
                @else
                        <div class="p-8 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-300 mb-6">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zM13 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V4z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-gray-500 font-medium">QR code not available</p>
                        </div>
                    @endif
                    <p class="text-gray-600 text-lg leading-relaxed">Show this QR code at the entrance for quick check-in.</p>
                </div>
                @endif
        </div>

        <!-- Reminder Modal -->
        <div id="reminderModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4" onclick="closeReminderModal()">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-900">Set Event Reminder</h3>
                        <button onclick="closeReminderModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <!-- Event Info -->
                        <div class="bg-gray-50 rounded-2xl p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">{{ $event->name }}</h4>
                            <p class="text-gray-600 text-sm">
                                {{ $startGuestTime->format('l, F j, Y \a\t g:i A') }} ({{ $startGuestTime->format('T') }})
                            </p>
                        </div>

                        <!-- Reminder Time Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">When would you like to be reminded?</label>
                            <div class="space-y-3">
                                <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" name="reminder_time" value="15min" class="mr-3 text-orange-600 focus:ring-orange-500">
                                    <div>
                                        <div class="font-medium text-gray-900">15 minutes before</div>
                                        <div class="text-sm text-gray-500">Perfect for last-minute prep</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" name="reminder_time" value="1hour" class="mr-3 text-orange-600 focus:ring-orange-500">
                                    <div>
                                        <div class="font-medium text-gray-900">1 hour before</div>
                                        <div class="text-sm text-gray-500">Time to get ready and travel</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" name="reminder_time" value="1day" class="mr-3 text-orange-600 focus:ring-orange-500">
                                    <div>
                                        <div class="font-medium text-gray-900">1 day before</div>
                                        <div class="text-sm text-gray-500">Plan your day ahead</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" name="reminder_time" value="custom" class="mr-3 text-orange-600 focus:ring-orange-500">
                                    <div>
                                        <div class="font-medium text-gray-900">Custom time</div>
                                        <div class="text-sm text-gray-500">Choose your own reminder time</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Custom Time Input (hidden by default) -->
                        <div id="customTimeInput" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Custom reminder time</label>
                            <input type="datetime-local" id="customReminderTime" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <div id="customTimePreview" class="mt-2 text-sm text-gray-600"></div>
                        </div>

                        <!-- Communication Platform Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">How would you like to receive the reminder?</label>
                            <div class="space-y-3">
                                @php
                                    $hasEmail = !empty($invitation->guest->email);
                                    $hasPhone = !empty($invitation->guest->phone);
                                    $availablePlatforms = [];
                                    if ($hasEmail) $availablePlatforms[] = 'email';
                                    if ($hasPhone) $availablePlatforms[] = 'whatsapp';
                                @endphp

                                @if($hasEmail)
                                <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" name="reminder_platform" value="email" class="mr-3 text-orange-600 focus:ring-orange-500">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                        </svg>
                                        <div>
                                            <div class="font-medium text-gray-900">Email</div>
                                            <div class="text-sm text-gray-500">{{ $invitation->guest->email }}</div>
                                        </div>
                                    </div>
                                </label>
                                @endif

                                @if($hasPhone)
                                <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" name="reminder_platform" value="whatsapp" class="mr-3 text-orange-600 focus:ring-orange-500">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <div class="font-medium text-gray-900">WhatsApp</div>
                                            <div class="text-sm text-gray-500">{{ $invitation->guest->phone }}</div>
                                        </div>
                                    </div>
                                </label>
                                @endif

                                @if(empty($availablePlatforms))
                                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-yellow-800 text-sm">No contact information available for reminders</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 pt-4">
                            <button onclick="closeReminderModal()" class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors font-medium">
                                Cancel
                            </button>
                            <button id="scheduleReminderBtn" onclick="scheduleReminder()" class="flex-1 px-4 py-3 bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-colors font-medium">
                                <span id="scheduleBtnText">Set Reminder</span>
                                <span id="scheduleBtnLoading" class="hidden">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Scheduling...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer with subtle branding -->
        <div class="mt-16 pb-8 text-center">
            <div class="inline-flex items-center px-6 py-3 bg-white/60 backdrop-blur-sm rounded-full border border-gray-200 shadow-sm">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-gray-600 font-medium">Powered by Invaro</span>
            </div>
        </div>
    </div>
</div>

            <script>
    // Enhanced QR code preview functionality with smooth animations
                (function(){
                    const yes = document.querySelector('input[name="rsvp_status"][value="yes"]');
                    const maybe = document.querySelector('input[name="rsvp_status"][value="maybe"]');
                    const no = document.querySelector('input[name="rsvp_status"][value="no"]');
                    const qr = document.getElementById('qr-preview');
        
                    function updateQR(){
                        if (!qr) return;
                        if ((yes && yes.checked) || (maybe && maybe.checked)) {
                            qr.classList.remove('hidden');
                qr.style.opacity = '0';
                qr.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    qr.style.transition = 'all 0.3s ease-out';
                    qr.style.opacity = '1';
                    qr.style.transform = 'translateY(0)';
                }, 10);
                        } else {
                qr.style.transition = 'all 0.3s ease-in';
                qr.style.opacity = '0';
                qr.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                            qr.classList.add('hidden');
                }, 300);
                        }
                    }
        
                    [yes, maybe, no].forEach(el => el && el.addEventListener('change', updateQR));
                    updateQR();
                })();
    
        // Add subtle parallax effect to hero section
    window.addEventListener('scroll', () => {
        const hero = document.querySelector('.bg-gradient-to-r');
        if (hero) {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        }
    });
    
    // Copy event details to clipboard
    window.copyEventDetails = function() {
        const eventDetails = `{{ $event->name }}

Date: {{ $startTime->format('l, F j, Y \\a\\t g:i A') }} ({{ $timezoneAbbr }})
@if($endTime)
@if($startTime->format('Y-m-d') !== $endTime->format('Y-m-d'))
Ends: {{ $endTime->format('l, F j, Y \\a\\t g:i A') }} ({{ $timezoneAbbr }})
@else
Ends: {{ $endTime->format('g:i A') }} ({{ $timezoneAbbr }})
@endif
@endif

@if(!empty($event->description))Description: {{ $event->description }}@endif

@if(!empty($event->venue_name))Venue: {{ $event->venue_name }}@endif
@if(!empty($event->venue_address))Address: {{ $event->venue_address }}@elseif(!empty($event->location))Location: {{ $event->location }}@endif

More info: {{ $inviteUrl }}`;
        
        navigator.clipboard.writeText(eventDetails).then(() => {
            showNotification('Event details copied to clipboard!', 'success');
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = eventDetails;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showNotification('Event details copied to clipboard!', 'success');
        });
    };
    
                    // Reminder Modal Functions
                window.openReminderModal = function() {
                    const modal = document.getElementById('reminderModal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    
                    // Set default custom time to 1 hour before event (using guest timezone)
                    const eventDate = new Date('{{ $startGuestTime->toISOString() }}');
                    const defaultReminderTime = new Date(eventDate.getTime() - (60 * 60 * 1000));
                    
                    // Convert to local time for datetime-local input
                    const localReminderTime = new Date(defaultReminderTime.getTime() + (defaultReminderTime.getTimezoneOffset() * 60000));
                    
                    // Format for datetime-local input (YYYY-MM-DDTHH:MM)
                    const year = localReminderTime.getFullYear();
                    const month = String(localReminderTime.getMonth() + 1).padStart(2, '0');
                    const day = String(localReminderTime.getDate()).padStart(2, '0');
                    const hours = String(localReminderTime.getHours()).padStart(2, '0');
                    const minutes = String(localReminderTime.getMinutes()).padStart(2, '0');
                    
                    const formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                    document.getElementById('customReminderTime').value = formattedDateTime;
                    
                    // Debug log to verify the default time
                    console.log('Default reminder time set:', {
                        eventDate: eventDate,
                        eventDateLocal: eventDate.toLocaleString(),
                        defaultReminderTime: defaultReminderTime,
                        defaultReminderTimeLocal: defaultReminderTime.toLocaleString(),
                        localReminderTime: localReminderTime,
                        localReminderTimeLocal: localReminderTime.toLocaleString(),
                        formattedDateTime: formattedDateTime,
                        timezoneOffset: defaultReminderTime.getTimezoneOffset()
                    });
                };
                
                window.closeReminderModal = function() {
                    const modal = document.getElementById('reminderModal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                };
                
                // Handle custom time input visibility and keyboard events
                document.addEventListener('DOMContentLoaded', function() {
                    const customTimeRadio = document.querySelector('input[name="reminder_time"][value="custom"]');
                    const customTimeInput = document.getElementById('customTimeInput');
                    const customTimeField = document.getElementById('customReminderTime');
                    const customTimePreview = document.getElementById('customTimePreview');
                    
                    // Function to update the preview
                    function updateCustomTimePreview() {
                        if (customTimeField.value) {
                            const [datePart, timePart] = customTimeField.value.split('T');
                            const [year, month, day] = datePart.split('-').map(Number);
                            const [hour, minute] = timePart.split(':').map(Number);
                            
                            // Create date in local timezone (what user sees)
                            const localDate = new Date(year, month - 1, day, hour, minute);
                            
                            // Convert to UTC for server (preserve local time)
                            const utcDate = new Date(localDate.getTime() - (localDate.getTimezoneOffset() * 60000));
                            
                            const formattedPreview = localDate.toLocaleString('en-US', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            });
                            
                            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                            customTimePreview.textContent = `Reminder will be set for: ${formattedPreview} (${timezone})`;
                        } else {
                            customTimePreview.textContent = '';
                        }
                    }
                    
                    if (customTimeRadio && customTimeInput) {
                        customTimeRadio.addEventListener('change', function() {
                            if (this.checked) {
                                customTimeInput.classList.remove('hidden');
                                
                                // Set a reasonable default if no value is set
                                if (!customTimeField.value) {
                                    const eventDate = new Date('{{ $startGuestTime->toISOString() }}');
                                    const defaultReminderTime = new Date(eventDate.getTime() - (60 * 60 * 1000));
                                    
                                    // Convert to local time for datetime-local input
                                    const localReminderTime = new Date(defaultReminderTime.getTime() + (defaultReminderTime.getTimezoneOffset() * 60000));
                                    
                                    const year = localReminderTime.getFullYear();
                                    const month = String(localReminderTime.getMonth() + 1).padStart(2, '0');
                                    const day = String(localReminderTime.getDate()).padStart(2, '0');
                                    const hours = String(localReminderTime.getHours()).padStart(2, '0');
                                    const minutes = String(localReminderTime.getMinutes()).padStart(2, '0');
                                    
                                    const formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                                    customTimeField.value = formattedDateTime;
                                }
                                
                                // Update preview
                                updateCustomTimePreview();
                            } else {
                                customTimeInput.classList.add('hidden');
                            }
                        });
                    }
                    
                    // Update preview when custom time changes
                    if (customTimeField) {
                        customTimeField.addEventListener('change', updateCustomTimePreview);
                        customTimeField.addEventListener('input', updateCustomTimePreview);
                    }
                    
                    // Handle Escape key to close modal
                    document.addEventListener('keydown', function(event) {
                        if (event.key === 'Escape') {
                            const modal = document.getElementById('reminderModal');
                            if (!modal.classList.contains('hidden')) {
                                closeReminderModal();
                            }
                        }
                    });
                });
                
                // Schedule reminder function
                window.scheduleReminder = function() {
                    const selectedTime = document.querySelector('input[name="reminder_time"]:checked');
                    const selectedPlatform = document.querySelector('input[name="reminder_platform"]:checked');
                    const scheduleBtn = document.getElementById('scheduleReminderBtn');
                    const scheduleBtnText = document.getElementById('scheduleBtnText');
                    const scheduleBtnLoading = document.getElementById('scheduleBtnLoading');
                    
                    if (!selectedTime) {
                        showNotification('Please select a reminder time', 'warning');
                        return;
                    }
                    
                    if (!selectedPlatform) {
                        showNotification('Please select a communication platform', 'warning');
                        return;
                    }
                    
                    // Show loading state
                    scheduleBtn.disabled = true;
                    scheduleBtnText.classList.add('hidden');
                    scheduleBtnLoading.classList.remove('hidden');
                    
                    let reminderDateTime;
                    const eventDate = new Date('{{ $startGuestTime->toISOString() }}');
                    
                    switch (selectedTime.value) {
                        case '15min':
                            reminderDateTime = new Date(eventDate.getTime() - (15 * 60 * 1000));
                            break;
                        case '1hour':
                            reminderDateTime = new Date(eventDate.getTime() - (60 * 60 * 1000));
                            break;
                        case '1day':
                            reminderDateTime = new Date(eventDate.getTime() - (24 * 60 * 60 * 1000));
                            break;
                        case 'custom':
                            const customTime = document.getElementById('customReminderTime').value;
                            if (!customTime) {
                                showNotification('Please enter a custom reminder time', 'warning');
                                return;
                            }
                            
                            // Validate datetime-local format
                            const datetimeRegex = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/;
                            if (!datetimeRegex.test(customTime)) {
                                showNotification('Please enter a valid date and time', 'warning');
                                return;
                            }
                            
                            // Parse the datetime-local input (this is in user's local timezone)
                            const [datePart, timePart] = customTime.split('T');
                            const [year, month, day] = datePart.split('-').map(Number);
                            const [hour, minute] = timePart.split(':').map(Number);
                            
                            // Create date in local timezone (this is what the user selected)
                            const localDate = new Date(year, month - 1, day, hour, minute, 0, 0);
                            
                            // Send the local time to server (backend will handle timezone conversion)
                            reminderDateTime = localDate;
                            
                            // Debug the parsing
                            console.log('Custom time parsing:', {
                                input: customTime,
                                parsed: { year, month, day, hour, minute },
                                localDate: localDate.toString(),
                                localDateISO: localDate.toISOString(),
                                timezoneOffset: localDate.getTimezoneOffset(),
                                finalReminderTime: reminderDateTime.toISOString()
                            });
                            
                            // Validate the date was parsed correctly
                            if (isNaN(reminderDateTime.getTime())) {
                                showNotification('Invalid date/time format. Please try again.', 'warning');
                                return;
                            }
                            
                            // Validate custom time is not in the past
                            if (reminderDateTime <= new Date()) {
                                showNotification('Custom reminder time must be in the future', 'warning');
                                return;
                            }
                            break;
                        default:
                            showNotification('Invalid reminder time selected', 'error');
                            return;
                    }
                    
                    // Check if reminder time is in the future
                    const now = new Date();
                    if (reminderDateTime <= now) {
                        showNotification('Reminder time must be in the future', 'warning');
                        return;
                    }
                    
                    // Prepare reminder data
                    // Send the reminder time in the guest's timezone (not UTC)
                    // For custom time, the user selected time is already in their local timezone
                    // We need to send it as-is without timezone conversion
                    const guestTimezone = '{{ $guestTimezone }}';
                    let reminderTimeInGuestTimezone;
                    
                    if (selectedTime.value === 'custom') {
                        // For custom time, use the datetime-local input value directly
                        const customTime = document.getElementById('customReminderTime').value;
                        reminderTimeInGuestTimezone = customTime + ':00'; // Add seconds
                    } else {
                        // For preset times, convert to guest's timezone
                        reminderTimeInGuestTimezone = new Intl.DateTimeFormat('sv-CA', {
                            timeZone: guestTimezone,
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        }).format(reminderDateTime).replace(',', 'T');
                    }
                    
                    const reminderData = {
                        invitation_token: '{{ $invitation->token }}',
                        reminder_time: reminderTimeInGuestTimezone,
                        timezone: '{{ $guestTimezone }}',
                        platform: selectedPlatform.value,
                        event_name: '{{ $event->name }}',
                        event_date: '{{ $startGuestTime->toISOString() }}',
                        guest_name: '{{ $invitation->guest->name ?? $guest->name ?? 'Guest' }}',
                        guest_email: '{{ $invitation->guest->email ?? $guest->email ?? '' }}',
                        guest_phone: '{{ $invitation->guest->phone ?? $guest->phone ?? '' }}'
                    };
                    
                    // Debug logging
                    console.log('Reminder Data:', {
                        selectedTime: selectedTime.value,
                        customTimeValue: selectedTime.value === 'custom' ? document.getElementById('customReminderTime').value : 'N/A',
                        reminderDateTime: reminderDateTime,
                        reminderTimeISO: reminderDateTime.toISOString(),
                        guestTimezone: guestTimezone,
                        reminderTimeInGuestTimezone: reminderTimeInGuestTimezone,
                        reminderTimeLocal: reminderDateTime.toString(),
                        reminderTimeHours: reminderDateTime.getHours(),
                        reminderTimeMinutes: reminderDateTime.getMinutes(),
                        reminderData: reminderData
                    });
                    
                    // Send reminder request to server
                    fetch('{{ route("public.invite.reminder", ["token" => $invitation->token]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Timezone': Intl.DateTimeFormat().resolvedOptions().timeZone
                        },
                        body: JSON.stringify(reminderData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Reset loading state
                        scheduleBtn.disabled = false;
                        scheduleBtnText.classList.remove('hidden');
                        scheduleBtnLoading.classList.add('hidden');
                        
                        if (data.success) {
                            showNotification(data.message, 'success');
                            closeReminderModal();
                        } else {
                            showNotification(data.message || 'Failed to schedule reminder', 'error');
                        }
                    })
                    .catch(error => {
                        // Reset loading state
                        scheduleBtn.disabled = false;
                        scheduleBtnText.classList.remove('hidden');
                        scheduleBtnLoading.classList.add('hidden');
                        
                        console.error('Error scheduling reminder:', error);
                        showNotification('Failed to schedule reminder. Please try again.', 'error');
                    });
                };
    
    // Simple notification function
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
        
        // Set colors based on type
        switch(type) {
            case 'success':
                notification.className += ' bg-green-500 text-white';
                break;
            case 'warning':
                notification.className += ' bg-yellow-500 text-white';
                break;
            case 'error':
                notification.className += ' bg-red-500 text-white';
                break;
            default:
                notification.className += ' bg-blue-500 text-white';
        }
        
        notification.innerHTML = `
            <div class="flex items-center">
                <span class="mr-2">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white/80 hover:text-white">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }, 5000);
    }

    // Delete reminder (used in the list under calendar)
    window.deleteReminder = function(reminderId, buttonEl) {
        if (!confirm('Are you sure you want to delete this reminder?')) {
            return;
        }

        const deleteBtn = buttonEl || event.target.closest('button');
        if (!deleteBtn) return;
        const originalContent = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path></svg>';
        deleteBtn.disabled = true;

        const deleteUrl = `/invite/{{ $invitation->token }}/reminder/${reminderId}`;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                const item = deleteBtn.closest('[data-reminder-id]');
                if (item) {
                    item.style.transition = 'all 0.3s ease-out';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-20px)';
                    setTimeout(() => {
                        item.remove();
                        const list = document.getElementById('guestRemindersList');
                        if (list && list.children.length === 0) {
                            const section = document.getElementById('guestRemindersSection');
                            if (section) {
                                section.style.transition = 'all 0.3s ease-out';
                                section.style.opacity = '0';
                                section.style.transform = 'translateY(-20px)';
                                setTimeout(() => section.remove(), 300);
                            }
                        }
                    }, 300);
                }
            } else {
                showNotification(data.message || 'Failed to delete reminder', 'error');
                deleteBtn.innerHTML = originalContent;
                deleteBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error deleting reminder:', error);
            showNotification('Failed to delete reminder. Please try again.', 'error');
            deleteBtn.innerHTML = originalContent;
            deleteBtn.disabled = false;
        });
    };
            </script>
@endsection

