<?php

namespace App\Filament\Resources\Tournaments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TournamentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label(__('admin.fields.slug'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                DateTimePicker::make('starts_at')
                    ->label(__('admin.fields.starts_at')),
                DateTimePicker::make('ends_at')
                    ->label(__('admin.fields.ends_at')),
                TextInput::make('prediction_lock_minutes')
                    ->label(__('admin.fields.prediction_lock_minutes'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(120),
                Select::make('status')
                    ->label(__('admin.fields.status'))
                    ->options([
                        'upcoming' => __('admin.options.upcoming'),
                        'active' => __('admin.options.active'),
                        'finished' => __('admin.options.finished'),
                    ])
                    ->required()
                    ->default('upcoming'),
            ]);
    }
}
