<?php

namespace Database\Factories;

use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'battalion_id' => Battalion::factory(),
            'brigade_id' => null,
            'name' => fake()->numberBetween(1, 9).' рота',
        ];
    }

    /**
     * Attach the unit directly to a military unit (brigade), without a
     * battalion in between.
     */
    public function directToBrigade(?Brigade $brigade = null): static
    {
        return $this->state(fn (array $attributes) => [
            'battalion_id' => null,
            'brigade_id' => $brigade?->id ?? Brigade::factory(),
        ]);
    }
}
