<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SubscriptionPackage extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionPackageFactory> */
    use HasFactory;

    public const CAP_KITCHEN = 'kitchen_display';

    public const CAP_PAYMENTS = 'mobile_payments';

    public const CAP_ANALYTICS = 'advanced_analytics';

    /**
     * Capability keys that can be gated, with human labels for the admin UI.
     *
     * @var array<string, string>
     */
    public const CAPABILITIES = [
        self::CAP_KITCHEN => 'Kitchen display',
        self::CAP_PAYMENTS => 'Mobile money payments',
        self::CAP_ANALYTICS => 'Advanced analytics',
    ];

    /**
     * Suggested marketing features the admin can tick when building a plan.
     * Admins may still add custom feature lines on top of these.
     *
     * @var list<string>
     */
    public const FEATURE_CATALOG = [
        'QR ordering',
        'WhatsApp bot (TipTap Rafiki)',
        'Mobile money payments',
        'Kitchen display',
        'Unlimited tables',
        'Unlimited waiters',
        'Basic analytics',
        'Advanced analytics',
    ];

    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'description',
        'price',
        'currency',
        'billing_period',
        'trial_days',
        'table_limit',
        'waiter_limit',
        'features',
        'capabilities',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'trial_days' => 'integer',
            'table_limit' => 'integer',
            'waiter_limit' => 'integer',
            'features' => 'array',
            'capabilities' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function hasCapability(string $capability): bool
    {
        $caps = $this->capabilities;

        // null = never configured → treat as full access (avoid accidental lockout).
        if ($caps === null) {
            return true;
        }

        return in_array($capability, $caps, true);
    }

    protected static function booted(): void
    {
        static::saving(function (SubscriptionPackage $package): void {
            if (blank($package->slug) && filled($package->name)) {
                $package->slug = static::uniqueSlug($package->name, $package->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'plan';
        $slug = $base;
        $counter = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }

    /**
     * @param  Builder<SubscriptionPackage>  $query
     * @return Builder<SubscriptionPackage>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<SubscriptionPackage>  $query
     * @return Builder<SubscriptionPackage>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    public function isFree(): bool
    {
        return (float) $this->price <= 0;
    }

    public function priceLabel(): string
    {
        if ($this->isFree()) {
            return $this->trial_days > 0 ? 'Free' : 'Free';
        }

        return $this->currency.' '.number_format((float) $this->price, 0);
    }

    public function periodLabel(): string
    {
        return match ($this->billing_period) {
            'yearly' => '/ year',
            'trial' => $this->trial_days > 0 ? '/ '.$this->trial_days.' days' : 'trial',
            'one_time' => 'one-time',
            default => '/ month',
        };
    }

    public function tableLimitLabel(): string
    {
        return $this->table_limit === null ? 'Unlimited tables' : 'Up to '.$this->table_limit.' tables';
    }
}
