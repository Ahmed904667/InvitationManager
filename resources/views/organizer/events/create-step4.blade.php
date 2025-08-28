@extends('layouts.organizer')

@section('content')
<style>
/* Additional calendar restrictions */
input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    /* Ensure the calendar picker respects min/max attributes */
    cursor: pointer;
}

/* Disable past dates in the calendar picker */
input[type="datetime-local"]:invalid {
    border-color: #ef4444;
}

/* Custom styling for disabled dates in calendar */
input[type="datetime-local"]::-webkit-datetime-edit-fields-wrapper {
    /* Ensure proper date formatting */
}

/* Prevent manual entry of invalid dates */
input[type="datetime-local"]:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>
<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Page Header --}}
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-primary mb-2">
            @if(request()->has('mode') && request()->get('mode') === 'update')
                Update Event & Send Invitations
            @else
                Send Invitations
            @endif
        </h1>
        <p class="text-lg text-secondary">
            @if(request()->has('mode') && request()->get('mode') === 'update')
                Review your event updates and choose when to send invitations
            @else
                Review your event and choose when to send invitations
            @endif
        </p>
    </div>



    <form action="{{ route('organizer.events.create.step4.process') }}" method="POST" id="event-form-4">
        @csrf
        
        {{-- Preserve mode parameter --}}
        @if(request()->has('mode'))
            <input type="hidden" name="mode" value="{{ request()->get('mode') }}">
        @endif
        
        {{-- Preserve all previous step data --}}
        @if(isset($allData['name']))
            <input type="hidden" name="name" value="{{ $allData['name'] }}">
        @endif
        
        @if(isset($allData['description']))
            <input type="hidden" name="description" value="{{ $allData['description'] }}">
        @endif
        
        @if(isset($allData['start_date']))
            <input type="hidden" name="start_date" value="{{ $allData['start_date'] }}">
        @endif
        
        @if(isset($allData['end_date']))
            <input type="hidden" name="end_date" value="{{ $allData['end_date'] }}">
        @endif
        
        @if(isset($allData['guest_list_ids']) && is_array($allData['guest_list_ids']))
            @foreach($allData['guest_list_ids'] as $guestListId)
                <input type="hidden" name="guest_list_ids[]" value="{{ $guestListId }}">
            @endforeach
        @endif
        
        @if(isset($allData['invitation_platforms']) && is_array($allData['invitation_platforms']))
            @foreach($allData['invitation_platforms'] as $platform)
                <input type="hidden" name="invitation_platforms[]" value="{{ $platform }}">
            @endforeach
        @endif
        
        @if(isset($allData['qr_checkin_enabled']))
            <input type="hidden" name="qr_checkin_enabled" value="1">
        @endif
        
        @if(isset($allData['rsvp_enabled']))
            <input type="hidden" name="rsvp_enabled" value="1">
        @endif
        
        {{-- Do NOT include general_message as hidden here to avoid overwriting clears when navigating --}}
        
        @if(isset($allData['ai_generated']))
            <input type="hidden" name="ai_generated" value="1">
        @endif
        
        {{-- Preserve mode parameter for update/create distinction --}}
        @if(request()->has('mode'))
            <input type="hidden" name="mode" value="{{ request()->get('mode') }}">
        @endif
        
        {{-- Step Navigation --}}
        @include('organizer.events.partials.step-navigation', ['currentStep' => 4])

        {{-- Step 4 Content --}}
        <div class="card">
            <div class="card-header text-center">
                <h2 class="text-2xl font-semibold text-primary mb-2">
                    <i class="fas fa-paper-plane text-primary-500 mr-2"></i> Send Invitations
                </h2>
                <p class="text-secondary">Review your event details and schedule your invitations</p>
            </div>

            <div class="card-body space-y-8">
                {{-- Event Summary --}}
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-primary mb-4 flex items-center">
                        <i class="fas fa-clipboard-list text-primary-500 mr-2"></i> Event Summary
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-calendar text-primary-500 mr-2"></i>
                                <span class="font-semibold text-primary">Event Details</span>
                            </div>
                            <p class="text-sm text-secondary">{{ $mergedData['name'] ?? 'Not set' }}</p>
                            <p class="text-xs text-secondary mt-1">
                                {{ isset($mergedData['start_date']) ? \Carbon\Carbon::parse($mergedData['start_date'])->format('M j, Y g:i A') : 'Not set' }}
                            </p>
                        </div>
                        
                        <div class="bg-success-50 border border-success-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-users text-success-500 mr-2"></i>
                                <span class="font-semibold text-success-700">Guest Lists</span>
                            </div>
                            <p class="text-sm text-secondary">{{ count($mergedData['guest_list_ids'] ?? []) }} list(s) selected</p>
                            <p class="text-xs text-secondary mt-1">{{ $totalGuests ?? 0 }} total guests</p>
                        </div>
                        
                        <div class="bg-info-50 border border-info-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-envelope text-info-500 mr-2"></i>
                                <span class="font-semibold text-info-700">Message Type</span>
                            </div>
                            <p class="text-sm text-secondary">
                                @php
                                    $hasGeneral = !empty($mergedData['general_message'] ?? null);
                                    $hasGroup = !empty($mergedData['group_messages'] ?? []);
                                    $hasPerGuest = !empty($mergedData['per_guest_messages'] ?? []);
                                @endphp
                                @if($hasGeneral && !$hasGroup && !$hasPerGuest)
                                    General Message
                                @elseif(!$hasGeneral && $hasGroup && !$hasPerGuest)
                                    Group Messages
                                @elseif(!$hasGeneral && !$hasGroup && $hasPerGuest)
                                    Individual Messages
                                @else
                                    Mixed Messages
                                @endif
                            </p>
                            @if(isset($mergedData['ai_generated']) && $mergedData['ai_generated'])
                                <p class="text-xs text-info-600 mt-1">AI-Generated</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Event Review --}}
                <div>
                    <h3 class="text-xl font-semibold text-primary mb-4 flex items-center">
                        <i class="fas fa-eye text-primary-500 mr-2"></i> Event Review
                    </h3>
                    
                    <div class="card">
                        <div class="bg-gradient-to-r from-primary-500 to-primary-700 text-white p-6 rounded-t-lg">
                            <div class="flex justify-between items-center">
                                <h2 class="text-2xl font-semibold">{{ $mergedData['name'] ?? '' }}</h2>
                                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm font-semibold uppercase tracking-wide">Draft</span>
                            </div>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <div class="flex gap-4">
                                <i class="fas fa-calendar-alt text-primary-500 text-xl mt-1"></i>
                                <div>
                                    <strong class="text-primary block mb-1">Date & Time</strong>
                                    <p class="text-secondary">{{ isset($mergedData['start_date']) ? \Carbon\Carbon::parse($mergedData['start_date'])->format('l, F j, Y \a\t g:i A') : '' }}</p>
                                    @if(isset($mergedData['end_date']) && $mergedData['end_date'])
                                        <p class="text-secondary text-sm italic">Ends: {{ \Carbon\Carbon::parse($mergedData['end_date'])->format('g:i A') }}</p>
                                    @endif
                                </div>
                            </div>

                            @if(isset($mergedData['description']) && $mergedData['description'])
                            <div class="flex gap-4">
                                <i class="fas fa-align-left text-primary-500 text-xl mt-1"></i>
                                <div>
                                    <strong class="text-primary block mb-1">Description</strong>
                                    <p class="text-secondary">{{ $mergedData['description'] }}</p>
                                </div>
                            </div>
                            @endif

                            @if(isset($mergedData['additional_information']) && $mergedData['additional_information'])
                            <div class="flex gap-4">
                                <i class="fas fa-info-circle text-primary-500 text-xl mt-1"></i>
                                <div>
                                    <strong class="text-primary block mb-1">Additional Information</strong>
                                    <p class="text-secondary">{{ $mergedData['additional_information'] }}</p>
                                </div>
                            </div>
                            @endif

                            <div class="flex gap-4">
                                <i class="fas fa-cogs text-primary-500 text-xl mt-1"></i>
                                <div>
                                    <strong class="text-primary block mb-1">Features Enabled</strong>
                                    <div class="flex gap-2 flex-wrap">
                                        @if(isset($mergedData['qr_checkin_enabled']) && $mergedData['qr_checkin_enabled'])
                                            <span class="bg-info-100 text-info-700 px-3 py-1 rounded-full text-sm font-semibold">QR Check-in</span>
                                        @endif
                                        @if(isset($mergedData['rsvp_enabled']) && $mergedData['rsvp_enabled'])
                                            <span class="bg-info-100 text-info-700 px-3 py-1 rounded-full text-sm font-semibold">RSVP Required</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <i class="fas fa-paper-plane text-primary-500 text-xl mt-1"></i>
                                <div>
                                    <strong class="text-primary block mb-1">Invitation Platforms</strong>
                                    <div class="flex gap-2 flex-wrap">
                                        @if(isset($mergedData['invitation_platforms']))
                                            @foreach($mergedData['invitation_platforms'] as $platform)
                                                <span class="px-3 py-1 rounded-full text-sm font-semibold text-white {{ $platform === 'email' ? 'bg-primary-500' : 'bg-green-500' }}">
                                                    @if($platform === 'email')
                                                        <i class="fas fa-envelope mr-1"></i> Email
                                                    @elseif($platform === 'whatsapp')
                                                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                                                    @endif
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <i class="fas fa-users text-primary-500 text-xl mt-1"></i>
                                <div class="flex-1">
                                    <strong class="text-primary block mb-1">Guests</strong>
                                    <div class="pt-2 border-t-2 border-gray-200 flex items-center justify-between">
                                        <strong class="text-primary">Total: {{ $totalGuests }} guests</strong>
                                        <button type="button" class="text-primary text-sm underline" onclick="toggleGuestDetails()">View guests</button>
                                    </div>
                                    
                                    <div id="guest-details" class="mt-3 hidden">
                                        <div class="bg-white border border-gray-200 rounded-lg p-3 max-h-64 overflow-y-auto">
                                            @foreach($guestLists as $guestList)
                                                @if($guestList->guests->count())
                                                    <div class="mb-2">
                                                        <div class="text-primary font-semibold mb-1">{{ $guestList->name }} ({{ $guestList->guests->count() }})</div>
                                                        <ul class="list-disc pl-5 text-secondary text-sm space-y-1">
                                                            @foreach($guestList->guests as $guest)
                                                                <li>{{ $guest->name }}
                                                                    @if($guest->email)
                                                                        <span class="text-gray-400">— {{ $guest->email }}</span>
                                                                    @elseif($guest->phone)
                                                                        <span class="text-gray-400">— {{ $guest->phone }}</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    
 
                                </div>
                            </div>

                                    {{-- Message Preview Section --}}
                                    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                        <h4 class="text-lg font-semibold text-primary mb-3">Message Preview</h4>
                                        
                                        @if(!empty($mergedData['per_guest_messages']))
                                            <div class="bg-white p-4 rounded border">
                                                <h5 class="font-semibold text-primary mb-2">Individual Messages Preview:</h5>
                                                <div class="space-y-3">
                                                    @php $messageCount = 0; @endphp
                                                    @foreach($guestLists as $guestList)
                                                        @if($messageCount >= 3) @break @endif
                                                        @foreach($guestList->guests as $guest)
                                                            @if($messageCount >= 3) @break @endif
                                                            @php $gm = $mergedData['per_guest_messages'][$guest->id] ?? null; @endphp
                                                            @if(is_string($gm) && $gm !== '')
                                                                <div class="border rounded-md p-3 bg-gray-50 shadow-sm">
                                                                    <div class="flex items-center justify-between mb-2">
                                                                        <div class="font-semibold text-primary">{{ $guest->name }}</div>
                                                                        <div class="text-xs text-secondary">Group: {{ optional($guest->guestGroup)->name ?? '-' }}</div>
                                                                    </div>
                                                                    <div class="text-sm text-secondary">{{ Str::limit($gm, 100) }}</div>
                                                                </div>
                                                                @php $messageCount++; @endphp
                                                            @endif
                                                        @endforeach
                                                    @endforeach
                                                </div>
                                                
                                                @php
                                                    $totalMessages = 0;
                                                    foreach($guestLists as $guestList) {
                                                        foreach($guestList->guests as $guest) {
                                                            $gm = $mergedData['per_guest_messages'][$guest->id] ?? null;
                                                            if(is_string($gm) && $gm !== '') {
                                                                $totalMessages++;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($totalMessages > 3)
                                                    <div class="mt-3 text-center">
                                                        <button type="button" class="text-primary text-sm hover:text-primary-600 font-medium" onclick="showGuestMessages()">
                                                            Show {{ $totalMessages - 3 }} more messages
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="mt-3 text-center">
                                                        <button type="button" class="text-primary text-sm hover:text-primary-600 font-medium" onclick="showGuestMessages()">
                                                            View all individual messages
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="bg-white p-4 rounded border">
                                                <p class="text-secondary text-sm">No individual messages have been generated yet.</p>
                                            </div>
                                        @endif
                                    </div>
                        </div>
                    </div>
                </div>

                {{-- Sending Options --}}
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-xl font-semibold text-primary mb-4 flex items-center">
                        <i class="fas fa-clock text-primary-500 mr-2"></i> Sending Options
                    </h3>
                    <p class="text-secondary mb-6">Choose when to send your invitations</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative">
                            <input 
                                type="radio" 
                                id="send_now" 
                                name="send_type" 
                                value="now"
                                class="sr-only peer"
                                {{ old('send_type', $data['send_type'] ?? 'now') == 'now' ? 'checked' : '' }}
                            >
                            <label for="send_now" class="block p-6 border-2 border-gray-200 rounded-xl bg-white cursor-pointer transition-all duration-300 hover:border-primary-300 peer-checked:border-primary-500 peer-checked:bg-primary-50 shadow-sm peer-checked:shadow-md">
                                <div class="w-12 h-12 bg-warning-500 rounded-xl flex items-center justify-center mb-4">
                                    <i class="fas fa-bolt text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-primary mb-2">Send Now</h4>
                                    <p class="text-secondary mb-4">Immediately dispatch invitations to all guests</p>
                                    <div class="space-y-1">
                                        <span class="text-sm text-success-500 flex items-center">✓ Instant delivery</span>
                                        <span class="text-sm text-success-500 flex items-center">✓ Immediate confirmation</span>
                                        <span class="text-sm text-success-500 flex items-center">✓ Real-time tracking</span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="relative">
                            <input 
                                type="radio" 
                                id="send_scheduled" 
                                name="send_type" 
                                value="scheduled"
                                class="sr-only peer"
                                {{ old('send_type', $data['send_type'] ?? 'now') == 'scheduled' ? 'checked' : '' }}
                            >
                            <label for="send_scheduled" class="block p-6 border-2 border-gray-200 rounded-xl bg-white cursor-pointer transition-all duration-300 hover:border-primary-300 peer-checked:border-primary-500 peer-checked:bg-primary-50 shadow-sm peer-checked:shadow-md">
                                <div class="w-12 h-12 bg-primary-500 rounded-xl flex items-center justify-center mb-4">
                                    <i class="fas fa-calendar-check text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-primary mb-2">Schedule Sending</h4>
                                    <p class="text-secondary mb-4">Choose a specific date and time to send invitations</p>
                                    <div class="space-y-1">
                                        <span class="text-sm text-success-500 flex items-center">✓ Perfect timing</span>
                                        <span class="text-sm text-success-500 flex items-center">✓ Advance planning</span>
                                        <span class="text-sm text-success-500 flex items-center">✓ Optimal delivery</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    @error('send_type')
                        <div class="text-danger-500 text-sm mt-2 p-2 bg-danger-50 rounded border-l-4 border-danger-500">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Scheduled Time --}}
                <div id="scheduled-time-section" class="border-b border-gray-200 pb-6 {{ old('send_type', $data['send_type'] ?? 'now') == 'scheduled' ? 'block' : 'hidden' }}">
                    <h3 class="text-xl font-semibold text-primary mb-4 flex items-center">
                        <i class="fas fa-calendar-alt text-primary-500 mr-2"></i> Schedule Date & Time
                    </h3>
                    
                    <div class="max-w-md">
                        <div class="mb-6">
                            <label for="scheduled_at" class="form-label">
                                Send Date & Time <span class="text-danger-500">*</span>
                            </label>
                            <input 
                                type="datetime-local" 
                                id="scheduled_at" 
                                name="scheduled_at" 
                                class="form-input @error('scheduled_at') border-danger-500 @enderror"
                                value="{{ old('scheduled_at', $data['scheduled_at'] ?? '') }}"
                                @if(isset($allData['start_date']))
                                    max="{{ \Carbon\Carbon::parse($allData['start_date'])->format('Y-m-d\TH:i') }}"
                                @endif
                            >
                            @error('scheduled_at')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            @php 
                                $eventStart = isset($allData['start_date']) ? \Carbon\Carbon::parse($allData['start_date']) : null;
                                $userTimezone = Auth::user()->timezone ?? 'UTC';
                                $timezoneDisplay = $userTimezone === 'UTC' ? 'UTC' : $userTimezone;
                            @endphp
                            <small class="text-secondary text-sm mt-1 block">
                                @if($eventStart)
                                    Schedule between now and {{ $eventStart->format('M j, Y g:i A') }} (event start)
                                @else
                                    Choose an optimal time when your guests are likely to check their messages
                                @endif
                                <br>
                                <span class="text-primary-600 font-medium">
                                    <i class="fas fa-clock mr-1"></i> Your timezone: {{ $timezoneDisplay }}
                                </span>
                                @if($userTimezone === 'UTC')
                                    <br>
                                    <span class="text-warning-600 text-xs">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> 
                                        You're using UTC timezone. Consider setting your local timezone in your profile for better scheduling.
                                    </span>
                                @endif
                            </small>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold text-primary mb-4">Suggested Times</h4>
                            <div class="grid grid-cols-2 gap-3" id="suggested-times-container">
                                <button type="button" class="suggested-time-btn bg-gray-50 border-2 border-gray-200 rounded-lg p-4 text-center hover:border-gray-300 transition-colors" data-hour="9" data-minute="0">
                                    <strong class="text-primary block mb-1">9:00 AM</strong>
                                    <span class="text-secondary text-sm">Morning read</span>
                                </button>
                                <button type="button" class="suggested-time-btn bg-gray-50 border-2 border-gray-200 rounded-lg p-4 text-center hover:border-gray-300 transition-colors" data-hour="12" data-minute="0">
                                    <strong class="text-primary block mb-1">12:00 PM</strong>
                                    <span class="text-secondary text-sm">Lunch break</span>
                                </button>
                                <button type="button" class="suggested-time-btn bg-gray-50 border-2 border-gray-200 rounded-lg p-4 text-center hover:border-gray-300 transition-colors" data-hour="18" data-minute="0">
                                    <strong class="text-primary block mb-1">6:00 PM</strong>
                                    <span class="text-secondary text-sm">Evening check</span>
                                </button>
                                <button type="button" class="suggested-time-btn bg-gray-50 border-2 border-gray-200 rounded-lg p-4 text-center hover:border-gray-300 transition-colors" data-hour="20" data-minute="0">
                                    <strong class="text-primary block mb-1">8:00 PM</strong>
                                    <span class="text-secondary text-sm">Night check</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-between items-center pt-6">
                    <a href="{{ route('organizer.events.create.step3') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Messages
                    </a>
                    
                    <button type="submit" class="btn btn-primary" id="finalSubmit">
                        <i class="fas fa-paper-plane mr-2"></i> 
                        @if(request()->has('mode') && request()->get('mode') === 'update')
                            Update Event & Send Invitations
                        @else
                            Create Event & Send Invitations
                        @endif
                    </button>
                </div>

                {{-- Final Confirmation --}}
                <div>
                    <div class="bg-warning-50 border-2 border-warning-200 rounded-xl p-6">
                        <div class="mb-4">
                            <h3 class="text-xl font-semibold text-warning-700 mb-4 flex items-center">
                                <i class="fas fa-check-circle text-warning-500 mr-2"></i> Final Confirmation
                            </h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-warning-100 border-2 border-warning-200 rounded-lg p-4">
                                <div class="flex gap-3">
                                    <i class="fas fa-exclamation-triangle text-warning-500 text-xl mt-1"></i>
                                    <div>
                                        <h4 class="text-warning-700 font-semibold mb-2">Before you send:</h4>
                                        <ul class="text-warning-700 space-y-1">
                                            <li class="flex items-center">
                                                <span class="text-warning-500 mr-2">•</span>
                                                Double-check all event details above
                                            </li>
                                            <li class="flex items-center">
                                                <span class="text-warning-500 mr-2">•</span>
                                                Ensure your guest lists are up to date
                                            </li>
                                            <li class="flex items-center">
                                                <span class="text-warning-500 mr-2">•</span>
                                                Review your invitation messages
                                            </li>
                                            <li class="flex items-center">
                                                <span class="text-warning-500 mr-2">•</span>
                                                Confirm your sending schedule
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white rounded-lg p-4 text-center">
                                <p id="action-text" class="text-lg font-semibold text-primary">
                                    <strong>
                                        @if(request()->has('mode') && request()->get('mode') === 'update')
                                            You are about to update your event and send invitations to {{ $totalGuests ?? 0 }} guests immediately.
                                        @else
                                            You are about to send invitations to {{ $totalGuests ?? 0 }} guests immediately.
                                        @endif
                                    </strong>
                                </p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Schedule Error Modal --}}
