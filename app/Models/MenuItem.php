<?php

namespace App\Models;

use App\Support\MenuItemEta;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = ['restaurant_id', 'category_id', 'name', 'description', 'price', 'image', 'is_available', 'preparation_time'];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\RestaurantScope);
    }

    public function effectivePreparationMinutes(?Restaurant $restaurant = null): int
    {
        $base = MenuItemEta::minutes($this->preparation_time !== null ? (int) $this->preparation_time : null);

        $restaurant = $restaurant
            ?? ($this->relationLoaded('restaurant') ? $this->restaurant : null);

        if ($restaurant && $restaurant->isBusy()) {
            return MenuItemEta::applyBusy($base, $restaurant->busyEtaMultiplier());
        }

        return $base;
    }

    public function withEta(?Restaurant $restaurant = null): self
    {
        return MenuItemEta::decorate($this, $restaurant);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Image URL for display (works with or without storage:link via storage.serve).
     */
    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return route('storage.serve', ['path' => $this->image]);
    }
}
