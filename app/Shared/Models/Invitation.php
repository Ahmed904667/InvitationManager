<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $event_id
 * @property int $guest_id
 * @property string $token
 * @property string $channel
 * @property string|null $recipient
 * @property string|null $message
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property string $rsvp_status
 * @property \Illuminate\Support\Carbon|null $rsvp_at
 * @property string|null $rsvp_note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $expired_at
 * @property string|null $external_id
 * @property array<array-key, mixed>|null $delivery_details
 * @property-read \App\Shared\Models\Event $event
 * @property-read \App\Shared\Models\Guest $guest
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereDeliveryDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereGuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereRecipient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereRsvpAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereRsvpNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereRsvpStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Invitation extends Model
{
    protected $fillable = [
        'event_id',
        'guest_id',
        'token',
        'channel',
        'recipient',
        'message',
        'status',
        'sent_at',
        'expired_at',
        'rsvp_status',
        'rsvp_at',
        'rsvp_note',
        'external_id',
        'delivery_details',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'expired_at' => 'datetime',
        'rsvp_at' => 'datetime',
        'delivery_details' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';

    // RSVP status constants
    const RSVP_YES = 'yes';
    const RSVP_NO = 'no';
    const RSVP_MAYBE = 'maybe';
    const RSVP_NONE = 'none';

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}