@include('components.confirmation-modal', [
    'id' => 'scheduleErrorModal',
    'title' => 'Invalid Schedule Time',
    'message' => 'Please choose a valid time before the event start and after the current time.',
    'confirmText' => 'OK',
    'cancelText' => 'Close',
    'warning' => true,
    'showWarningBox' => false
])

<script>
// Global variables and functions
let autoSaveTimeout;
let lastSavedData = '';
let isAutoSaving = false;

// Function to navigate to event creation with specific mode
window.navigateToEventCreation = function(mode = 'create') {
    const baseUrl = '{{ route("organizer.events.create.step1") }}';
    const url = mode === 'update' ? `${baseUrl}?mode=update` : baseUrl;
    window.location.href = url;
};

// Global function to update action text
window.updateActionText = function() {
    const scheduledInputEl = document.getElementById('scheduled_at');
    const actionText = document.getElementById('action-text');
    const eventStartIso = '{{ isset($allData['start_date']) ? \Carbon\Carbon::parse($allData['start_date'])->toIso8601String() : '' }}';
    const isUpdate = {{ request()->has('mode') && request()->get('mode') === 'update' ? 'true' : 'false' }};
    
    if (!scheduledInputEl || !actionText) return;
    
    const scheduledValue = scheduledInputEl.value;
    if (scheduledValue) {
        const scheduledDate = new Date(scheduledValue);
        const formattedDate = scheduledDate.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
        const userTimezone = '{{ Auth::user()->timezone ?? "UTC" }}';
        const timezoneDisplay = userTimezone === 'UTC' ? 'UTC' : userTimezone;
        
        if (isUpdate) {
            actionText.innerHTML = `<strong>You are about to update your event and schedule invitations for {{ $totalGuests ?? 0 }} guests to be sent on ${formattedDate} (${timezoneDisplay}).</strong>`;
        } else {
            actionText.innerHTML = `<strong>You are about to schedule invitations for {{ $totalGuests ?? 0 }} guests to be sent on ${formattedDate} (${timezoneDisplay}).</strong>`;
        }
        
        if (eventStartIso) {
            const eventStart = new Date(eventStartIso);
            if (scheduledDate >= eventStart) {
                actionText.innerHTML += `<div class="text-danger-600 text-sm mt-1">Selected time must be before the event start time.</div>`;
            }
        }
    } else {
        if (isUpdate) {
            actionText.innerHTML = '<strong>Please select a date and time for updating your event and sending invitations.</strong>';
        } else {
            actionText.innerHTML = '<strong>Please select a date and time for sending invitations.</strong>';
        }
    }
};

