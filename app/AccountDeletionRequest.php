<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Shared\Models\User;

/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $email
 * @property \Illuminate\Support\Carbon $requested_at
 * @property \Illuminate\Support\Carbon $expires_at
 * @property bool $confirmed
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest whereConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountDeletionRequest whereUserId($value)
 * @mixin \Eloquent
 */
class AccountDeletionRequest extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'email',
        'requested_at',
        'expires_at',
        'confirmed',
        'confirmed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'confirmed' => 'boolean',
    ];

    /**
     * Get the user that owns the deletion request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Shared\Models\User::class);
    }

    /**
     * Check if the deletion request is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the deletion request is valid (not expired and not confirmed).
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->confirmed;
    }

    /**
     * Mark the deletion request as confirmed.
     */
    public function markAsConfirmed(): void
    {
        $this->update([
            'confirmed' => true,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Create a new deletion request for a user.
     */
    public static function createForUser(\App\Shared\Models\User $user): self
    {
        // Delete any existing unconfirmed requests for this user
        static::where('user_id', $user->id)
            ->where('confirmed', false)
            ->delete();

        return static::create([
            'user_id' => $user->id,
            'token' => \Str::random(64),
            'email' => $user->email,
            'requested_at' => now(),
            'expires_at' => now()->addHours(24), // 24 hours to confirm
        ]);
    }
}
