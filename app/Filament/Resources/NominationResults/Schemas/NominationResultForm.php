<?php

namespace App\Filament\Resources\NominationResults\Schemas;

use App\Models\NominationCategory;
use App\Models\Player;
use App\Models\Team;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class NominationResultForm
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
                Select::make('nomination_category_id')
                    ->label(__('admin.fields.nomination_category'))
                    ->options(fn (Get $get): array => NominationCategory::query()
                        ->where('tournament_id', $get('tournament_id'))
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),
                Select::make('player_id')
                    ->label(__('admin.fields.player'))
                    ->options(fn (Get $get): array => Player::query()
                        ->where('tournament_id', $get('tournament_id'))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => self::categoryType($get) === 'player')
                    ->visible(fn (Get $get): bool => self::categoryType($get) === 'player'),
                Select::make('team_id')
                    ->label(__('admin.fields.team'))
                    ->options(fn (Get $get): array => Team::query()
                        ->where('tournament_id', $get('tournament_id'))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => self::categoryType($get) === 'team')
                    ->visible(fn (Get $get): bool => self::categoryType($get) === 'team'),
                TextInput::make('value_text')
                    ->label(__('admin.fields.value_text'))
                    ->maxLength(255)
                    ->required(fn (Get $get): bool => self::categoryType($get) === 'text')
                    ->visible(fn (Get $get): bool => self::categoryType($get) === 'text'),
                TextInput::make('value_number')
                    ->label(__('admin.fields.value_number'))
                    ->numeric()
                    ->required(fn (Get $get): bool => self::categoryType($get) === 'number')
                    ->visible(fn (Get $get): bool => self::categoryType($get) === 'number'),
            ]);
    }

    private static function categoryType(Get $get): ?string
    {
        $categoryId = $get('nomination_category_id');

        if (! $categoryId) {
            return null;
        }

        return NominationCategory::query()->whereKey($categoryId)->value('type');
    }
}
