<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Shared\Models\Event;
use App\Shared\Models\Guest;
use App\Shared\Models\User;

/**
 * @property int $id
 * @property int $event_id
 * @property int $guest_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $removed_at
 * @property string|null $removal_reason
 * @property int|null $removed_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $checked_in
 * @property \Illuminate\Support\Carbon|null $checked_in_at
 * @property int|null $checked_in_by
 * @property string|null $check_in_notes
 * @property int|null $scanned_by_scanner_id
 * @property string|null $scanner_name
 * @property-read User|null $checkedInBy
 * @property-read Event $event
 * @property-read Guest $guest
 * @property-read User|null $removedBy
 * @property-read \App\Scanner|null $scannedByScanner
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereCheckInNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereCheckedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereCheckedInBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereGuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereRemovalReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereRemovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereRemovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereScannedByScannerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereScannerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventGuest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EventGuest extends Model
{
    protected $table = 'event_guest';
    
    protected $fillable = [
        'event_id',
        'guest_id',
        'status',
        'removed_at',
        'removal_reason',
        'removed_by',
        'checked_in',
        'checked_in_at',
        'checked_in_by',
        'check_in_notes',
        'scanned_by_scanner_id',
        'scanner_name'
    ];

    protected $casts = [
        'removed_at' => 'datetime',
        'checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_REMOVED = 'removed';
    const STATUS_EXPIRED = 'expired';

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class)->withoutGlobalScope('not_deleted');
    }

    public function removedBy()
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isRemoved(): bool
    {
        return $this->status === self::STATUS_REMOVED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function markAsRemoved(?string $reason = null, ?int $removedBy = null): void
    {
        $this->update([
            'status' => self::STATUS_REMOVED,
            'removed_at' => now(),
            'removal_reason' => $reason,
            'removed_by' => $removedBy
        ]);
    }

    public function markAsExpired(?string $reason = null, ?int $removedBy = null): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
            'removed_at' => now(),
            'removal_reason' => $reason,
            'removed_by' => $removedBy
        ]);
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in === true;
    }

    public function checkIn(\App\Scanner $scanner, ?string $notes = null): void
    {
        $this->update([
            'checked_in' => true,
            'checked_in_at' => now(),
            'check_in_notes' => $notes,
            'scanned_by_scanner_id' => $scanner->id,
            'scanner_name' => $scanner->name,
        ]);
        
        // Update scanner last used
        $scanner->updateLastUsed();
    }

    public function undoCheckIn(): void
    {
        $this->update([
            'checked_in' => false,
            'checked_in_at' => null,
            'checked_in_by' => null,
            'check_in_notes' => null,
            'scanned_by_scanner_id' => null,
            'scanner_name' => null,
        ]);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function scannedByScanner()
    {
        return $this->belongsTo(\App\Scanner::class, 'scanned_by_scanner_id');
    }
}
