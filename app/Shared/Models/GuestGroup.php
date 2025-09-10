<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $guest_list_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $description
 * @property string|null $color
 * @property-read \App\Shared\Models\GuestList $guestList
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Shared\Models\Guest> $guests
 * @property-read int|null $guests_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestGroup whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestGroup whereGuestListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
