<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public function scannedGuests()
    {
        return $this->hasMany(\App\Shared\Models\Guest::class, 'scanned_by_scanner_id');
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
        return $this->scannedGuests()->where('checked_in', true)->count();
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
        
        return $this->scannedGuests()
            ->whereNotNull('checked_in_at')
            ->where('checked_in_at', '>=', $startTime)
            ->get();
    }
}
