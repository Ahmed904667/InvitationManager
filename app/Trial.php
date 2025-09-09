<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Trial extends Model
{
    protected $fillable = [
        'contact',
        'name',
        'eventType',
        'contact_method',
        'invitation_message',
        'status',
        'ip_address',
        'user_agent',
        'invite_token',
        'sample_event_data',
        'rsvp_status',
        'rsvp_note',
        'rsvp_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'sample_event_data' => 'array',
        'rsvp_at' => 'datetime',
    ];

    /**
     * Boot the model and generate invite token
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($trial) {
            if (empty($trial->invite_token)) {
                $trial->invite_token = Str::random(32);
            }
        });
    }

    /**
     * Generate the invite URL for this trial
     */
    public function getInviteUrlAttribute(): string
    {
        return route('trial.invite', ['token' => $this->invite_token]);
    }

    /**
     * Check if the trial invite is still valid (not expired)
     */
    public function isInviteValid(): bool
    {
        // Trial invites are valid for 7 days
        return $this->created_at->addDays(7)->isFuture();
    }
}
