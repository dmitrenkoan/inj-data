<?php

namespace Database\Factories;

use App\Models\Serviceman;
use App\Models\ServicemanPaymentIssue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicemanPaymentIssue>
 */
class ServicemanPaymentIssueFactory extends Factory
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
            'type' => 'monetary_allowance',
            'description' => fake()->sentence(),
            'status' => 'in_progress',
        ];
    }
}
