<?php

namespace App\Filament\Resources\LeaderboardEntries\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeaderboardEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('User')->searchable()->sortable(),
                TextColumn::make('match_points')->sortable(),
                TextColumn::make('nomination_points')->sortable(),
                TextColumn::make('total_points')->sortable(),
                TextColumn::make('exact_scores_count')->sortable(),
                TextColumn::make('goal_difference_count')->sortable(),
                TextColumn::make('result_count')->sortable(),
                TextColumn::make('rank')->sortable(),
            ])
            ->filters([
                SelectFilter::make('tournament')
                    ->relationship('tournament', 'name'),
            ])
            ->defaultSort('rank');
    }
}
