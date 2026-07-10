<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaiterShift extends Model
{
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'shift_date',
        'starts_at',
        'ends_at',
        'label',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new Scopes\RestaurantScope);
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timeRangeLabel(): string
    {
        $start = substr((string) $this->starts_at, 0, 5);
        $end = substr((string) $this->ends_at, 0, 5);

        return $start.' – '.$end;
    }
}
