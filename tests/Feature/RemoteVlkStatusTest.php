<?php

use App\Models\Serviceman;
use App\Models\User;

it('stores a remote VLK status for a serviceman treated at a foreign facility', function () {
    $serviceman = Serviceman::factory()->create(['facility_type' => 'foreign']);
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)->put(route('servicemen.update', $serviceman), [
        'unit_id' => $serviceman->unit_id,
        'full_name' => $serviceman->full_name,
        'facility_type' => 'foreign',
        'remote_vlk_status' => 'needs_provision',
    ])->assertRedirect(route('servicemen.show', $serviceman));

    expect($serviceman->fresh()->remote_vlk_status->value)->toBe('needs_provision');
});

it('rejects an invalid remote VLK status value', function () {
    $serviceman = Serviceman::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)->put(route('servicemen.update', $serviceman), [
        'unit_id' => $serviceman->unit_id,
        'full_name' => $serviceman->full_name,
        'remote_vlk_status' => 'not_a_real_status',
    ])->assertSessionHasErrors('remote_vlk_status');
});
