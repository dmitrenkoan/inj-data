<?php

use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\Serviceman;
use App\Models\Unit;
use App\Models\User;

it('lets a super admin create a unit directly under a military unit (brigade), with no battalion', function () {
    $brigade = Brigade::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('units.store'), ['name' => 'Розвідрота', 'brigade_id' => $brigade->id])
        ->assertRedirect(route('units.index'));

    $unit = Unit::where('name', 'Розвідрота')->first();

    expect($unit)->not->toBeNull()
        ->and($unit->battalion_id)->toBeNull()
        ->and($unit->brigade_id)->toBe($brigade->id);
});

it('lets a super admin create a unit under a battalion as before', function () {
    $battalion = Battalion::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('units.store'), ['name' => '1 рота', 'battalion_id' => $battalion->id])
        ->assertRedirect(route('units.index'));

    $unit = Unit::where('name', '1 рота')->first();

    expect($unit->battalion_id)->toBe($battalion->id)
        ->and($unit->brigade_id)->toBeNull();
});

it('requires either a battalion or a military unit when creating a unit', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('units.store'), ['name' => 'Без прив\'язки'])
        ->assertSessionHasErrors(['battalion_id', 'brigade_id']);
});

it('rejects setting both a battalion and a military unit when creating a unit', function () {
    $brigade = Brigade::factory()->create();
    $battalion = Battalion::factory()->create(['brigade_id' => $brigade->id]);
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('units.store'), [
            'name' => 'Конфлікт',
            'battalion_id' => $battalion->id,
            'brigade_id' => $brigade->id,
        ])
        ->assertSessionHasErrors('brigade_id');
});

it('lets a brigade user create a unit directly under their own brigade', function () {
    $brigade = Brigade::factory()->create();
    $user = User::factory()->brigadeUser($brigade)->create();

    $this->actingAs($user)
        ->post(route('units.store'), ['name' => 'Штабна рота', 'brigade_id' => $brigade->id])
        ->assertRedirect(route('units.index'));

    $unit = Unit::where('name', 'Штабна рота')->first();

    expect($unit->brigade_id)->toBe($brigade->id)
        ->and($unit->battalion_id)->toBeNull();
});

it('blocks a brigade user from attaching a unit directly to another brigade', function () {
    $ownBrigade = Brigade::factory()->create();
    $otherBrigade = Brigade::factory()->create();
    $user = User::factory()->brigadeUser($ownBrigade)->create();

    $this->actingAs($user)
        ->post(route('units.store'), ['name' => 'Чужа рота', 'brigade_id' => $otherBrigade->id])
        ->assertSessionHasErrors('brigade_id');
});

it('lets a brigade user see servicemen in units attached directly to their brigade', function () {
    $brigade = Brigade::factory()->create();
    $unit = Unit::factory()->directToBrigade($brigade)->create();
    $serviceman = Serviceman::factory()->create(['unit_id' => $unit->id]);

    $user = User::factory()->brigadeUser($brigade)->create();

    $this->actingAs($user)->get(route('servicemen.show', $serviceman))->assertOk();
});

it('does not let a battalion user see servicemen in units attached directly to their brigade', function () {
    $brigade = Brigade::factory()->create();
    $battalion = Battalion::factory()->create(['brigade_id' => $brigade->id]);
    $directUnit = Unit::factory()->directToBrigade($brigade)->create();
    $serviceman = Serviceman::factory()->create(['unit_id' => $directUnit->id]);

    $user = User::factory()->battalionUser($battalion)->create();

    $this->actingAs($user)->get(route('servicemen.show', $serviceman))->assertForbidden();
});

it('blocks deleting a military unit (brigade) that has units directly attached', function () {
    $brigade = Brigade::factory()->create();
    Unit::factory()->directToBrigade($brigade)->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->delete(route('brigades.destroy', $brigade))
        ->assertRedirect(route('brigades.index'));

    expect(Brigade::find($brigade->id))->not->toBeNull();
});
