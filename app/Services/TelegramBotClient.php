<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class TelegramBotClient
{
    public function sendApprovalMessage(User $user): void
    {
        if (blank(config('services.telegram.bot_token')) || blank($user->telegram_id)) {
            return;
        }

        $this->sendMessage(
            chatId: (string) $user->telegram_id,
            text: $this->approvalMessage(),
        );
    }

    public function sendMessage(string $chatId, string $text): void
    {
        Http::timeout(10)
            ->connectTimeout(5)
            ->retry(2, 100)
            ->post($this->apiUrl('sendMessage'), [
                'chat_id' => $chatId,
                'text' => $text,
                'disable_web_page_preview' => true,
            ])
            ->throw();
    }

    private function approvalMessage(): string
    {
        return 'Akkauntingiz admin tomonidan tasdiqlandi. Endi Mundial 26 Predict platformasiga kirishingiz mumkin: '.url('/login');
    }

    private function apiUrl(string $method): string
    {
        return sprintf(
            'https://api.telegram.org/bot%s/%s',
            config('services.telegram.bot_token'),
            $method,
        );
    }
}
