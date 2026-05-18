<?php

use App\Models\User;
use App\Services\Auth\TelegramOidcClient;
use App\Services\Auth\TelegramUserData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;

function prepareTelegramLoginDatabase(): void
{
    if (! extension_loaded('pdo_sqlite')) {
        test()->markTestSkipped('The pdo_sqlite extension is not available in this environment.');
    }

    test()->artisan('migrate:fresh')->run();
}

beforeEach(function (): void {
    config([
        'services.telegram.client_id' => '123456789',
        'services.telegram.client_secret' => 'secret',
    ]);
});

test('telegram id token verifier accepts signed telegram claims', function () {
    $key = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);

    if ($key === false) {
        test()->markTestSkipped('OpenSSL could not generate an RSA key.');
    }

    $details = openssl_pkey_get_details($key);

    expect($details)->toBeArray();

    Cache::forget('telegram.oidc.jwks');
    Http::fake([
        'https://oauth.telegram.org/.well-known/jwks.json' => Http::response([
            'keys' => [[
                'kty' => 'RSA',
                'kid' => 'test-key',
                'alg' => 'RS256',
                'n' => telegramLoginBase64UrlEncode($details['rsa']['n']),
                'e' => telegramLoginBase64UrlEncode($details['rsa']['e']),
            ]],
        ]),
    ]);

    $header = telegramLoginBase64UrlEncode(json_encode(['alg' => 'RS256', 'kid' => 'test-key']));
    $payload = telegramLoginBase64UrlEncode(json_encode([
        'iss' => 'https://oauth.telegram.org',
        'aud' => '123456789',
        'sub' => '1234123412341234123',
        'iat' => now()->timestamp,
        'exp' => now()->addHour()->timestamp,
        'id' => 987654321,
        'name' => 'Telegram User',
        'preferred_username' => 'telegram_user',
        'picture' => 'https://cdn.example/avatar.jpg',
        'phone_number' => '998901234567',
    ]));
    $signedPayload = $header.'.'.$payload;

    openssl_sign($signedPayload, $signature, $key, OPENSSL_ALGO_SHA256);

    $user = app(TelegramOidcClient::class)->userFromIdToken($signedPayload.'.'.telegramLoginBase64UrlEncode($signature));

    expect($user->id)->toBe('987654321')
        ->and($user->sub)->toBe('1234123412341234123')
        ->and($user->name)->toBe('Telegram User')
        ->and($user->username)->toBe('telegram_user')
        ->and($user->photoUrl)->toBe('https://cdn.example/avatar.jpg')
        ->and($user->phoneNumber)->toBe('998901234567');
});

test('telegram redirect sends users to telegram with state and pkce', function () {
    $response = $this->get(route('telegram.redirect'));

    $response->assertRedirectContains('https://oauth.telegram.org/auth');
    $response->assertRedirectContains('client_id=123456789');
    $response->assertRedirectContains('code_challenge_method=S256');

    expect(session('telegram_login_state'))->toBeString()->not->toBeEmpty()
        ->and(session('telegram_login_code_verifier'))->toBeString()->not->toBeEmpty();
});

test('telegram callback creates a pending user on first login', function () {
    prepareTelegramLoginDatabase();

    $this->mock(TelegramOidcClient::class, function (MockInterface $mock): void {
        $mock->shouldReceive('userFromAuthorizationCode')
            ->once()
            ->with('code', route('telegram.callback'), 'verifier')
            ->andReturn(new TelegramUserData(
                id: '987654321',
                sub: '1234123412341234123',
                name: 'Telegram User',
                username: null,
                photoUrl: 'https://cdn.example/avatar.jpg',
                phoneNumber: null,
            ));
    });

    $this->withSession([
        'telegram_login_state' => 'state',
        'telegram_login_code_verifier' => 'verifier',
    ])->get(route('telegram.callback', ['state' => 'state', 'code' => 'code']))
        ->assertRedirect('/login?telegram_status=pending');

    $user = User::query()->firstOrFail();

    expect($user->telegram_id)->toBe('987654321')
        ->and($user->telegram_sub)->toBe('1234123412341234123')
        ->and($user->telegram_username)->toBeNull()
        ->and($user->telegram_photo_url)->toBe('https://cdn.example/avatar.jpg')
        ->and($user->is_approved)->toBeFalse()
        ->and($user->tokens()->count())->toBe(0);
});

test('telegram callback logs in approved existing users', function () {
    prepareTelegramLoginDatabase();

    $user = User::factory()->create([
        'telegram_id' => '987654321',
        'telegram_sub' => '1234123412341234123',
        'telegram_username' => 'old_username',
        'is_approved' => true,
    ]);

    $this->mock(TelegramOidcClient::class, function (MockInterface $mock): void {
        $mock->shouldReceive('userFromAuthorizationCode')
            ->once()
            ->andReturn(new TelegramUserData(
                id: '987654321',
                sub: '1234123412341234123',
                name: 'Updated Name',
                username: 'new_username',
            ));
    });

    $response = $this->withSession([
        'telegram_login_state' => 'state',
        'telegram_login_code_verifier' => 'verifier',
    ])->get(route('telegram.callback', ['state' => 'state', 'code' => 'code']));

    $response->assertOk();
    $response->assertSee('mundial_token', false);

    expect($user->refresh()->name)->toBe('Updated Name')
        ->and($user->telegram_username)->toBe('new_username')
        ->and($user->tokens()->count())->toBe(1);
});

test('telegram callback rejects invalid state', function () {
    prepareTelegramLoginDatabase();

    $this->mock(TelegramOidcClient::class, function (MockInterface $mock): void {
        $mock->shouldNotReceive('userFromAuthorizationCode');
    });

    $this->withSession([
        'telegram_login_state' => 'state',
        'telegram_login_code_verifier' => 'verifier',
    ])->get(route('telegram.callback', ['state' => 'wrong', 'code' => 'code']))
        ->assertRedirect('/login?telegram_status=failed');

    expect(User::query()->count())->toBe(0);
});

function telegramLoginBase64UrlEncode(string|false $value): string
{
    expect($value)->toBeString();

    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}
