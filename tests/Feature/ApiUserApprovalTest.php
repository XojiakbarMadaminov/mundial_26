<?php

test('public api email registration and login are not exposed', function () {
    $this->postJson('/api/register', [
        'name' => 'Pending User',
        'email' => 'pending@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    $this->postJson('/api/login', [
        'email' => 'pending@example.com',
        'password' => 'password',
    ])->assertNotFound();
});
