<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAbsence extends Model
{
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'starts_on',
        'ends_on',
        'reason',
        'notes',
        'marked_by',
        'reassigned_to_user_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
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

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function reassignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reassigned_to_user_id');
    }

    public function coversDate(\Carbon\CarbonInterface $date): bool
    {
        return $date->between($this->starts_on->startOfDay(), $this->ends_on->endOfDay());
    }
}
