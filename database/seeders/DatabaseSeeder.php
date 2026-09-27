<?php

namespace Database\Seeders;

use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->superAdmin()->create([
            'name' => 'Супер адмін',
            'email' => 'admin@example.com',
        ]);

        $brigade = Brigade::factory()->create(['name' => '1 окрема військова частина']);
        $battalionOne = Battalion::factory()->create(['brigade_id' => $brigade->id, 'name' => '1 батальйон']);
        Battalion::factory()->create(['brigade_id' => $brigade->id, 'name' => '2 батальйон']);

        Unit::factory()->create(['battalion_id' => $battalionOne->id, 'name' => '1 рота']);
        Unit::factory()->create(['battalion_id' => $battalionOne->id, 'name' => '2 рота']);

        User::factory()->brigadeUser($brigade)->create([
            'name' => 'Користувач військової частини',
            'email' => 'brigade@example.com',
        ]);

        User::factory()->battalionUser($battalionOne)->create([
            'name' => 'Користувач батальйону',
            'email' => 'battalion@example.com',
        ]);
    }
}
