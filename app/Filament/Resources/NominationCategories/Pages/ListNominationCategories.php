<?php

namespace App\Filament\Resources\NominationCategories\Pages;

use App\Filament\Resources\NominationCategories\NominationCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNominationCategories extends ListRecords
{
    protected static string $resource = NominationCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
