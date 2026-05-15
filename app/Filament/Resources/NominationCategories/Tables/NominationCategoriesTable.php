<?php

namespace App\Filament\Resources\NominationCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NominationCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tournament.name')->label(__('admin.fields.tournament'))->sortable()->searchable(),
                TextColumn::make('key')->label(__('admin.fields.key'))->searchable(),
                TextColumn::make('name')->label(__('admin.fields.name'))->searchable()->sortable(),
                TextColumn::make('type')->label(__('admin.fields.type'))->badge()->sortable(),
                TextColumn::make('points')->label(__('admin.fields.points'))->sortable(),
                TextColumn::make('sort_order')->label(__('admin.fields.sort_order'))->sortable(),
            ])
            ->filters([
                SelectFilter::make('tournament')
                    ->label(__('admin.fields.tournament'))
                    ->relationship('tournament', 'name'),
                SelectFilter::make('type')
                    ->label(__('admin.fields.type'))
                    ->options([
                        'player' => __('admin.options.player'),
                        'team' => __('admin.options.team'),
                        'number' => __('admin.options.number'),
                        'text' => __('admin.options.text'),
                    ]),
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
