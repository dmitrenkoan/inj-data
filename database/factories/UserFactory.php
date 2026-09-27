<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::Battalion,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SuperAdmin,
            'brigade_id' => null,
            'battalion_id' => null,
        ]);
    }

    public function brigadeUser(?Brigade $brigade = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Brigade,
            'brigade_id' => $brigade?->id ?? Brigade::factory(),
            'battalion_id' => null,
        ]);
    }

    public function battalionUser(?Battalion $battalion = null): static
    {
        return $this->state(function (array $attributes) use ($battalion) {
            $battalion ??= Battalion::factory()->create();

            return [
                'role' => UserRole::Battalion,
                'brigade_id' => $battalion->brigade_id,
                'battalion_id' => $battalion->id,
            ];
        });
    }
}
