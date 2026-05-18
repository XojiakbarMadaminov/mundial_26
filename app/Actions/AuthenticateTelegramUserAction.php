<?php

namespace App\Actions;

use App\Models\User;
use App\Services\Auth\TelegramUserData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthenticateTelegramUserAction
{
    public function execute(TelegramUserData $telegramUser): User
    {
        return DB::transaction(function () use ($telegramUser): User {
            $user = $this->findUser($telegramUser);

            if (! $user) {
                return User::query()->create([
                    'name' => $telegramUser->name,
                    'telegram_id' => $telegramUser->id,
                    'telegram_sub' => $telegramUser->sub,
                    'telegram_username' => $this->availableUsername($telegramUser),
                    'telegram_photo_url' => $telegramUser->photoUrl,
                    'phone' => $this->availablePhoneNumber($telegramUser),
                    'password' => Str::password(48),
                    'role' => 'user',
                    'is_approved' => false,
                ]);
            }

            $user->forceFill([
                'name' => $telegramUser->name,
                'telegram_id' => $telegramUser->id,
                'telegram_sub' => $telegramUser->sub,
                'telegram_username' => $this->availableUsername($telegramUser, $user),
                'telegram_photo_url' => $telegramUser->photoUrl,
                'phone' => $user->phone ?: $this->availablePhoneNumber($telegramUser),
            ])->save();

            return $user;
        });
    }

    private function findUser(TelegramUserData $telegramUser): ?User
    {
        $user = User::query()
            ->where('telegram_id', $telegramUser->id)
            ->lockForUpdate()
            ->first();

        if ($user || ! $telegramUser->username) {
            return $user;
        }

        return User::query()
            ->whereNull('telegram_id')
            ->where('telegram_username', $telegramUser->username)
            ->lockForUpdate()
            ->first();
    }

    private function availablePhoneNumber(TelegramUserData $telegramUser): ?string
    {
        if (! $telegramUser->phoneNumber) {
            return null;
        }

        return User::query()->where('phone', $telegramUser->phoneNumber)->doesntExist()
            ? $telegramUser->phoneNumber
            : null;
    }

    private function availableUsername(TelegramUserData $telegramUser, ?User $currentUser = null): ?string
    {
        if (! $telegramUser->username) {
            return null;
        }

        $query = User::query()->where('telegram_username', $telegramUser->username);

        if ($currentUser) {
            $query->whereKeyNot($currentUser->id);
        }

        return $query->doesntExist() ? $telegramUser->username : null;
    }
}
