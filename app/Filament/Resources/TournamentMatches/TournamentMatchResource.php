<?php

namespace App\Filament\Resources\TournamentMatches;

use App\Filament\Resources\TournamentMatches\Pages\CreateTournamentMatch;
use App\Filament\Resources\TournamentMatches\Pages\EditTournamentMatch;
use App\Filament\Resources\TournamentMatches\Pages\ListTournamentMatches;
use App\Filament\Resources\TournamentMatches\Schemas\TournamentMatchForm;
use App\Filament\Resources\TournamentMatches\Tables\TournamentMatchesTable;
use App\Models\TournamentMatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TournamentMatchResource extends Resource
{
    protected static ?string $model = TournamentMatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'match_number';

    public static function form(Schema $schema): Schema
    {
        return TournamentMatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TournamentMatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.tournament_match.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.tournament_match.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.tournament_match.plural');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTournamentMatches::route('/'),
            'create' => CreateTournamentMatch::route('/create'),
            'edit' => EditTournamentMatch::route('/{record}/edit'),
        ];
    }
}
