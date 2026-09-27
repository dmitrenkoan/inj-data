<?php

use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\User;

it('offers only users of the same battalion as curators to a battalion user', function () {
    $battalion = Battalion::factory()->create();
    $user = User::factory()->battalionUser($battalion)->create();
    $sameBattalionColleague = User::factory()->battalionUser($battalion)->create();
    $otherBattalionUser = User::factory()->battalionUser()->create();

    $this->actingAs($user)
        ->get(route('servicemen.create'))
        ->assertInertia(fn ($page) => $page->has('curators', 2));

    expect(User::availableAsCuratorFor($user)->pluck('id'))
        ->toContain($user->id, $sameBattalionColleague->id)
        ->not->toContain($otherBattalionUser->id);
});

it('offers only users of the same brigade as curators to a brigade user', function () {
    $brigade = Brigade::factory()->create();
    $battalion = Battalion::factory()->create(['brigade_id' => $brigade->id]);
    $user = User::factory()->brigadeUser($brigade)->create();
    $battalionUserInBrigade = User::factory()->battalionUser($battalion)->create();
    $otherBrigadeUser = User::factory()->brigadeUser()->create();

    $ids = User::availableAsCuratorFor($user)->pluck('id');

    expect($ids)->toContain($user->id, $battalionUserInBrigade->id)
        ->not->toContain($otherBrigadeUser->id);
});

it('offers all brigade and battalion users as curators to a super admin', function () {
    $admin = User::factory()->superAdmin()->create();
    $brigadeUser = User::factory()->brigadeUser()->create();
    $battalionUser = User::factory()->battalionUser()->create();

    $ids = User::availableAsCuratorFor($admin)->pluck('id');

    expect($ids)->toContain($brigadeUser->id, $battalionUser->id)
        ->not->toContain($admin->id);
});
