<?php

namespace App\Filament\Resources\NominationResults\Tables;

use App\Actions\RecalculateNominationPointsAction;
use App\Models\NominationResult;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Throwable;

class NominationResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tournament.name')->label(__('admin.fields.tournament'))->sortable()->searchable(),
                TextColumn::make('nominationCategory.name')->label(__('admin.fields.category'))->sortable()->searchable(),
                TextColumn::make('player.name')->label(__('admin.fields.player'))->searchable(),
                TextColumn::make('team.name')->label(__('admin.fields.team'))->searchable(),
                TextColumn::make('value_text')->label(__('admin.fields.value_text'))->searchable(),
                TextColumn::make('value_number')->label(__('admin.fields.value_number'))->sortable(),
            ])
            ->filters([
                SelectFilter::make('tournament')
                    ->label(__('admin.fields.tournament'))
                    ->relationship('tournament', 'name'),
                SelectFilter::make('nominationCategory')
                    ->label(__('admin.fields.nomination_category'))
                    ->relationship('nominationCategory', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('recalculateNominationPoints')
                    ->label(__('admin.actions.recalculate_nomination_points'))
                    ->icon('heroicon-o-calculator')
                    ->requiresConfirmation()
                    ->action(function (NominationResult $record): void {
                        try {
                            app(RecalculateNominationPointsAction::class)->execute($record->tournament_id);

                            Notification::make()
                                ->title(__('admin.notifications.nomination_points_recalculated'))
                                ->success()
                                ->send();
                        } catch (ValidationException|DomainException $exception) {
                            Notification::make()
                                ->title(__('admin.notifications.unable_to_recalculate_nomination_points'))
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title(__('admin.notifications.unable_to_recalculate_nomination_points'))
                                ->body(__('admin.notifications.unexpected_nomination_recalculation_error'))
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
