<?php

namespace App\Filament\Resources\NominationCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NominationCategoryForm
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
                TextInput::make('key')
                    ->required()
                    ->maxLength(255),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options([
                        'player' => 'Player',
                        'team' => 'Team',
                        'number' => 'Number',
                        'text' => 'Text',
                    ])
                    ->required(),
                TextInput::make('points')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(30),
                TextInput::make('sort_order')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
            ]);
    }
}
