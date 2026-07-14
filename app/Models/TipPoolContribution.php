<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipPoolContribution extends Model
{
    protected $fillable = [
        'tip_pool_id',
        'restaurant_id',
        'payment_id',
        'order_id',
        'amount',
        'distribution_method',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function tipPool(): BelongsTo
    {
        return $this->belongsTo(TipPool::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(TipPoolAllocation::class);
    }
}
