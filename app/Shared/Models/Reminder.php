<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Reminder extends Model
{
    use HasFactory;

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
        'event_date' => 'datetime',
        'sent_at' => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    /**
     * Get the invitation that owns the reminder.
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    /**
     * Get the event that owns the reminder.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the guest that owns the reminder.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Scope for pending reminders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for due reminders (scheduled_for <= now)
     */
    public function scopeDue($query)
    {
        return $query->where('scheduled_for', '<=', now());
    }

    /**
     * Scope for overdue reminders
     */
    public function scopeOverdue($query)
    {
        return $query->where('scheduled_for', '<', now()->subMinutes(5));
    }

    /**
     * Check if reminder is due
     */
    public function isDue(): bool
    {
        return $this->scheduled_for <= now();
    }

    /**
     * Check if reminder is overdue
     */
    public function isOverdue(): bool
    {
        return $this->scheduled_for < now()->subMinutes(5);
    }

    /**
     * Check if reminder can be sent
     */
    public function canBeSent(): bool
    {
        return $this->status === 'pending' && $this->isDue();
    }

    /**
     * Mark reminder as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'attempts' => $this->attempts + 1,
            'last_attempt_at' => now()
        ]);
    }

    /**
     * Mark reminder as failed
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'attempts' => $this->attempts + 1,
            'last_attempt_at' => now()
        ]);
    }

    /**
     * Mark reminder as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
            'last_attempt_at' => now()
        ]);
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
     * Get formatted scheduled time
     */
    public function getFormattedScheduledTimeAttribute(): string
    {
        return $this->scheduled_for->format('l, F j, Y \a\t g:i A');
    }

    /**
     * Get time until reminder
     */
    public function getTimeUntilReminderAttribute(): string
    {
        return $this->scheduled_for->diffForHumans();
    }

    /**
     * Get platform display name
     */
    public function getPlatformDisplayNameAttribute(): string
    {
        return match($this->platform) {
            'email' => 'Email',
            'whatsapp' => 'WhatsApp',
            default => ucfirst($this->platform)
        };
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'sent' => 'Sent',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status color class
     */
    public function getStatusColorClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'text-yellow-600 bg-yellow-100',
            'sent' => 'text-green-600 bg-green-100',
            'failed' => 'text-red-600 bg-red-100',
            'cancelled' => 'text-gray-600 bg-gray-100',
            default => 'text-gray-600 bg-gray-100'
        };
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
     * Get contact information for the platform
     */
    public function getContactInfo(): ?string
    {
        return match($this->platform) {
            'email' => $this->guest_email,
            'whatsapp' => $this->guest_phone,
            default => null
        };
    }
}
