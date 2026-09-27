<?php

use App\Models\Serviceman;
use App\Models\ServicemanPaymentIssue;
use App\Models\User;

it('adds a payment issue and returns to the show page by default', function () {
    $serviceman = Serviceman::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('servicemen.payment-issues.store', $serviceman), [
            'type' => 'monetary_allowance',
            'description' => 'Затримка виплати',
        ])
        ->assertRedirect(route('servicemen.show', $serviceman));

    expect($serviceman->paymentIssues)->toHaveCount(1)
        ->and($serviceman->paymentIssues->first()->status->value)->toBe('in_progress');
});

it('adds a payment issue and returns to the edit page when requested', function () {
    $serviceman = Serviceman::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('servicemen.payment-issues.store', $serviceman), [
            'type' => 'additional_remuneration',
            'description' => 'Не нарахували додаткову винагороду',
            'return_to' => 'edit',
        ])
        ->assertRedirect(route('servicemen.edit', $serviceman));
});

it('toggles a payment issue status and returns to the requested page', function () {
    $serviceman = Serviceman::factory()->create();
    $issue = ServicemanPaymentIssue::factory()->for($serviceman)->create(['status' => 'in_progress']);
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch(route('servicemen.payment-issues.update', [$serviceman, $issue]), [
            'status' => 'completed',
            'return_to' => 'edit',
        ])
        ->assertRedirect(route('servicemen.edit', $serviceman));

    expect($issue->fresh()->status->value)->toBe('completed');
});

it('rejects updating a payment issue that belongs to a different serviceman', function () {
    $serviceman = Serviceman::factory()->create();
    $otherServiceman = Serviceman::factory()->create();
    $issue = ServicemanPaymentIssue::factory()->for($otherServiceman)->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch(route('servicemen.payment-issues.update', [$serviceman, $issue]), ['status' => 'completed'])
        ->assertNotFound();
});

it('fully edits a payment issue\'s type, description and status', function () {
    $serviceman = Serviceman::factory()->create();
    $issue = ServicemanPaymentIssue::factory()->for($serviceman)->create([
        'type' => 'monetary_allowance',
        'description' => 'Стара суть',
        'status' => 'in_progress',
    ]);
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch(route('servicemen.payment-issues.update', [$serviceman, $issue]), [
            'type' => 'additional_remuneration',
            'description' => 'Нова суть проблеми',
            'status' => 'completed',
            'return_to' => 'edit',
        ])
        ->assertRedirect(route('servicemen.edit', $serviceman));

    $issue->refresh();

    expect($issue->type->value)->toBe('additional_remuneration')
        ->and($issue->description)->toBe('Нова суть проблеми')
        ->and($issue->status->value)->toBe('completed');
});

it('deletes a payment issue and returns to the requested page', function () {
    $serviceman = Serviceman::factory()->create();
    $issue = ServicemanPaymentIssue::factory()->for($serviceman)->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->delete(route('servicemen.payment-issues.destroy', [$serviceman, $issue]), ['return_to' => 'edit'])
        ->assertRedirect(route('servicemen.edit', $serviceman));

    expect(ServicemanPaymentIssue::find($issue->id))->toBeNull();
});

it('rejects deleting a payment issue that belongs to a different serviceman', function () {
    $serviceman = Serviceman::factory()->create();
    $otherServiceman = Serviceman::factory()->create();
    $issue = ServicemanPaymentIssue::factory()->for($otherServiceman)->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->delete(route('servicemen.payment-issues.destroy', [$serviceman, $issue]))
        ->assertNotFound();

    expect(ServicemanPaymentIssue::find($issue->id))->not->toBeNull();
});
