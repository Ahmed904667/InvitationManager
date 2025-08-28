<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Models\GuestGroup;
use App\Shared\Models\Invitation;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_list_id',
        'name',
        'email',
        'phone',
        'timezone',
        'group_id',
        'language',
        'notes',
        'checked_in',
        'checked_in_at',
        'checked_in_by',
        'check_in_notes'
    ];

    protected $casts = [
        'checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    public function guestList()
    {
        return $this->belongsTo(GuestList::class);
    }

    public function group()
    {
        return $this->belongsTo(GuestGroup::class);
    }

    public function guestGroup()
    {
        return $this->belongsTo(GuestGroup::class, 'group_id');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function eventGuests()
    {
        return $this->hasMany(\App\EventGuest::class);
    }

    public function activeEvents()
    {
        return $this->belongsToMany(Event::class, 'event_guest')
                    ->wherePivot('status', \App\EventGuest::STATUS_ACTIVE)
                    ->withTimestamps();
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in === true;
    }

    public function checkIn(User $user, ?string $notes = null): void
    {
        $this->checked_in = true;
        $this->checked_in_at = now();
        $this->checked_in_by = $user->id;
        $this->check_in_notes = $notes;
        $this->save();
    }

    public function undoCheckIn(): void
    {
        $this->checked_in = false;
        $this->checked_in_at = null;
        $this->checked_in_by = null;
        $this->check_in_notes = null;
        $this->save();
    }

    public function getCheckInTime(): ?string
    {
        return $this->checked_in_at?->format('Y-m-d H:i:s');
    }

    public function getCheckInDuration(): ?string
    {
        if (!$this->checked_in_at) return null;
        
        return $this->checked_in_at->diffForHumans();
    }

    /**
     * Check if the guest is standalone (not part of any guest list)
     */
    public function isStandalone(): bool
    {
        return is_null($this->guest_list_id);
    }

    /**
     * Get the guest list name or return 'Standalone Guest'
     */
    public function getGuestListName(): string
    {
        return $this->guestList ? $this->guestList->name : 'Standalone Guest';
    }
}
