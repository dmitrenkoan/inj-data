<?php

use App\Models\Serviceman;
use App\Models\Settlement;
use App\Models\User;

it('computes vlk control dates from the evacuation date', function () {
    $serviceman = Serviceman::factory()->create(['evacuation_date' => '2026-01-15']);

    expect($serviceman->vlk4_months_date->toDateString())->toBe('2026-05-15')
        ->and($serviceman->vlk8_months_date->toDateString())->toBe('2026-09-15')
        ->and($serviceman->vlk12_months_date->toDateString())->toBe('2027-01-15');
});

it('archives the previous treatment facility when it changes', function () {
    $kyiv = Settlement::factory()->create(['name' => 'Київ', 'oblast' => 'м. Київ', 'raion' => null, 'type' => 'місто']);
    $lviv = Settlement::factory()->create(['name' => 'Львів', 'oblast' => 'Львівська область', 'raion' => 'Львівський район', 'type' => 'місто']);

    $serviceman = Serviceman::factory()->create([
        'facility_type' => 'mou',
        'facility_settlement_id' => $kyiv->id,
        'facility_name' => 'Госпіталь №1',
    ]);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)->put(route('servicemen.update', $serviceman), [
        'unit_id' => $serviceman->unit_id,
        'full_name' => $serviceman->full_name,
        'facility_type' => 'moz',
        'facility_settlement_id' => $lviv->id,
        'facility_name' => 'Госпіталь №2',
    ])->assertRedirect(route('servicemen.show', $serviceman));

    $serviceman->refresh();

    expect($serviceman->facility_city)->toBe('Львів')
        ->and($serviceman->treatmentFacilityHistory)->toHaveCount(1)
        ->and($serviceman->treatmentFacilityHistory->first()->city)->toBe('Київ');
});

it('does not log history when the facility fields are unchanged', function () {
    $kyiv = Settlement::factory()->create(['name' => 'Київ', 'oblast' => 'м. Київ', 'raion' => null, 'type' => 'місто']);

    $serviceman = Serviceman::factory()->create([
        'facility_type' => 'mou',
        'facility_settlement_id' => $kyiv->id,
        'facility_name' => 'Госпіталь №1',
    ]);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)->put(route('servicemen.update', $serviceman), [
        'unit_id' => $serviceman->unit_id,
        'full_name' => 'Оновлене ПІБ',
        'facility_type' => 'mou',
        'facility_settlement_id' => $kyiv->id,
        'facility_name' => 'Госпіталь №1',
    ]);

    expect($serviceman->fresh()->treatmentFacilityHistory)->toHaveCount(0);
});
