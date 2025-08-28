<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'event_date',
        'max_guests',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'event_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guestGroups()
    {
        return $this->hasMany(GuestGroup::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function checkedInGuests()
    {
        return $this->hasMany(Guest::class)->where('checked_in', true);
    }

    public function getDefaultSettings()
    {
        return [
            'fields' => [
                'email' => true,
                'phone' => false,
                'group' => false,
                'notes' => false
            ],
            'check_in' => [
                'require_confirmation' => false,
                'allow_manual_checkin' => true,
                'qr_code_enabled' => true
            ],
            'notifications' => [
                'email_reminders' => false,
                'sms_reminders' => false
            ]
        ];
    }

    public function getCheckInRate(): float
    {
        $totalGuests = $this->guests()->count();
        if ($totalGuests === 0) return 0;
        
        $checkedInGuests = $this->guests()->where('checked_in', true)->count();
        return round(($checkedInGuests / $totalGuests) * 100, 2);
    }

    public function isEventToday(): bool
    {
        return $this->event_date && $this->event_date->isToday();
    }

    public function isEventUpcoming(): bool
    {
        return $this->event_date && $this->event_date->isFuture();
    }

    public function isEventPast(): bool
    {
        return $this->event_date && $this->event_date->isPast();
    }
}
