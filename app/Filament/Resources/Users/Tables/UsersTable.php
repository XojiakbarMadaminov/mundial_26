<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('admin.fields.name'))->searchable()->sortable(),
                TextColumn::make('telegram_username')->label(__('admin.fields.telegram_username'))->searchable(),
                TextColumn::make('email')->label(__('admin.fields.email'))->searchable(),
                TextColumn::make('phone')->label(__('admin.fields.phone'))->searchable(),
                TextColumn::make('role')->label(__('admin.fields.role'))->badge()->sortable(),
                IconColumn::make('is_approved')
                    ->label(__('admin.fields.approved'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')->label(__('admin.fields.created_at'))->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label(__('admin.fields.role'))
                    ->options([
                        'admin' => __('admin.options.admin'),
                        'user' => __('admin.options.user'),
                    ]),
                SelectFilter::make('is_approved')
                    ->label(__('admin.fields.approval'))
                    ->options([
                        '1' => __('admin.fields.approved'),
                        '0' => __('admin.options.pending'),
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('admin.actions.approve'))
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => ! $record->is_approved)
                    ->action(function (User $record): void {
                        $record->update(['is_approved' => true]);

                        Notification::make()
                            ->title(__('admin.notifications.user_approved'))
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label(__('admin.actions.approve_selected'))
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_approved' => true]);

                            Notification::make()
                                ->title(__('admin.notifications.selected_users_approved'))
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
