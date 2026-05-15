<?php

namespace App\Filament\Resources\Players\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PlayersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tournament.name')->label(__('admin.fields.tournament'))->sortable()->searchable(),
                TextColumn::make('name')->label(__('admin.fields.name'))->searchable()->sortable(),
                TextColumn::make('team.name')->label(__('admin.fields.team'))->searchable()->sortable(),
                TextColumn::make('position')->label(__('admin.fields.position'))->searchable(),
            ])
            ->filters([
                SelectFilter::make('tournament')
                    ->label(__('admin.fields.tournament'))
                    ->relationship('tournament', 'name'),
                SelectFilter::make('team')
                    ->label(__('admin.fields.team'))
                    ->relationship('team', 'name'),
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
