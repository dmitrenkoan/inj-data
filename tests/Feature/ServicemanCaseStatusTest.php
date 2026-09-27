<?php

use App\Models\Serviceman;
use App\Models\Unit;
use App\Models\User;

it('saves the new case status fields when creating a serviceman', function () {
    $unit = Unit::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('servicemen.store'), [
            'unit_id' => $unit->id,
            'full_name' => 'Петренко Петро Петрович',
            'military_status' => 'discharged',
            'is_combat_veteran' => true,
            'treatment_status' => 'rehabilitation',
        ])
        ->assertRedirect();

    $serviceman = Serviceman::first();

    expect($serviceman->military_status->value)->toBe('discharged')
        ->and($serviceman->is_combat_veteran)->toBeTrue()
        ->and($serviceman->treatment_status->value)->toBe('rehabilitation');
});

it('defaults military status to active and combat veteran to false', function () {
    $serviceman = Serviceman::factory()->create()->fresh();

    expect($serviceman->military_status->value)->toBe('active')
        ->and($serviceman->is_combat_veteran)->toBeFalse()
        ->and($serviceman->treatment_status)->toBeNull();
});
