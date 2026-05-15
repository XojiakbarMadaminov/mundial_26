<?php

namespace App\Filament\Resources\NominationCategories;

use App\Filament\Resources\NominationCategories\Pages\CreateNominationCategory;
use App\Filament\Resources\NominationCategories\Pages\EditNominationCategory;
use App\Filament\Resources\NominationCategories\Pages\ListNominationCategories;
use App\Filament\Resources\NominationCategories\Schemas\NominationCategoryForm;
use App\Filament\Resources\NominationCategories\Tables\NominationCategoriesTable;
use App\Models\NominationCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NominationCategoryResource extends Resource
{
    protected static ?string $model = NominationCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return NominationCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NominationCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.nomination_category.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.nomination_category.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.nomination_category.plural');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNominationCategories::route('/'),
            'create' => CreateNominationCategory::route('/create'),
            'edit' => EditNominationCategory::route('/{record}/edit'),
        ];
    }
}
