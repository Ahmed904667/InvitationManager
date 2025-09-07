<?php

namespace App\Shared\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'scanner_settings',
        'organizer_settings',
        'last_offline_sync',
        'timezone',
        'phone',
        'profile_photo_path',
        'bio',
    ];

    public function guestLists()
    {
        return $this->hasMany(GuestList::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function checkedInGuests()
    {
        return $this->hasMany(Guest::class, 'checked_in_by');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
                    'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'last_offline_sync' => 'datetime',
        'scanner_settings' => 'array',
        'organizer_settings' => 'array',
        ];
    }

    // Role-based methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOrganizer(): bool
    {
        return in_array($this->role, ['admin', 'organizer']);
    }

    public function isScanner(): bool
    {
        return in_array($this->role, ['admin', 'organizer', 'scanner']);
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'organizer' => 'Event Organizer',
            'scanner' => 'Event Scanner',
            default => 'User'
        };
    }

    /**
     * Get the user's profile photo URL.
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            return Storage::disk('public')->url($this->profile_photo_path);
        }
        
        return null;
    }
}
