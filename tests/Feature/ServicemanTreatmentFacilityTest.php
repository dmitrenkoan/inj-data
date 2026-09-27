<?php

use App\Models\Battalion;
use App\Models\Serviceman;
use App\Models\ServicemanTreatmentFacility;
use App\Models\Settlement;
use App\Models\User;

it('changes the current facility, archives the previous one, and returns to the requested page', function () {
    $kyiv = Settlement::factory()->create(['name' => 'Київ', 'oblast' => 'м. Київ', 'raion' => null, 'type' => 'місто']);
    $lviv = Settlement::factory()->create(['name' => 'Львів', 'oblast' => 'Львівська область', 'raion' => 'Львівський район', 'type' => 'місто']);

    $serviceman = Serviceman::factory()->create([
        'facility_type' => 'mou',
        'facility_settlement_id' => $kyiv->id,
        'facility_name' => 'Госпіталь №1',
    ]);
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch(route('servicemen.facility.update', $serviceman), [
            'facility_type' => 'moz',
            'facility_settlement_id' => $lviv->id,
            'facility_name' => 'Госпіталь №2',
            'return_to' => 'edit',
        ])
        ->assertRedirect(route('servicemen.edit', $serviceman));

    $serviceman->refresh();

    expect($serviceman->facility_type->value)->toBe('moz')
        ->and($serviceman->facility_city)->toBe('Львів')
        ->and($serviceman->treatmentFacilityHistory)->toHaveCount(1)
        ->and($serviceman->treatmentFacilityHistory->first()->city)->toBe('Київ');
});

it('exposes the oblast and raion of the chosen facility settlement', function () {
    $lviv = Settlement::factory()->create(['name' => 'Львів', 'oblast' => 'Львівська область', 'raion' => 'Львівський район', 'type' => 'місто']);
    $serviceman = Serviceman::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)->patch(route('servicemen.facility.update', $serviceman), [
        'facility_settlement_id' => $lviv->id,
    ]);

    expect($serviceman->fresh()->facility_oblast)->toBe('Львівська область')
        ->and($serviceman->fresh()->facility_raion)->toBe('Львівський район');
});

it('rejects a facility_settlement_id that does not exist', function () {
    $serviceman = Serviceman::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch(route('servicemen.facility.update', $serviceman), [
            'facility_settlement_id' => 999999,
        ])
        ->assertSessionHasErrors('facility_settlement_id');

    expect($serviceman->fresh()->facility_settlement_id)->toBeNull();
});

it('returns to the show page by default when changing the facility', function () {
    $serviceman = Serviceman::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch(route('servicemen.facility.update', $serviceman), ['facility_type' => 'moz'])
        ->assertRedirect(route('servicemen.show', $serviceman));
});

it('blocks a battalion user from changing the facility of a serviceman outside their battalion', function () {
    $serviceman = Serviceman::factory()->create();
    $user = User::factory()->battalionUser(Battalion::factory()->create())->create();

    $this->actingAs($user)
        ->patch(route('servicemen.facility.update', $serviceman), ['facility_type' => 'moz'])
        ->assertForbidden();
});

it('updates a treatment facility history entry and returns to the requested page', function () {
    $serviceman = Serviceman::factory()->create();
    $history = ServicemanTreatmentFacility::factory()->for($serviceman)->create([
        'city' => 'Одеса',
    ]);
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch(route('servicemen.treatment-facilities.update', [$serviceman, $history]), [
            'facility_type' => 'moz',
            'city' => 'Дніпро',
            'facility_name' => 'Госпіталь №3',
            'return_to' => 'edit',
        ])
        ->assertRedirect(route('servicemen.edit', $serviceman));

    expect($history->fresh()->city)->toBe('Дніпро');
});

it('deletes a treatment facility history entry and returns to the requested page', function () {
    $serviceman = Serviceman::factory()->create();
    $history = ServicemanTreatmentFacility::factory()->for($serviceman)->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->delete(route('servicemen.treatment-facilities.destroy', [$serviceman, $history]), ['return_to' => 'edit'])
        ->assertRedirect(route('servicemen.edit', $serviceman));

    expect(ServicemanTreatmentFacility::find($history->id))->toBeNull();
});

it('rejects editing a history entry that belongs to a different serviceman', function () {
    $serviceman = Serviceman::factory()->create();
    $otherServiceman = Serviceman::factory()->create();
    $history = ServicemanTreatmentFacility::factory()->for($otherServiceman)->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch(route('servicemen.treatment-facilities.update', [$serviceman, $history]), ['city' => 'Хтось'])
        ->assertNotFound();
});
