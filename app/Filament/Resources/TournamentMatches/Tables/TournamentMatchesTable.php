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
                TextColumn::make('tournament.name')->label(__('admin.fields.tournament'))->sortable()->searchable(),
                TextColumn::make('match_number')->label(__('admin.fields.match_number'))->sortable(),
                TextColumn::make('homeTeam.name')->label(__('admin.fields.home_team'))->searchable(),
                TextColumn::make('awayTeam.name')->label(__('admin.fields.away_team'))->searchable(),
                TextColumn::make('stage')->label(__('admin.fields.stage'))->badge()->sortable(),
                TextColumn::make('group_name')->label(__('admin.fields.group'))->sortable(),
                TextColumn::make('starts_at')->label(__('admin.fields.starts_at'))->dateTime()->sortable(),
                TextColumn::make('status')->label(__('admin.fields.status'))->badge()->sortable(),
                TextColumn::make('home_score')->label(__('admin.fields.home_score')),
                TextColumn::make('away_score')->label(__('admin.fields.away_score')),
                IconColumn::make('has_penalty')->label(__('admin.fields.has_penalty'))->boolean(),
            ])
            ->filters([
                SelectFilter::make('tournament')
                    ->label(__('admin.fields.tournament'))
                    ->relationship('tournament', 'name'),
                SelectFilter::make('stage')
                    ->label(__('admin.fields.stage'))
                    ->options([
                        'group' => __('admin.options.group'),
                        'round_32' => __('admin.options.round_32'),
                        'round_16' => __('admin.options.round_16'),
                        'quarter_final' => __('admin.options.quarter_final'),
                        'semi_final' => __('admin.options.semi_final'),
                        'third_place' => __('admin.options.third_place'),
                        'final' => __('admin.options.final'),
                    ]),
                SelectFilter::make('status')
                    ->label(__('admin.fields.status'))
                    ->options([
                        'scheduled' => __('admin.options.scheduled'),
                        'live' => __('admin.options.live'),
                        'finished' => __('admin.options.finished'),
                    ]),
            ])
            ->recordActions([
                Action::make('recalculatePoints')
                    ->label(__('admin.actions.recalculate_points'))
                    ->icon('heroicon-o-calculator')
                    ->requiresConfirmation()
                    ->action(function (TournamentMatch $record): void {
                        try {
                            app(RecalculateMatchPointsAction::class)->execute($record);

                            Notification::make()
                                ->title(__('admin.notifications.points_recalculated'))
                                ->success()
                                ->send();
                        } catch (DomainException|ValidationException $exception) {
                            Notification::make()
                                ->title(__('admin.notifications.unable_to_recalculate_points'))
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title(__('admin.notifications.unable_to_recalculate_points'))
                                ->body(__('admin.notifications.unexpected_error'))
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('markAsFinished')
                    ->label(__('admin.actions.mark_as_finished'))
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (TournamentMatch $record): bool => $record->status !== 'finished')
                    ->action(function (TournamentMatch $record): void {
                        try {
                            app(MarkTournamentMatchFinishedAction::class)->execute($record);

                            Notification::make()
                                ->title(__('admin.notifications.match_marked_as_finished'))
                                ->success()
                                ->send();
                        } catch (DomainException $exception) {
                            Notification::make()
                                ->title(__('admin.notifications.unable_to_finish_match'))
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
