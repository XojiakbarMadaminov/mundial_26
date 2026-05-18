<?php

namespace App\Http\Controllers\Auth;

use App\Actions\AuthenticateTelegramUserAction;
use App\Http\Controllers\Controller;
use App\Services\Auth\TelegramOidcClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class TelegramLoginController extends Controller
{
    public function redirect(Request $request, TelegramOidcClient $telegram): RedirectResponse
    {
        if (blank(config('services.telegram.client_id')) || blank(config('services.telegram.client_secret'))) {
            return redirect('/login?telegram_status=unconfigured');
        }

        $state = Str::random(40);
        $codeVerifier = Str::random(96);

        $request->session()->put('telegram_login_state', $state);
        $request->session()->put('telegram_login_code_verifier', $codeVerifier);

        return redirect()->away($telegram->authorizationUrl(
            state: $state,
            codeChallenge: $this->codeChallenge($codeVerifier),
        ));
    }

    public function callback(
        Request $request,
        TelegramOidcClient $telegram,
        AuthenticateTelegramUserAction $authenticateTelegramUser,
    ): RedirectResponse|View {
        $state = $request->session()->pull('telegram_login_state');
        $codeVerifier = $request->session()->pull('telegram_login_code_verifier');

        if (! is_string($state) || ! is_string($codeVerifier) || ! hash_equals($state, (string) $request->query('state'))) {
            return redirect('/login?telegram_status=failed');
        }

        $code = (string) $request->query('code');

        if ($code === '') {
            return redirect('/login?telegram_status=failed');
        }

        try {
            $telegramUser = $telegram->userFromAuthorizationCode(
                code: $code,
                redirectUri: route('telegram.callback'),
                codeVerifier: $codeVerifier,
            );

            $user = $authenticateTelegramUser->execute($telegramUser);
        } catch (Throwable $exception) {
            report($exception);

            return redirect('/login?telegram_status=failed');
        }

        if (! $user->is_approved) {
            return redirect('/login?telegram_status=pending');
        }

        return view('auth.telegram-authenticated', [
            'token' => $user->createToken('telegram-login')->plainTextToken,
            'redirectTo' => route('dashboard'),
        ]);
    }

    private function codeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }
}
