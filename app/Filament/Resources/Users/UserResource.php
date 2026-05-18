<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label(__('admin.fields.name')),
                TextEntry::make('telegram_username')->label(__('admin.fields.telegram_username'))->placeholder('-'),
                TextEntry::make('telegram_id')->label(__('admin.fields.telegram_id'))->placeholder('-'),
                TextEntry::make('telegram_sub')->label(__('admin.fields.telegram_sub'))->placeholder('-'),
                TextEntry::make('telegram_photo_url')->label(__('admin.fields.telegram_photo_url'))->placeholder('-')->url(fn (?string $state): ?string => $state),
                TextEntry::make('email')->label(__('admin.fields.email'))->placeholder('-'),
                TextEntry::make('phone')->label(__('admin.fields.phone'))->placeholder('-'),
                TextEntry::make('role')->label(__('admin.fields.role'))->badge(),
                IconEntry::make('is_approved')->label(__('admin.fields.approved'))->boolean(),
                TextEntry::make('created_at')->label(__('admin.fields.created_at'))->dateTime(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.user.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.user.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.user.plural');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
