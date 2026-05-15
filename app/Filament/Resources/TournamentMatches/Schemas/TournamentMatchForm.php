<?php

namespace App\Filament\Resources\TournamentMatches\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TournamentMatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.sections.match'))
                    ->schema([
                        Select::make('tournament_id')
                            ->label(__('admin.fields.tournament'))
                            ->relationship('tournament', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('match_number')
                            ->label(__('admin.fields.match_number'))
                            ->numeric()
                            ->minValue(1),
                        Select::make('home_team_id')
                            ->label(__('admin.fields.home_team'))
                            ->relationship('homeTeam', 'name')
                            ->searchable()
                            ->live()
                            ->preload(),
                        TextInput::make('home_placeholder')
                            ->label(__('admin.fields.home_placeholder'))
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => blank($get('home_team_id'))),
                        Select::make('away_team_id')
                            ->label(__('admin.fields.away_team'))
                            ->relationship('awayTeam', 'name')
                            ->searchable()
                            ->live()
                            ->preload(),
                        TextInput::make('away_placeholder')
                            ->label(__('admin.fields.away_placeholder'))
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => blank($get('away_team_id'))),
                        Select::make('stage')
                            ->label(__('admin.fields.stage'))
                            ->options([
                                'group' => __('admin.options.group'),
                                'round_32' => __('admin.options.round_32'),
                                'round_16' => __('admin.options.round_16'),
                                'quarter_final' => __('admin.options.quarter_final'),
                                'semi_final' => __('admin.options.semi_final'),
                                'third_place' => __('admin.options.third_place'),
                                'final' => __('admin.options.final'),
                            ])
                            ->required(),
                        TextInput::make('group_name')
                            ->label(__('admin.fields.group_name'))
                            ->maxLength(255),
                        TextInput::make('stadium')
                            ->label(__('admin.fields.stadium'))
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label(__('admin.fields.city'))
                            ->maxLength(255),
                        DateTimePicker::make('starts_at')
                            ->label(__('admin.fields.starts_at'))
                            ->required(),
                        Select::make('status')
                            ->label(__('admin.fields.status'))
                            ->options([
                                'scheduled' => __('admin.options.scheduled'),
                                'live' => __('admin.options.live'),
                                'finished' => __('admin.options.finished'),
                            ])
                            ->disableOptionWhen(fn (string $value, Get $get): bool => $value === 'finished' && (blank($get('home_score')) || blank($get('away_score'))))
                            ->live()
                            ->required()
                            ->default('scheduled'),
                    ])
                    ->columns(2),
                Section::make(__('admin.sections.result'))
                    ->schema([
                        TextInput::make('home_score')
                            ->label(__('admin.fields.home_score'))
                            ->numeric()
                            ->live()
                            ->minValue(0)
                            ->maxValue(30),
                        TextInput::make('away_score')
                            ->label(__('admin.fields.away_score'))
                            ->numeric()
                            ->live()
                            ->minValue(0)
                            ->maxValue(30),
                        Toggle::make('has_penalty')
                            ->label(__('admin.fields.has_penalty'))
                            ->live()
                            ->default(false),
                        TextInput::make('home_penalty_score')
                            ->label(__('admin.fields.home_penalty_score'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(30)
                            ->visible(fn (Get $get): bool => (bool) $get('has_penalty')),
                        TextInput::make('away_penalty_score')
                            ->label(__('admin.fields.away_penalty_score'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(30)
                            ->visible(fn (Get $get): bool => (bool) $get('has_penalty')),
                    ])
                    ->columns(2),
            ]);
    }
}
