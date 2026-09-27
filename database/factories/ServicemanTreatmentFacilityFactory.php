<?php

namespace Database\Factories;

use App\Models\Serviceman;
use App\Models\ServicemanTreatmentFacility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicemanTreatmentFacility>
 */
class ServicemanTreatmentFacilityFactory extends Factory
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
            'facility_type' => 'mou',
            'city' => fake()->city(),
            'facility_name' => 'Госпіталь №'.fake()->numberBetween(1, 20),
            'changed_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