// Smart suggested time function
window.setSuggestedTime = function(hour, minute) {
    // Get user timezone
    const userTimezone = '{{ Auth::user()->timezone ?? "UTC" }}';
    
    // Get current time and event start time
    const now = new Date();
    const eventStartIso = '{{ isset($allData['start_date']) ? \Carbon\Carbon::parse($allData['start_date'])->toIso8601String() : '' }}';
    const eventStart = eventStartIso ? new Date(eventStartIso) : null;
    
    // Try today first
    const today = new Date();
    today.setHours(hour, minute, 0, 0);
    
    // Try tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(hour, minute, 0, 0);
    
    // Convert to user's timezone if not UTC
    let todayInUserTz = today;
    let tomorrowInUserTz = tomorrow;
    
    if (userTimezone !== 'UTC') {
        // Convert today to user's timezone
        const todayUserTime = new Date(today.toLocaleString("en-US", {timeZone: userTimezone}));
        todayInUserTz = new Date(todayUserTime);
        todayInUserTz.setHours(hour, minute, 0, 0);
        
        // Convert tomorrow to user's timezone
        const tomorrowUserTime = new Date(tomorrow.toLocaleString("en-US", {timeZone: userTimezone}));
        tomorrowInUserTz = new Date(tomorrowUserTime);
        tomorrowInUserTz.setHours(hour, minute, 0, 0);
    }
    
    // Determine which date to use
    let suggestedDate = null;
    
    // Check if today's time is valid (not in the past and before event start)
    if (todayInUserTz > now && (!eventStart || todayInUserTz < eventStart)) {
        suggestedDate = todayInUserTz;
    }
    // If today is not valid, try tomorrow
    else if (tomorrowInUserTz > now && (!eventStart || tomorrowInUserTz < eventStart)) {
        suggestedDate = tomorrowInUserTz;
    }
    
    // If no valid date found, don't set anything
    if (!suggestedDate) {
        return;
    }
    
    // Format for datetime-local input
    const year = suggestedDate.getFullYear();
    const month = String(suggestedDate.getMonth() + 1).padStart(2, '0');
    const day = String(suggestedDate.getDate()).padStart(2, '0');
    const hours = String(suggestedDate.getHours()).padStart(2, '0');
    const minutes = String(suggestedDate.getMinutes()).padStart(2, '0');
    const datetimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
    
    // Set the datetime input value
    const scheduledInput = document.getElementById('scheduled_at');
    if (scheduledInput) {
        scheduledInput.value = datetimeString;
        
        // Trigger change event to update UI
        scheduledInput.dispatchEvent(new Event('change', { bubbles: true }));
        
        // Update action text
        if (typeof updateActionText === 'function') {
            updateActionText();
        }
    }
};

