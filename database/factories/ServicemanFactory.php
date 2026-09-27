<?php

namespace Database\Factories;

use App\Enums\MaterialAidStatus;
use App\Enums\ServicemanStatus;
use App\Enums\Severity;
use App\Models\Serviceman;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Serviceman>
 */
class ServicemanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'full_name' => fake()->name(),
            'rank' => fake()->randomElement(['солдат', 'молодший сержант', 'сержант', 'лейтенант']),
            'status' => ServicemanStatus::InProgress,
            'evacuation_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'severity' => fake()->randomElement(Severity::cases()),
            'material_aid_status' => MaterialAidStatus::NotApplicable,
        ];
    }
}
