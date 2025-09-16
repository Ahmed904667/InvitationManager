@extends('layouts.organizer')

@section('title', 'Update Sent Event')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <!-- Back Button - Left -->
        <a href="{{ route('organizer.events.show', $event) }}" class="inline-flex items-center text-primary-600 hover:text-primary-800 text-sm font-medium">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to Event
        </a>
        
        <!-- Title - Center -->
        <h1 class="text-4xl font-bold text-primary">
            Update Sent Event
        </h1>
        
        <!-- Spacer for balance -->
        <div class="w-24"></div>
    </div>

    <div class="text-center mb-8">
        <p class="text-lg text-secondary">
            Manage your event and guests after invitations have been sent
        </p>
        <div class="mt-4">
            <span class="badge badge-sent text-primary px-4 py-2 rounded-full">
                <i class="fas fa-paper-plane mr-2"></i>Invitations Sent
            </span>
        </div>
    </div>

    {{-- Event Summary Card --}}
    <div class="card mb-8">
        <div class="card-header">
            <h2 class="text-2xl font-semibold text-primary">
                <i class="fas fa-calendar-alt text-primary-500 mr-2"></i> Event Summary
            </h2>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-calendar text-primary-500 mr-2"></i>
                        <span class="font-semibold text-primary">Event Details</span>
                    </div>
                    <p class="text-sm text-secondary">{{ $event->name }}</p>
                    <p class="text-xs text-secondary mt-1">
                        {{ $event->start_date->format('M j, Y g:i A') }}
                    </p>
                </div>
                
                <div class="bg-success-50 border border-success-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-users text-success-500 mr-2"></i>
                        <span class="font-semibold text-success-700">Total Guests</span>
                    </div>
                    <p class="text-sm text-secondary">{{ $totalGuests }} guests</p>
                    <p class="text-xs text-secondary mt-1">{{ $event->guestLists->count() }} guest lists</p>
                </div>
                
                <div class="bg-info-50 border border-info-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-envelope text-info-500 mr-2"></i>
                        <span class="font-semibold text-info-700">Invitations</span>
                    </div>
                    <p class="text-sm text-secondary">{{ $invitationsSent }} sent</p>
                    <p class="text-xs text-secondary mt-1">{{ $invitationsPending }} pending</p>
                </div>
                

            </div>
        </div>
    </div>

    {{-- Update Options Tabs --}}
    <div class="card">
        <div class="card-header">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button onclick="showTab('basic-info')" id="tab-basic-info" class="tab-button active border-primary-500 text-primary-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-edit mr-2"></i>Basic Information
                    </button>
                    <button onclick="showTab('guest-management')" id="tab-guest-management" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-users mr-2"></i>Guest Management
                    </button>
                    <button onclick="showTab('event-settings')" id="tab-event-settings" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-cog mr-2"></i>Event Settings
                    </button>
                </nav>
            </div>
        </div>

        <div class="card-body">
            {{-- Basic Information Tab --}}
            <div id="tab-content-basic-info" class="tab-content active">
                <form action="{{ route('organizer.events.update-sent.basic', $event) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="form-label">Event Name <span class="text-danger-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $event->name) }}" class="form-input" required>
                        </div>
                        
                        <div>
                            <label for="start_date" class="form-label">Start Date & Time <span class="text-danger-500">*</span></label>
                            <input type="datetime-local" id="start_date" name="start_date" 
                                   value="{{ old('start_date', $event->start_date->format('Y-m-d\TH:i')) }}" 
                                   class="form-input" required>
                        </div>
                        
                        <div>
                            <label for="end_date" class="form-label">End Date & Time</label>
                            <input type="datetime-local" id="end_date" name="end_date" 
                                   value="{{ old('end_date', $event->end_date ? $event->end_date->format('Y-m-d\TH:i') : '') }}" 
                                   class="form-input">
                        </div>
                        
                        <div>
                            <label for="venue_name" class="form-label">Venue Name</label>
                            <input type="text" id="venue_name" name="venue_name" value="{{ old('venue_name', $event->venue_name) }}" class="form-input">
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="form-label">Event Description</label>
                        <textarea id="description" name="description" rows="3" class="form-input @error('description') border-danger-500 @enderror">{{ old('description', $event->description) }}</textarea>
                    </div>
                    
                    <div>
                        <label for="venue_address" class="form-label">Venue Address</label>
                        <input type="text" id="venue_address" name="venue_address" value="{{ old('venue_address', $event->venue_address) }}" class="form-input">
                        <small class="text-secondary text-sm">You can also select a point on the map below.</small>
                    </div>
                    
                    <div class="mt-4">
                        <div id="map" class="w-full rounded-lg border border-gray-200" style="height: 320px;"></div>
                        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $event->latitude ?? '') }}">
                        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $event->longitude ?? '') }}">
                    </div>
                    
                    {{-- Update Notification Checkbox --}}
                    <div class="border-t pt-6">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" id="send_update_notification" name="send_update_notification" class="form-checkbox h-5 w-5 text-primary-600" value="1">
                            <label for="send_update_notification" class="form-label text-lg">
                                <i class="fas fa-bell text-primary-500 mr-2"></i>Send update notification to all guests
                            </label>
                        </div>
                        <p class="text-secondary text-sm mt-2 ml-8">
                            Check this box if you want to notify all guests about the changes made to the event.
                        </p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Update Basic Information
                        </button>
                    </div>
                </form>
            </div>

            {{-- Guest Management Tab --}}
            <div id="tab-content-guest-management" class="tab-content hidden">
                {{-- Toolbar --}}
                <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <h3 class="text-lg font-semibold text-primary">
                                <i class="fas fa-users mr-2"></i>Guest Management
                            </h3>
                            <span class="text-sm text-secondary bg-gray-100 px-2 py-1 rounded">
                                {{ $totalGuests }} total guests
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button onclick="showPendingGuestsModal()" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus mr-1"></i>Add Guests
                            </button>
                            <button id="send-invitations-btn" onclick="showInvitationModal()" class="btn btn-success btn-sm" style="display: none;">
                                <i class="fas fa-paper-plane mr-1"></i>Send Invitations (<span id="pending-count">0</span>)
                            </button>
                        </div>
                    </div>
                </div>

                {{-- New Guests Section (Hidden by default) --}}
                <div id="pending-guests-section" class="mb-6" style="display: none;">
                    <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-warning-500 mr-2"></i>
                                <h4 class="font-semibold text-warning-700">New Guests - Ready for Invitation</h4>
                            </div>
                            <button onclick="hidePendingGuestsSection()" class="text-warning-600 hover:text-warning-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <p class="text-sm text-warning-600 mt-2">
                            These guests have been added but haven't received invitations yet. Write personalized messages and send invitations when ready.
                        </p>
                    </div>
                    <div id="pending-guests-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Pending guests will be dynamically added here -->
                    </div>
                </div>

                {{-- Current Guests Grid --}}
                <div>
                    <h4 class="text-md font-semibold text-primary mb-4">
                        <i class="fas fa-users mr-2"></i>Current Guests
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="current-guests-grid">
                        @foreach($activeEventGuests as $eventGuest)
                            @php $guest = $eventGuest->guest @endphp
                            <div class="guest-card bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow" data-guest-id="{{ $guest->id }}">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h5 class="font-semibold text-primary">{{ $guest->name }}</h5>
                                        <p class="text-sm text-secondary">{{ $guest->getGuestListName() }}</p>
                                        @if($guest->email)
                                            <p class="text-xs text-gray-500">{{ $guest->email }}</p>
                                        @endif
                                        @if($guest->phone)
                                            <p class="text-xs text-gray-500">{{ $guest->phone }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center">
                                        <button onclick="confirmDeleteGuest({{ $guest->id }}, '{{ $guest->name }}')" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- New Guests Modal --}}
                <div id="pending-guests-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                    <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-primary">Add New Guests</h3>
                            <button onclick="hidePendingGuestsModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="space-y-6">
                            {{-- Individual Guest Form --}}
                            <div class="border-b pb-4">
                                <h4 class="font-medium text-primary mb-3">Add Individual Guest</h4>
                                <form id="add-individual-guest-form" class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                            <label for="individual_guest_name" class="form-label">Guest Name <span class="text-danger-500">*</span></label>
                                            <input type="text" id="individual_guest_name" name="name" class="form-input" required>
                            </div>
                            <div>
                                            <label for="individual_guest_email" class="form-label">Email</label>
                                            <input type="email" id="individual_guest_email" name="email" class="form-input">
                            </div>
                                    </div>
                                    <div>
                                        <label for="individual_guest_phone" class="form-label">Phone</label>
                                        <input type="text" id="individual_guest_phone" name="phone" class="form-input phone-input">
                                    </div>
                                    <button type="button" onclick="addIndividualGuestToPending()" class="btn btn-primary btn-sm">
                                        <i class="fas fa-user-plus mr-1"></i>Add Guest
                                    </button>
                        </form>
                </div>

                            {{-- Guest List Selection --}}
                            <div class="border-b pb-4">
                                <h4 class="font-medium text-primary mb-3">Add Guest List</h4>
                                <form id="add-guest-list-form" class="space-y-4">
                            <div>
                                <label for="modal_guest_list_id" class="form-label">Select Guest List</label>
                                <select id="modal_guest_list_id" name="guest_list_id" class="form-select" required>
                                    <option value="">Choose a guest list...</option>
                                    @foreach($availableGuestLists as $guestList)
                                        <option value="{{ $guestList->id }}">
                                            {{ $guestList->name }} ({{ $guestList->guests->count() }} guests)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                                    <button type="button" onclick="addGuestListToPending()" class="btn btn-primary btn-sm">
                                        <i class="fas fa-list-plus mr-1"></i>Add List to New Guests
                                    </button>
                                </form>
                            </div>

                            {{-- Current New Guests List --}}
                            <div>
                                <h4 class="font-medium text-primary mb-3">New Guests (<span id="pending-modal-count">0</span>)</h4>
                                <div id="pending-modal-list" class="space-y-2 max-h-40 overflow-y-auto">
                                    <p class="text-gray-500 text-sm">No new guests yet. Add guests above.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                            <button type="button" onclick="hidePendingGuestsModal()" class="btn btn-secondary">
                                    Cancel
                                </button>
                            <button type="button" onclick="proceedToInvitations()" class="btn btn-success" id="proceed-btn" disabled>
                                <i class="fas fa-arrow-right mr-1"></i>Proceed to Send Invitations
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Invitation Composition Modal --}}
                <div id="invitation-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                    <div class="bg-white rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-primary">Send Invitations to New Guests</h3>
                            <button onclick="hideInvitationModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <form id="send-invitations-form" class="space-y-6">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                {{-- Platform Selection --}}
                                <div>
                                    <h4 class="font-medium text-primary mb-3">Invitation Platforms</h4>
                                    <div class="space-y-3">
                                        @if(in_array('email', $event->invitation_platforms ?? []))
                                            <label class="flex items-center space-x-3">
                                                <input type="checkbox" name="platforms[]" value="email" class="form-checkbox h-5 w-5 text-primary-600" checked>
                                                <span class="text-primary">
                                                    <i class="fas fa-envelope mr-2"></i>Email
                                                </span>
                                            </label>
                                        @endif
                                        @if(in_array('whatsapp', $event->invitation_platforms ?? []))
                                            <label class="flex items-center space-x-3">
                                                <input type="checkbox" name="platforms[]" value="whatsapp" class="form-checkbox h-5 w-5 text-primary-600" checked>
                                                <span class="text-primary">
                                                    <i class="fab fa-whatsapp mr-2"></i>WhatsApp
                                                </span>
                                            </label>
                                        @endif
                                    </div>
                                </div>

                                {{-- Message Type Selection --}}
                                <div>
                                    <h4 class="font-medium text-primary mb-3">Message Type</h4>
                                    <div class="space-y-3">
                                        <label class="flex items-center space-x-3">
                                            <input type="radio" name="message_type" value="general" class="form-radio h-5 w-5 text-primary-600" checked>
                                            <span>Use general message for all guests</span>
                                        </label>
                                        <label class="flex items-center space-x-3">
                                            <input type="radio" name="message_type" value="individual" class="form-radio h-5 w-5 text-primary-600">
                                            <span>Create individual messages for each guest</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- General Message Section --}}
                            <div id="general-message-section">
                                <h4 class="font-medium text-primary mb-3">General Invitation Message</h4>
                                <textarea id="general_message" name="general_message" rows="6" class="form-textarea w-full" 
                                          placeholder="Example: Hello! You're invited to join us for {{ $event->name }}. We're excited to have you celebrate with us. Please mark your calendar for {{ $event->start_date->format('M j, Y') }} at {{ $event->venue_name ?? '[Venue]' }}. Your personal invitation with all details and RSVP link will follow shortly.">{{ $event->general_message ?? "Hello! You're invited to join us for {$event->name}. We're excited to have you celebrate with us on {$event->start_date->format('M j, Y')} at {$event->venue_name}. Please save the date!" }}</textarea>
                                <div class="mt-2 space-y-2">
                                    <p class="text-secondary text-sm">
                                        This message will be sent to all new guests. Event details and RSVP link will be automatically included.
                                    </p>
                                    <div class="text-xs text-gray-600 bg-gray-50 p-2 rounded">
                                        <strong>Suggestions:</strong>
                                        <ul class="list-disc list-inside mt-1 space-y-1">
                                            <li>Welcome guests warmly and express excitement</li>
                                            <li>Mention the event name: {{ $event->name }}</li>
                                            <li>Include the date: {{ $event->start_date->format('M j, Y') }}</li>
                                            @if($event->venue_name)
                                            <li>Reference the venue: {{ $event->venue_name }}</li>
                                            @endif
                                            <li>Create anticipation and encourage attendance</li>
                                            <li>Keep it personal and friendly</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- Individual Messages Section --}}
                            <div id="individual-messages-section" class="hidden">
                                <h4 class="font-medium text-primary mb-3">Individual Messages</h4>
                                <div id="individual-messages-container">
                                    <!-- Individual message forms will be populated here -->
                                </div>
                            </div>

                            {{-- Guest Summary --}}
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-primary mb-3">Invitation Summary</h4>
                                <div id="invitation-summary">
                                    <p class="text-gray-600">No pending guests selected.</p>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3 pt-4 border-t">
                                <button type="button" onclick="hideInvitationModal()" class="btn btn-secondary">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane mr-1"></i>Send Invitations
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Delete Confirmation Modal --}}
                <div id="delete-confirmation-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                    <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-exclamation-triangle text-warning-500 text-4xl mb-4"></i>
                            <h3 class="text-lg font-semibold text-primary mb-2">Remove Guest from Event</h3>
                            <p class="text-secondary" id="delete-confirmation-text">
                                Are you sure you want to remove this guest? They will no longer receive event updates.
                            </p>
                        </div>
                        
                        <form id="delete-guest-form" action="{{ route('organizer.events.update-sent.remove-guest', $event) }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" id="delete-guest-id" name="guest_id">
                            
                            {{-- Guest Info Display --}}
                            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                <div class="text-left">
                                    <h4 class="font-semibold text-primary" id="delete-guest-name"></h4>
                                    <p class="text-sm text-secondary" id="delete-guest-email"></p>
                                    <p class="text-sm text-secondary" id="delete-guest-phone"></p>
                                </div>
                            </div>
                            
                            {{-- Notification Options --}}
                            <div class="text-left">
                                <h4 class="font-medium text-primary mb-3">Send Notification Message</h4>
                                <p class="text-sm text-secondary mb-4">Send a message to inform the guest about their removal from the event.</p>
                                
                                {{-- Platform Selection --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Send via:</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="notify_email" value="1" class="form-checkbox mr-2" checked>
                                            <span class="text-sm">Email</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="notify_whatsapp" value="1" class="form-checkbox mr-2">
                                            <span class="text-sm">WhatsApp</span>
                                        </label>
                                    </div>
                                </div>
                                
                                {{-- Message Composition --}}
                                <div class="mb-4">
                                    <label for="delete-notification-message" class="block text-sm font-medium text-gray-700 mb-2">
                                        Message to Guest:
                                    </label>
                                    <textarea 
                                        id="delete-notification-message" 
                                        name="notification_message" 
                                        rows="4" 
                                        class="form-textarea w-full"
                                        placeholder="Dear [Guest Name],&#10;&#10;We regret to inform you that your invitation to [Event Name] has been cancelled.&#10;&#10;If you have any questions, please don't hesitate to contact us.&#10;&#10;Best regards,&#10;[Your Name]"
                                    ></textarea>
                                    <p class="text-xs text-gray-500 mt-1">This message will be sent to inform the guest about their removal from the event.</p>
                                </div>
                            </div>
                            
                            <div class="flex justify-center space-x-3 pt-4 border-t">
                                <button type="button" onclick="hideDeleteConfirmationModal()" class="btn btn-secondary">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash mr-1"></i>Remove Guest & Send Notification
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            {{-- Event Settings Tab --}}
            <div id="tab-content-event-settings" class="tab-content hidden">
                <form action="{{ route('organizer.events.update-sent.settings', $event) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- QR Code Settings --}}
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-primary mb-4">
                                <i class="fas fa-qrcode text-primary-500 mr-2"></i>QR Code Check-in
                            </h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Enable QR Code Check-in</label>
                                    <p class="text-xs text-gray-500 mt-1">Allow guests to check in using QR codes at the event</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="qr_checkin_enabled" value="1" 
                                           {{ old('qr_checkin_enabled', $event->qr_checkin_enabled) ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                        </div>

                        {{-- RSVP Settings --}}
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-primary mb-4">
                                <i class="fas fa-reply text-primary-500 mr-2"></i>RSVP
                            </h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Enable RSVP</label>
                                    <p class="text-xs text-gray-500 mt-1">Allow guests to respond to invitations with RSVP</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="rsvp_enabled" value="1" 
                                           {{ old('rsvp_enabled', $event->rsvp_enabled) ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Update Notification Checkbox --}}
                    <div class="border-t pt-6">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" id="send_update_notification" name="send_update_notification" class="form-checkbox h-5 w-5 text-primary-600" value="1">
                            <label for="send_update_notification" class="form-label text-lg">
                                <i class="fas fa-bell text-primary-500 mr-2"></i>Send update notification to all guests
                            </label>
                        </div>
                        <p class="text-secondary text-sm mt-2 ml-8">
                            Check this box if you want to notify all guests about the changes made to the event settings.
                        </p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Update Event Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Guest List Modal --}}
<div id="guestListModal" class="modal hidden">
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="modal-title">Guest List Details</h3>
            <button onclick="closeModal('guestListModal')" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="guestListContent"></div>
        </div>
    </div>
</div>

{{-- Individual Messages Modal --}}
<div id="individualMessagesModal" class="modal hidden">
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="modal-title">Individual Messages</h3>
            <button onclick="closeModal('individualMessagesModal')" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="individualMessagesContent"></div>
        </div>
    </div>
</div>

<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-primary-500', 'text-primary-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('tab-content-' + tabName).classList.remove('hidden');
    document.getElementById('tab-content-' + tabName).classList.add('active');
    
    // Activate selected tab button
    document.getElementById('tab-' + tabName).classList.add('active', 'border-primary-500', 'text-primary-600');
    document.getElementById('tab-' + tabName).classList.remove('border-transparent', 'text-gray-500');
}

// Modal functionality
function showModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Pending Guests Management
let pendingGuestsList = [];

function showPendingGuestsModal() {
    showModal('pending-guests-modal');
}

function hidePendingGuestsModal() {
    hideModal('pending-guests-modal');
    clearPendingGuestsForms();
}

function showInvitationModal() {
    if (pendingGuestsList.length === 0) {
        showNotification('No pending guests to send invitations to.', 'error');
        return;
    }
    
    updateInvitationSummary();
    updateIndividualMessagesSection();
    showModal('invitation-modal');
}

function hideInvitationModal() {
    hideModal('invitation-modal');
}

function showDeleteConfirmationModal() {
    showModal('delete-confirmation-modal');
}

function hideDeleteConfirmationModal() {
    hideModal('delete-confirmation-modal');
}

function confirmDeleteGuest(guestId, guestName) {
    // Find the guest card to get additional information
    const guestCard = document.querySelector(`[data-guest-id="${guestId}"]`);
    
    // Set the guest ID
    document.getElementById('delete-guest-id').value = guestId;
    
    // Set the guest name
    document.getElementById('delete-guest-name').textContent = guestName;
    
    // Get email and phone from the card
    const emailElement = guestCard.querySelector('.text-gray-500');
    const phoneElement = guestCard.querySelectorAll('.text-gray-500')[1];
    
    const email = emailElement ? emailElement.textContent : '';
    const phone = phoneElement ? phoneElement.textContent : '';
    
    document.getElementById('delete-guest-email').textContent = email || 'No email provided';
    document.getElementById('delete-guest-phone').textContent = phone || 'No phone provided';
    
    // Update confirmation text
    const isStandalone = !guestCard.querySelector('.text-secondary').textContent.includes('Guest List');
    const confirmationText = isStandalone 
        ? `Are you sure you want to remove "${guestName}" from the event? They will no longer receive event updates and their invitation will be expired.`
        : `Are you sure you want to remove "${guestName}" from this event? They will no longer receive event updates and their invitation will be expired, but they will remain in their guest list for other events.`;
    
    document.getElementById('delete-confirmation-text').textContent = confirmationText;
    
    // Pre-populate message with event name
    const eventName = '{{ $event->name }}';
    const messageTextarea = document.getElementById('delete-notification-message');
    if (!messageTextarea.value.trim()) {
        messageTextarea.value = `Dear ${guestName},\n\nWe regret to inform you that your invitation to ${eventName} has been cancelled.\n\nIf you have any questions, please don't hesitate to contact us.\n\nBest regards,\n{{ Auth::user()->name }}`;
    }
    
    showDeleteConfirmationModal();
}

function toggleMessageBox(guestId) {
    const messageBox = document.getElementById(`message-box-${guestId}`);
    messageBox.classList.toggle('hidden');
}

function saveMessage(guestId) {
    const messageText = document.getElementById(`message-text-${guestId}`).value;
    
    // Send AJAX request to save the message
    fetch(`{{ route('organizer.events.update-sent.save-message', $event) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            guest_id: guestId,
            message: messageText
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Message saved successfully!', 'success');
            toggleMessageBox(guestId);
        } else {
            showNotification('Failed to save message. Please try again.', 'error');
        }
    })
    .catch(error => {
        
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function addIndividualGuestToPending() {
    const form = document.getElementById('add-individual-guest-form');
    const formData = new FormData(form);
    
    const guestData = {
        id: 'temp_' + Date.now(),
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        guest_list_name: 'Standalone Guest',
        type: 'individual'
    };
    
    // Validation
    if (!guestData.name.trim()) {
        showNotification('Guest name is required.', 'error');
        return;
    }
    
    // Email validation
    if (guestData.email && guestData.email.trim()) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(guestData.email.trim())) {
            showNotification('Please enter a valid email address.', 'error');
            return;
        }
    }
    
    // Phone validation (basic format check)
    if (guestData.phone && guestData.phone.trim()) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{8,15}$/;
        if (!phoneRegex.test(guestData.phone.trim())) {
            showNotification('Please enter a valid phone number.', 'error');
            return;
        }
    }
    
    // Check if either email or phone is provided
    if (!guestData.email?.trim() && !guestData.phone?.trim()) {
        showNotification('Please provide either an email address or phone number.', 'error');
        return;
    }
    
    pendingGuestsList.push(guestData);
    updatePendingGuestsDisplay();
    form.reset();
    showNotification('Guest added successfully!', 'success');
}

function addGuestListToPending() {
    const guestListId = document.getElementById('modal_guest_list_id').value;
    if (!guestListId) {
        showNotification('Please select a guest list.', 'error');
        return;
    }
    
    // In a real implementation, you'd fetch guest list data via AJAX
    // For now, we'll simulate adding a guest list
    const guestListOption = document.querySelector(`option[value="${guestListId}"]`);
    const guestListData = {
        id: 'list_' + guestListId + '_' + Date.now(),
        guest_list_id: parseInt(guestListId),
        guest_list_name: guestListOption.textContent,
        type: 'guest_list'
    };
    
    pendingGuestsList.push(guestListData);
    updatePendingGuestsDisplay();
    document.getElementById('modal_guest_list_id').value = '';
    showNotification('Guest list added to pending!', 'success');
}

function updatePendingGuestsDisplay() {
    const pendingModalList = document.getElementById('pending-modal-list');
    const pendingModalCount = document.getElementById('pending-modal-count');
    const proceedBtn = document.getElementById('proceed-btn');
    const sendInvitationsBtn = document.getElementById('send-invitations-btn');
    const pendingCountSpan = document.getElementById('pending-count');
    
    pendingModalCount.textContent = pendingGuestsList.length;
    pendingCountSpan.textContent = pendingGuestsList.length;
    
    if (pendingGuestsList.length === 0) {
        pendingModalList.innerHTML = '<p class="text-gray-500 text-sm">No new guests yet. Add guests above.</p>';
        proceedBtn.disabled = true;
        sendInvitationsBtn.style.display = 'none';
    } else {
        pendingModalList.innerHTML = pendingGuestsList.map((guest, index) => `
            <div class="flex items-center justify-between bg-white border rounded p-2">
                <div class="flex-1">
                    <span class="font-medium">${guest.name || guest.guest_list_name}</span>
                    <span class="text-sm text-gray-500 ml-2">(${guest.type === 'individual' ? 'Individual' : 'Guest List'})</span>
                    ${guest.type === 'individual' && (guest.email || guest.phone) ? 
                        `<div class="text-xs text-gray-400 mt-1">
                            ${guest.email ? guest.email : ''} ${guest.email && guest.phone ? ' | ' : ''} ${guest.phone ? guest.phone : ''}
                        </div>` : ''
                    }
                </div>
                <div class="flex items-center space-x-2">
                    ${guest.type === 'individual' ? 
                        `<button onclick="editPendingGuest(${index})" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>` : ''
                    }
                    <button onclick="removePendingGuest(${index})" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                </div>
            </div>
        `).join('');
        proceedBtn.disabled = false;
        sendInvitationsBtn.style.display = 'inline-flex';
    }
}

function removePendingGuest(index) {
    const guest = pendingGuestsList[index];
    const guestName = guest.name || guest.guest_list_name;
    pendingGuestsList.splice(index, 1);
    updatePendingGuestsDisplay();
    showNotification(`${guest.type === 'individual' ? 'Guest' : 'Guest list'} "${guestName}" removed.`, 'info');
}

function editPendingGuest(index) {
    const guest = pendingGuestsList[index];
    if (guest.type !== 'individual') return;
    
    // Fill the form with guest data
    document.getElementById('individual_guest_name').value = guest.name || '';
    document.getElementById('individual_guest_email').value = guest.email || '';
    document.getElementById('individual_guest_phone').value = guest.phone || '';
    
    // Remove the guest from the list temporarily
    removePendingGuest(index);
    
    // Show notification
    showNotification(`Editing guest "${guest.name}". Make changes and click "Add Guest" to save.`, 'info');
}

function clearPendingGuestsForms() {
    document.getElementById('add-individual-guest-form').reset();
    document.getElementById('modal_guest_list_id').value = '';
}

function proceedToInvitations() {
    if (pendingGuestsList.length === 0) {
        showNotification('Please add at least one guest before proceeding.', 'error');
        return;
    }
    hidePendingGuestsModal();
    showInvitationModal();
}

function showPendingGuestsSection() {
    document.getElementById('pending-guests-section').style.display = 'block';
}

function hidePendingGuestsSection() {
    document.getElementById('pending-guests-section').style.display = 'none';
}

function createCurrentGuestCard(guestData) {
    const card = document.createElement('div');
    card.className = 'guest-card bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow';
    card.setAttribute('data-guest-id', guestData.id);
    
    card.innerHTML = `
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h5 class="font-semibold text-primary">${guestData.name}</h5>
                <p class="text-sm text-secondary">${guestData.guest_list_name || 'Standalone Guest'}</p>
                ${guestData.email ? `<p class="text-xs text-gray-500">${guestData.email}</p>` : ''}
                ${guestData.phone ? `<p class="text-xs text-gray-500">${guestData.phone}</p>` : ''}
            </div>
            <div class="flex items-center">
                <button onclick="confirmDeleteGuest(${guestData.id}, '${guestData.name}')" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash mr-1"></i>Delete
                </button>
            </div>
        </div>
    `;
    
    return card;
}

function updateInvitationSummary() {
    const summaryDiv = document.getElementById('invitation-summary');
    if (pendingGuestsList.length === 0) {
        summaryDiv.innerHTML = '<p class="text-gray-600">No pending guests selected.</p>';
        return;
    }
    
    const individualGuests = pendingGuestsList.filter(g => g.type === 'individual');
    const guestLists = pendingGuestsList.filter(g => g.type === 'guest_list');
    
    let html = '<div class="space-y-2">';
    
    if (individualGuests.length > 0) {
        html += `<div>
            <span class="font-medium">Individual Guests (${individualGuests.length}):</span>
            <div class="ml-4 text-sm text-gray-600">
                ${individualGuests.map(g => g.name).join(', ')}
            </div>
        </div>`;
    }
    
    if (guestLists.length > 0) {
        html += `<div>
            <span class="font-medium">Guest Lists (${guestLists.length}):</span>
            <div class="ml-4 text-sm text-gray-600">
                ${guestLists.map(g => g.guest_list_name).join(', ')}
            </div>
        </div>`;
    }
    
    html += '</div>';
    summaryDiv.innerHTML = html;
}

function updateIndividualMessagesSection() {
    const messageTypeRadios = document.querySelectorAll('input[name="message_type"]');
    const generalSection = document.getElementById('general-message-section');
    const individualSection = document.getElementById('individual-messages-section');
    const container = document.getElementById('individual-messages-container');
    
    // Add event listeners for message type change
    messageTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'general') {
                generalSection.classList.remove('hidden');
                individualSection.classList.add('hidden');
            } else {
                generalSection.classList.add('hidden');
                individualSection.classList.remove('hidden');
                
                // Generate individual message forms for all guests (individual + guests in lists)
                generateIndividualMessageForms();
            }
        });
    });
}

async function generateIndividualMessageForms() {
    const container = document.getElementById('individual-messages-container');
    container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading guests...</div>';
    
    let allGuests = [];
    
    // Add individual guests
    const individualGuests = pendingGuestsList.filter(g => g.type === 'individual');
    allGuests = allGuests.concat(individualGuests);
    
    // Fetch and add guests from guest lists
    const guestLists = pendingGuestsList.filter(g => g.type === 'guest_list');
    
    try {
        for (const list of guestLists) {
            // Construct the URL properly for the guest list guests endpoint
            const url = `{{ url('organizer/guest-lists') }}/${list.guest_list_id}/guests`;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.guests && Array.isArray(data.guests)) {
                    // Add each guest from the list with their own message field
                    data.guests.forEach(guest => {
                        allGuests.push({
                            id: guest.id, // Use the actual guest ID for message mapping
                            name: guest.name,
                            email: guest.email || '',
                            phone: guest.phone || '',
                            type: 'guest_from_list',
                            guest_list_name: list.guest_list_name,
                            original_guest_id: guest.id
                        });
                    });
                }
            } else {
                
                // Fallback: add a placeholder for the list
                allGuests.push({
                    id: list.id + '_fallback',
                    name: `Guests from ${list.guest_list_name}`,
                    email: 'Multiple contacts',
                    phone: 'Multiple contacts',
                    type: 'guest_list_fallback',
                    guest_list_name: list.guest_list_name
                });
            }
        }
    } catch (error) {
        
        showNotification('Error loading guest lists. Please try again.', 'error');
        // Show fallback for all guest lists
        guestLists.forEach(list => {
            allGuests.push({
                id: list.id + '_fallback',
                name: `Guests from ${list.guest_list_name}`,
                email: 'Multiple contacts',
                phone: 'Multiple contacts',
                type: 'guest_list_fallback',
                guest_list_name: list.guest_list_name
            });
        });
    }
    
    // Generate the HTML for all guests
    if (allGuests.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No guests to create individual messages for.</p>';
        return;
    }
    
    container.innerHTML = allGuests.map(guest => `
        <div class="border rounded-lg p-4 mb-4">
            <h5 class="font-medium text-primary mb-2">${guest.name}</h5>
            <p class="text-sm text-gray-600 mb-3">${guest.email || 'No email'} | ${guest.phone || 'No phone'}</p>
            ${guest.type === 'guest_from_list' ? 
                `<p class="text-xs text-blue-600 mb-2"><i class="fas fa-users mr-1"></i>From guest list: ${guest.guest_list_name}</p>` : ''
            }
            ${guest.type === 'guest_list_fallback' ? 
                `<p class="text-xs text-amber-600 mb-2"><i class="fas fa-info-circle mr-1"></i>This message will be sent to all guests in the "${guest.guest_list_name}" list</p>` : ''
            }
            <textarea 
                name="individual_messages[${guest.id}]" 
                rows="4" 
                class="form-textarea w-full" 
                placeholder="Write a personalized message for ${guest.name}..."
            >Hello ${guest.name}, you're invited to {{ $event->name }}...</textarea>
        </div>
    `).join('');
}


function sendInvitationsToNewGuests(invitationData) {
    // Show loading state
    const submitBtn = document.querySelector('#send-invitations-form button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Sending Invitations...';
    submitBtn.disabled = true;
    

    
    
    fetch('{{ route("organizer.events.update-sent.send-new-invitations", $event) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(invitationData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
               
                throw new Error(`Server error: ${response.status} - ${JSON.stringify(errorData)}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Invitations sent successfully!', 'success');
            
            // Move pending guests to the current guests section
            movePendingGuestsToCurrent(data.guests || []);
            
            // Clear pending guests
            pendingGuestsList = [];
            updatePendingGuestsDisplay();
            
            // Hide modal
            hideInvitationModal();
            
            // Optionally refresh the page after a delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showNotification(data.message || 'Failed to send invitations.', 'error');
        }
    })
    .catch(error => {
        
        showNotification('An error occurred while sending invitations: ' + error.message, 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function movePendingGuestsToCurrent(guestData) {
    const currentGuestsGrid = document.getElementById('current-guests-grid');
    
    guestData.forEach(guest => {
        const guestCard = createCurrentGuestCard(guest);
        currentGuestsGrid.appendChild(guestCard);
    });
    
    // Update total guests count if displayed
    const totalGuestsSpan = document.querySelector('.bg-gray-100');
    if (totalGuestsSpan && totalGuestsSpan.textContent.includes('total guests')) {
        const currentCount = parseInt(totalGuestsSpan.textContent.match(/\d+/)[0]);
        const newCount = currentCount + guestData.length;
        totalGuestsSpan.textContent = `${newCount} total guests`;
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-[9999] p-4 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Handle form submissions
document.addEventListener('DOMContentLoaded', function() {
    showTab('basic-info');
    
    
    // Handle send invitations form submission
    const sendInvitationsForm = document.getElementById('send-invitations-form');
    if (sendInvitationsForm) {
        sendInvitationsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const platforms = Array.from(formData.getAll('platforms[]'));
            const messageType = formData.get('message_type');
            
            if (platforms.length === 0) {
                showNotification('Please select at least one invitation platform before sending invitations.', 'error');
                return;
            }
            
            if (pendingGuestsList.length === 0) {
                showNotification('No pending guests to send invitations to.', 'error');
                return;
            }
            
            // Prepare invitation data
            const invitationData = {
                pending_guests: pendingGuestsList,
                platforms: platforms,
                message_type: messageType,
                general_message: formData.get('general_message'),
                individual_messages: {}
            };
            
            // Collect individual messages if selected
            if (messageType === 'individual') {
                const individualInputs = document.querySelectorAll('textarea[name^="individual_messages"]');
                
                individualInputs.forEach(input => {
                    const guestId = input.name.match(/\[(.*?)\]/)[1];
                    invitationData.individual_messages[guestId] = input.value;
                    
                });
                
                // Clear general message when using individual messages
                invitationData.general_message = '';
            } else {
                // Clear individual messages when using general message
                invitationData.individual_messages = {};
            }
            
            sendInvitationsToNewGuests(invitationData);
        });
    }
});

// Google Maps functionality for location selection
function initMap() {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const addressInput = document.getElementById('venue_address');
    const venueNameInput = document.getElementById('venue_name');

    // Default to Kuala Lumpur if we don't have a user location
    const defaultLat = 3.139003;   // Kuala Lumpur
    const defaultLng = 101.686855;
    const initialLat = parseFloat(latInput.value);
    const initialLng = parseFloat(lngInput.value);
    const hasInitial = Number.isFinite(initialLat) && Number.isFinite(initialLng);

    const map = new google.maps.Map(document.getElementById('map'), {
        center: hasInitial ? { lat: initialLat, lng: initialLng } : { lat: defaultLat, lng: defaultLng },
        zoom: hasInitial ? 14 : 12,
        mapTypeControl: false,
        streetViewControl: false
    });

    let marker = new google.maps.Marker({
        position: hasInitial ? { lat: initialLat, lng: initialLng } : { lat: defaultLat, lng: defaultLng },
        map,
        draggable: true
    });
    if (!hasInitial) marker.setVisible(false);

    const geocoder = new google.maps.Geocoder();
    const placesService = new google.maps.places.PlacesService(map);

    function reverseGeocodeAndSet(loc) {
        geocoder.geocode({ location: loc }, (results, status) => {
            if (status === 'OK' && results && results.length) {
                const best = results[0];
                if (best.formatted_address) {
                    addressInput.value = best.formatted_address;
                }
                if (best.place_id) {
                    placesService.getDetails({ placeId: best.place_id, fields: ['name'] }, (place, s) => {
                        if (s === 'OK' && place && place.name) {
                            venueNameInput.value = place.name;
                        } else {
                            // Fallback: use first segment of address as a rough venue name
                            if (best.formatted_address) {
                                venueNameInput.value = best.formatted_address.split(',')[0];
                            }
                        }
                    });
                } else if (best.formatted_address) {
                    venueNameInput.value = best.formatted_address.split(',')[0];
                }
            }
        });
    }

    const autocomplete = new google.maps.places.Autocomplete(addressInput, {
        fields: ['geometry', 'formatted_address', 'name'],
        componentRestrictions: { country: ['my'] }
    });
    autocomplete.bindTo('bounds', map);

    // Autocomplete for venue name (establishments)
    const venueAutocomplete = new google.maps.places.Autocomplete(venueNameInput, {
        fields: ['geometry', 'formatted_address', 'name'],
        types: ['establishment'],
        componentRestrictions: { country: ['my'] }
    });
    venueAutocomplete.bindTo('bounds', map);

    function setAutocompleteBoundsFrom(latLng) {
        const circle = new google.maps.Circle({ center: latLng, radius: 5000 }); // 5km bias
        const bounds = circle.getBounds();
        autocomplete.setBounds(bounds);
        autocomplete.setOptions({ strictBounds: false }); // bias, not restrict
        venueAutocomplete.setBounds(bounds);
        venueAutocomplete.setOptions({ strictBounds: false });
    }

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.geometry) return;
        const loc = place.geometry.location;
        map.setCenter(loc);
        map.setZoom(15);
        marker.setPosition(loc);
        marker.setVisible(true);
        latInput.value = loc.lat().toFixed(6);
        lngInput.value = loc.lng().toFixed(6);
        if (place.formatted_address) {
            addressInput.value = place.formatted_address;
        }
        if (place.name) {
            venueNameInput.value = place.name;
        }
        setAutocompleteBoundsFrom(loc);
    });

    venueAutocomplete.addListener('place_changed', () => {
        const place = venueAutocomplete.getPlace();
        if (!place.geometry) return;
        const loc = place.geometry.location;
        map.setCenter(loc);
        map.setZoom(15);
        marker.setPosition(loc);
        marker.setVisible(true);
        latInput.value = loc.lat().toFixed(6);
        lngInput.value = loc.lng().toFixed(6);
        if (place.name) {
            venueNameInput.value = place.name;
        }
        if (place.formatted_address) {
            addressInput.value = place.formatted_address;
        }
        setAutocompleteBoundsFrom(loc);
    });

    map.addListener('click', (e) => {
        const loc = e.latLng;
        marker.setPosition(loc);
        marker.setVisible(true);
        latInput.value = loc.lat().toFixed(6);
        lngInput.value = loc.lng().toFixed(6);
        setAutocompleteBoundsFrom(loc);
        reverseGeocodeAndSet(loc);
    });

    marker.addListener('dragend', (e) => {
        const loc = e.latLng;
        latInput.value = loc.lat().toFixed(6);
        lngInput.value = loc.lng().toFixed(6);
        setAutocompleteBoundsFrom(loc);
        reverseGeocodeAndSet(loc);
    });

    // Try to use user's current location if no initial coordinates provided
    if (!hasInitial && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const loc = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                map.setCenter(loc);
                map.setZoom(14);
                marker.setPosition(loc);
                marker.setVisible(true);
                latInput.value = loc.lat.toFixed(6);
                lngInput.value = loc.lng.toFixed(6);
                setAutocompleteBoundsFrom(loc);
                reverseGeocodeAndSet(loc);
            },
            () => {
                // If denied or failed, we keep default KL
                map.setCenter({ lat: defaultLat, lng: defaultLng });
                map.setZoom(12);
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    }
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        hideModal(e.target.id);
    }
});


