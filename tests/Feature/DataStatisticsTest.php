<?php

use App\Models\AppSetting;
use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\Serviceman;
use App\Models\ServicemanPaymentIssue;
use App\Models\Unit;
use App\Models\User;

beforeEach(function () {
    AppSetting::forgetCached();
});

it('breaks down servicemen by accompaniment status with percentages', function () {
    $unit = Unit::factory()->create();

    Serviceman::factory()->count(3)->create(['unit_id' => $unit->id, 'status' => 'completed']);
    Serviceman::factory()->count(1)->create(['unit_id' => $unit->id, 'status' => 'in_progress']);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('data.statistics'))
        ->assertInertia(fn ($page) => $page
            ->where('total', 4)
            ->where('rows.0.status', 'in_progress')
            ->where('rows.0.count', 1)
            ->where('rows.0.percentage', 25)
            ->where('rows.1.status', 'completed')
            ->where('rows.1.count', 3)
            ->where('rows.1.percentage', 75)
        );
});

it('scopes the statistics page to the current user\'s battalion', function () {
    $battalion = Battalion::factory()->create();
    $unit = Unit::factory()->create(['battalion_id' => $battalion->id]);
    $otherUnit = Unit::factory()->create();

    Serviceman::factory()->create(['unit_id' => $unit->id, 'status' => 'completed']);
    Serviceman::factory()->create(['unit_id' => $otherUnit->id, 'status' => 'completed']);

    $user = User::factory()->battalionUser($battalion)->create();

    $this->actingAs($user)
        ->get(route('data.statistics'))
        ->assertInertia(fn ($page) => $page->where('total', 1));
});

it('filters the statistics page by brigade and battalion', function () {
    $brigadeOne = Brigade::factory()->create();
    $brigadeTwo = Brigade::factory()->create();
    $battalionOne = Battalion::factory()->create(['brigade_id' => $brigadeOne->id]);
    $battalionTwo = Battalion::factory()->create(['brigade_id' => $brigadeOne->id]);
    $battalionThree = Battalion::factory()->create(['brigade_id' => $brigadeTwo->id]);
    $unitOne = Unit::factory()->create(['battalion_id' => $battalionOne->id]);
    $unitTwo = Unit::factory()->create(['battalion_id' => $battalionTwo->id]);
    $unitThree = Unit::factory()->create(['battalion_id' => $battalionThree->id]);

    Serviceman::factory()->create(['unit_id' => $unitOne->id]);
    Serviceman::factory()->create(['unit_id' => $unitTwo->id]);
    Serviceman::factory()->create(['unit_id' => $unitThree->id]);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('data.statistics', ['brigade_id' => $brigadeOne->id]))
        ->assertInertia(fn ($page) => $page->where('total', 2));

    $this->actingAs($admin)
        ->get(route('data.statistics', ['battalion_id' => $battalionOne->id]))
        ->assertInertia(fn ($page) => $page->where('total', 1));
});

it('breaks down servicemen by treatment status with a row for missing status', function () {
    $unit = Unit::factory()->create();

    Serviceman::factory()->count(2)->create(['unit_id' => $unit->id, 'treatment_status' => 'treatment']);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'treatment_status' => 'rehabilitation']);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'treatment_status' => null]);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('data.statistics'))
        ->assertInertia(fn ($page) => $page
            ->where('total', 4)
            ->where('treatmentRows.0.status', 'treatment')
            ->where('treatmentRows.0.count', 2)
            ->where('treatmentRows.0.percentage', 50)
            ->where('treatmentRows.1.status', 'rehabilitation')
            ->where('treatmentRows.1.count', 1)
            ->has('treatmentRows', 9)
            ->where('treatmentRows.8.status', null)
            ->where('treatmentRows.8.label', 'Без статусу')
            ->where('treatmentRows.8.count', 1)
        );
});

it('omits the missing-treatment-status row when every serviceman has a status', function () {
    $unit = Unit::factory()->create();
    Serviceman::factory()->create(['unit_id' => $unit->id, 'treatment_status' => 'treatment']);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('data.statistics'))
        ->assertInertia(fn ($page) => $page->has('treatmentRows', 8));
});

