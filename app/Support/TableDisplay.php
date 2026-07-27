<?php

namespace App\Support;

class TableDisplay
{
    public static function label(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '' || strtoupper($value) === 'N/A') {
            return 'Table N/A';
        }

        if (preg_match('/^table\b/i', $value)) {
            $number = trim((string) preg_replace('/^table\s*#?\s*/i', '', $value));

            return $number === '' ? 'Table' : 'Table #'.$number;
        }

        return 'Table #'.$value;
    }
}
