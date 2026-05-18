<?php

namespace App\Services\Auth;

class TelegramUserData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $sub,
        public readonly string $name,
        public readonly ?string $username = null,
        public readonly ?string $photoUrl = null,
        public readonly ?string $phoneNumber = null,
    ) {
        //
    }
}
