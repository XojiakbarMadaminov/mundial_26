<?php

namespace App\Filament\Resources\Players\Schemas;

use App\Models\Team;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PlayerForm
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
                    ->live()
                    ->required(),
                Select::make('team_id')
                    ->label(__('admin.fields.team'))
                    ->options(fn (Get $get): array => Team::query()
                        ->where('tournament_id', $get('tournament_id'))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload(),
                TextInput::make('name')->label(__('admin.fields.name'))->required()->maxLength(255),
                TextInput::make('position')->label(__('admin.fields.position'))->maxLength(255),
            ]);
    }
}
