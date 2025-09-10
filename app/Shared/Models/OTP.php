<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $type
 * @property string $identifier
 * @property string $code
 * @property \Illuminate\Support\Carbon $expires_at
 * @property bool $used
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Shared\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP whereUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OTP whereUserId($value)
 * @mixin \Eloquent
 */
class OTP extends Model
{
    protected $table = 'otps';
    
    protected $fillable = [
        'user_id',
        'type',
        'identifier',
        'code',
        'expires_at',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new OTP code
     */
    public static function generateOTP(int $userId, string $type, string $identifier): string
    {
        // Delete any existing unused OTPs for this user and type
        self::where('user_id', $userId)
            ->where('type', $type)
            ->where('used', false)
            ->delete();

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Create new OTP record
        self::create([
            'user_id' => $userId,
            'type' => $type,
            'identifier' => $identifier,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(10), // 10 minutes expiry
        ]);

        return $code;
    }

    /**
     * Validate OTP code
     */
    public static function validateOTP(int $userId, string $type, string $identifier, string $code): bool
    {
        $otp = self::where('user_id', $userId)
            ->where('type', $type)
            ->where('identifier', $identifier)
            ->where('code', $code)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($otp) {
            $otp->update(['used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if OTP exists and is valid
     */
    public static function hasValidOTP(int $userId, string $type, string $identifier): bool
    {
        return self::where('user_id', $userId)
            ->where('type', $type)
            ->where('identifier', $identifier)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }

    /**
     * Clean up expired OTPs
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', Carbon::now())->delete();
    }
}
