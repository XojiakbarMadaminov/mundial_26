<?php

namespace App\Filament\Resources\Tournaments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TournamentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('admin.fields.name'))->searchable()->sortable(),
                TextColumn::make('slug')->label(__('admin.fields.slug'))->searchable(),
                TextColumn::make('starts_at')->label(__('admin.fields.starts_at'))->dateTime()->sortable(),
                TextColumn::make('status')->label(__('admin.fields.status'))->badge()->sortable(),
                TextColumn::make('prediction_lock_minutes')->label(__('admin.fields.prediction_lock_minutes'))->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.fields.status'))
                    ->options([
                        'upcoming' => __('admin.options.upcoming'),
                        'active' => __('admin.options.active'),
                        'finished' => __('admin.options.finished'),
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
