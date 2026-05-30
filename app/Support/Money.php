<?php

namespace App\Support;

class Money
{
    public static function symbol(): string
    {
        return (string) config('tiptap.currency_symbol', 'Tsh');
    }

    public static function format(int|float|string|null $amount, int $decimals = 0): string
    {
        $value = is_numeric($amount) ? (float) $amount : 0.0;

        return self::symbol().' '.number_format($value, $decimals);
    }
}
