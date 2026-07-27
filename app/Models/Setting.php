<?php

namespace App\Models;

use App\Support\LandingPageContent;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, ?string $value, string $group = 'general'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'group' => $group]
        );

        if ($key === 'whatsapp_bot_number' || str_starts_with($key, 'landing_')) {
            LandingPageContent::flushCache();
        }
    }
}
