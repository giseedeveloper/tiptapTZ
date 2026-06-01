<?php

namespace App\Services;

use App\Models\Setting;

class SystemPaymentGateway
{
    public const VENDOR_ID_KEY = 'payment_vendor_id';

    public const API_KEY_KEY = 'payment_api_key';

    public const API_SECRET_KEY = 'payment_api_secret';

    public const IS_LIVE_KEY = 'payment_is_live';

    /**
     * @return array{vendor_id: ?string, api_key: ?string, api_secret: ?string, is_live: bool}
     */
    public function credentials(): array
    {
        return [
            'vendor_id' => Setting::get(self::VENDOR_ID_KEY),
            'api_key' => Setting::get(self::API_KEY_KEY),
            'api_secret' => Setting::get(self::API_SECRET_KEY),
            'is_live' => Setting::get(self::IS_LIVE_KEY, '0') === '1',
        ];
    }

    public function isConfigured(): bool
    {
        $credentials = $this->credentials();

        return filled($credentials['vendor_id'])
            && filled($credentials['api_key'])
            && filled($credentials['api_secret']);
    }

    /**
     * @return array<string, string|null>
     */
    public function displayValues(): array
    {
        $credentials = $this->credentials();

        return [
            'vendor_id' => $credentials['vendor_id'],
            'api_key' => $credentials['api_key'],
            'api_secret' => $credentials['api_secret'],
            'is_live' => $credentials['is_live'] ? '1' : '0',
        ];
    }

    /**
     * @param  array{payment_vendor_id: string, payment_api_key: string, payment_api_secret: string, payment_is_live?: mixed}  $data
     */
    public function persist(array $data): void
    {
        Setting::set(self::VENDOR_ID_KEY, $data['payment_vendor_id'], 'payments');
        Setting::set(self::API_KEY_KEY, $data['payment_api_key'], 'payments');
        Setting::set(self::API_SECRET_KEY, $data['payment_api_secret'], 'payments');
        Setting::set(self::IS_LIVE_KEY, ! empty($data['payment_is_live']) ? '1' : '0', 'payments');
    }
}
