<?php

use App\Models\AppSetting;
use App\Models\Battalion;
use App\Models\User;

beforeEach(function () {
    AppSetting::forgetCached();
});

it('allows a super admin to view and update the alert threshold settings', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)->get(route('settings.edit'))->assertOk();

    $this->actingAs($admin)->put(route('settings.update'), [
        'warning_days' => 5,
        'attention_days' => 10,
        'first_contact_days' => 3,
        'visit_days' => 14,
    ])->assertRedirect(route('settings.edit'));

    $setting = AppSetting::current();

    expect($setting->warning_days)->toBe(5)
        ->and($setting->attention_days)->toBe(10)
        ->and($setting->first_contact_days)->toBe(3)
        ->and($setting->visit_days)->toBe(14);
});

it('blocks non super admins from viewing or updating the settings', function () {
    $user = User::factory()->battalionUser(Battalion::factory()->create())->create();

    $this->actingAs($user)->get(route('settings.edit'))->assertForbidden();
    $this->actingAs($user)->put(route('settings.update'), ['warning_days' => 1])->assertForbidden();
});

it('allows clearing a threshold to disable that check', function () {
    $admin = User::factory()->superAdmin()->create();
    AppSetting::current()->update(['warning_days' => 5]);

    $this->actingAs($admin)->put(route('settings.update'), [
        'warning_days' => '',
        'attention_days' => '',
        'first_contact_days' => '',
        'visit_days' => '',
    ])->assertRedirect(route('settings.edit'));

    $setting = AppSetting::current()->fresh();

    expect($setting->warning_days)->toBeNull();
});
