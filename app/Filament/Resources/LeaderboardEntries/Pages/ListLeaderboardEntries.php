<?php

namespace App\Filament\Resources\LeaderboardEntries\Pages;

use App\Filament\Resources\LeaderboardEntries\LeaderboardEntryResource;
use Filament\Resources\Pages\ListRecords;

class ListLeaderboardEntries extends ListRecords
{
    protected static string $resource = LeaderboardEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
