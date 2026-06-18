<?php

namespace App\Models;

use App\Enums\BotEngagementEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'wa_id',
        'restaurant_id',
        'event_type',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function engagementType(): ?BotEngagementEvent
    {
        return BotEngagementEvent::tryFrom($this->event_type);
    }
}
