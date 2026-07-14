<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipPoolMember extends Model
{
    protected $fillable = [
        'tip_pool_id',
        'user_id',
        'weight',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tipPool(): BelongsTo
    {
        return $this->belongsTo(TipPool::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
