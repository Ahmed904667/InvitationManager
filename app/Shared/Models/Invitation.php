<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;

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

