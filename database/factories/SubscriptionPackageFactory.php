<?php

namespace Database\Factories;

use App\Models\SubscriptionPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionPackage>
 */
class SubscriptionPackageFactory extends Factory
{
    protected $model = SubscriptionPackage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'tagline' => $this->faker->sentence(4),
            'description' => $this->faker->sentence(10),
            'price' => $this->faker->randomElement([0, 25000, 50000, 120000]),
            'currency' => config('tiptap.currency_code', 'TZS'),
            'billing_period' => 'monthly',
            'trial_days' => 0,
            'table_limit' => $this->faker->randomElement([10, 25, null]),
            'features' => ['QR ordering', 'Basic analytics', 'WhatsApp bot'],
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function free(): static
    {
        return $this->state(fn () => ['price' => 0, 'billing_period' => 'trial', 'trial_days' => 14]);
    }
}
