<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string $token
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property array<array-key, mixed>|null $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $timezone
 * @property-read \App\Shared\Models\Event $event
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\EventGuest> $scannedEventGuests
 * @property-read int|null $scanned_event_guests_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scanner whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Scanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'timezone',
        'token',
        'is_active',
        'last_used_at',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'settings' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($scanner) {
            if (empty($scanner->token)) {
                $scanner->token = Str::random(32);
            }
        });
    }

    public function event()
    {
        return $this->belongsTo(\App\Shared\Models\Event::class);
    }

    public function scannedEventGuests()
    {
        return $this->hasMany(\App\EventGuest::class, 'scanned_by_scanner_id');
    }

    /**
     * Generate scanner URL for this scanner
     */
    public function getScannerUrl(): string
    {
        return route('mobile.scanner.scan', ['token' => $this->token]);
    }

    /**
     * Update last used timestamp
     */
    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get total check-ins by this scanner
     */
    public function getCheckInCount(): int
    {
        return $this->scannedEventGuests()->where('checked_in', true)->count();
    }

    /**
     * Get the scanner's timezone or default to UTC
     */
    public function getTimezone(): string
    {
        return $this->timezone ?? 'UTC';
    }

    /**
     * Convert a datetime to the scanner's timezone
     */
    public function toScannerTimezone($datetime): \Carbon\Carbon
    {
        return \Carbon\Carbon::parse($datetime)->setTimezone($this->getTimezone());
    }

    /**
     * Get current time in scanner's timezone
     */
    public function getCurrentTime(): \Carbon\Carbon
    {
        return now()->setTimezone($this->getTimezone());
    }

    /**
     * Get check-ins in the last 12 hours based on scanner's timezone
     */
    public function getRecentCheckIns($hours = 12): \Illuminate\Database\Eloquent\Collection
    {
        $currentTime = $this->getCurrentTime();
        $startTime = $currentTime->copy()->subHours($hours);
        
        return $this->scannedEventGuests()
            ->whereNotNull('checked_in_at')
            ->where('checked_in_at', '>=', $startTime)
            ->get();
    }
}
