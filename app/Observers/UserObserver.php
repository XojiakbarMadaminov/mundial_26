<?php

namespace App\Observers;

use App\Models\User;
use App\Services\TelegramBotClient;
use Throwable;

class UserObserver
{
    public function __construct(
        private readonly TelegramBotClient $telegramBot,
    ) {
        //
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if (! $user->wasChanged('is_approved') || ! $user->is_approved || (bool) $user->getOriginal('is_approved')) {
            return;
        }

        try {
            $this->telegramBot->sendApprovalMessage($user);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
