<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipPool extends Model
{
    public const METHOD_EQUAL = 'equal';

    public const METHOD_WEIGHTED = 'weighted';

    public const CODE_KITCHEN = 'kitchen';

    protected $fillable = [
        'restaurant_id',
        'name',
        'code',
        'is_enabled',
        'distribution_method',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(TipPoolMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->members()->where('is_active', true)->where('weight', '>', 0);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(TipPoolContribution::class);
    }

    public function isTippable(): bool
    {
        return $this->is_enabled && $this->activeMembers()->exists();
    }

    public static function distributionMethods(): array
    {
        return [
            self::METHOD_EQUAL => 'Equal split among active kitchen staff',
            self::METHOD_WEIGHTED => 'Weighted split (by member weight)',
        ];
    }
}
