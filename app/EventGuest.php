<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Shared\Models\Event;
use App\Shared\Models\Guest;
use App\Shared\Models\User;

class EventGuest extends Model
{
    protected $table = 'event_guest';
    
    protected $fillable = [
        'event_id',
        'guest_id',
        'status',
        'removed_at',
        'removal_reason',
        'removed_by'
    ];

    protected $casts = [
        'removed_at' => 'datetime',
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
        return $this->belongsTo(Guest::class);
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
}
