<?php

namespace App\Filament\Resources\NominationResults\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NominationResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tournament.name')->sortable()->searchable(),
                TextColumn::make('nominationCategory.name')->label('Category')->sortable()->searchable(),
                TextColumn::make('value_text')->searchable(),
                TextColumn::make('value_number')->sortable(),
            ])
            ->filters([
                SelectFilter::make('tournament')
                    ->relationship('tournament', 'name'),
                SelectFilter::make('nominationCategory')
                    ->relationship('nominationCategory', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
