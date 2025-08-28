@extends('layouts.organizer')

@php
    $sessionData = $sessionData ?? [];
    
    // If we're editing a draft, get the data from EventCreationService
    if (Session::has('editing_draft_event_id')) {
        $eventCreationService = app(\App\Organizer\Services\EventCreationService::class);
        $sessionData = $eventCreationService->getAllStepsData();
    }
    
    // Debug: Log what session data we have
    if (app()->environment('local')) {
        \Log::info('Create page session data', [
            'sessionData' => $sessionData,
            'has_editing_draft' => Session::has('editing_draft_event_id'),
            'editing_draft_id' => Session::get('editing_draft_event_id'),
            'session_data_keys' => array_keys($sessionData),
        ]);
    }
@endphp

@section('content')
<div class="editor-container">
    <!-- Left Panel - Editor -->
    <div class="editor-panel">
        <div class="editor-header">
            <div class="header-content">
                <div>
                    <h1>
                        @if(Session::has('editing_draft_event_id'))
                            Edit Draft Event
                        @else
                            Event Creation
                        @endif
                    </h1>
                    <p>
                        @if(Session::has('editing_draft_event_id'))
                            Continue editing your draft event
                        @else
                            Create and customize your event invitation
                        @endif
                    </p>
                </div>
                <div class="auto-save-status">
                    <div class="auto-save-indicator">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Auto-save enabled</span>
                    </div>
                    <button type="button" id="start-fresh-btn" class="ml-3 text-xs text-white opacity-75 hover:opacity-100 underline">
                        Start Fresh
                    </button>
                </div>
            </div>
        </div>
        
        <form action="{{ route('organizer.events.store') }}" method="POST" enctype="multipart/form-data" id="eventForm">
            @csrf
            
            <div class="editor-content" id="editorContent">
                <!-- Event Details Section -->
                <div class="editor-section" data-section="event" draggable="true">
                    <div class="section-header active" data-section="event">
                        <div class="section-header-left">
                            <span class="drag-handle">⋮⋮</span>
                            <span>Event Details</span>
                        </div>
                        <span class="toggle-icon active">▼</span>
                    </div>
                    <div class="section-content active" id="event-section">
                        <div class="form-group">
                            <label for="name">Event Name *</label>
                            <input type="text" id="name" name="name" value="{{ Session::has('editing_draft_event_id') ? (old('name', $sessionData['name'] ?? '')) : '' }}" required oninput="updatePreview()">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Event Description</label>
                            <textarea id="description" name="description" rows="3" oninput="updatePreview()">{{ Session::has('editing_draft_event_id') ? (old('description', $sessionData['description'] ?? '')) : '' }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="start_date">Start Date & Time *</label>
                            <input type="datetime-local" id="start_date" name="start_date" value="{{ Session::has('editing_draft_event_id') ? (old('start_date', $sessionData['start_date'] ?? '')) : '' }}" required oninput="updatePreview()">
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date">End Date & Time</label>
                            <input type="datetime-local" id="end_date" name="end_date" value="{{ Session::has('editing_draft_event_id') ? (old('end_date', $sessionData['end_date'] ?? '')) : '' }}" oninput="updatePreview()">
                            <small class="text-gray-500 text-sm">Optional - must be at least {{ config('app.min_event_duration', 15) }} minutes after start time</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="invitation_title">Invitation Title</label>
                            <input type="text" id="invitation_title" name="invitation_title" value="{{ Session::has('editing_draft_event_id') ? (old('invitation_title', $sessionData['invitation_title'] ?? "You're Invited!")) : "You're Invited!" }}" oninput="updatePreview()">
                        </div>
                        
                        <div class="form-group">
                            <label for="invitation_subtitle">Invitation Subtitle</label>
                            <input type="text" id="invitation_subtitle" name="invitation_subtitle" value="{{ Session::has('editing_draft_event_id') ? (old('invitation_subtitle', $sessionData['invitation_subtitle'] ?? '')) : '' }}" oninput="updatePreview()">
                        </div>
                        
                        <div class="form-group">
                            <label for="invitation_message">Invitation Message</label>
                            <textarea id="invitation_message" name="invitation_message" rows="3" oninput="updatePreview()">{{ Session::has('editing_draft_event_id') ? (old('invitation_message', $sessionData['invitation_message'] ?? '')) : '' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Location Section -->
                <div class="editor-section" data-section="location" draggable="true">
                    <div class="section-header" data-section="location">
                        <div class="section-header-left">
                            <span class="drag-handle">⋮⋮</span>
                            <span>Location</span>
                        </div>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="location-section">
                        <div class="form-group">
                            <label for="venue_name">Venue Name</label>
                            <input type="text" id="venue_name" name="venue_name" value="{{ Session::has('editing_draft_event_id') ? (old('venue_name', $sessionData['venue_name'] ?? '')) : old('venue_name') }}" oninput="updatePreview()">
                        </div>
                        
                        <div class="form-group">
                            <label for="venue_address">Venue Address</label>
                            <textarea id="venue_address" name="venue_address" rows="3" oninput="updatePreview()">{{ Session::has('editing_draft_event_id') ? (old('venue_address', $sessionData['venue_address'] ?? '')) : old('venue_address') }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="parking_info">Parking Information</label>
                            <input type="text" id="parking_info" name="parking_info" value="{{ Session::has('editing_draft_event_id') ? (old('parking_info', $sessionData['parking_info'] ?? '')) : old('parking_info') }}" oninput="updatePreview()">
                        </div>
                    </div>
                </div>

                <!-- Guest Lists Section -->
                <div class="editor-section" data-section="guests" draggable="true">
                    <div class="section-header" data-section="guests">
                        <div class="section-header-left">
                            <span class="drag-handle">⋮⋮</span>
                            <span>Guest Lists</span>
                        </div>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="guests-section">
                        <div class="form-group">
                            <label>Select Guest Lists</label>
                            @if(isset($guestLists) && $guestLists->count() > 0)
                                @foreach($guestLists as $guestList)
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="guest_list_{{ $guestList->id }}" name="guest_list_ids[]" value="{{ $guestList->id }}" {{ (in_array($guestList->id, Session::has('editing_draft_event_id') ? ($sessionData['guest_list_ids'] ?? []) : old('guest_list_ids', []))) ? 'checked' : '' }} onchange="autoSave()">
                                        <label for="guest_list_{{ $guestList->id }}">{{ $guestList->name }} ({{ $guestList->guests->count() }} guests)</label>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-500">No guest lists available. <a href="{{ route('organizer.guest-lists.create') }}" class="text-blue-600">Create one first</a></p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- RSVP Section -->
                <div class="editor-section" data-section="rsvp" draggable="true">
                    <div class="section-header" data-section="rsvp">
                        <div class="section-header-left">
                            <span class="drag-handle">⋮⋮</span>
                            <span>RSVP Details</span>
                        </div>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="rsvp-section">
                        <div class="checkbox-group">
                            <input type="checkbox" id="rsvp_enabled" name="rsvp_enabled" value="1" {{ (Session::has('editing_draft_event_id') ? ($sessionData['rsvp_enabled'] ?? true) : old('rsvp_enabled', true)) ? 'checked' : '' }} onchange="updatePreview()">
                            <label for="rsvp_enabled">Enable RSVP Section</label>
                        </div>
                        
                        <div class="form-group">
                            <label for="rsvp_message">RSVP Message</label>
                            <textarea id="rsvp_message" name="rsvp_message" rows="2" oninput="updatePreview()">{{ Session::has('editing_draft_event_id') ? (old('rsvp_message', $sessionData['rsvp_message'] ?? 'Please confirm your attendance by responding to this invitation.')) : old('rsvp_message', 'Please confirm your attendance by responding to this invitation.') }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="rsvp_deadline">RSVP Deadline</label>
                            <input type="text" id="rsvp_deadline" name="rsvp_deadline" value="{{ Session::has('editing_draft_event_id') ? (old('rsvp_deadline', $sessionData['rsvp_deadline'] ?? '')) : old('rsvp_deadline') }}" oninput="updatePreview()">
                        </div>
                        
                        <div class="form-group">
                            <label for="rsvp_contact">Contact Information</label>
                            <input type="text" id="rsvp_contact" name="rsvp_contact" value="{{ Session::has('editing_draft_event_id') ? (old('rsvp_contact', $sessionData['rsvp_contact'] ?? '')) : old('rsvp_contact') }}" oninput="updatePreview()">
                        </div>
                    </div>
                </div>

                <!-- QR Code Section -->
                <div class="editor-section" data-section="qr" draggable="true">
                    <div class="section-header" data-section="qr">
                        <div class="section-header-left">
                            <span class="drag-handle">⋮⋮</span>
                            <span>QR Code</span>
                        </div>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="qr-section">
                        <div class="checkbox-group">
                            <input type="checkbox" id="qr_checkin_enabled" name="qr_checkin_enabled" value="1" {{ old('qr_checkin_enabled', true) ? 'checked' : '' }} onchange="updatePreview()">
                            <label for="qr_checkin_enabled">Enable QR Check-in</label>
                        </div>
                        
                        <div class="form-group">
                            <label for="qr_code_url">QR Code URL</label>
                            <input type="url" id="qr_code_url" name="qr_code_url" placeholder="https://your-event-details.com" value="{{ old('qr_code_url') }}" oninput="updatePreview()">
                        </div>
                        
                        <div class="form-group">
                            <label for="qr_description">QR Description</label>
                            <input type="text" id="qr_description" name="qr_description" value="{{ old('qr_description', 'Scan for event details & contact info') }}" oninput="updatePreview()">
                        </div>
                    </div>
                </div>

                <!-- Message Template Section -->
                <div class="editor-section" data-section="message" draggable="true">
                    <div class="section-header" data-section="message">
                        <div class="section-header-left">
                            <span class="drag-handle">⋮⋮</span>
                            <span>Message Template</span>
                        </div>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="message-section">
                        <div class="form-group">
                            <label for="message_template">Base Message Template</label>
                            <textarea id="message_template" name="message_template" rows="4" placeholder="Use placeholders like {guestName}, {eventLocation}, {eventDate}...">{{ old('message_template') }}</textarea>
                            <small class="text-gray-500">Available placeholders: {guestName}, {eventName}, {eventDate}, {eventLocation}, {organizerName}</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="attachments">Attachments</label>
                            <input type="file" id="attachments" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.ics">
                            <small class="text-gray-500">PDF, Images, or Calendar files</small>
                        </div>
                    </div>
                </div>

                <!-- Design Section -->
                <div class="editor-section" data-section="design" draggable="true">
                    <div class="section-header" data-section="design">
                        <div class="section-header-left">
                            <span class="drag-handle">⋮⋮</span>
                            <span>Design & Colors</span>
                        </div>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="design-section">
                        <div class="form-group">
                            <label for="hero_color1">Hero Gradient Start</label>
                            <div class="color-group">
                                <input type="text" id="heroColor1Text" value="{{ old('hero_color1', '#ff6b6b') }}" readonly>
                                <input type="color" id="hero_color1" name="hero_color1" class="color-picker" value="{{ old('hero_color1', '#ff6b6b') }}" onchange="updateColors()">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="hero_color2">Hero Gradient End</label>
                            <div class="color-group">
                                <input type="text" id="heroColor2Text" value="{{ old('hero_color2', '#ffa726') }}" readonly>
                                <input type="color" id="hero_color2" name="hero_color2" class="color-picker" value="{{ old('hero_color2', '#ffa726') }}" onchange="updateColors()">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="accent_color">Accent Color</label>
                            <div class="color-group">
                                <input type="text" id="accentColorText" value="{{ old('accent_color', '#667eea') }}" readonly>
                                <input type="color" id="accent_color" name="accent_color" class="color-picker" value="{{ old('accent_color', '#667eea') }}" onchange="updateColors()">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="font_family">Font Style</label>
                            <select id="font_family" name="font_family" onchange="updatePreview()">
                                <option value="Segoe UI" {{ old('font_family', 'Segoe UI') == 'Segoe UI' ? 'selected' : '' }}>Segoe UI</option>
                                <option value="Arial" {{ old('font_family') == 'Arial' ? 'selected' : '' }}>Arial</option>
                                <option value="Georgia" {{ old('font_family') == 'Georgia' ? 'selected' : '' }}>Georgia</option>
                                <option value="Times New Roman" {{ old('font_family') == 'Times New Roman' ? 'selected' : '' }}>Times New Roman</option>
                                <option value="Helvetica" {{ old('font_family') == 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Scheduling Section -->
                <div class="editor-section" data-section="scheduling" draggable="true">
                    <div class="section-header" data-section="scheduling">
                        <div class="section-header-left">
                            <span class="drag-handle">⋮⋮</span>
                            <span>Scheduling</span>
                        </div>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="scheduling-section">
                        <div class="form-group">
                            <label>Send Type</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="send_type" value="now" {{ old('send_type', 'now') == 'now' ? 'checked' : '' }}>
                                    <span>Send Now</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="send_type" value="scheduled" {{ old('send_type') == 'scheduled' ? 'checked' : '' }}>
                                    <span>Schedule for Later</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group" id="scheduledAtGroup" style="display: none;">
                            <label for="scheduled_at">Schedule Date & Time</label>
                            <input type="datetime-local" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}">
                        </div>
                        
                        <input type="hidden" name="status" value="draft">
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="submit" class="btn btn-primary">Create Event</button>
                <button type="button" class="btn btn-secondary" onclick="resetToDefaults()">Reset</button>
            </div>
        </form>
    </div>

    <!-- Right Panel - Preview -->
    <div class="preview-panel">
        <div class="preview-container">
            <div class="preview-invitation" id="previewInvitation">
                <div class="preview-hero" id="previewHero">
                    <div class="preview-hero-content">
                        <h1 class="preview-title" id="previewTitle">You're Invited!</h1>
                        <p class="preview-subtitle" id="previewSubtitle">Join us for an unforgettable celebration</p>
                        <div class="preview-date" id="previewDate">Saturday, August 15th, 2025 • 7:00 PM</div>
                    </div>
                </div>
                
                <div class="preview-content" id="previewContent">
                    <!-- Dynamic content will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Preview Button -->
<button class="mobile-preview-btn" onclick="showMobilePreview()">
    📱 Preview
</button>

<!-- Mobile Preview Modal -->
<div class="mobile-preview-modal" id="mobilePreviewModal">
    <div class="mobile-preview-content">
        <button class="mobile-preview-close" onclick="hideMobilePreview()">×</button>
        <div id="mobilePreviewContent"></div>
    </div>
</div>

<div class="success-toast" id="successToast">Changes saved successfully!</div>

@push('styles')
<style>
    /* Include all the CSS from the HTML file */
    .editor-container {
        display: grid;
        grid-template-columns: 400px 1fr;
        height: 100vh;
    }

    .editor-panel {
        background: white;
        border-right: 1px solid #e0e6ed;
        overflow-y: auto;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .editor-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 20px;
        text-align: center;
    }

    .editor-header h1 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .editor-header p {
        opacity: 0.9;
        font-size: 0.9rem;
    }
    
    /* Header Content */
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        width: 100%;
    }
    
    .auto-save-status {
        display: flex;
        align-items: center;
    }
    
    .auto-save-indicator {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 6px;
        color: white;
        font-size: 12px;
        font-weight: 500;
    }
    
    .auto-save-indicator.saving {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
    }
    
    .auto-save-indicator.error {
        background: rgba(239, 68, 68, 0.2);
        border-color: rgba(239, 68, 68, 0.4);
    }
    
    .auto-save-indicator.typing {
        background: rgba(59, 130, 246, 0.2);
        border-color: rgba(59, 130, 246, 0.4);
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    .editor-content {
        padding: 0;
    }

    .editor-section {
        border-bottom: 1px solid #f0f0f0;
        position: relative;
    }

    .section-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 600;
        color: #495057;
        transition: background-color 0.2s ease;
    }

    .section-header:hover {
        background: #e9ecef;
    }

    .section-header.active {
        background: #667eea;
        color: white;
    }

    .section-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .drag-handle {
        cursor: grab;
        color: #6c757d;
        font-size: 14px;
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    .section-content {
        padding: 20px;
        display: none;
    }

    .section-content.active {
        display: block;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #495057;
        font-size: 14px;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s ease;
        background: white;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .checkbox-group input[type="checkbox"] {
        width: auto;
        transform: scale(1.2);
    }

    .color-group {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: end;
    }

    .color-picker {
        width: 40px;
        height: 36px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        cursor: pointer;
        padding: 0;
    }

    .toggle-icon {
        transition: transform 0.2s ease;
    }

    .toggle-icon.active {
        transform: rotate(180deg);
    }

    .preview-panel {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
    }

    .preview-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        overflow: auto;
        position: relative;
    }

    .preview-invitation {
        border-radius: 20px;
        overflow: hidden;
    }

    .preview-hero {
        background: linear-gradient(135deg, #ff6b6b, #ffa726);
        padding: 40px 30px;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .preview-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }

    .preview-hero-content {
        position: relative;
        z-index: 2;
    }

    .preview-title {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .preview-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 8px;
    }

    .preview-date {
        font-size: 1rem;
        background: rgba(255,255,255,0.2);
        padding: 8px 20px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 15px;
        backdrop-filter: blur(10px);
    }

    .preview-content {
        padding: 30px;
    }

    .preview-section {
        margin-bottom: 25px;
    }

    .preview-section h3 {
        font-size: 1.3rem;
        color: #333;
        margin-bottom: 15px;
        border-bottom: 2px solid #ff6b6b;
        padding-bottom: 8px;
        display: inline-block;
    }

    .action-buttons {
        position: sticky;
        bottom: 0;
        background: white;
        padding: 15px 20px;
        border-top: 1px solid #e9ecef;
        display: flex;
        gap: 10px;
    }

    .btn {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }

    .radio-group {
        display: flex;
        gap: 20px;
        margin-top: 10px;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 10px 16px;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }

    .radio-option:hover {
        background-color: rgba(255, 107, 107, 0.1);
    }

    .radio-option input[type="radio"] {
        width: 18px;
        height: 18px;
        margin: 0;
    }

    @media (max-width: 768px) {
        .editor-container {
            grid-template-columns: 1fr;
        }
        
        .preview-panel {
            display: none;
        }
        
        .editor-panel {
            height: auto;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    console.log('=== SCRIPT SECTION LOADED ===');
    
    let sectionOrder = ['event', 'location', 'guests', 'rsvp', 'qr', 'message', 'design', 'scheduling'];
    let draggedElement = null;

    // Section toggle functionality
    document.querySelectorAll('.section-header').forEach(header => {
        header.addEventListener('click', function(e) {
            if (e.target.classList.contains('drag-handle')) return;
            
            const isActive = this.classList.contains('active');
            
            document.querySelectorAll('.section-header').forEach(h => {
                h.classList.remove('active');
                h.querySelector('.toggle-icon').classList.remove('active');
            });
            document.querySelectorAll('.section-content').forEach(c => {
                c.classList.remove('active');
            });
            
            if (!isActive) {
                this.classList.add('active');
                this.querySelector('.toggle-icon').classList.add('active');
                const sectionId = this.getAttribute('data-section') + '-section';
                document.getElementById(sectionId).classList.add('active');
            }
        });
    });

    // Update preview function
    function updatePreview() {
        const title = document.getElementById('invitation_title').value || "You're Invited!";
        const subtitle = document.getElementById('invitation_subtitle').value || '';
        const name = document.getElementById('name').value || 'Event Name';
        const startDate = document.getElementById('start_date').value;
        const description = document.getElementById('invitation_message').value || '';
        const fontFamily = document.getElementById('font_family').value;

        // Update hero section
        document.getElementById('previewTitle').textContent = title;
        document.getElementById('previewSubtitle').textContent = subtitle;
        
        // Format date
        let dateText = '';
        if (startDate) {
            const date = new Date(startDate);
            dateText = date.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            }) + ' • ' + date.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit' 
            });
        }
        document.getElementById('previewDate').textContent = dateText;
        
        // Update font family
        document.querySelector('.preview-invitation').style.fontFamily = fontFamily;

        // Update content sections
        updatePreviewContent();
    }

    function updatePreviewContent() {
        const previewContent = document.getElementById('previewContent');
        previewContent.innerHTML = '';

        // Event description
        const description = document.getElementById('description').value;
        if (description.trim()) {
            const descSection = createPreviewSection('Event Details', `
                <div class="description-content">${description}</div>
            `);
            previewContent.appendChild(descSection);
        }

        // Location section
        const venue = document.getElementById('venue_name').value;
        const address = document.getElementById('venue_address').value;
        const parking = document.getElementById('parking_info').value;

        if (venue || address || parking) {
            const locationSection = createPreviewSection('Location', `
                <div class="location-info">
                    ${venue ? `<strong>${venue}</strong><br>` : ''}
                    ${address ? `${address.replace(/\n/g, '<br>')}<br>` : ''}
                    ${parking ? `<em>${parking}</em>` : ''}
                </div>
            `);
            previewContent.appendChild(locationSection);
        }

        // RSVP section
        const rsvpEnabled = document.getElementById('rsvp_enabled').checked;
        if (rsvpEnabled) {
            const rsvpMessage = document.getElementById('rsvp_message').value;
            const rsvpDeadline = document.getElementById('rsvp_deadline').value;
            const rsvpContact = document.getElementById('rsvp_contact').value;

            const rsvpSection = createPreviewSection('RSVP', `
                <div class="rsvp-info">
                    <div class="rsvp-details">${rsvpMessage}</div>
                    ${rsvpDeadline ? `<div class="rsvp-deadline">${rsvpDeadline}</div>` : ''}
                    ${rsvpContact ? `<div class="rsvp-contact">${rsvpContact}</div>` : ''}
                </div>
            `);
            previewContent.appendChild(rsvpSection);
        }

        // QR Code section
        const qrEnabled = document.getElementById('qr_checkin_enabled').checked;
        if (qrEnabled) {
            const qrDesc = document.getElementById('qr_description').value;
            
            const qrSection = createPreviewSection('Quick Access', `
                <div class="qr-preview">
                    <div class="qr-code-preview">
                        <div class="qr-pattern">
                            ${Array(36).fill('<div class="qr-dot"></div>').join('')}
                        </div>
                    </div>
                    <div class="qr-description">${qrDesc}</div>
                </div>
            `);
            previewContent.appendChild(qrSection);
        }
    }

    function createPreviewSection(title, content) {
        const section = document.createElement('div');
        section.className = 'preview-section';
        section.innerHTML = `
            <h3>${title}</h3>
            ${content}
        `;
        return section;
    }

    // Update colors function
    function updateColors() {
        const heroColor1 = document.getElementById('hero_color1').value;
        const heroColor2 = document.getElementById('hero_color2').value;
        const accentColor = document.getElementById('accent_color').value;

        document.getElementById('heroColor1Text').value = heroColor1;
        document.getElementById('heroColor2Text').value = heroColor2;
        document.getElementById('accentColorText').value = accentColor;

        document.getElementById('previewHero').style.background = `linear-gradient(135deg, ${heroColor1}, ${heroColor2})`;
        
        document.querySelectorAll('.preview-section h3').forEach(h3 => {
            h3.style.borderBottomColor = accentColor;
        });
    }

    // Scheduling toggle
    document.querySelectorAll('input[name="send_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const scheduledAtGroup = document.getElementById('scheduledAtGroup');
            if (this.value === 'scheduled') {
                scheduledAtGroup.style.display = 'block';
            } else {
                scheduledAtGroup.style.display = 'none';
            }
        });
    });

    // Reset to defaults function
    function resetToDefaults() {
        if (confirm('Are you sure you want to reset all settings to default values?')) {
            document.getElementById('name').value = '';
            document.getElementById('description').value = '';
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            document.getElementById('invitation_title').value = "You're Invited!";
            document.getElementById('invitation_subtitle').value = '';
            document.getElementById('invitation_message').value = '';
            document.getElementById('venue_name').value = '';
            document.getElementById('venue_address').value = '';
            document.getElementById('parking_info').value = '';
            document.getElementById('rsvp_enabled').checked = true;
            document.getElementById('rsvp_message').value = 'Please confirm your attendance by responding to this invitation.';
            document.getElementById('rsvp_deadline').value = '';
            document.getElementById('rsvp_contact').value = '';
            document.getElementById('qr_checkin_enabled').checked = true;
            document.getElementById('qr_code_url').value = '';
            document.getElementById('qr_description').value = 'Scan for event details & contact info';
            document.getElementById('message_template').value = '';
            document.getElementById('hero_color1').value = '#ff6b6b';
            document.getElementById('hero_color2').value = '#ffa726';
            document.getElementById('accent_color').value = '#667eea';
            document.getElementById('font_family').value = 'Segoe UI';
            
            // Clear guest list selections
            document.querySelectorAll('input[name="guest_list_ids[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Reset send type
            document.querySelector('input[name="send_type"][value="now"]').checked = true;
            document.getElementById('scheduledAtGroup').style.display = 'none';
            document.getElementById('scheduled_at').value = '';
            
            updatePreview();
            updateColors();
        }
    }

    // Auto-save functionality
    console.log('=== AUTO-SAVE SECTION LOADED ===');
    
    let autoSaveTimeout;
    let lastSavedData = '';
    let isAutoSaving = false;
    let isTyping = false;
    
    // Test function to verify JavaScript is working
    function testAutoSave() {
        console.log('Auto-save test function called - JavaScript is working!');
    }
    
    function autoSave() {
        if (isAutoSaving) return;
        
        const formData = new FormData(document.getElementById('eventForm'));
        const currentData = JSON.stringify(Object.fromEntries(formData));
        
        // Debug: Log auto-save attempt
        console.log('Auto-save triggered', {
            'formData': Object.fromEntries(formData),
            'hasName': formData.get('name'),
            'hasStartDate': formData.get('start_date'),
            'dataChanged': currentData !== lastSavedData
        });
        
        // Only save if data has changed
        if (currentData === lastSavedData) return;
        
        isAutoSaving = true;
        lastSavedData = currentData;
        
        // Show auto-save indicator immediately
        showAutoSaveIndicator('Auto-saving...');
        
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
            console.log('Auto-save response:', data);
            if (data.success) {
                showAutoSaveIndicator('Saved at ' + data.timestamp);
            } else {
                showAutoSaveIndicator('Save failed', 'error');
            }
        })
        .catch(error => {
            console.error('Auto-save error:', error);
            showAutoSaveIndicator('Save failed', 'error');
        })
        .finally(() => {
            isAutoSaving = false;
            isTyping = false;
        });
    }
    
    function showAutoSaveIndicator(message, type = 'success') {
        // Update header indicator
        const headerIndicator = document.querySelector('.auto-save-indicator');
        if (headerIndicator) {
            headerIndicator.className = 'auto-save-indicator ' + type;
            const span = headerIndicator.querySelector('span');
            if (span) {
                span.textContent = message;
            }
        }
        
        // Show floating notification
        let indicator = document.getElementById('auto-save-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'auto-save-indicator';
            indicator.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 8px 16px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 500;
                z-index: 1000;
                transition: opacity 0.3s ease;
                opacity: 0;
            `;
            document.body.appendChild(indicator);
        }
        
        indicator.textContent = message;
        indicator.style.backgroundColor = type === 'error' ? '#ef4444' : '#10b981';
        indicator.style.color = 'white';
        indicator.style.opacity = '1';
        
        // Hide after 3 seconds
        setTimeout(() => {
            indicator.style.opacity = '0';
        }, 3000);
    }
    
    function debounceAutoSave() {
        console.log('debounceAutoSave triggered for:', this.name);
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(autoSave, 500); // Save after 0.5 seconds of inactivity for faster response
    }
    
    // Immediate save for important fields
    function immediateAutoSave() {
        console.log('immediateAutoSave triggered for:', this.name);
        clearTimeout(autoSaveTimeout);
        showTypingIndicator();
        autoSave(); // Save immediately
    }
    
    // Show typing indicator
    function showTypingIndicator() {
        if (!isTyping) {
            isTyping = true;
            showAutoSaveIndicator('Typing...', 'typing');
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Event creation page loaded - setting up auto-save...');
        
        // Test if JavaScript is working
        testAutoSave();
        
        updatePreview();
        updateColors();
        
        // Trigger send type change to show/hide scheduled date
        const selectedSendType = document.querySelector('input[name="send_type"]:checked');
        if (selectedSendType) {
            selectedSendType.dispatchEvent(new Event('change'));
        }
        
        // Set up auto-save listeners
        const autoSaveInputs = document.querySelectorAll('input, textarea, select');
        console.log('Found form inputs for auto-save:', autoSaveInputs.length);
        
        autoSaveInputs.forEach(input => {
            console.log('Setting up auto-save for input:', input.name, input.type);
            
            // Use immediate save for important fields
            if (input.name === 'name' || input.name === 'start_date' || input.name === 'description' || input.name === 'guest_list_ids[]') {
                input.addEventListener('input', immediateAutoSave);
                input.addEventListener('change', immediateAutoSave);
                console.log('Added immediate auto-save for:', input.name);
            } else {
                // Use faster debounced save for other fields
                input.addEventListener('input', debounceAutoSave);
                input.addEventListener('change', debounceAutoSave);
                console.log('Added debounced auto-save for:', input.name);
            }
        });
        
        // Auto-save on form submission
        document.getElementById('eventForm').addEventListener('submit', function() {
            clearTimeout(autoSaveTimeout);
            autoSave();
        });
        
        // Auto-save on page unload
        window.addEventListener('beforeunload', function(e) {
            // Check if user has made significant progress
            const formData = new FormData(document.getElementById('eventForm'));
            const name = formData.get('name');
            const startDate = formData.get('start_date');
            
            if (name && startDate) {
                // User has significant progress, save as draft
                if (autoSaveTimeout) {
                    clearTimeout(autoSaveTimeout);
                }
                
                // Force immediate save
                const currentData = JSON.stringify(Object.fromEntries(formData));
                if (currentData !== lastSavedData) {
                    // Show a message to user
                    e.preventDefault();
                    e.returnValue = 'Your event progress will be saved as a draft. Are you sure you want to leave?';
                    
                    // Force save in background
                    fetch('{{ route("organizer.events.create.auto-save") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    }).catch(error => console.error('Background save failed:', error));
                }
            }
        });
        
        // Start Fresh button
        document.getElementById('start-fresh-btn').addEventListener('click', function() {
            if (confirm('Are you sure you want to start fresh? This will clear all your saved progress.')) {
                // Redirect to new event creation page
                window.location.href = '{{ route("organizer.events.create.new") }}';
            }
        });
        
        // Time validation
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        if (startDateInput && endDateInput) {
            // When start date changes, set minimum time for end date
            startDateInput.addEventListener('change', function() {
                if (this.value) {
                    // Calculate minimum end time (15 minutes after start time - configurable)
                    const minDurationMinutes = {{ config('app.min_event_duration', 15) }};
                    const startDate = new Date(this.value);
                    const minEndDate = new Date(startDate.getTime() + (minDurationMinutes * 60 * 1000)); // minimum duration later
                    const minEndDateTime = minEndDate.toISOString().slice(0, 16);
                    
                    endDateInput.min = minEndDateTime;
                    
                    // Clear end date if it's before the new minimum
                    if (endDateInput.value && endDateInput.value < minEndDateTime) {
                        endDateInput.value = '';
                        updatePreview();
                    }
                }
            });
            
            // Validate end date when it changes
            endDateInput.addEventListener('change', function() {
                if (this.value && startDateInput.value) {
                    const startDate = new Date(startDateInput.value);
                    const endDate = new Date(this.value);
                    const minDurationMinutes = {{ config('app.min_event_duration', 15) }};
                    const minEndDate = new Date(startDate.getTime() + (minDurationMinutes * 60 * 1000)); // minimum duration later
                    
                    if (endDate < minEndDate) {
                        alert('End time must be at least ' + minDurationMinutes + ' minutes after start time.');
                        this.value = '';
                        updatePreview();
                        return;
                    }
                }
                updatePreview();
            });
            
            // Initialize validation if start date is already set
            if (startDateInput.value) {
                startDateInput.dispatchEvent(new Event('change'));
            }
        }
    });
</script>
@endpush
@endsection 