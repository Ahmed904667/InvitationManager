<?php

namespace App\Shared\Models;

use App\Observers\EventObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property string|null $location
 * @property string|null $venue_name
 * @property string|null $venue_address
 * @property string $invitation_title
 * @property bool $rsvp_enabled
 * @property bool $qr_checkin_enabled
 * @property array<array-key, mixed>|null $guest_list_ids
 * @property string $send_type
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property string $status
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $invitation_platforms
 * @property string $message_mode
 * @property string|null $general_message
 * @property array<array-key, mixed>|null $group_messages
 * @property array<array-key, mixed>|null $per_guest_messages
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Shared\Models\Guest> $activeGuests
 * @property-read int|null $active_guests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Scanner> $activeScanners
 * @property-read int|null $active_scanners_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\EventGuest> $eventGuests
 * @property-read int|null $event_guests_count
 * @property-read string $formatted_date
 * @property-read string $formatted_end_date
 * @property-read string $formatted_start_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Shared\Models\GuestList> $guestLists
 * @property-read int|null $guest_lists_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Shared\Models\Invitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Shared\Models\Notification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Shared\Models\Guest> $removedGuests
 * @property-read int|null $removed_guests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Scanner> $scanners
 * @property-read int|null $scanners_count
 * @property-read \App\Shared\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event past()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event upcoming()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAccentColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAdditionalInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAiGenerated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCustomMessages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereDatesInUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereFontFamily($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereGeneralMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereGroupMessages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereGuestListIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereHeroColor1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereHeroColor2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereInvitationMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereInvitationPlatforms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereInvitationTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereMessageMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereMessageTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event wherePerGuestMessages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereQrCheckinEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereRsvpEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereSendType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereVenueAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereVenueName($value)
 * @mixin \Eloquent
 */
class Event extends Model
{
    use HasFactory;

    /**
     * Boot the model and register observers
     */
    protected static function boot()
    {
        parent::boot();
        
        // Register the EventObserver
        static::observe(EventObserver::class);
    }

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'end_date_explicitly_set',
        'location',
        'venue_name',
        'venue_address',
        'latitude',
        'longitude',
        'invitation_title',
        'rsvp_enabled',
        'qr_checkin_enabled',
        'invitation_platforms',
        'message_mode',
        'general_message',
        'group_messages',
        'per_guest_messages',
        'excluded_guest_ids',
        'guest_list_ids',
        'send_type',
        'scheduled_at',
        'status',
        'user_id',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'scheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rsvp_enabled' => 'boolean',
        'qr_checkin_enabled' => 'boolean',
        'end_date_explicitly_set' => 'boolean',
        'guest_list_ids' => 'array',
        'invitation_platforms' => 'array',
        'group_messages' => 'array',
        'per_guest_messages' => 'array',
        'excluded_guest_ids' => 'array',
    ];

    /**
     * Automatically set end_date to the day after start_date if not provided
     */
    public function setEndDateAttribute($value)
    {
        // If end_date is explicitly provided and not empty, use it and mark as explicitly set
        if (!empty($value)) {
            $this->attributes['end_date'] = $value;
            $this->attributes['end_date_explicitly_set'] = true;
            return;
        }

        // If start_date is set and end_date is not provided, set end_date to the day after start_date
        if (!empty($this->attributes['start_date'])) {
            $startDate = \Carbon\Carbon::parse($this->attributes['start_date']);
            $this->attributes['end_date'] = $startDate->copy()->addDay()->format('Y-m-d H:i:s');
            $this->attributes['end_date_explicitly_set'] = false; // Auto-generated
        } else {
            // If no start_date, set end_date to null
            $this->attributes['end_date'] = null;
            $this->attributes['end_date_explicitly_set'] = false;
        }
    }

    /**
     * When start_date is set, automatically set end_date if not already set
     */
    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = $value;
        
        // If end_date is not set or is null, set it to the day after start_date
        if (empty($this->attributes['end_date']) && !empty($value)) {
            $startDate = \Carbon\Carbon::parse($value);
            $this->attributes['end_date'] = $startDate->copy()->addDay()->format('Y-m-d H:i:s');
            $this->attributes['end_date_explicitly_set'] = false; // Auto-generated
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guestLists(): BelongsToMany
    {
        return $this->belongsToMany(GuestList::class, 'event_guest_list');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function eventGuests()
    {
        return $this->hasMany(\App\EventGuest::class);
    }

    public function notifications()
    {
        return $this->hasMany(\App\Shared\Models\Notification::class);
    }

    public function activeGuests()
    {
        return $this->belongsToMany(Guest::class, 'event_guest')
                    ->wherePivot('status', \App\EventGuest::STATUS_ACTIVE)
                    ->withTimestamps();
    }

    public function removedGuests()
    {
        return $this->belongsToMany(Guest::class, 'event_guest')
                    ->wherePivot('status', \App\EventGuest::STATUS_REMOVED)
                    ->withTimestamps();
    }

    public function scanners()
    {
        return $this->hasMany(\App\Scanner::class);
    }

    public function activeScanners()
    {
        return $this->hasMany(\App\Scanner::class)->where('is_active', true);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->start_date->format('l, F jS, Y • g:i A');
    }

    public function getFormattedStartDateAttribute(): string
    {
        return $this->start_date->format('Y-m-d\TH:i');
    }

    public function getFormattedEndDateAttribute(): string
    {
        return $this->end_date ? $this->end_date->format('Y-m-d\TH:i') : '';
    }

    /**
     * Convert date from user timezone to UTC
     */
    public function convertToUtc($date, $userTimezone = null): \Carbon\Carbon
    {
        if (!$userTimezone) {
            $userTimezone = $this->user->timezone ?? 'UTC';
        }
        
        // Create Carbon instance in user timezone then convert to UTC
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date, $userTimezone)->utc();
    }
    
    /**
     * Convert date from UTC to user timezone
     */
    public function convertFromUtc($date, $userTimezone = null): \Carbon\Carbon
    {
        if (!$userTimezone) {
            $userTimezone = $this->user->timezone ?? 'UTC';
        }
        
        return \Carbon\Carbon::parse($date, 'UTC')->setTimezone($userTimezone);
    }
    
    /**
     * Check if the event is completed based on start/end dates
     * Takes into account user timezone for proper comparison
     */
    public function isCompleted(): bool
    {
        $now = now();
        
        // If there's an end date, check if it has passed
        if ($this->end_date) {
            return $now->isAfter($this->end_date);
        }
        
        // If no end date, check if start date has passed (by day)
        // This handles the case where end_date is null and we want to mark as completed after one day
        // Use copy() to avoid mutating the original Carbon instance
        $startOfDay = $this->start_date->copy()->startOfDay();
        $nowStartOfDay = $now->copy()->startOfDay();
        
        // Event is completed if the start date's day has passed
        return $nowStartOfDay->isAfter($startOfDay);
    }

    /**
     * Check if the event is currently ongoing
     */
    public function isOngoing(): bool
    {
        $now = now();
        
        // Event is ongoing if current time is between start and end dates
        if ($this->end_date) {
            return $now->isBetween($this->start_date, $this->end_date);
        }
        
        // If no end date, event is ongoing only if current time is past the start time
        // AND it's the same day (to avoid marking events as running days in advance)
        return $now->copy()->startOfDay()->equalTo($this->start_date->copy()->startOfDay()) 
               && $now->gte($this->start_date);
    }

    /**
     * Check if the event is upcoming (not started yet)
     */
    public function isUpcoming(): bool
    {
        $now = now();
        
        // If there's an end date, check if start date is in the future
        if ($this->end_date) {
            return $now->isBefore($this->start_date);
        }
        
        // If no end date, check if start date is in the future (by day)
        return $now->copy()->startOfDay()->isBefore($this->start_date->copy()->startOfDay());
    }

    /**
     * Mark the event as running
     */
    public function markAsRunning(): void
    {
        if ($this->status !== 'running' && $this->status !== 'completed' && $this->status !== 'draft') {
            $this->update(['status' => 'running']);
        }
    }

    /**
     * Mark the event as completed
     */
    public function markAsCompleted(): void
    {
        if ($this->status !== 'completed' && $this->status !== 'draft') {
            $this->update(['status' => 'completed']);
        }
    }

    /**
     * Scope to get completed events
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get active events (not completed)
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'completed');
    }

    /**
     * Scope to get upcoming events
     */
    public function scopeUpcoming($query)
    {
        $now = now();
        $startOfDay = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();
        
        return $query->where(function ($q) use ($now, $startOfDay, $endOfDay) {
            $q->where('start_date', '>', $now)
              ->orWhere(function ($subQ) use ($startOfDay, $endOfDay) {
                  $subQ->whereNull('end_date')
                       ->where('start_date', '>=', $startOfDay)
                       ->where('start_date', '<', $endOfDay);
              });
        });
    }

    /**
     * Scope to get past events (completed based on dates)
     */
    public function scopePast($query)
    {
        $now = now();
        $startOfDay = $now->copy()->startOfDay();
        
        return $query->where(function ($q) use ($now, $startOfDay) {
            $q->where('end_date', '<', $now)
              ->orWhere(function ($subQ) use ($startOfDay) {
                  $subQ->whereNull('end_date')
                       ->where('start_date', '<', $startOfDay);
              });
        });
    }

    /**
     * Check if this event can use scanner functionality
     */
    public function canUseScanner(): bool
    {
        return $this->qr_checkin_enabled && 
               in_array($this->status, ['sent', 'scheduled', 'running']);
    }

    /**
     * Generate a new scanner for this event
     */
    public function createScanner(string $name): \App\Scanner
    {
        return $this->scanners()->create([
            'name' => $name,
            'is_active' => true,
            'timezone' => $this->user->timezone ?? 'UTC'
        ]);
    }

    /**
     * Get the main scanner URL for this event (creates default scanner if none exists)
     */
    public function getMainScannerUrl(): string
    {
        $scanner = $this->activeScanners()->first();
        
        if (!$scanner) {
            $scanner = $this->createScanner('Main Scanner');
        }
        
        return $scanner->getScannerUrl();
    }
} 