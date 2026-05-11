<?php

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tournament_id')
                    ->relationship('tournament', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('code')->maxLength(255),
                TextInput::make('flag')->maxLength(255),
                TextInput::make('group_name')->maxLength(255),
            ]);
    }
}
