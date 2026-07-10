<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuEngagementSession extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_NOTIFIED = 'notified';

    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = [
        'restaurant_id',
        'table_id',
        'table_number',
        'wa_id',
        'menu_viewed_at',
        'converted_at',
        'notified_at',
        'dismissed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'menu_viewed_at' => 'datetime',
            'converted_at' => 'datetime',
            'notified_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function resolvedTableLabel(): string
    {
        if (filled($this->table_number)) {
            return (string) $this->table_number;
        }

        if ($this->table_id) {
            $table = $this->relationLoaded('table')
                ? $this->table
                : $this->table()->withoutGlobalScopes()->first();

            if ($table?->name) {
                return (string) $table->name;
            }
        }

        return 'Unknown';
    }

    public function minutesSinceView(): int
    {
        return (int) $this->menu_viewed_at->diffInMinutes(now());
    }

    public function alertMessage(int $timeoutMinutes): string
    {
        return sprintf(
            'Customer scanned Menu - Table %s - No order placed after %d minutes.',
            $this->resolvedTableLabel(),
            $timeoutMinutes,
        );
    }
}
