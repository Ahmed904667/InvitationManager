<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $contact
 * @property string $name
 * @property string $eventType
 * @property string $status
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $contact_method
 * @property string|null $invitation_message
 * @property string|null $invite_token
 * @property array<array-key, mixed>|null $sample_event_data
 * @property string|null $rsvp_status
 * @property string|null $rsvp_note
 * @property \Illuminate\Support\Carbon|null $rsvp_at
 * @property-read string $invite_url
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereContactMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereInvitationMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereInviteToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereRsvpAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereRsvpNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereRsvpStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereSampleEventData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trial whereUserAgent($value)
 * @mixin \Eloquent
 */
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
