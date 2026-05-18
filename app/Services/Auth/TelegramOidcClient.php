<?php

namespace App\Services\Auth;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class TelegramOidcClient
{
    public function authorizationUrl(string $state, string $codeChallenge): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => route('telegram.callback'),
            'response_type' => 'code',
            'scope' => 'openid profile phone',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);

        return $this->authorizationEndpoint().'?'.$query;
    }

    public function userFromAuthorizationCode(string $code, string $redirectUri, string $codeVerifier): TelegramUserData
    {
        $response = Http::asForm()
            ->withBasicAuth($this->clientId(), $this->clientSecret())
            ->timeout(10)
            ->connectTimeout(5)
            ->retry(2, 100)
            ->post($this->tokenEndpoint(), [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'client_id' => $this->clientId(),
                'code_verifier' => $codeVerifier,
            ])
            ->throw()
            ->json();

        $idToken = Arr::get($response, 'id_token');

        if (! is_string($idToken) || $idToken === '') {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_invalid_token'),
            ]);
        }

        return $this->userFromIdToken($idToken);
    }

    public function userFromIdToken(string $idToken): TelegramUserData
    {
        [$header, $payload, $signature, $signedPayload] = $this->decodeJwt($idToken);

        if (($header['alg'] ?? null) !== 'RS256') {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_invalid_token'),
            ]);
        }

        $key = $this->publicKeyFor((string) ($header['kid'] ?? ''));

        if (openssl_verify($signedPayload, $signature, $key, OPENSSL_ALGO_SHA256) !== 1) {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_invalid_token'),
            ]);
        }

        $this->validateClaims($payload);

        return new TelegramUserData(
            id: (string) $payload['id'],
            sub: (string) $payload['sub'],
            name: (string) ($payload['name'] ?? 'Telegram User'),
            username: isset($payload['preferred_username']) ? (string) $payload['preferred_username'] : null,
            photoUrl: isset($payload['picture']) ? (string) $payload['picture'] : null,
            phoneNumber: isset($payload['phone_number']) ? (string) $payload['phone_number'] : null,
        );
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>, 2: string, 3: string}
     */
    private function decodeJwt(string $idToken): array
    {
        $parts = explode('.', $idToken);

        if (count($parts) !== 3) {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_invalid_token'),
            ]);
        }

        try {
            $header = json_decode($this->base64UrlDecode($parts[0]), true, 512, JSON_THROW_ON_ERROR);
            $payload = json_decode($this->base64UrlDecode($parts[1]), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_invalid_token'),
            ]);
        }

        if (! is_array($header) || ! is_array($payload)) {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_invalid_token'),
            ]);
        }

        return [$header, $payload, $this->base64UrlDecode($parts[2]), $parts[0].'.'.$parts[1]];
    }

    private function validateClaims(array $payload): void
    {
        if (($payload['iss'] ?? null) !== 'https://oauth.telegram.org') {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_invalid_token'),
            ]);
        }

        if ((string) ($payload['aud'] ?? '') !== $this->clientId()) {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_invalid_token'),
            ]);
        }

        if (! isset($payload['exp']) || (int) $payload['exp'] <= now()->timestamp) {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_expired_token'),
            ]);
        }

        if (! isset($payload['id'], $payload['sub'])) {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_invalid_token'),
            ]);
        }
    }

    private function publicKeyFor(string $keyId): string
    {
        $keys = Cache::remember('telegram.oidc.jwks', now()->addHours(12), fn (): array => Http::timeout(10)
            ->connectTimeout(5)
            ->retry(2, 100)
            ->get($this->jwksEndpoint())
            ->throw()
            ->json('keys') ?? []);

        $key = collect($keys)->first(fn (array $key): bool => ($key['kid'] ?? null) === $keyId);

        if (! is_array($key) || ($key['kty'] ?? null) !== 'RSA' || ! isset($key['n'], $key['e'])) {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_invalid_token'),
            ]);
        }

        return $this->rsaJwkToPem($key);
    }

    /**
     * @param  array{n: string, e: string}  $key
     */
    private function rsaJwkToPem(array $key): string
    {
        $modulus = $this->encodeDerInteger($this->base64UrlDecode($key['n']));
        $exponent = $this->encodeDerInteger($this->base64UrlDecode($key['e']));
        $rsaPublicKey = $this->encodeDerSequence($modulus.$exponent);
        $rsaEncryptionOid = "\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01";
        $algorithmIdentifier = $this->encodeDerSequence($rsaEncryptionOid."\x05\x00");
        $subjectPublicKey = "\x03".$this->encodeDerLength(strlen($rsaPublicKey) + 1)."\x00".$rsaPublicKey;
        $publicKeyInfo = $this->encodeDerSequence($algorithmIdentifier.$subjectPublicKey);

        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(base64_encode($publicKeyInfo), 64, "\n")
            ."-----END PUBLIC KEY-----\n";
    }

    private function encodeDerSequence(string $value): string
    {
        return "\x30".$this->encodeDerLength(strlen($value)).$value;
    }

    private function encodeDerInteger(string $value): string
    {
        $value = ltrim($value, "\x00");

        if ($value === '') {
            $value = "\x00";
        }

        if ((ord($value[0]) & 0x80) !== 0) {
            $value = "\x00".$value;
        }

        return "\x02".$this->encodeDerLength(strlen($value)).$value;
    }

    private function encodeDerLength(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $encoded = '';

        while ($length > 0) {
            $encoded = chr($length & 0xFF).$encoded;
            $length >>= 8;
        }

        return chr(0x80 | strlen($encoded)).$encoded;
    }

    private function base64UrlDecode(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true);

        if ($decoded === false) {
            throw ValidationException::withMessages([
                'telegram' => __('auth.telegram_invalid_token'),
            ]);
        }

        return $decoded;
    }

    private function clientId(): string
    {
        return (string) config('services.telegram.client_id');
    }

    private function clientSecret(): string
    {
        return (string) config('services.telegram.client_secret');
    }

    private function authorizationEndpoint(): string
    {
        return (string) config('services.telegram.authorization_url');
    }

    private function tokenEndpoint(): string
    {
        return (string) config('services.telegram.token_url');
    }

    private function jwksEndpoint(): string
    {
        return (string) config('services.telegram.jwks_url');
    }
}
