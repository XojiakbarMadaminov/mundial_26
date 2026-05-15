<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('telegram_username')
                    ->label(__('admin.fields.telegram_username'))
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label(__('admin.fields.email'))
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label(__('admin.fields.phone'))
                    ->tel()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label(__('admin.fields.password'))
                    ->password()
                    ->required(fn ($record): bool => $record === null)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->maxLength(255),
                Select::make('role')
                    ->label(__('admin.fields.role'))
                    ->options([
                        'admin' => __('admin.options.admin'),
                        'user' => __('admin.options.user'),
                    ])
                    ->required()
                    ->default('user'),
                Toggle::make('is_approved')
                    ->label(__('admin.fields.approved'))
                    ->default(true),
            ]);
    }
}
