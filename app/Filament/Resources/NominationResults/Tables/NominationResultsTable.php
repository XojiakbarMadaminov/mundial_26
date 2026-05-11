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
                Action::make('recalculateNominationPoints')
                    ->label('Recalculate Nomination Points')
                    ->icon('heroicon-o-calculator')
                    ->requiresConfirmation()
                    ->action(function (NominationResult $record): void {
                        try {
                            app(RecalculateNominationPointsAction::class)->execute($record->tournament_id);

                            Notification::make()
                                ->title('Nomination points recalculated')
                                ->success()
                                ->send();
                        } catch (ValidationException|DomainException $exception) {
                            Notification::make()
                                ->title('Could not recalculate nomination points')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title('Could not recalculate nomination points')
                                ->body('Unexpected error while recalculating nomination points.')
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
