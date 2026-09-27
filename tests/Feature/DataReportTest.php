<?php

use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\Serviceman;
use App\Models\ServicemanAward;
use App\Models\ServicemanPaymentIssue;
use App\Models\Unit;
use App\Models\User;

it('builds the brigade-level report with all metrics', function () {
    $brigade = Brigade::factory()->create(['name' => '81 окрема бригада']);
    $battalion = Battalion::factory()->create(['brigade_id' => $brigade->id]);
    $unit = Unit::factory()->create(['battalion_id' => $battalion->id]);

    $severeInMou = Serviceman::factory()->create([
        'unit_id' => $unit->id,
        'severity' => 'severe',
        'has_amputation' => false,
        'has_prosthetic' => false,
        'facility_type' => 'mou',
        'treatment_status' => 'on_duty',
    ]);
    $amputeeInMoz = Serviceman::factory()->create([
        'unit_id' => $unit->id,
        'severity' => 'light',
        'has_amputation' => true,
        'has_prosthetic' => true,
        'facility_type' => 'moz',
        'treatment_status' => 'treatment',
    ]);
    $needsProsthetic = Serviceman::factory()->create([
        'unit_id' => $unit->id,
        'severity' => 'light',
        'has_amputation' => true,
        'has_prosthetic' => false,
        'facility_type' => 'foreign',
        'treatment_status' => 'treatment',
    ]);

    ServicemanPaymentIssue::factory()->for($severeInMou)->create(['type' => 'additional_remuneration']);
    ServicemanAward::factory()->for($amputeeInMoz)->create();

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('data.report'))
        ->assertInertia(fn ($page) => $page
            ->where('rows.0.brigade', '81 окрема бригада')
            ->where('rows.0.total', 3)
            ->where('rows.0.severe', 1)
            ->where('rows.0.amputated', 2)
            ->where('rows.0.prosthetic', 1)
            ->where('rows.0.needs_prosthetic', 1)
            ->where('rows.0.severe_or_amputated_in_mou', 1)
            ->where('rows.0.severe_or_amputated_in_moz', 1)
            ->where('rows.0.gets_additional_remuneration', 2)
            ->where('rows.0.awarded', 1)
            ->where('rows.0.returned_to_duty', 1)
            ->where('totals.brigade', 'Всього')
            ->where('totals.total', 3)
        );
});

it('scopes the report to the current user\'s battalion', function () {
    $battalion = Battalion::factory()->create();
    $unit = Unit::factory()->create(['battalion_id' => $battalion->id]);
    $otherUnit = Unit::factory()->create();

    Serviceman::factory()->create(['unit_id' => $unit->id]);
    Serviceman::factory()->create(['unit_id' => $otherUnit->id]);

    $user = User::factory()->battalionUser($battalion)->create();

    $this->actingAs($user)
        ->get(route('data.report'))
        ->assertInertia(fn ($page) => $page
            ->has('rows', 1)
            ->where('rows.0.total', 1)
        );
});
