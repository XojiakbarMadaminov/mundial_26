<?php

namespace App\Filament\Resources\NominationResults;

use App\Filament\Resources\NominationResults\Pages\CreateNominationResult;
use App\Filament\Resources\NominationResults\Pages\EditNominationResult;
use App\Filament\Resources\NominationResults\Pages\ListNominationResults;
use App\Filament\Resources\NominationResults\Schemas\NominationResultForm;
use App\Filament\Resources\NominationResults\Tables\NominationResultsTable;
use App\Models\NominationResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NominationResultResource extends Resource
{
    protected static ?string $model = NominationResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'value_text';

    public static function form(Schema $schema): Schema
    {
        return NominationResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NominationResultsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNominationResults::route('/'),
            'create' => CreateNominationResult::route('/create'),
            'edit' => EditNominationResult::route('/{record}/edit'),
        ];
    }
}
