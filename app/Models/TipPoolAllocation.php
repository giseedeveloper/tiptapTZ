<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipPoolAllocation extends Model
{
    protected $fillable = [
        'tip_pool_contribution_id',
        'user_id',
        'tip_id',
        'amount',
        'weight_used',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'weight_used' => 'integer',
        ];
    }

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(TipPoolContribution::class, 'tip_pool_contribution_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tip(): BelongsTo
    {
        return $this->belongsTo(Tip::class);
    }
}
