<?php

namespace App\Filament\Resources\Teams\Tables;

use App\Models\Team;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tournament.name')->label(__('admin.fields.tournament'))->sortable()->searchable(),
                TextColumn::make('name')->label(__('admin.fields.name'))->searchable()->sortable(),
                TextColumn::make('code')->label(__('admin.fields.code'))->searchable(),
                TextColumn::make('group_name')->label(__('admin.fields.group'))->sortable(),
            ])
            ->filters([
                SelectFilter::make('tournament')
                    ->label(__('admin.fields.tournament'))
                    ->relationship('tournament', 'name'),
                SelectFilter::make('group_name')
                    ->label(__('admin.fields.group'))
                    ->options(fn (): array => Team::query()
                        ->whereNotNull('group_name')
                        ->distinct()
                        ->orderBy('group_name')
                        ->pluck('group_name', 'group_name')
                        ->all()),
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
