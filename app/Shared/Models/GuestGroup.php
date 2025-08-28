<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_list_id',
        'name',
        'description',
        'color'
    ];

    public function guestList()
    {
        return $this->belongsTo(GuestList::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class, 'group_id');
    }

    public function getGuestCount(): int
    {
        return $this->guests()->count();
    }

    public function getCheckedInCount(): int
    {
        return $this->guests()->where('checked_in', true)->count();
    }
}
