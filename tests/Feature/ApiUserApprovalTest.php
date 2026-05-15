<?php

use App\Models\User;

function prepareUserApprovalDatabase(): void
{
    if (! extension_loaded('pdo_sqlite')) {
        test()->markTestSkipped('The pdo_sqlite extension is not available in this environment.');
    }

    test()->artisan('migrate:fresh')->run();
}

test('registered users are pending moderation and are not logged in', function () {
    prepareUserApprovalDatabase();

    $this->postJson('/api/register', [
        'name' => 'Pending User',
        'email' => 'pending@example.com',
        'telegram_username' => 'pending_user',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertCreated()
        ->assertJsonPath('message', "Ma'lumotlaringiz qabul qilindi. Akkauntingiz moderatsiya jarayonida.");

    $user = User::query()->firstOrFail();

    expect($user->is_approved)->toBeFalse();
    expect($user->telegram_username)->toBe('pending_user');
});

test('api registration requires phone or telegram username', function () {
    prepareUserApprovalDatabase();

    $this->postJson('/api/register', [
        'name' => 'Pending User',
        'email' => 'pending@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['telegram_username', 'phone']);
});

test('pending users cannot login until approved', function () {
    prepareUserApprovalDatabase();

    $user = User::query()->create([
        'name' => 'Pending User',
        'email' => 'pending@example.com',
        'password' => 'password',
        'is_approved' => false,
    ]);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $user->update(['is_approved' => true]);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['token', 'user']);
});
