<?php

use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\Serviceman;
use App\Models\Settlement;
use App\Models\Unit;
use App\Models\User;

it('filters the servicemen list by military status, treatment status, and amputation flag', function () {
    $unit = Unit::factory()->create();

    $matching = Serviceman::factory()->create([
        'unit_id' => $unit->id,
        'military_status' => 'active',
        'treatment_status' => 'rehabilitation',
        'has_amputation' => true,
    ]);
    Serviceman::factory()->create([
        'unit_id' => $unit->id,
        'military_status' => 'discharged',
        'treatment_status' => 'treatment',
        'has_amputation' => false,
    ]);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('servicemen.index', [
            'military_status' => 'active',
            'treatment_status' => 'rehabilitation',
            'has_amputation' => '1',
        ]))
        ->assertInertia(fn ($page) => $page
            ->has('servicemen.data', 1)
            ->where('servicemen.data.0.id', $matching->id)
        );
});

it('filters the servicemen list by evacuation date range', function () {
    $unit = Unit::factory()->create();

    $inRange = Serviceman::factory()->create(['unit_id' => $unit->id, 'evacuation_date' => '2026-03-15']);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'evacuation_date' => '2026-01-01']);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'evacuation_date' => '2026-06-01']);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('servicemen.index', [
            'evacuation_date_from' => '2026-02-01',
            'evacuation_date_to' => '2026-04-01',
        ]))
        ->assertInertia(fn ($page) => $page
            ->has('servicemen.data', 1)
            ->where('servicemen.data.0.id', $inRange->id)
        );
});

it('filters the servicemen list by treatment oblast and settlement', function () {
    $unit = Unit::factory()->create();
    $lviv = Settlement::factory()->create(['name' => 'Львів', 'oblast' => 'Львівська область']);
    $odesa = Settlement::factory()->create(['name' => 'Одеса', 'oblast' => 'Одеська область']);

    $matching = Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_settlement_id' => $lviv->id]);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'facility_settlement_id' => $odesa->id]);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('servicemen.index', ['facility_oblast' => 'Львівська область']))
        ->assertInertia(fn ($page) => $page
            ->has('servicemen.data', 1)
            ->where('servicemen.data.0.id', $matching->id)
        );

    $this->actingAs($admin)
        ->get(route('servicemen.index', ['facility_settlement_id' => $lviv->id]))
        ->assertInertia(fn ($page) => $page
            ->has('servicemen.data', 1)
            ->where('servicemen.data.0.id', $matching->id)
        );
});

it('sorts the servicemen list by evacuation date', function () {
    $unit = Unit::factory()->create();

    $early = Serviceman::factory()->create(['unit_id' => $unit->id, 'full_name' => 'A', 'evacuation_date' => '2026-01-01']);
    $late = Serviceman::factory()->create(['unit_id' => $unit->id, 'full_name' => 'B', 'evacuation_date' => '2026-06-01']);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('servicemen.index', ['sort' => 'evacuation_date', 'direction' => 'desc']))
        ->assertInertia(fn ($page) => $page
            ->where('sort', 'evacuation_date')
            ->where('direction', 'desc')
            ->where('servicemen.data.0.id', $late->id)
            ->where('servicemen.data.1.id', $early->id)
        );

    $this->actingAs($admin)
        ->get(route('servicemen.index', ['sort' => 'evacuation_date', 'direction' => 'asc']))
        ->assertInertia(fn ($page) => $page
            ->where('servicemen.data.0.id', $early->id)
            ->where('servicemen.data.1.id', $late->id)
        );
});

it('falls back to sorting by full name for an unknown sort column', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('servicemen.index', ['sort' => 'notes']))
        ->assertInertia(fn ($page) => $page->where('sort', 'full_name'));
});

it('allows a super admin to filter the servicemen list by brigade', function () {
    $brigadeOne = Brigade::factory()->create();
    $brigadeTwo = Brigade::factory()->create();
    $battalionOne = Battalion::factory()->create(['brigade_id' => $brigadeOne->id]);
    $battalionTwo = Battalion::factory()->create(['brigade_id' => $brigadeTwo->id]);
    $unitOne = Unit::factory()->create(['battalion_id' => $battalionOne->id]);
    $unitTwo = Unit::factory()->create(['battalion_id' => $battalionTwo->id]);

    $matching = Serviceman::factory()->create(['unit_id' => $unitOne->id]);
    Serviceman::factory()->create(['unit_id' => $unitTwo->id]);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('servicemen.index', ['brigade_id' => $brigadeOne->id]))
        ->assertInertia(fn ($page) => $page
            ->has('servicemen.data', 1)
            ->where('servicemen.data.0.id', $matching->id)
        );
});

it('does not expose the brigade filter option to non super admins', function () {
    $battalion = Battalion::factory()->create();
    $user = User::factory()->battalionUser($battalion)->create();

    $this->actingAs($user)
        ->get(route('servicemen.index'))
        ->assertInertia(fn ($page) => $page->where('brigades', []));
});

it('filters the servicemen list by curator', function () {
    $unit = Unit::factory()->create();
    $admin = User::factory()->superAdmin()->create();
    $curator = User::factory()->superAdmin()->create();

    $matching = Serviceman::factory()->create(['unit_id' => $unit->id, 'curator_id' => $curator->id]);
    Serviceman::factory()->create(['unit_id' => $unit->id, 'curator_id' => null]);

    $this->actingAs($admin)
        ->get(route('servicemen.index', ['curator_id' => $curator->id]))
        ->assertInertia(fn ($page) => $page
            ->has('servicemen.data', 1)
            ->where('servicemen.data.0.id', $matching->id)
        );
});
