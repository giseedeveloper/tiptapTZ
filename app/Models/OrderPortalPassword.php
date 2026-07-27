<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class OrderPortalPassword extends Model
{
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'password',
        'lookup_hash',
        'generated_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
        $this->attributes['lookup_hash'] = self::passwordLookupHash($value);
    }

    public function checkPassword(string $plain): bool
    {
        return Hash::check($plain, $this->password);
    }

    public static function passwordLookupHash(string $plain): string
    {
        return hash_hmac(
            'sha256',
            strtoupper(trim($plain)),
            (string) config('app.key'),
        );
    }

    public function versionFingerprint(): string
    {
        return hash('sha256', (string) $this->password);
    }

    public static function tokenCacheKey(string $token): string
    {
        return 'order_portal_token:'.hash('sha256', $token);
    }

    /**
     * Generate a random password suitable for display once (e.g. 8 alphanumeric).
     */
    public static function generateRandomPassword(): string
    {
        return strtoupper(bin2hex(random_bytes(4)));
    }
}
