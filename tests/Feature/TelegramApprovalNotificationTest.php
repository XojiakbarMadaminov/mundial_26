<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

function prepareTelegramApprovalNotificationDatabase(): void
{
    if (! extension_loaded('pdo_sqlite')) {
        test()->markTestSkipped('The pdo_sqlite extension is not available in this environment.');
    }

    test()->artisan('migrate:fresh')->run();
}

test('approved telegram users are notified by bot', function () {
    prepareTelegramApprovalNotificationDatabase();

    config([
        'app.url' => 'https://mundial.test',
        'services.telegram.bot_token' => 'bot-token',
    ]);

    Http::fake([
        'https://api.telegram.org/botbot-token/sendMessage' => Http::response(['ok' => true]),
    ]);

    $user = User::factory()->create([
        'telegram_id' => '987654321',
        'is_approved' => false,
    ]);

    $user->update(['is_approved' => true]);

    Http::assertSent(fn ($request): bool => $request->url() === 'https://api.telegram.org/botbot-token/sendMessage'
        && $request['chat_id'] === '987654321'
        && str_contains($request['text'], 'admin tomonidan tasdiqlandi')
        && str_contains($request['text'], 'https://mundial.test/login'));
});

test('approval notification is sent only when status changes to approved', function () {
    prepareTelegramApprovalNotificationDatabase();

    config(['services.telegram.bot_token' => 'bot-token']);

    Http::fake();

    $user = User::factory()->create([
        'telegram_id' => '987654321',
        'is_approved' => true,
    ]);

    $user->update(['name' => 'Updated Name']);

    Http::assertNothingSent();
});

test('approval still succeeds when bot cannot message the user', function () {
    prepareTelegramApprovalNotificationDatabase();

    config(['services.telegram.bot_token' => 'bot-token']);

    Http::fake([
        'https://api.telegram.org/botbot-token/sendMessage' => Http::response(['ok' => false], 403),
    ]);

    $user = User::factory()->create([
        'telegram_id' => '987654321',
        'is_approved' => false,
    ]);

    $user->update(['is_approved' => true]);

    expect($user->refresh()->is_approved)->toBeTrue();
});
