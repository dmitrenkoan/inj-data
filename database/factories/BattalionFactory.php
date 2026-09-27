<?php

namespace Database\Factories;

use App\Models\Battalion;
use App\Models\Brigade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Battalion>
 */
class BattalionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brigade_id' => Brigade::factory(),
            'name' => fake()->numberBetween(1, 9).' батальйон',
        ];
    }
}
