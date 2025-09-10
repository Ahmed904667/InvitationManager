<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $invitation_id
 * @property int $event_id
 * @property int $guest_id
 * @property \Illuminate\Support\Carbon $scheduled_for
 * @property string $platform
 * @property string $status
 * @property string $event_name
 * @property string $event_date
 * @property string $guest_name
 * @property string|null $guest_email
 * @property string|null $guest_phone
 * @property string $guest_timezone
 * @property string|null $message_content
 * @property string|null $subject
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property string|null $error_message
 * @property int $attempts
 * @property string|null $last_attempt_at
 * @property string|null $job_id
 * @property string|null $queue
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Shared\Models\Event $event
 * @property-read \App\Shared\Models\Guest $guest
 * @property-read \App\Shared\Models\Invitation $invitation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereEventDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereGuestEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereGuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereGuestName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereGuestPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereGuestTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereInvitationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereLastAttemptAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereMessageContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereQueue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereScheduledFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
