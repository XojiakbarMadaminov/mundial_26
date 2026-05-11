<?php

namespace App\Filament\Resources\LeaderboardEntries\Pages;

use App\Filament\Resources\LeaderboardEntries\LeaderboardEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeaderboardEntry extends EditRecord
{
    protected static string $resource = LeaderboardEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
