<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $eventType;
    public $contact;
    public $invitationMessage;
    public $subject;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $eventType, $contact, $invitationMessage = null, $subject = null)
    {
        $this->name = $name;
        $this->eventType = $eventType;
        $this->contact = $contact;
        $this->invitationMessage = $invitationMessage;
        $this->subject = $subject;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject ?? "Welcome to Guest Manager - Your {$this->eventType} Event Trial",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-request',
            with: [
                'name' => $this->name,
                'eventType' => $this->eventType,
                'contact' => $this->contact,
                'invitationMessage' => $this->invitationMessage,
                'subject' => $this->subject,
            ],
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
}