function showSuccessMessage(message) {
    // Create a temporary success message
    const successDiv = document.createElement('div');
    successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-[9999]';
    successDiv.innerHTML = `<i class="fas fa-check mr-2"></i>${message}`;
    document.body.appendChild(successDiv);
    
    // Remove after 3 seconds
    setTimeout(() => {
        successDiv.remove();
    }, 3000);
}

function showErrorMessage(message) {
    // Create a temporary error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-[9999]';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${message}`;
    document.body.appendChild(errorDiv);
    
    // Remove after 3 seconds
    setTimeout(() => {
        errorDiv.remove();
    }, 3000);
}

</script>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: between;
    align-items: center;
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
}

.modal-close:hover {
    color: #374151;
}

.modal-body {
    padding: 1.5rem;
}





.btn-danger {
    @apply bg-red-500 text-white hover:bg-red-600 border border-red-500 hover:border-red-600 px-3 py-2 rounded text-sm transition-colors font-medium shadow-sm;
}

.btn-outline-secondary {
    @apply border border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400 px-3 py-2 rounded text-sm transition-colors font-medium shadow-sm;
}

.btn-sm {
    @apply px-2 py-1 text-xs;
}

.message-box {
    @apply border-t border-gray-100 pt-3;
}




</style>


{{-- Google Maps for location selection --}}
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&language=en&region=MY&callback=initMap"></script>

@endsection
