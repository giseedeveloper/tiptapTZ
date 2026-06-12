<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRequest extends Model
{
    protected $fillable = [
        'restaurant_id',
        'table_number',
        'table_id',
        'waiter_id',
        'type',
        'status',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\RestaurantScope);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    /**
     * @return list<string>
     */
    public static function invalidTableNumberTokens(): array
    {
        return [
            '-',
            'change_language',
            'call_waiter',
            'rate_service',
            'view_menu',
            'track_order',
            'go_payment',
            'pay_cash',
            'give_tips',
            'customer_support',
            'exit_bot',
            'home',
            'home_more_1',
            'home_more_2',
            'home_back_main',
            'call_only',
            'request_bill',
            'list_waiters',
        ];
    }

    public static function sanitizeTableNumber(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '' || $trimmed === '-') {
            return null;
        }

        $lower = strtolower($trimmed);

        if (in_array($lower, static::invalidTableNumberTokens(), true)) {
            return null;
        }

        if (str_contains($lower, '_') && ! preg_match('/^\d+$/', $lower)) {
            return null;
        }

        return $trimmed;
    }

    public function resolvedTableLabel(): ?string
    {
        $number = static::sanitizeTableNumber($this->table_number);

        if ($number === null && $this->table_id) {
            $table = $this->relationLoaded('table')
                ? $this->table
                : $this->table()->withoutGlobalScopes()->first();
            $number = $table?->name;
        }

        if ($number === null || $number === '') {
            return null;
        }

        return $number;
    }
}
