<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';
    
    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Check if token is expired (24 hours)
     */
    public function isExpired(): bool
    {
        return $this->created_at->addHours(24)->isPast();
    }

    /**
     * Generate a new reset token
     */
    public static function generateToken(string $email): string
    {
        // Delete any existing tokens for this email
        self::where('email', $email)->delete();
        
        // Generate new token
        $token = bin2hex(random_bytes(32));
        
        // Store token
        self::create([
            'email' => $email,
            'token' => $token,
            'created_at' => now()
        ]);
        
        return $token;
    }

    /**
     * Validate token
     */
    public static function validateToken(string $email, string $token): bool
    {
        $resetToken = self::where('email', $email)
            ->where('token', $token)
            ->first();
        
        if (!$resetToken) {
            return false;
        }
        
        if ($resetToken->isExpired()) {
            $resetToken->delete();
            return false;
        }
        
        return true;
    }

    /**
     * Delete token after use
     */
    public static function deleteToken(string $email): void
    {
        self::where('email', $email)->delete();
    }
}