// Helper function to get timezone abbreviation - make it globally accessible
window.getTimezoneAbbr = function(timezone) {
    const timezoneAbbreviations = {
        'America/New_York': 'EST/EDT',
        'America/Chicago': 'CST/CDT',
        'America/Denver': 'MST/MDT',
        'America/Los_Angeles': 'PST/PDT',
        'Europe/London': 'GMT/BST',
        'Europe/Paris': 'CET/CEST',
        'Asia/Tokyo': 'JST',
        'Asia/Shanghai': 'CST',
        'Asia/Singapore': 'SGT',
        'Australia/Sydney': 'AEST/AEDT',
        'Pacific/Auckland': 'NZST/NZDT'
    };
    
    return timezoneAbbreviations[timezone] || timezone.split('/').pop().replace('_', ' ');
};

// Show schedule error modal using the reusable confirmation modal
window.showScheduleErrorModal = function(message) {
    const modalId = 'scheduleErrorModal';
    const msgEl = document.querySelector(`#${modalId} .modal-body .text-center p`);
    if (msgEl) {
        msgEl.textContent = message;
    }
    // Show modal; no action on confirm
    if (typeof showConfirmationModal === 'function') {
        showConfirmationModal(modalId, () => {});
    }
}

// Show scheduled date error message using existing notification system
window.showScheduledDateError = function(message) {
    // Use the existing GuestManager.showNotification function from app.js
    if (window.GuestManager?.showNotification) {
        window.GuestManager.showNotification(message, 'error');
    } else {
        // Fallback to console log if notification function is not available
        console.error('Scheduled date validation error:', message);
    }
}

