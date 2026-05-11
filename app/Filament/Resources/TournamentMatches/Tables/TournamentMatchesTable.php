<?php

namespace App\Filament\Resources\TournamentMatches\Tables;

use App\Actions\MarkTournamentMatchFinishedAction;
use App\Actions\RecalculateMatchPointsAction;
use App\Models\TournamentMatch;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Throwable;

class TournamentMatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tournament.name')->sortable()->searchable(),
                TextColumn::make('match_number')->sortable(),
                TextColumn::make('homeTeam.name')->label('Home')->searchable(),
                TextColumn::make('awayTeam.name')->label('Away')->searchable(),
                TextColumn::make('stage')->badge()->sortable(),
                TextColumn::make('group_name')->sortable(),
                TextColumn::make('starts_at')->dateTime()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('home_score')->label('Home score'),
                TextColumn::make('away_score')->label('Away score'),
                IconColumn::make('has_penalty')->boolean(),
            ])
            ->filters([
                SelectFilter::make('tournament')
                    ->relationship('tournament', 'name'),
                SelectFilter::make('stage')
                    ->options([
                        'group' => 'Group',
                        'round_32' => 'Round of 32',
                        'round_16' => 'Round of 16',
                        'quarter_final' => 'Quarter-final',
                        'semi_final' => 'Semi-final',
                        'third_place' => 'Third place',
                        'final' => 'Final',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'live' => 'Live',
                        'finished' => 'Finished',
                    ]),
            ])
            ->recordActions([
                Action::make('recalculatePoints')
                    ->label('Recalculate Points')
                    ->icon('heroicon-o-calculator')
                    ->requiresConfirmation()
                    ->action(function (TournamentMatch $record): void {
                        try {
                            app(RecalculateMatchPointsAction::class)->execute($record);

                            Notification::make()
                                ->title('Points recalculated')
                                ->success()
                                ->send();
                        } catch (DomainException|ValidationException $exception) {
                            Notification::make()
                                ->title('Unable to recalculate points')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title('Unable to recalculate points')
                                ->body('An unexpected error occurred.')
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('markAsFinished')
                    ->label('Mark as Finished')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (TournamentMatch $record): bool => $record->status !== 'finished')
                    ->action(function (TournamentMatch $record): void {
                        try {
                            app(MarkTournamentMatchFinishedAction::class)->execute($record);

                            Notification::make()
                                ->title('Match marked as finished')
                                ->success()
                                ->send();
                        } catch (DomainException $exception) {
                            Notification::make()
                                ->title('Unable to finish match')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
