<?php

use App\Models\AppSetting;
use App\Models\Serviceman;

beforeEach(function () {
    AppSetting::forgetCached();
});

it('flags needs_first_contact when no first contact was logged and the threshold has passed', function () {
    AppSetting::current()->update(['first_contact_days' => 3]);

    $serviceman = Serviceman::factory()->create([
        'evacuation_date' => now()->subDays(10),
        'first_contact_after_evacuation_date' => null,
    ]);

    expect($serviceman->needs_first_contact)->toBeTrue();
});

it('does not flag needs_first_contact once a first contact is logged', function () {
    AppSetting::current()->update(['first_contact_days' => 3]);

    $serviceman = Serviceman::factory()->create([
        'evacuation_date' => now()->subDays(10),
        'first_contact_after_evacuation_date' => now()->subDay(),
    ]);

    expect($serviceman->needs_first_contact)->toBeFalse();
});

it('escalates from warning to attention based on days since the last contact', function () {
    AppSetting::current()->update(['warning_days' => 5, 'attention_days' => 10]);

    $warning = Serviceman::factory()->create([
        'evacuation_date' => now()->subDays(30),
        'last_contact_date' => now()->subDays(7),
    ]);
    $attention = Serviceman::factory()->create([
        'evacuation_date' => now()->subDays(30),
        'last_contact_date' => now()->subDays(15),
    ]);
    $fine = Serviceman::factory()->create([
        'evacuation_date' => now()->subDays(30),
        'last_contact_date' => now()->subDays(2),
    ]);

    expect($warning->needs_warning)->toBeTrue()
        ->and($warning->needs_attention)->toBeFalse()
        ->and($attention->needs_warning)->toBeTrue()
        ->and($attention->needs_attention)->toBeTrue()
        ->and($fine->needs_warning)->toBeFalse()
        ->and($fine->needs_attention)->toBeFalse();
});

it('falls back to the evacuation date for the attention check when there was never a contact', function () {
    AppSetting::current()->update(['attention_days' => 5]);

    $serviceman = Serviceman::factory()->create([
        'evacuation_date' => now()->subDays(10),
        'last_contact_date' => null,
    ]);

    expect($serviceman->needs_attention)->toBeTrue();
});

it('flags needs_visit based on days since the last visit', function () {
    AppSetting::current()->update(['visit_days' => 5]);

    $overdue = Serviceman::factory()->create([
        'evacuation_date' => now()->subDays(30),
        'last_visit_date' => now()->subDays(10),
    ]);
    $recent = Serviceman::factory()->create([
        'evacuation_date' => now()->subDays(30),
        'last_visit_date' => now()->subDays(2),
    ]);

    expect($overdue->needs_visit)->toBeTrue()
        ->and($recent->needs_visit)->toBeFalse();
});

it('disables a check entirely when its threshold is left blank', function () {
    AppSetting::current()->update(['attention_days' => null]);

    $serviceman = Serviceman::factory()->create([
        'evacuation_date' => now()->subYears(2),
        'last_contact_date' => now()->subYears(1),
    ]);

    expect($serviceman->needs_attention)->toBeFalse();
});
