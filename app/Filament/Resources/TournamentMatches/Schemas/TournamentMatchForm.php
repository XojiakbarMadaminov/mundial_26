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
                Section::make('Match')
                    ->schema([
                        Select::make('tournament_id')
                            ->label('Tournament')
                            ->relationship('tournament', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('match_number')
                            ->numeric()
                            ->minValue(1),
                        Select::make('home_team_id')
                            ->label('Home team')
                            ->relationship('homeTeam', 'name')
                            ->searchable()
                            ->live()
                            ->preload(),
                        TextInput::make('home_placeholder')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => blank($get('home_team_id'))),
                        Select::make('away_team_id')
                            ->label('Away team')
                            ->relationship('awayTeam', 'name')
                            ->searchable()
                            ->live()
                            ->preload(),
                        TextInput::make('away_placeholder')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => blank($get('away_team_id'))),
                        Select::make('stage')
                            ->options([
                                'group' => 'Group',
                                'round_32' => 'Round of 32',
                                'round_16' => 'Round of 16',
                                'quarter_final' => 'Quarter-final',
                                'semi_final' => 'Semi-final',
                                'third_place' => 'Third place',
                                'final' => 'Final',
                            ])
                            ->required(),
                        TextInput::make('group_name')
                            ->maxLength(255),
                        TextInput::make('stadium')
                            ->maxLength(255),
                        TextInput::make('city')
                            ->maxLength(255),
                        DateTimePicker::make('starts_at')
                            ->required(),
                        Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'live' => 'Live',
                                'finished' => 'Finished',
                            ])
                            ->required()
                            ->default('scheduled'),
                    ])
                    ->columns(2),
                Section::make('Result')
                    ->schema([
                        TextInput::make('home_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(30),
                        TextInput::make('away_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(30),
                        Toggle::make('has_penalty')
                            ->live()
                            ->default(false),
                        TextInput::make('home_penalty_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(30)
                            ->visible(fn (Get $get): bool => (bool) $get('has_penalty')),
                        TextInput::make('away_penalty_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(30)
                            ->visible(fn (Get $get): bool => (bool) $get('has_penalty')),
                    ])
                    ->columns(2),
            ]);
    }
}
