<?php

use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\Serviceman;
use App\Models\Unit;
use App\Models\User;

it('lets a battalion user see servicemen across all units in their own battalion', function () {
    $battalion = Battalion::factory()->create();
    $unitOne = Unit::factory()->create(['battalion_id' => $battalion->id]);
    $unitTwo = Unit::factory()->create(['battalion_id' => $battalion->id]);
    $otherBattalionUnit = Unit::factory()->create();

    $ownServiceman = Serviceman::factory()->create(['unit_id' => $unitOne->id]);
    $alsoOwnServiceman = Serviceman::factory()->create(['unit_id' => $unitTwo->id]);
    $otherServiceman = Serviceman::factory()->create(['unit_id' => $otherBattalionUnit->id]);

    $user = User::factory()->battalionUser($battalion)->create();

    $this->actingAs($user)->get(route('servicemen.show', $ownServiceman))->assertOk();
    $this->actingAs($user)->get(route('servicemen.show', $alsoOwnServiceman))->assertOk();
    $this->actingAs($user)->get(route('servicemen.show', $otherServiceman))->assertForbidden();
});

it('lets a brigade user see servicemen across all battalions and units in their brigade', function () {
    $brigade = Brigade::factory()->create();
    $battalionOne = Battalion::factory()->create(['brigade_id' => $brigade->id]);
    $battalionTwo = Battalion::factory()->create(['brigade_id' => $brigade->id]);
    $unitOne = Unit::factory()->create(['battalion_id' => $battalionOne->id]);
    $unitTwo = Unit::factory()->create(['battalion_id' => $battalionTwo->id]);
    $otherBrigadeUnit = Unit::factory()->create();

    $inBrigade = Serviceman::factory()->create(['unit_id' => $unitOne->id]);
    $alsoInBrigade = Serviceman::factory()->create(['unit_id' => $unitTwo->id]);
    $outsideBrigade = Serviceman::factory()->create(['unit_id' => $otherBrigadeUnit->id]);

    $user = User::factory()->brigadeUser($brigade)->create();

    $this->actingAs($user)->get(route('servicemen.show', $inBrigade))->assertOk();
    $this->actingAs($user)->get(route('servicemen.show', $alsoInBrigade))->assertOk();
    $this->actingAs($user)->get(route('servicemen.show', $outsideBrigade))->assertForbidden();
});

it('lets a super admin see any serviceman and all management pages', function () {
    $serviceman = Serviceman::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)->get(route('servicemen.show', $serviceman))->assertOk();
    $this->actingAs($admin)->get(route('brigades.index'))->assertOk();
    $this->actingAs($admin)->get(route('battalions.index'))->assertOk();
    $this->actingAs($admin)->get(route('units.index'))->assertOk();
    $this->actingAs($admin)->get(route('users.index'))->assertOk();
});

it('lets a battalion user manage units but not battalions, brigades or users', function () {
    $battalion = Battalion::factory()->create();
    $user = User::factory()->battalionUser($battalion)->create();

    $this->actingAs($user)->get(route('units.index'))->assertOk();
    $this->actingAs($user)->get(route('battalions.index'))->assertForbidden();
    $this->actingAs($user)->get(route('brigades.index'))->assertForbidden();
    $this->actingAs($user)->get(route('users.index'))->assertForbidden();
});

it('rejects creating a serviceman in a unit outside the user\'s scope', function () {
    $battalion = Battalion::factory()->create();
    $otherUnit = Unit::factory()->create();
    $user = User::factory()->battalionUser($battalion)->create();

    $this->actingAs($user)
        ->post(route('servicemen.store'), [
            'unit_id' => $otherUnit->id,
            'full_name' => 'Тест Тестенко',
        ])
        ->assertSessionHasErrors('unit_id');

    expect(Serviceman::count())->toBe(0);
});
