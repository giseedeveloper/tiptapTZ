<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'wa_id',
        'state',
        'lang',
        'data',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'last_message_at' => 'datetime',
        ];
    }

    /**
     * Normalize an incoming WhatsApp id to digits only (no @s.whatsapp.net suffix).
     */
    public static function normalizeWaId(string $raw): string
    {
        $digits = preg_replace('/[^0-9]/', '', $raw) ?? '';

        return ltrim($digits, '0');
    }

    public static function idleTimeoutHours(): int
    {
        return max(1, (int) config('services.bot.session_idle_hours', 12));
    }

    public function hasRestaurantContext(): bool
    {
        $data = $this->data ?? [];

        return ! empty($data['restaurant_id']);
    }

    public function restaurantNameFromData(): ?string
    {
        $name = ($this->data ?? [])['restaurant_name'] ?? null;

        if (! is_string($name)) {
            return null;
        }

        $trimmed = trim($name);

        return $trimmed !== '' ? $trimmed : null;
    }

    public function isIdleExpired(?\Illuminate\Support\Carbon $now = null): bool
    {
        if ($this->last_message_at === null || ! $this->hasRestaurantContext()) {
            return false;
        }

        $now = $now ?? now();

        return $this->last_message_at->lte(
            $now->copy()->subHours(static::idleTimeoutHours())
        );
    }
}
