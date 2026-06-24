<?php

namespace Database\Factories;

use App\Models\LandingLead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LandingLead>
 */
class LandingLeadFactory extends Factory
{
    protected $model = LandingLead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'market' => config('tiptap.market', 'tz'),
            'source' => 'efficiency_guide',
            'ip_address' => fake()->ipv4(),
        ];
    }
}
