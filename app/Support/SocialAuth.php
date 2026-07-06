<?php

namespace App\Support;

class SocialAuth
{
    public const PROVIDER_EMAIL = 'email';

    public const PROVIDER_GOOGLE = 'google';

    public const PROVIDER_FACEBOOK = 'facebook';

    public const PROVIDER_APPLE = 'apple';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_WAITER = 'waiter';

    public const INTENT_LOGIN = 'login';

    public const INTENT_REGISTER = 'register';

    /** @return list<string> */
    public static function activeProviders(): array
    {
        return array_values(array_filter([
            self::providerEnabled(self::PROVIDER_GOOGLE) ? self::PROVIDER_GOOGLE : null,
        ]));
    }

    /** @return list<string> */
    public static function visibleProviders(): array
    {
        return [
            self::PROVIDER_GOOGLE,
            self::PROVIDER_FACEBOOK,
            self::PROVIDER_APPLE,
        ];
    }

    public static function providerEnabled(string $provider): bool
    {
        return match ($provider) {
            self::PROVIDER_GOOGLE => filled(config('services.google.client_id'))
                && filled(config('services.google.client_secret')),
            self::PROVIDER_FACEBOOK => filled(config('services.facebook.client_id'))
                && filled(config('services.facebook.client_secret')),
            self::PROVIDER_APPLE => filled(config('services.apple.client_id'))
                && filled(config('services.apple.client_secret')),
            default => false,
        };
    }

    public static function providerLabel(string $provider): string
    {
        return match ($provider) {
            self::PROVIDER_GOOGLE => 'Google',
            self::PROVIDER_FACEBOOK => 'Facebook',
            self::PROVIDER_APPLE => 'Apple',
            default => ucfirst($provider),
        };
    }

    public static function roleToSpatie(string $role): string
    {
        return match ($role) {
            self::ROLE_MANAGER => 'manager',
            self::ROLE_WAITER => 'waiter',
            default => $role,
        };
    }
}
