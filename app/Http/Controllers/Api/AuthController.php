<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telegram_username' => ['nullable', 'string', 'max:255', 'unique:users,telegram_username', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:255', 'unique:users,phone', 'required_without:telegram_username'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telegram_username' => $validated['telegram_username'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'is_approved' => false,
        ]);

        return response()->json([
            'message' => __('auth.registration_pending'),
        ], 201);
    }

    public function login(Request $request): array
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.invalid_credentials'),
            ]);
        }

        if (! $user->is_approved) {
            throw ValidationException::withMessages([
                'email' => __('auth.pending_moderation'),
            ]);
        }

        return [
            'token' => $user->createToken('api')->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_approved' => $user->is_approved,
            ],
        ];
    }

    public function user(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'telegram_username' => $user->telegram_username,
            'is_approved' => $user->is_approved,
        ];
    }

    public function logout(Request $request): array
    {
        $token = $request->user()->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return ['message' => __('auth.logged_out')];
    }
}
