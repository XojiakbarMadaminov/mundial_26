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
                    ->label(__('admin.fields.tournament'))
                    ->relationship('tournament', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')->label(__('admin.fields.name'))->required()->maxLength(255),
                TextInput::make('code')->label(__('admin.fields.code'))->maxLength(255),
                TextInput::make('flag')->label(__('admin.fields.flag'))->maxLength(255),
                TextInput::make('group_name')->label(__('admin.fields.group_name'))->maxLength(255),
            ]);
    }
}
