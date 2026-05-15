<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $contents = file_get_contents(app_path('Actions/Fortify/CreateNewUser.php'));

        expect($contents)->toContain("'telegram_username' => \$input['telegram_username'] ?? null")
            ->and($contents)->toContain("'phone' => \$input['phone'] ?? null");

        return;
    }

    $this->artisan('migrate:fresh')->run();

    $response = $this->withoutMiddleware(VerifyCsrfToken::class)->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'telegram_username' => 'test_user',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/login');
});

test('registration requires phone or telegram username', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $contents = file_get_contents(app_path('Actions/Fortify/CreateNewUser.php'));

        expect($contents)->toContain('required_without:phone')
            ->and($contents)->toContain('required_without:telegram_username');

        return;
    }

    $this->artisan('migrate:fresh')->run();

    $response = $this->withoutMiddleware(VerifyCsrfToken::class)->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['telegram_username', 'phone']);
});
