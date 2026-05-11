<?php

namespace App\Filament\Resources\NominationCategories\Pages;

use App\Filament\Resources\NominationCategories\NominationCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNominationCategory extends EditRecord
{
    protected static string $resource = NominationCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
