<?php

namespace App\Shared\Models;

use App\Observers\EventObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'location',
        'venue_name',
        'venue_address',
        'parking_info',
        'additional_information',
        'invitation_title',
        'invitation_subtitle',
        'invitation_message',
        'rsvp_message',
        'rsvp_deadline',
        'rsvp_contact',
        'rsvp_enabled',
        'qr_checkin_enabled',
        'qr_code_url',
        'qr_description',
        'invitation_platforms',
        'message_mode',
        'general_message',
        'group_messages',
        'per_guest_messages',
        'ai_generated',
        'hero_color1',
        'hero_color2',
        'accent_color',
        'font_family',
        'guest_list_ids',
        'message_template',
        'custom_messages',
        'attachments',
        'send_type',
        'scheduled_at',
        'status',
        'dates_in_utc',
        'user_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'scheduled_at' => 'datetime',
        'rsvp_enabled' => 'boolean',
        'qr_checkin_enabled' => 'boolean',
        'ai_generated' => 'boolean',
        'dates_in_utc' => 'boolean',
        'guest_list_ids' => 'array',
        'invitation_platforms' => 'array',
        'group_messages' => 'array',
        'per_guest_messages' => 'array',
        'custom_messages' => 'array',
        'attachments' => 'array',
    ];

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
        // Use copy() to avoid mutating the original Carbon instance
        return $this->start_date->copy()->startOfDay()->isBefore($now->copy()->startOfDay());
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
        
        // If no end date, event is ongoing on the start date
        return $now->copy()->startOfDay()->equalTo($this->start_date->copy()->startOfDay());
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
        if ($this->status !== 'running' && $this->status !== 'completed') {
            $this->update(['status' => 'running']);
        }
    }

    /**
     * Mark the event as completed
     */
    public function markAsCompleted(): void
    {
        if ($this->status !== 'completed') {
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
} 