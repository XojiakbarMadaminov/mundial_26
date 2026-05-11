<?php

namespace App\Filament\Resources\NominationResults\Pages;

use App\Filament\Resources\NominationResults\NominationResultResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNominationResults extends ListRecords
{
    protected static string $resource = NominationResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
