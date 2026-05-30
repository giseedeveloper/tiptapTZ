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
}