it('breaks down the control checks by count and percentage', function () {
    AppSetting::current()->update(['first_contact_days' => 3, 'attention_days' => 10, 'visit_days' => 5]);

    $unit = Unit::factory()->create();

    Serviceman::factory()->create([
        'unit_id' => $unit->id,
        'evacuation_date' => now()->subDays(30),
        'first_contact_after_evacuation_date' => null,
        'last_contact_date' => now()->subDays(15),
        'last_visit_date' => now()->subDays(10),
    ]);
    Serviceman::factory()->create([
        'unit_id' => $unit->id,
        'evacuation_date' => now()->subDays(30),
        'first_contact_after_evacuation_date' => now()->subDay(),
        'last_contact_date' => now()->subDay(),
        'last_visit_date' => now()->subDay(),
    ]);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('data.statistics'))
        ->assertInertia(fn ($page) => $page
            ->where('total', 2)
            ->where('controls.0.key', 'first_contact')
            ->where('controls.0.needs_count', 1)
            ->where('controls.0.needs_percentage', 50)
            ->where('controls.0.ok_count', 1)
            ->where('controls.1.key', 'attention')
            ->where('controls.1.needs_count', 1)
            ->where('controls.1.ok_count', 1)
            ->where('controls.2.key', 'visit')
            ->where('controls.2.needs_count', 1)
            ->where('controls.2.ok_count', 1)
        );
});

it('breaks down the additional indicators by count and percentage', function () {
    $unit = Unit::factory()->create();

    $amputee = Serviceman::factory()->create(['unit_id' => $unit->id, 'has_amputation' => true, 'has_certificate_5' => true, 'material_aid_status' => 'yes', 'is_combat_veteran' => true]);
    $noCertificate = Serviceman::factory()->create(['unit_id' => $unit->id, 'has_amputation' => false, 'has_certificate_5' => false, 'material_aid_status' => 'yes', 'is_combat_veteran' => true]);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'has_amputation' => false, 'has_certificate_5' => true, 'material_aid_status' => 'no', 'is_combat_veteran' => true]);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'has_amputation' => false, 'has_certificate_5' => true, 'material_aid_status' => 'yes', 'is_combat_veteran' => false, 'treatment_status' => 'treatment']);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'has_amputation' => false, 'has_certificate_5' => true, 'material_aid_status' => 'yes', 'is_combat_veteran' => false, 'treatment_status' => 'awol']);

    ServicemanPaymentIssue::factory()->for($amputee)->create(['type' => 'additional_remuneration']);
    ServicemanPaymentIssue::factory()->for($noCertificate)->create(['type' => 'monetary_allowance']);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('data.statistics'))
        ->assertInertia(fn ($page) => $page
            ->where('total', 5)
            ->where('additionalRows.0.key', 'amputation')
            ->where('additionalRows.0.count', 1)
            ->where('additionalRows.0.percentage', 20)
            ->where('additionalRows.1.key', 'no_certificate_5')
            ->where('additionalRows.1.count', 1)
            ->where('additionalRows.2.key', 'additional_remuneration_issue')
            ->where('additionalRows.2.count', 1)
            ->where('additionalRows.3.key', 'material_aid_needed')
            ->where('additionalRows.3.count', 1)
            ->where('additionalRows.4.key', 'no_combat_veteran_status')
            ->where('additionalRows.4.count', 1)
        );
});

it('breaks down the remote VLK status among servicemen treated abroad', function () {
    $unit = Unit::factory()->create();

    Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_type' => 'foreign', 'remote_vlk_status' => 'needs_provision']);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_type' => 'foreign', 'remote_vlk_status' => 'provided']);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_type' => 'foreign', 'remote_vlk_status' => 'provided']);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_type' => 'moz']);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('data.statistics'))
        ->assertInertia(fn ($page) => $page
            ->where('remoteVlkTotal', 3)
            ->where('remoteVlkRows.0.status', 'needs_provision')
            ->where('remoteVlkRows.0.count', 1)
            ->where('remoteVlkRows.0.percentage', 33.3)
            ->where('remoteVlkRows.1.status', 'provided')
            ->where('remoteVlkRows.1.count', 2)
            ->where('remoteVlkRows.1.percentage', 66.7)
        );
});

it('does not expose the brigade filter option to non super admins', function () {
    $battalion = Battalion::factory()->create();
    $user = User::factory()->battalionUser($battalion)->create();

    $this->actingAs($user)
        ->get(route('data.statistics'))
        ->assertInertia(fn ($page) => $page->where('brigades', []));
});
