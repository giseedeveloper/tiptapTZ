<?php

namespace App\Enums;

enum BotQrEntryType: string
{
    case Waiter = 'qr_waiter';
    case Table = 'qr_table';
    case Restaurant = 'qr_restaurant';

    public function label(): string
    {
        return match ($this) {
            self::Waiter => 'Waiter QR',
            self::Table => 'Table QR',
            self::Restaurant => 'Restaurant Tag',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Waiter => '#8C71F6',
            self::Table => '#06b6d4',
            self::Restaurant => '#10b981',
        };
    }

    public static function fromParseType(string $type): ?self
    {
        return match ($type) {
            'waiter' => self::Waiter,
            'table' => self::Table,
            'restaurant' => self::Restaurant,
            default => null,
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
