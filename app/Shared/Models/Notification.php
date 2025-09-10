<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $event_id
 * @property int|null $guest_id
 * @property int|null $user_id
 * @property string $type
 * @property string $channel
 * @property string $message
 * @property string $status
 * @property string|null $external_id
 * @property array<array-key, mixed>|null $delivery_details
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $failed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Shared\Models\Event|null $event
 * @property-read \App\Shared\Models\Guest|null $guest
 * @property-read \App\Shared\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereDeliveryDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereFailedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereGuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 * @mixin \Eloquent
 */
class Notification extends Model
{
    protected $fillable = [
        'event_id',
        'guest_id',
        'user_id',
        'type',
        'channel',
        'message',
        'status',
        'external_id',
        'delivery_details',
        'error_message',
        'sent_at',
        'delivered_at',
        'failed_at',
    ];

    protected $casts = [
        'delivery_details' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    // Type constants
    const TYPE_GUEST_REMOVAL = 'guest_removal';
    const TYPE_EVENT_UPDATE = 'event_update';
    const TYPE_EVENT_REMINDER = 'event_reminder';
    const TYPE_EVENT_START_REMINDER = 'event_start_reminder';
    const TYPE_RSVP_RESPONSE = 'rsvp_response';
    const TYPE_CUSTOM = 'custom';

    // Channel constants
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_WHATSAPP = 'whatsapp';
    const CHANNEL_BOTH = 'both';

    // Status constants - Core notification statuses
    const STATUS_PENDING = 'pending';
    const STATUS_QUEUED = 'queued';
    const STATUS_SENDING = 'sending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_FAILED = 'failed';
    const STATUS_BOUNCED = 'bounced';
    const STATUS_UNDELIVERED = 'undelivered';
    const STATUS_CANCELED = 'canceled';

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Shared\Models\User::class);
    }

    // Helper methods
    public function markAsQueued($deliveryDetails = null)
    {
        $this->update([
            'status' => self::STATUS_QUEUED,
            'delivery_details' => $deliveryDetails,
        ]);
    }
    


    public function markAsDelivered($externalId = null, $deliveryDetails = null)
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
            'external_id' => $externalId,
            'delivery_details' => $deliveryDetails,
        ]);
    }
    
    public function markAsRead($deliveryDetails = null)
    {
        $this->update([
            'status' => self::STATUS_READ,
            'delivery_details' => $deliveryDetails,
        ]);
    }

    public function markAsFailed($errorMessage, $deliveryDetails = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'error_message' => $errorMessage,
            'delivery_details' => $deliveryDetails,
        ]);
    }
    
    public function markAsPending($deliveryDetails = null)
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'delivery_details' => $deliveryDetails,
        ]);
    }
    
    public function markAsSending($deliveryDetails = null)
    {
        $this->update([
            'status' => self::STATUS_SENDING,
            'delivery_details' => $deliveryDetails,
        ]);
    }
    
    public function markAsSent($deliveryDetails = null)
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'delivery_details' => $deliveryDetails,
        ]);
    }
    
    public function markAsBounced($deliveryDetails = null)
    {
        $this->update([
            'status' => self::STATUS_BOUNCED,
            'failed_at' => now(),
            'delivery_details' => $deliveryDetails,
        ]);
    }
    
    public function markAsUndelivered($deliveryDetails = null)
    {
        $this->update([
            'status' => self::STATUS_UNDELIVERED,
            'failed_at' => now(),
            'delivery_details' => $deliveryDetails,
        ]);
    }
    
    public function markAsCanceled($deliveryDetails = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELED,
            'failed_at' => now(),
            'delivery_details' => $deliveryDetails,
        ]);
    }

    public function isSuccessful()
    {
        return in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_READ, self::STATUS_SENT]);
    }

    public function isFailed()
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_BOUNCED, self::STATUS_UNDELIVERED, self::STATUS_CANCELED]);
    }
    
    public function isInProgress()
    {
        return in_array($this->status, [self::STATUS_QUEUED, self::STATUS_PENDING, self::STATUS_SENDING]);
    }
    
    public function isDelivered()
    {
        return in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_READ, self::STATUS_SENT]);
    }
    
    /**
     * Update delivery details without changing the main status
     */
    public function updateDeliveryDetails($deliveryDetails)
    {
        $this->update([
            'delivery_details' => array_merge($this->delivery_details ?? [], $deliveryDetails)
        ]);
    }
}
