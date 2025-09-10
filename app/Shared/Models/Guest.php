<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Models\GuestGroup;
use App\Shared\Models\Invitation;

/**
 * @property int $id
 * @property int|null $guest_list_id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $language
 * @property int|null $group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $notes
 * @property bool $checked_in
 * @property \Illuminate\Support\Carbon|null $checked_in_at
 * @property int|null $checked_in_by
 * @property string|null $check_in_notes
 * @property string|null $timezone
 * @property int|null $scanned_by_scanner_id
 * @property string|null $scanner_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Shared\Models\Event> $activeEvents
 * @property-read int|null $active_events_count
 * @property-read \App\Shared\Models\User|null $checkedInBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\EventGuest> $eventGuests
 * @property-read int|null $event_guests_count
 * @property-read GuestGroup|null $group
 * @property-read GuestGroup|null $guestGroup
 * @property-read \App\Shared\Models\GuestList|null $guestList
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Invitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read \App\Scanner|null $scannedByScanner
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereCheckInNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereCheckedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereCheckedInBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereGuestListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereScannedByScannerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereScannerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        'check_in_notes',
        'scanned_by_scanner_id',
        'scanner_name'
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

    public function scannedByScanner()
    {
        return $this->belongsTo(\App\Scanner::class, 'scanned_by_scanner_id');
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

    /**
     * Check in guest via scanner
     */
    public function scannerCheckIn(\App\Scanner $scanner, ?string $notes = null): void
    {
        $this->checked_in = true;
        $this->checked_in_at = now(); // Store in UTC
        $this->check_in_notes = $notes;
        $this->scanned_by_scanner_id = $scanner->id;
        $this->scanner_name = $scanner->name;
        $this->save();
        
        // Update scanner last used
        $scanner->updateLastUsed();
    }

    /**
     * Get display contact info based on event settings
     */
    public function getContactInfo(array $eventSettings = []): array
    {
        $contacts = [];
        
        if (!empty($this->email) && ($eventSettings['show_email'] ?? true)) {
            $contacts['email'] = $this->email;
        }
        
        if (!empty($this->phone) && ($eventSettings['show_phone'] ?? true)) {
            $contacts['phone'] = $this->phone;
        }
        
        return $contacts;
    }
}
