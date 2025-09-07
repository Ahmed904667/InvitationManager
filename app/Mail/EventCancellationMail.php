<?php

namespace App\Mail;

use App\Shared\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventCancellationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $apologyMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(Event $event, string $apologyMessage)
    {
        $this->event = $event;
        $this->apologyMessage = $apologyMessage;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Event Cancellation: ' . $this->event->name)
                    ->view('emails.event-cancellation')
                    ->with([
                        'event' => $this->event,
                        'apologyMessage' => $this->apologyMessage
                    ]);
    }
}
