<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'invitation_id',
        'event_id',
        'guest_id',
        'scheduled_for',
        'platform',
        'status',
        'event_name',
        'event_date',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_timezone',
        'message_content',
        'subject',
        'sent_at',
        'error_message',
        'attempts',
        'last_attempt_at',
        'job_id',
        'queue'
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Check if reminder can be sent
     */
    public function canBeSent(): bool
    {
        return $this->status === 'pending' && 
               $this->scheduled_for <= now() && 
               $this->attempts < 3;
    }

    /**
     * Check if reminder is for email
     */
    public function isEmail(): bool
    {
        return $this->platform === 'email';
    }

    /**
     * Check if reminder is for WhatsApp
     */
    public function isWhatsApp(): bool
    {
        return $this->platform === 'whatsapp';
    }

    /**
     * Increment attempt count
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
        $this->update(['last_attempt_at' => now()]);
    }

    /**
     * Mark reminder as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    /**
     * Mark reminder as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'last_attempt_at' => now()
        ]);
    }
}
