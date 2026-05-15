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
                    ->label(__('admin.fields.tournament'))
                    ->relationship('tournament', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('key')
                    ->label(__('admin.fields.key'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('name')
                    ->label(__('admin.fields.name'))
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label(__('admin.fields.type'))
                    ->options([
                        'player' => __('admin.options.player'),
                        'team' => __('admin.options.team'),
                        'number' => __('admin.options.number'),
                        'text' => __('admin.options.text'),
                    ])
                    ->required(),
                TextInput::make('points')
                    ->label(__('admin.fields.points'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(30),
                TextInput::make('sort_order')
                    ->label(__('admin.fields.sort_order'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
            ]);
    }
}
