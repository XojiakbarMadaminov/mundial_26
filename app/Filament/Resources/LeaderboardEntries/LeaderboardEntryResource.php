<?php

namespace App\Filament\Resources\LeaderboardEntries;

use App\Filament\Resources\LeaderboardEntries\Pages\ListLeaderboardEntries;
use App\Filament\Resources\LeaderboardEntries\Schemas\LeaderboardEntryForm;
use App\Filament\Resources\LeaderboardEntries\Tables\LeaderboardEntriesTable;
use App\Models\LeaderboardEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LeaderboardEntryResource extends Resource
{
    protected static ?string $model = LeaderboardEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'rank';

    public static function form(Schema $schema): Schema
    {
        return LeaderboardEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeaderboardEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.leaderboard_entry.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.leaderboard_entry.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.leaderboard_entry.plural');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeaderboardEntries::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
