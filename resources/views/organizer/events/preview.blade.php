@extends('layouts.organizer')

@section('content')
<div class="preview-page">
    <div class="preview-header">
        <h1>Event Preview</h1>
        <p>This is how your invitation will appear to guests</p>
        <div class="preview-actions">
            <a href="{{ route('organizer.events.edit', $event) }}" class="btn btn-secondary">Edit Event</a>
            <a href="{{ route('organizer.events.show', $event) }}" class="btn btn-primary">Back to Event</a>
        </div>
    </div>

    <div class="preview-container">
        <div class="invitation-card" style="font-family: {{ $event->font_family ?? 'Segoe UI' }};">
            <!-- Hero Section -->
            <div class="invitation-hero" style="background: linear-gradient(135deg, {{ $event->hero_color1 ?? '#ff6b6b' }}, {{ $event->hero_color2 ?? '#ffa726' }});">
                <div class="hero-content">
                    <h1 class="hero-title">{{ $event->invitation_title ?? "You're Invited!" }}</h1>
                    @if($event->invitation_subtitle)
                        <p class="hero-subtitle">{{ $event->invitation_subtitle }}</p>
                    @endif
                    <div class="hero-date">{{ $event->formatted_date }}</div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="invitation-content">
                @if($event->name)
                    <div class="content-section">
                        <h2>Event Details</h2>
                        <h3>{{ $event->name }}</h3>
                        @if($event->description)
                            <p>{{ $event->description }}</p>
                        @endif
                        @if($event->invitation_message)
                            <p class="invitation-message">{{ $event->invitation_message }}</p>
                        @endif
                    </div>
                @endif

                @if($event->venue_name || $event->venue_address || $event->parking_info)
                    <div class="content-section">
                        <h2>Location</h2>
                        @if($event->venue_name)
                            <h3>{{ $event->venue_name }}</h3>
                        @endif
                        @if($event->venue_address)
                            <p class="venue-address">{{ nl2br($event->venue_address) }}</p>
                        @endif
                        @if($event->parking_info)
                            <p class="parking-info"><em>{{ $event->parking_info }}</em></p>
                        @endif
                    </div>
                @endif

                @if($event->rsvp_enabled)
                    <div class="content-section">
                        <h2>RSVP</h2>
                        @if($event->rsvp_message)
                            <p>{{ $event->rsvp_message }}</p>
                        @endif
                        @if($event->rsvp_deadline)
                            <p class="rsvp-deadline"><strong>Deadline:</strong> {{ $event->rsvp_deadline }}</p>
                        @endif
                        @if($event->rsvp_contact)
                            <p class="rsvp-contact"><strong>Contact:</strong> {{ $event->rsvp_contact }}</p>
                        @endif
                    </div>
                @endif

                @if($event->qr_checkin_enabled)
                    <div class="content-section">
                        <h2>Quick Access</h2>
                        <div class="qr-section">
                            <div class="qr-code-placeholder">
                                <div class="qr-pattern">
                                    @for($i = 0; $i < 36; $i++)
                                        <div class="qr-dot"></div>
                                    @endfor
                                </div>
                            </div>
                            @if($event->qr_description)
                                <p class="qr-description">{{ $event->qr_description }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if($event->guestLists->count() > 0)
                    <div class="content-section">
                        <h2>Guest Lists</h2>
                        <ul class="guest-lists">
                            @foreach($event->guestLists as $guestList)
                                <li>{{ $guestList->name }} ({{ $guestList->guests->count() }} guests)</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .preview-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .preview-header {
        text-align: center;
        margin-bottom: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-radius: 10px;
    }

    .preview-header h1 {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .preview-actions {
        margin-top: 20px;
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5a67d8;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }

    .preview-container {
        display: flex;
        justify-content: center;
        padding: 20px 0;
    }

    .invitation-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        max-width: 600px;
        width: 100%;
    }

    .invitation-hero {
        padding: 40px 30px;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .invitation-hero::before {
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

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 15px;
    }

    .hero-date {
        font-size: 1.1rem;
        background: rgba(255,255,255,0.2);
        padding: 10px 25px;
        border-radius: 25px;
        display: inline-block;
        backdrop-filter: blur(10px);
    }

    .invitation-content {
        padding: 30px;
    }

    .content-section {
        margin-bottom: 30px;
    }

    .content-section h2 {
        font-size: 1.5rem;
        color: #333;
        margin-bottom: 15px;
        border-bottom: 2px solid {{ $event->accent_color ?? '#667eea' }};
        padding-bottom: 8px;
        display: inline-block;
    }

    .content-section h3 {
        font-size: 1.3rem;
        color: #444;
        margin-bottom: 10px;
    }

    .invitation-message {
        font-style: italic;
        color: #666;
        line-height: 1.6;
    }

    .venue-address {
        line-height: 1.6;
        margin-bottom: 10px;
    }

    .parking-info {
        color: #666;
        font-size: 0.9rem;
    }

    .rsvp-deadline,
    .rsvp-contact {
        margin: 5px 0;
        color: #555;
    }

    .qr-section {
        text-align: center;
    }

    .qr-code-placeholder {
        display: inline-block;
        width: 120px;
        height: 120px;
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        margin-bottom: 15px;
        padding: 10px;
    }

    .qr-pattern {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 2px;
        height: 100%;
    }

    .qr-dot {
        background: #333;
        border-radius: 1px;
    }

    .qr-description {
        color: #666;
        font-size: 0.9rem;
    }

    .guest-lists {
        list-style: none;
        padding: 0;
    }

    .guest-lists li {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
        color: #555;
    }

    .guest-lists li:last-child {
        border-bottom: none;
    }

    @media (max-width: 768px) {
        .preview-page {
            padding: 10px;
        }

        .hero-title {
            font-size: 2rem;
        }

        .invitation-content {
            padding: 20px;
        }
    }
</style>
@endpush
@endsection 