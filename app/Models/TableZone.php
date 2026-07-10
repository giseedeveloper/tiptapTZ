<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableZone extends Model
{
    protected $fillable = ["restaurant_id", "name", "sort_order", "supervisor_id"];

    protected static function booted(): void
    {
        static::addGlobalScope(new Scopes\RestaurantScope());
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class, "zone_id");
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, "supervisor_id");
    }
}
