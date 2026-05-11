<?php

namespace App\Filament\Resources\NominationResults\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NominationResultForm
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
                Select::make('nomination_category_id')
                    ->relationship('nominationCategory', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('value_text')
                    ->maxLength(255),
                TextInput::make('value_number')
                    ->numeric(),
            ]);
    }
}
