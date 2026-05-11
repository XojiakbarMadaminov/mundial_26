<?php

use App\Models\User;

test('guests can render the dashboard spa shell', function () {
    $response = $this->get(route('dashboard'));

    $response->assertOk();
});

test('authenticated users can render the dashboard spa shell', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('The pdo_sqlite extension is not available in this environment.');
    }

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
