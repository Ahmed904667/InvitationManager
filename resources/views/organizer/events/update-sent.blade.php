@extends('layouts.organizer')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    {{-- Page Header --}}
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-primary mb-2">
            Update Sent Event
        </h1>
        <p class="text-lg text-secondary">
            Manage your event and guests after invitations have been sent
        </p>
        <div class="mt-4">
            <span class="badge badge-sent text-white px-4 py-2 rounded-full">
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
                    <button onclick="showTab('notifications')" id="tab-notifications" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-bell mr-2"></i>Notifications
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
                            <button onclick="showAddGuestModal()" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus mr-1"></i>Add Guest
                            </button>
                            <button onclick="showAddGuestListModal()" class="btn btn-secondary btn-sm">
                                <i class="fas fa-list-plus mr-1"></i>Add Guest List
                            </button>
                        </div>
                    </div>
                </div>

                {{-- New Guests Section (Hidden by default) --}}
                <div id="new-guests-section" class="mb-6" style="display: none;">
                    <div class="bg-info-50 border border-info-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-star text-info-500 mr-2"></i>
                                <h4 class="font-semibold text-info-700">New Guests Added</h4>
                            </div>
                            <button onclick="hideNewGuestsSection()" class="text-info-600 hover:text-info-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div id="new-guests-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- New guests will be dynamically added here -->
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
                                        <button onclick="confirmDeleteGuest({{ $guest->id }}, '{{ $guest->name }}')" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Add Guest Modal --}}
                <div id="add-guest-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-primary">Add New Guest</h3>
                            <button onclick="hideAddGuestModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="add-guest-form" action="{{ route('organizer.events.update-sent.add-guest', $event) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="modal_guest_name" class="form-label">Guest Name <span class="text-danger-500">*</span></label>
                                <input type="text" id="modal_guest_name" name="name" class="form-input" required>
                            </div>
                            <div>
                                <label for="modal_guest_email" class="form-label">Email</label>
                                <input type="email" id="modal_guest_email" name="email" class="form-input">
                            </div>
                            <div>
                                <label for="modal_guest_phone" class="form-label">Phone</label>
                                <input type="text" id="modal_guest_phone" name="phone" class="form-input">
                            </div>
                            <div>
                                <label for="modal_guest_list_id" class="form-label">Add to Guest List (Optional)</label>
                                <select id="modal_guest_list_id" name="guest_list_id" class="form-select">
                                    <option value="">Add as standalone guest</option>
                                    @foreach($event->guestLists as $guestList)
                                        <option value="{{ $guestList->id }}">{{ $guestList->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="hideAddGuestModal()" class="btn btn-secondary">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus mr-1"></i>Add Guest
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Add Guest List Modal --}}
                <div id="add-guest-list-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-primary">Add Guest List</h3>
                            <button onclick="hideAddGuestListModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="add-guest-list-form" action="{{ route('organizer.events.update-sent.add-guest-list', $event) }}" method="POST" class="space-y-4">
                            @csrf
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
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="hideAddGuestListModal()" class="btn btn-secondary">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-list-plus mr-1"></i>Add Guest List
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



            {{-- Notifications Tab --}}
            <div id="tab-content-notifications" class="tab-content hidden">
                <div class="space-y-6">
                    {{-- Send Update Notification --}}
                    <div>
                        <h3 class="text-lg font-semibold text-primary mb-4">
                            <i class="fas fa-bell mr-2"></i>Send Update Notification
                        </h3>
                        <p class="text-secondary mb-4">
                            Notify all guests about the changes made to the event.
                        </p>
                        
                        <form action="{{ route('organizer.events.update-sent.notify-guests', $event) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="notification_platform" class="form-label">Notification Platform</label>
                                    <select id="notification_platform" name="platform" class="form-select" required>
                                        <option value="">Choose platform...</option>
                                        @if(in_array('email', $event->invitation_platforms ?? []))
                                            <option value="email">Email</option>
                                        @endif
                                        @if(in_array('whatsapp', $event->invitation_platforms ?? []))
                                            <option value="whatsapp">WhatsApp</option>
                                        @endif
                                        <option value="both">Both (Email & WhatsApp)</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="notification_type" class="form-label">Notification Type</label>
                                    <select id="notification_type" name="notification_type" class="form-select" required>
                                        <option value="update">Event Update</option>
                                        <option value="reminder">Event Reminder</option>
                                        <option value="custom">Custom Message</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label for="custom_message" class="form-label">Custom Message (Optional)</label>
                                <textarea id="custom_message" name="custom_message" rows="4" class="form-textarea" 
                                          placeholder="Enter a custom message to include with the notification..."></textarea>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane mr-2"></i>Send Notification
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Notification History --}}
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-primary mb-4">
                            <i class="fas fa-history mr-2"></i>Recent Notifications
                        </h3>
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            @if($recentNotifications->count() > 0)
                                <div class="space-y-3">
                                    @foreach($recentNotifications as $notification)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-primary">{{ $notification->type }}</div>
                                                <div class="text-sm text-secondary">{{ $notification->created_at->format('M j, Y g:i A') }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-secondary">{{ $notification->recipients_count }} recipients</div>
                                                <div class="text-sm text-{{ $notification->status === 'sent' ? 'success' : 'warning' }}-600">
                                                    {{ ucfirst($notification->status) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-secondary text-center py-4">No recent notifications</p>
                            @endif
                        </div>
                    </div>
                </div>
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

// Guest Management functions
function showAddGuestModal() {
    showModal('add-guest-modal');
}

function hideAddGuestModal() {
    hideModal('add-guest-modal');
}

function showAddGuestListModal() {
    showModal('add-guest-list-modal');
}

function hideAddGuestListModal() {
    hideModal('add-guest-list-modal');
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
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function showNewGuestsSection() {
    document.getElementById('new-guests-section').style.display = 'block';
}

function hideNewGuestsSection() {
    document.getElementById('new-guests-section').style.display = 'none';
}

function addNewGuestToGrid(guestData) {
    console.log('addNewGuestToGrid called with:', guestData);
    
    const newGuestsGrid = document.getElementById('new-guests-grid');
    console.log('newGuestsGrid element:', newGuestsGrid);
    
    if (!newGuestsGrid) {
        console.error('newGuestsGrid element not found!');
        return;
    }
    
    const guestCard = createGuestCard(guestData);
    console.log('guestCard created:', guestCard);
    
    newGuestsGrid.appendChild(guestCard);
    console.log('guestCard added to grid');
    
    showNewGuestsSection();
    console.log('new guests section shown');
    
    // Move guest to current guests section after 5 seconds (simulating "processed" state)
    setTimeout(() => {
        console.log('Moving guest to current section after timeout');
        moveGuestToCurrentSection(guestData);
    }, 5000);
}

function moveGuestToCurrentSection(guestData) {
    // Remove from new guests section
    const newGuestsGrid = document.getElementById('new-guests-grid');
    const guestCard = newGuestsGrid.querySelector(`[data-guest-id="${guestData.id}"]`);
    if (guestCard) {
        guestCard.remove();
    }
    
    // Add to current guests section (without message functionality)
    const currentGuestsGrid = document.getElementById('current-guests-grid');
    const currentGuestCard = createCurrentGuestCard(guestData);
    currentGuestsGrid.appendChild(currentGuestCard);
    
    // Hide new guests section if empty
    if (newGuestsGrid.children.length === 0) {
        hideNewGuestsSection();
    }
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
                <button onclick="confirmDeleteGuest(${guestData.id}, '${guestData.name}')" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    return card;
}

function createGuestCard(guestData) {
    const card = document.createElement('div');
    card.className = 'guest-card bg-white border border-info-200 rounded-lg p-4 hover:shadow-md transition-shadow';
    card.setAttribute('data-guest-id', guestData.id);
    
    card.innerHTML = `
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
                <h5 class="font-semibold text-primary">${guestData.name}</h5>
                <p class="text-sm text-secondary">${guestData.guest_list_name || 'Standalone Guest'}</p>
                ${guestData.email ? `<p class="text-xs text-gray-500">${guestData.email}</p>` : ''}
                ${guestData.phone ? `<p class="text-xs text-gray-500">${guestData.phone}</p>` : ''}
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="toggleMessageBox(${guestData.id})" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-comment mr-1"></i>Message
                </button>
                <button onclick="confirmDeleteGuest(${guestData.id}, '${guestData.name}')" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        
        <div id="message-box-${guestData.id}" class="message-box hidden mt-3">
            <textarea 
                id="message-text-${guestData.id}" 
                class="form-textarea w-full text-sm" 
                rows="3" 
                placeholder="Enter personalized message for ${guestData.name}..."
            ></textarea>
            <div class="flex justify-end mt-2">
                <button onclick="saveMessage(${guestData.id})" class="btn btn-sm btn-primary">
                    <i class="fas fa-save mr-1"></i>Save
                </button>
            </div>
        </div>
    `;
    
    return card;
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
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
    
    // Handle add guest form submission
    const addGuestForm = document.getElementById('add-guest-form');
    if (addGuestForm) {
        addGuestForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addNewGuestToGrid(data.guest);
                    hideAddGuestModal();
                    this.reset();
                    showNotification('Guest added successfully!', 'success');
                } else {
                    showNotification(data.message || 'Failed to add guest.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        });
    }

    // Handle add guest list form submission
    const addGuestListForm = document.getElementById('add-guest-list-form');
    if (addGuestListForm) {
        addGuestListForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.guests.forEach(guest => {
                        addNewGuestToGrid(guest);
                    });
                    hideAddGuestListModal();
                    this.reset();
                    showNotification(`Added ${data.guests.length} guests from guest list!`, 'success');
                } else {
                    showNotification(data.message || 'Failed to add guest list.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
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

.tab-button.active {
    border-color: #3b82f6;
    color: #3b82f6;
}

.tab-content.active {
    display: block;
}

.btn-outline-primary {
    @apply border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-3 py-1 rounded text-sm transition-colors;
}

.btn-outline-danger {
    @apply border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded text-sm transition-colors;
}

.btn-sm {
    @apply px-2 py-1 text-xs;
}

.message-box {
    @apply border-t border-gray-100 pt-3;
}

.hidden {
    display: none !important;
}
</style>

{{-- Google Maps for location selection --}}
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&language=en&region=MY&callback=initMap"></script>

@endsection
