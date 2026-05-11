<?php

namespace App\Filament\Resources\LeaderboardEntries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeaderboardEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('rank')->readOnly(),
                TextInput::make('match_points')->readOnly(),
                TextInput::make('nomination_points')->readOnly(),
                TextInput::make('total_points')->readOnly(),
                TextInput::make('exact_scores_count')->readOnly(),
                TextInput::make('goal_difference_count')->readOnly(),
                TextInput::make('result_count')->readOnly(),
            ]);
    }
}
