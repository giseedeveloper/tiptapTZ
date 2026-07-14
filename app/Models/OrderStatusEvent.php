<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusEvent extends Model
{
    protected $fillable = [
        'order_id',
        'restaurant_id',
        'from_status',
        'to_status',
        'changed_by',
        'source',
        'duration_seconds',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'duration_seconds' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
