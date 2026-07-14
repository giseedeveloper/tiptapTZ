<?php

namespace App\Support;

use App\Models\MenuItem;

class MenuItemEta
{
    public const DEFAULT_MINUTES = 15;

    public static function minutes(?int $preparationTime): int
    {
        $minutes = (int) ($preparationTime ?? self::DEFAULT_MINUTES);

        return $minutes > 0 ? $minutes : self::DEFAULT_MINUTES;
    }

    /**
     * Apply a busy-period multiplier to a base ETA (rounded up to whole minutes).
     */
    public static function applyBusy(int $baseMinutes, float $multiplier): int
    {
        $multiplier = $multiplier >= 1.0 ? $multiplier : 1.0;

        return (int) max($baseMinutes, ceil($baseMinutes * $multiplier));
    }

    /**
     * Attach customer-facing ETA fields without persisting them.
     */
    public static function decorate(MenuItem $item, ?\App\Models\Restaurant $restaurant = null): MenuItem
    {
        $minutes = $item->effectivePreparationMinutes($restaurant);

        $item->setAttribute('eta_minutes', $minutes);
        $item->setAttribute('eta_label', 'Ready in ~'.$minutes.' min');

        return $item;
    }

    /**
     * Order ETA when kitchen prepares cart items in parallel (max prep time).
     *
     * @param  iterable<int, object|array{preparation_time?: int|null, eta_minutes?: int|null}>  $items
     */
    public static function orderMinutes(iterable $items): int
    {
        $max = 0;

        foreach ($items as $item) {
            $value = is_array($item)
                ? ($item['eta_minutes'] ?? $item['preparation_time'] ?? null)
                : ($item->eta_minutes ?? $item->preparation_time ?? null);

            $max = max($max, self::minutes($value !== null ? (int) $value : null));
        }

        return $max > 0 ? $max : self::DEFAULT_MINUTES;
    }
}
