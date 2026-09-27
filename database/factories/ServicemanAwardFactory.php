<?php

namespace Database\Factories;

use App\Models\Serviceman;
use App\Models\ServicemanAward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicemanAward>
 */
class ServicemanAwardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'serviceman_id' => Serviceman::factory(),
            'name' => 'Медаль «За відвагу»',
            'submission_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'awarded_date' => null,
        ];
    }
}