// Show notification function
function showNotification(message, type = 'info') {
    // Use the existing GuestManager.showNotification function from app.js
    if (window.GuestManager?.showNotification) {
        window.GuestManager.showNotification(message, type);
    } else {
        // Fallback to alert if notification function is not available
        alert(message);
    }
}

// Show form errors function
function showFormErrors(errors) {
    // Clear any existing error messages
    clearFormErrors();
    
    // Show each error as a notification
    Object.keys(errors).forEach(field => {
        const errorMessages = errors[field];
        if (Array.isArray(errorMessages)) {
            errorMessages.forEach(message => {
                // Special handling for guest message errors
                if (message.includes('guests should have messages') || message.includes('all guests')) {
                    showNotification('❌ Error: All guests must have messages. Please go back to step 3 and ensure every guest has a message assigned.', 'error');
                } else {
                    showNotification(`${field}: ${message}`, 'error');
                }
            });
        } else if (typeof errorMessages === 'string') {
            // Special handling for guest message errors
            if (errorMessages.includes('guests should have messages') || errorMessages.includes('all guests')) {
                showNotification('❌ Error: All guests must have messages. Please go back to step 3 and ensure every guest has a message assigned.', 'error');
            } else {
                showNotification(`${field}: ${errorMessages}`, 'error');
            }
        }
    });
    
    // Also highlight form fields with errors
    Object.keys(errors).forEach(field => {
        const fieldElement = document.querySelector(`[name="${field}"]`);
        if (fieldElement) {
            fieldElement.classList.add('border-danger-500');
            fieldElement.classList.add('bg-danger-50');
            
            // Add error message below the field
            const errorContainer = document.createElement('div');
            errorContainer.className = 'text-danger-500 text-sm mt-1';
            errorContainer.id = `error-${field}`;
            
            const errorMessages = errors[field];
            if (Array.isArray(errorMessages)) {
                errorContainer.textContent = errorMessages.join(', ');
            } else if (typeof errorMessages === 'string') {
                errorContainer.textContent = errorMessages;
            }
            
            fieldElement.parentNode.appendChild(errorContainer);
        }
    });
    
    // If there are guest message errors, show a prominent warning
    const hasGuestMessageErrors = Object.values(errors).some(errorMessages => {
        if (Array.isArray(errorMessages)) {
            return errorMessages.some(message => 
                message.includes('guests should have messages') || 
                message.includes('all guests')
            );
        }
        return typeof errorMessages === 'string' && (
            errorMessages.includes('guests should have messages') || 
            errorMessages.includes('all guests')
        );
    });
    
    if (hasGuestMessageErrors) {
        // Add a prominent error banner
        const errorBanner = document.createElement('div');
        errorBanner.className = 'bg-danger-50 border-2 border-danger-200 rounded-lg p-4 mb-6';
        errorBanner.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-danger-500 text-xl mr-3"></i>
                <div>
                    <h4 class="text-danger-700 font-semibold mb-1">Guest Messages Required</h4>
                    <p class="text-danger-600 text-sm mb-2">All guests must have messages assigned before you can create the event.</p>
                    <a href="{{ route('organizer.events.create.step3') }}" class="inline-flex items-center text-danger-600 hover:text-danger-700 font-medium">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Go back to Step 3 to fix messages
                    </a>
                </div>
            </div>
        `;
        
        // Insert at the top of the form
        const form = document.getElementById('event-form-4');
        if (form) {
            form.insertBefore(errorBanner, form.firstChild);
        }
    }
}

// Clear form errors function
function clearFormErrors() {
    // Remove error styling from all fields
    document.querySelectorAll('.border-danger-500').forEach(field => {
        field.classList.remove('border-danger-500', 'bg-danger-50');
    });
    
    // Remove error message containers
    document.querySelectorAll('[id^="error-"]').forEach(errorContainer => {
        errorContainer.remove();
    });
}

// Message preview functions
window.showFullMessage = function(type) {
    let message = '';
    let title = '';
    
    @if(!empty($mergedData['general_message']))
        if (type === 'general') {
            message = `{{ addslashes($mergedData['general_message']) }}`;
            title = 'General Message';
        }
    @endif
    @if(!empty($mergedData['group_messages']))
        @foreach($guestLists as $guestList)
            @php $gm = $mergedData['group_messages'][$guestList->id] ?? null; @endphp
            @if(is_string($gm))
                if (type === 'group-{{ $guestList->id }}') {
                    message = `{{ addslashes($gm) }}`;
                    title = '{{ $guestList->name }} Group Message';
                }
            @elseif(is_array($gm))
                @foreach($gm as $groupId => $message)
                    @if(is_string($message))
                        if (type === 'group-{{ $guestList->id }}-{{ $groupId }}') {
                            message = `{{ addslashes($message) }}`;
                            title = '{{ $guestList->name }} - Group {{ $groupId }} Message';
                        }
                    @endif
                @endforeach
            @endif
        @endforeach
    @endif
    
    if (message) {
        showMessageModal(title, message);
    }
}

// Make functions globally accessible
window.showGuestMessages = function() {
    // Remove any existing modal first
    const existingModal = document.getElementById('guestMessagesModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    let html = '';
    @if(isset($mergedData['per_guest_messages']) && is_array($mergedData['per_guest_messages']) && !empty($mergedData['per_guest_messages']))
        html += `<div class="space-y-4">`;
        @foreach($guestLists as $guestList)
            @php $listHasAny = false; @endphp
            @foreach($guestList->guests as $guest)
                @php $gm = $mergedData['per_guest_messages'][$guest->id] ?? null; @endphp
                @if(is_string($gm) && $gm !== '')
                    @php $listHasAny = true; @endphp
                @endif
            @endforeach
            @if($listHasAny)
                html += `<div class="border rounded-lg overflow-hidden">`;
                html += `<div class="bg-primary-50 text-primary-700 px-4 py-2 font-semibold">{{ addslashes($guestList->name) }}</div>`;
                html += `<div class="p-4 space-y-3">`;
                @foreach($guestList->guests as $guest)
                    @php $gm = $mergedData['per_guest_messages'][$guest->id] ?? null; @endphp
                    @if(is_string($gm) && $gm !== '')
                        html += `<div class="border rounded-md p-3 bg-white shadow-sm">`;
                        html += `<div class="flex items-center justify-between mb-2">`;
                        html += `<div class="font-semibold text-primary">{{ addslashes($guest->name) }}</div>`;
                        html += `<div class="text-xs text-secondary">Group: {{ addslashes(optional($guest->guestGroup)->name ?? '-') }}</div>`;
                        html += `</div>`;
                        html += `<div class="text-sm text-secondary whitespace-pre-wrap">{{ addslashes($gm) }}</div>`;
                        html += `</div>`;
                    @endif
                @endforeach
                html += `</div>`;
                html += `</div>`;
            @endif
        @endforeach
        html += `</div>`;
        if (!html) {
            html = '<div class="text-secondary">Individual messages have been generated, but none matched the current guest list selection.</div>';
        }
    @else
        html = '<div class="text-secondary">Individual messages have been generated for each guest with personalized content.</div>';
    @endif
    const modalHTML = `
        <div id="guestMessagesModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[75vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-primary">Individual Messages</h3>
                    <button onclick="closeGuestMessagesModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                ${html}
                <div class="mt-4 text-right">
                    <button onclick="closeGuestMessagesModal()" class="bg-primary-500 text-white px-4 py-2 rounded hover:bg-primary-600">Close</button>
                </div>
            </div>
        </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

window.showMessageModal = function(title, message) {
    // Create modal HTML
    const modalHTML = `
        <div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-primary">${title}</h3>
                    <button onclick="closeMessageModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="text-secondary whitespace-pre-wrap">${message}</div>
                <div class="mt-4 text-right">
                    <button onclick="closeMessageModal()" class="bg-primary-500 text-white px-4 py-2 rounded hover:bg-primary-600">
                        Close
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

window.closeMessageModal = function() {
    const modal = document.getElementById('messageModal');
    if (modal) {
        modal.remove();
    }
}

window.closeGuestMessagesModal = function() {
    const modal = document.getElementById('guestMessagesModal');
    if (modal) {
        modal.remove();
    }
}

// Scheduled date validation functions
function updateScheduledDateMin() {
    const scheduledInputEl = document.getElementById('scheduled_at');
    if (!scheduledInputEl) return;
    
    // Get current date and time in user's timezone
    const now = new Date();
    
    // Add 1 minute buffer to ensure we're always in the future
    const nextMinute = new Date(now);
    nextMinute.setSeconds(0, 0);
    nextMinute.setMinutes(nextMinute.getMinutes() + 1);
    
    const year = nextMinute.getFullYear();
    const month = String(nextMinute.getMonth() + 1).padStart(2, '0');
    const day = String(nextMinute.getDate()).padStart(2, '0');
    const hours = String(nextMinute.getHours()).padStart(2, '0');
    const minutes = String(nextMinute.getMinutes()).padStart(2, '0');
    
    // Format as datetime-local input expects (YYYY-MM-DDTHH:MM)
    const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    
    // Update the min attribute to block past dates in calendar
    scheduledInputEl.min = minDateTime;
    
    // Also set the max attribute if event start date is available
    const startDateInput = document.querySelector('input[name="start_date"]');
    if (startDateInput && startDateInput.value) {
        const eventStartDate = new Date(startDateInput.value);
        const eventStartMinusOne = new Date(eventStartDate);
        eventStartMinusOne.setSeconds(0, 0);
        eventStartMinusOne.setMinutes(eventStartMinusOne.getMinutes() - 1);
        
        const maxYear = eventStartMinusOne.getFullYear();
        const maxMonth = String(eventStartMinusOne.getMonth() + 1).padStart(2, '0');
        const maxDay = String(eventStartMinusOne.getDate()).padStart(2, '0');
        const maxHours = String(eventStartMinusOne.getHours()).padStart(2, '0');
        const maxMinutes = String(eventStartMinusOne.getMinutes()).padStart(2, '0');
        const maxDateTime = `${maxYear}-${maxMonth}-${maxDay}T${maxHours}:${maxMinutes}`;
        scheduledInputEl.max = maxDateTime;
    }
    
    // Validate current value
    validateScheduledDate();
    
    // Add additional calendar blocking
    blockCalendarPastDates(scheduledInputEl);
    
    // Update suggested time buttons
    updateSuggestedTimeButtons();
}

// Validate scheduled date is in the future and before event start
function validateScheduledDate() {
    const scheduledInputEl = document.getElementById('scheduled_at');
    if (!scheduledInputEl || !scheduledInputEl.value) return true;
    
    const selectedDate = new Date(scheduledInputEl.value);
    const now = new Date();
    
    // Add a small buffer (1 minute) to account for time differences
    const bufferTime = 60 * 1000; // 1 minute in milliseconds
    const minAllowedTime = new Date(now.getTime() + bufferTime);
    
    // Check if date is in the past
    if (selectedDate <= minAllowedTime) {
        scheduledInputEl.value = '';
        immediateAutoSave();
        showScheduledDateError('Please select a future date and time for sending invitations.');
        return false;
    }
    
    // Check if date is after event start date
    const startDateInput = document.querySelector('input[name="start_date"]');
    if (startDateInput && startDateInput.value) {
        const eventStartDate = new Date(startDateInput.value);
        if (selectedDate >= eventStartDate) {
            scheduledInputEl.value = '';
            immediateAutoSave();
            showScheduledDateError('Send date must be before the event start date.');
            return false;
        }
    }
    
    return true;
}

// Update scheduled date validation every minute
function startScheduledDateValidation() {
    // Update every minute
    setInterval(updateScheduledDateMin, 60000);
}

// Block past dates in the calendar picker
function blockCalendarPastDates(input) {
    if (!input) return;
    
    // Add event listener to prevent manual entry of past dates
    input.addEventListener('keydown', function(e) {
        // Allow navigation keys
        if ([8, 9, 13, 27, 37, 38, 39, 40, 46].includes(e.keyCode)) {
            return;
        }
        
        // Allow numbers and common separators
        if (e.keyCode >= 48 && e.keyCode <= 57 || e.key === '-' || e.key === ':' || e.key === 'T') {
            return;
        }
        
        // Prevent other characters
        e.preventDefault();
    });
    
    // Add input event listener to validate as user types
    input.addEventListener('input', function() {
        if (this.value) {
            const selectedDate = new Date(this.value);
            const now = new Date();
            
            // If selected date is in the past, clear it
            if (selectedDate <= now) {
                this.value = '';
                showScheduledDateError('Cannot select past dates. Please choose a future date and time.');
            }
        }
    });
    
    // Add change event listener for calendar picker
    input.addEventListener('change', function() {
        if (this.value) {
            const selectedDate = new Date(this.value);
            const now = new Date();
            
            // If selected date is in the past, clear it and show error
            if (selectedDate <= now) {
                this.value = '';
                showScheduledDateError('Cannot select past dates. Please choose a future date and time.');
                return false;
            }
        }
    });
}

// Update suggested time buttons based on validity
function updateSuggestedTimeButtons() {
    const buttons = document.querySelectorAll('.suggested-time-btn');
    const userTimezone = '{{ Auth::user()->timezone ?? "UTC" }}';
    const now = new Date();
    const eventStartIso = '{{ isset($allData['start_date']) ? \Carbon\Carbon::parse($allData['start_date'])->toIso8601String() : '' }}';
    const eventStart = eventStartIso ? new Date(eventStartIso) : null;
    
    buttons.forEach(button => {
        const hour = parseInt(button.getAttribute('data-hour'));
        const minute = parseInt(button.getAttribute('data-minute'));
        
        // Try today first
        const today = new Date();
        today.setHours(hour, minute, 0, 0);
        
        // Try tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(hour, minute, 0, 0);
        
        // Convert to user's timezone if not UTC
        let todayInUserTz = today;
        let tomorrowInUserTz = tomorrow;
        
        if (userTimezone !== 'UTC') {
            // Convert today to user's timezone
            const todayUserTime = new Date(today.toLocaleString("en-US", {timeZone: userTimezone}));
            todayInUserTz = new Date(todayUserTime);
            todayInUserTz.setHours(hour, minute, 0, 0);
            
            // Convert tomorrow to user's timezone
            const tomorrowUserTime = new Date(tomorrow.toLocaleString("en-US", {timeZone: userTimezone}));
            tomorrowInUserTz = new Date(tomorrowUserTime);
            tomorrowInUserTz.setHours(hour, minute, 0, 0);
        }
        
        // Check if either today or tomorrow is valid
        const todayValid = todayInUserTz > now && (!eventStart || todayInUserTz < eventStart);
        const tomorrowValid = tomorrowInUserTz > now && (!eventStart || tomorrowInUserTz < eventStart);
        const isValid = todayValid || tomorrowValid;
        
        if (isValid) {
            // Enable button
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
            button.classList.add('hover:border-gray-300', 'cursor-pointer');
            
            // Add click event listener
            button.onclick = function() {
                setSuggestedTime(hour, minute);
            };
            
            // Update label to show which day
            const timeLabel = button.querySelector('strong');
            const dayLabel = button.querySelector('span');
            if (todayValid) {
                timeLabel.textContent = `${hour === 12 ? '12' : hour > 12 ? hour - 12 : hour}:${minute.toString().padStart(2, '0')} ${hour >= 12 ? 'PM' : 'AM'}`;
                dayLabel.textContent = 'Today';
            } else {
                timeLabel.textContent = `${hour === 12 ? '12' : hour > 12 ? hour - 12 : hour}:${minute.toString().padStart(2, '0')} ${hour >= 12 ? 'PM' : 'AM'}`;
                dayLabel.textContent = 'Tomorrow';
            }
        } else {
            // Disable button
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
            button.classList.remove('hover:border-gray-300', 'cursor-pointer');
            
            // Remove click event listener
            button.onclick = null;
            
            // Update label to show unavailable
            const timeLabel = button.querySelector('strong');
            const dayLabel = button.querySelector('span');
            timeLabel.textContent = `${hour === 12 ? '12' : hour > 12 ? hour - 12 : hour}:${minute.toString().padStart(2, '0')} ${hour >= 12 ? 'PM' : 'AM'}`;
            dayLabel.textContent = 'Unavailable';
        }
    });
}

// Auto-save functionality
function autoSave() {
    if (isAutoSaving) return;
    
    const formEl = document.getElementById('event-form-4');
    if (!formEl) return;
    const formData = new FormData(formEl);
    const currentData = JSON.stringify(Object.fromEntries(formData));
    
    // Only save if data has changed
    if (currentData === lastSavedData) return;
    
    isAutoSaving = true;
    lastSavedData = currentData;
    
    fetch('{{ route("organizer.events.create.auto-save") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        // Silent auto-save
    })
    .catch(error => {
        // Silent error handling
    })
    .finally(() => {
        isAutoSaving = false;
    });
}

// Immediate save for important fields
function immediateAutoSave() {
    clearTimeout(autoSaveTimeout);
    autoSave();
}

function debounceAutoSave() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(autoSave, 500); // Save after 0.5 seconds of inactivity for faster response
}

document.addEventListener('DOMContentLoaded', function() {
    const sendTypeInputs = document.querySelectorAll('input[name="send_type"]');
    const scheduledSection = document.getElementById('scheduled-time-section');
    const actionText = document.getElementById('action-text');
    const scheduledInput = document.getElementById('scheduled_at');
    const eventStartIso = '{{ isset($allData['start_date']) ? \Carbon\Carbon::parse($allData['start_date'])->toIso8601String() : '' }}';
    
    // Detect user's timezone and update display
    function detectAndDisplayTimezone() {
        const userTimezone = '{{ Auth::user()->timezone ?? "UTC" }}';
        const browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        
        // Update timezone display with more information
        const timezoneDisplay = document.querySelector('.text-primary-600.font-medium');
        if (timezoneDisplay) {
            if (userTimezone === 'UTC' && browserTimezone !== 'UTC') {
                timezoneDisplay.innerHTML = `
                    <i class="fas fa-clock mr-1"></i> Your timezone: ${userTimezone} 
                    <span class="text-warning-600 text-xs">(Browser detected: ${browserTimezone})</span>
                `;
            } else {
                // Show the actual timezone name if it's not UTC
                const timezoneName = userTimezone === 'UTC' ? 'UTC' : userTimezone;
                timezoneDisplay.innerHTML = `
                    <i class="fas fa-clock mr-1"></i> Your timezone: ${timezoneName}
                `;
            }
        }
    }
    
    // Call timezone detection
    detectAndDisplayTimezone();
    
    // Get user timezone from server
    const userTimezone = '{{ Auth::user()->timezone ?? "UTC" }}';
    
    // Set minimum datetime to the next full minute in user's timezone
    const now = new Date();
    let minDateTime;
    
    if (userTimezone === 'UTC') {
        // If user timezone is UTC, use UTC time
        const nextMinute = new Date(now);
        nextMinute.setSeconds(0, 0);
        nextMinute.setMinutes(nextMinute.getMinutes() + 1);
        minDateTime = nextMinute.toISOString().slice(0, 16);
    } else {
        // Convert current time to user's timezone and add 1 minute
        const userTime = new Date(now.toLocaleString("en-US", {timeZone: userTimezone}));
        const nextMinute = new Date(userTime);
        nextMinute.setMinutes(nextMinute.getMinutes() + 1);
        nextMinute.setSeconds(0, 0);
        
        // Format for datetime-local input (YYYY-MM-DDTHH:MM)
        const year = nextMinute.getFullYear();
        const month = String(nextMinute.getMonth() + 1).padStart(2, '0');
        const day = String(nextMinute.getDate()).padStart(2, '0');
        const hours = String(nextMinute.getHours()).padStart(2, '0');
        const minutes = String(nextMinute.getMinutes()).padStart(2, '0');
        minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    }
    
    if (scheduledInput) {
        scheduledInput.min = minDateTime;
    }

    // Set maximum datetime to 1 minute before event start (strictly before event start)
    if (eventStartIso) {
        const eventStart = new Date(eventStartIso);
        const eventStartMinusOne = new Date(eventStart);
        eventStartMinusOne.setSeconds(0, 0);
        eventStartMinusOne.setMinutes(eventStartMinusOne.getMinutes() - 1);
        const maxDateTime = eventStartMinusOne.toISOString().slice(0, 16);
        if (scheduledInput) {
            scheduledInput.max = maxDateTime;
        }
    }
    
    function updateSendingMode() {
        const selectedType = document.querySelector('input[name="send_type"]:checked').value;
        const isUpdate = {{ request()->has('mode') && request()->get('mode') === 'update' ? 'true' : 'false' }};
        
        if (selectedType === 'scheduled') {
            scheduledSection.classList.remove('hidden');
            scheduledSection.classList.add('block');
            updateActionText();
        } else {
            scheduledSection.classList.add('hidden');
            scheduledSection.classList.remove('block');
            if (isUpdate) {
                actionText.innerHTML = '<strong>You are about to update your event and send invitations to {{ $totalGuests ?? 0 }} guests immediately.</strong>';
            } else {
                actionText.innerHTML = '<strong>You are about to send invitations to {{ $totalGuests ?? 0 }} guests immediately.</strong>';
            }
        }
    }
    
    sendTypeInputs.forEach(input => {
        input.addEventListener('change', updateSendingMode);
    });
    
    if (scheduledInput) {
        scheduledInput.addEventListener('change', function() {
            updateActionText();
            const value = scheduledInput.value;
            if (!value) return;
            
            // Convert selected datetime to user's timezone for comparison
            const selected = new Date(value);
            const now = new Date();
            
            // Create comparison time in user's timezone
            let comparisonTime;
            if (userTimezone === 'UTC') {
                comparisonTime = new Date(now);
                comparisonTime.setSeconds(0, 0);
                comparisonTime.setMinutes(comparisonTime.getMinutes() + 1);
            } else {
                // Convert current time to user's timezone
                const userTime = new Date(now.toLocaleString("en-US", {timeZone: userTimezone}));
                comparisonTime = new Date(userTime);
                comparisonTime.setMinutes(comparisonTime.getMinutes() + 1);
                comparisonTime.setSeconds(0, 0);
            }
            
            if (selected < comparisonTime) {
                showScheduleErrorModal('Scheduled time must be after the current time (at least one minute ahead).');
                scheduledInput.value = '';
                updateActionText();
                return;
            }
            if (eventStartIso) {
                const eventStart = new Date(eventStartIso);
                if (selected >= eventStart) {
                    showScheduleErrorModal('Scheduled time must be strictly before the event start time.');
                    scheduledInput.value = '';
                    updateActionText();
                    return;
                }
                const eventStartMinusOne = new Date(eventStart);
                eventStartMinusOne.setSeconds(0, 0);
                eventStartMinusOne.setMinutes(eventStartMinusOne.getMinutes() - 1);
                if (selected > eventStartMinusOne) {
                    showScheduleErrorModal('Scheduled time must be at least one minute before the event start time.');
                    scheduledInput.value = '';
                    updateActionText();
                    return;
                }
            }
        });
    }
    
    // Initialize
    updateSendingMode();
    
    // Set initial min date in user's timezone immediately
    updateScheduledDateMin();

    // Initialize scheduled date validation
    startScheduledDateValidation();
    
    // Initialize suggested time buttons
    updateSuggestedTimeButtons();
    
    const autoSaveInputs = document.querySelectorAll('input, textarea, select');
    
    autoSaveInputs.forEach(input => {
        // Use immediate save for important fields
        if (input.name === 'send_type' || input.name === 'scheduled_at') {
            input.addEventListener('input', immediateAutoSave);
            input.addEventListener('change', immediateAutoSave);
        } else {
            input.addEventListener('input', debounceAutoSave);
            input.addEventListener('change', debounceAutoSave);
        }
        
        // Clear errors when user starts typing
        input.addEventListener('input', function() {
            if (this.classList.contains('border-danger-500')) {
                this.classList.remove('border-danger-500', 'bg-danger-50');
                const errorContainer = document.getElementById(`error-${this.name}`);
                if (errorContainer) {
                    errorContainer.remove();
                }
            }
        });
    });
    
    // Add scheduled date validation listeners
    if (scheduledInput) {
        scheduledInput.addEventListener('change', function() {
            // Validate scheduled date when it changes
            if (!validateScheduledDate()) {
                return; // Stop processing if validation failed
            }
        });
        
        // Also validate on input event for real-time feedback
        scheduledInput.addEventListener('input', function() {
            // Only validate if there's a complete datetime value
            if (this.value && this.value.length >= 16) {
                validateScheduledDate();
            }
        });
    }
    
    // Handle form submission via AJAX to prevent page refresh
    const form = document.getElementById('event-form-4');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validate scheduled date before submission
            if (!validateScheduledDate()) {
                e.preventDefault();
                return false;
            }
            
            e.preventDefault(); // Prevent default form submission

            clearTimeout(autoSaveTimeout);
            
            // Show loading state
            const submitButton = document.getElementById('finalSubmit');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            
            // Determine if this is an update or create based on session
            const isUpdate = {{ request()->has('mode') && request()->get('mode') === 'update' ? 'true' : 'false' }};
            const loadingText = isUpdate ? 'Updating Event...' : 'Creating Event...';
            submitButton.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${loadingText}`;
            
            // Submit form data via AJAX to processStep4
            const formData = new FormData(this);
            const currentData = JSON.stringify(Object.fromEntries(formData));
            
            fetch(this.closest('form').action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'X-Form-Submission': 'true', // Mark this as a form submission
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(response => {
                // Handle redirect response
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // If not JSON, get the text response
                    return response.text().then(text => {
                        throw new Error('Server returned non-JSON response: ' + text);
                    });
                }
            })
            .then(data => {
                if (data && data.success && data.redirect) {
                    // Success - redirect to the specified URL
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else if (data && data.errors) {
                    // Validation errors
                    showFormErrors(data.errors);
                } else if (data && data.message) {
                    // General error message
                    showNotification(data.message, 'error');
                } else {
                    // Unknown error
                    showNotification('An unexpected error occurred. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Form submission error:', error);
                showNotification('There was an error creating the event. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }
});


</script>
@endsection
<script>
window.toggleGuestDetails = function() {
    const el = document.getElementById('guest-details');
    if (!el) return;
    if (el.classList.contains('hidden')) {
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
};
</script>