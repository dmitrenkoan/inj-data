<?php

namespace Database\Factories;

use App\Models\Settlement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Settlement>
 */
class SettlementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'raion' => fake()->city().'ський',
            'oblast' => fake()->randomElement(['Львівська область', 'Одеська область', 'Київська область']),
            'type' => 'місто',
        ];
    }
}
