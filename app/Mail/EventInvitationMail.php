<?php

namespace App\Mail;

use App\Shared\Models\Event;
use App\Shared\Models\Guest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class EventInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Event $event;
    public Guest $guest;
    public string $message;
    public string $inviteUrl;
    public string $eventDate;
    public string $eventTime;
    public string $organizerName;

    /**
     * Create a new message instance.
     */
    public function __construct(Event $event, Guest $guest, string $message, string $inviteUrl)
    {
        $this->event = $event;
        $this->guest = $guest;
        $this->message = $message;
        $this->inviteUrl = $inviteUrl;
        
        // Get organizer name
        $this->organizerName = $event->user->name ?? 'Event Organizer';
        
        // Format event date and time
        $userTimezone = $event->user->timezone ?? 'UTC';
        if ($event->start_date) {
            $eventDateTime = Carbon::parse($event->start_date, 'UTC')->setTimezone($userTimezone);
            $this->eventDate = $eventDateTime->format('l, F j, Y');
            $this->eventTime = $eventDateTime->format('g:i A');
        } else {
            $this->eventDate = 'TBD';
            $this->eventTime = 'TBD';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->event->invitation_title ?? "Invitation: {$this->event->name}";
        
        return new Envelope(
            subject: $subject,
            from: config('mail.from.address', 'noreply@example.com'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.event-invitation',
            with: [
                'event' => $this->event,
                'guest' => $this->guest,
                'personalMessage' => $this->message,
                'inviteUrl' => $this->inviteUrl,
                'eventDate' => $this->eventDate,
                'eventTime' => $this->eventTime,
                'organizerName' => $this->organizerName,
                'eventLocation' => $this->getEventLocation(),
                'eventDescription' => $this->event->description,
                'hasRsvp' => $this->event->rsvp_enabled ?? false,
                'hasQrCheckin' => $this->event->qr_checkin_enabled ?? false,
                'organizerEmail' => $this->event->user->email ?? config('mail.from.address'),
                'organizerPhone' => $this->event->user->phone ?? null,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get formatted event location
     */
    private function getEventLocation(): string
    {
        $locationParts = [];
        
        if (!empty($this->event->venue_name)) {
            $locationParts[] = $this->event->venue_name;
        }
        
        if (!empty($this->event->location)) {
            $locationParts[] = $this->event->location;
        }
        
        if (!empty($this->event->venue_address)) {
            $locationParts[] = $this->event->venue_address;
        }
        
        return implode(', ', $locationParts) ?: 'Location TBD';
    }
}
