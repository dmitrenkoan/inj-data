<?php

use App\Models\Serviceman;
use App\Models\ServicemanAward;
use App\Models\User;

it('adds an award and returns to the show page by default', function () {
    $serviceman = Serviceman::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('servicemen.awards.store', $serviceman), [
            'name' => 'Медаль «За відвагу»',
            'submission_date' => '2026-01-10',
        ])
        ->assertRedirect(route('servicemen.show', $serviceman));

    expect($serviceman->awards)->toHaveCount(1)
        ->and($serviceman->awards->first()->name)->toBe('Медаль «За відвагу»');
});

it('adds an award and returns to the edit page when requested', function () {
    $serviceman = Serviceman::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('servicemen.awards.store', $serviceman), [
            'name' => 'Орден Богдана Хмельницького',
            'return_to' => 'edit',
        ])
        ->assertRedirect(route('servicemen.edit', $serviceman));
});

it('edits an award\'s name and dates', function () {
    $serviceman = Serviceman::factory()->create();
    $award = ServicemanAward::factory()->for($serviceman)->create([
        'name' => 'Стара назва',
        'awarded_date' => null,
    ]);
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch(route('servicemen.awards.update', [$serviceman, $award]), [
            'name' => 'Нова назва',
            'submission_date' => '2026-02-01',
            'awarded_date' => '2026-03-01',
            'return_to' => 'edit',
        ])
        ->assertRedirect(route('servicemen.edit', $serviceman));

    $award->refresh();

    expect($award->name)->toBe('Нова назва')
        ->and($award->submission_date->toDateString())->toBe('2026-02-01')
        ->and($award->awarded_date->toDateString())->toBe('2026-03-01');
});

it('deletes an award and returns to the requested page', function () {
    $serviceman = Serviceman::factory()->create();
    $award = ServicemanAward::factory()->for($serviceman)->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->delete(route('servicemen.awards.destroy', [$serviceman, $award]), ['return_to' => 'edit'])
        ->assertRedirect(route('servicemen.edit', $serviceman));

    expect(ServicemanAward::find($award->id))->toBeNull();
});

it('rejects editing or deleting an award that belongs to a different serviceman', function () {
    $serviceman = Serviceman::factory()->create();
    $otherServiceman = Serviceman::factory()->create();
    $award = ServicemanAward::factory()->for($otherServiceman)->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch(route('servicemen.awards.update', [$serviceman, $award]), ['name' => 'Хтось'])
        ->assertNotFound();

    $this->actingAs($admin)
        ->delete(route('servicemen.awards.destroy', [$serviceman, $award]))
        ->assertNotFound();

    expect(ServicemanAward::find($award->id))->not->toBeNull();
});
