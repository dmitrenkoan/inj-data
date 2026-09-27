<?php

use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\Serviceman;
use App\Models\Settlement;
use App\Models\Unit;
use App\Models\User;

it('aggregates servicemen by oblast and city', function () {
    $unit = Unit::factory()->create();
    $lviv = Settlement::factory()->create(['name' => 'Львів', 'oblast' => 'Львівська область']);
    $drohobych = Settlement::factory()->create(['name' => 'Дрогобич', 'oblast' => 'Львівська область']);
    $odesa = Settlement::factory()->create(['name' => 'Одеса', 'oblast' => 'Одеська область']);

    Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_settlement_id' => $lviv->id]);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_settlement_id' => $lviv->id]);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_settlement_id' => $drohobych->id]);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_settlement_id' => $odesa->id]);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_settlement_id' => null]);

    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get(route('data.index'));

    $response->assertInertia(fn ($page) => $page
        ->where('totalCount', 4)
        ->has('oblasts', 2)
        ->where('oblasts.0.oblast', 'Львівська область')
        ->where('oblasts.0.count', 3)
        ->has('oblasts.0.cities', 2)
    );
});

it('scopes the data page to the current user\'s battalion', function () {
    $battalion = Battalion::factory()->create();
    $unit = Unit::factory()->create(['battalion_id' => $battalion->id]);
    $otherUnit = Unit::factory()->create();

    $lviv = Settlement::factory()->create(['name' => 'Львів', 'oblast' => 'Львівська область']);
    $odesa = Settlement::factory()->create(['name' => 'Одеса', 'oblast' => 'Одеська область']);

    Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_settlement_id' => $lviv->id]);
    Serviceman::factory()->create(['unit_id' => $otherUnit->id, 'facility_settlement_id' => $odesa->id]);

    $user = User::factory()->battalionUser($battalion)->create();

    $this->actingAs($user)
        ->get(route('data.index'))
        ->assertInertia(fn ($page) => $page
            ->where('totalCount', 1)
            ->where('oblasts.0.oblast', 'Львівська область')
        );
});

it('filters the data page by brigade', function () {
    $brigadeOne = Brigade::factory()->create();
    $brigadeTwo = Brigade::factory()->create();
    $battalionOne = Battalion::factory()->create(['brigade_id' => $brigadeOne->id]);
    $battalionTwo = Battalion::factory()->create(['brigade_id' => $brigadeTwo->id]);
    $unitOne = Unit::factory()->create(['battalion_id' => $battalionOne->id]);
    $unitTwo = Unit::factory()->create(['battalion_id' => $battalionTwo->id]);

    $lviv = Settlement::factory()->create(['name' => 'Львів', 'oblast' => 'Львівська область']);
    $odesa = Settlement::factory()->create(['name' => 'Одеса', 'oblast' => 'Одеська область']);

    Serviceman::factory()->create(['unit_id' => $unitOne->id, 'facility_settlement_id' => $lviv->id]);
    Serviceman::factory()->create(['unit_id' => $unitTwo->id, 'facility_settlement_id' => $odesa->id]);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('data.index', ['brigade_id' => $brigadeOne->id]))
        ->assertInertia(fn ($page) => $page
            ->where('totalCount', 1)
            ->where('oblasts.0.oblast', 'Львівська область')
        );
});
