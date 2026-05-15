<?php

namespace App\Filament\Resources\TournamentMatches\Pages;

use App\Filament\Resources\TournamentMatches\TournamentMatchResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditTournamentMatch extends EditRecord
{
    protected static string $resource = TournamentMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (
            ($data['status'] ?? null) === 'finished'
            && (blank($data['home_score'] ?? null) || blank($data['away_score'] ?? null))
        ) {
            Notification::make()
                ->title(__('admin.notifications.score_required_to_finish_match'))
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'status' => __('admin.notifications.score_required_to_finish_match'),
            ]);
        }

        return $data;
    }
}
