<?php

use App\Models\User;

it('creates a super admin from command arguments', function () {
    $this->artisan('app:create-super-admin', [
        'name' => 'Нова людина',
        'email' => 'newadmin@example.com',
        'password' => 'secret123',
    ])->assertSuccessful();

    $user = User::where('email', 'newadmin@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Нова людина')
        ->and($user->isSuperAdmin())->toBeTrue();
});

it('rejects a duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->artisan('app:create-super-admin', [
        'name' => 'Дублікат',
        'email' => 'taken@example.com',
        'password' => 'secret123',
    ])->assertFailed();
});

it('rejects a password that is too short', function () {
    $this->artisan('app:create-super-admin', [
        'name' => 'Тест',
        'email' => 'short@example.com',
        'password' => 'short',
    ])->assertFailed();

    expect(User::where('email', 'short@example.com')->exists())->toBeFalse();
});
