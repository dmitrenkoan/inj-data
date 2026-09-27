<?php

namespace Database\Factories;

use App\Models\Brigade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brigade>
 */
class BrigadeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->numberBetween(1, 200).' окрема бригада',
        ];
    }
}
