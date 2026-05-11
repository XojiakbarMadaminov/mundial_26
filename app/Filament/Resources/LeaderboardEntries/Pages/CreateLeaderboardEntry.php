<?php

namespace App\Filament\Resources\LeaderboardEntries\Pages;

use App\Filament\Resources\LeaderboardEntries\LeaderboardEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLeaderboardEntry extends CreateRecord
{
    protected static string $resource = LeaderboardEntryResource::class;
}
