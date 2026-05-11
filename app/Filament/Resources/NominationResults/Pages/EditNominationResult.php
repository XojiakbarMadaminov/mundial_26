<?php

namespace App\Filament\Resources\NominationResults\Pages;

use App\Filament\Resources\NominationResults\NominationResultResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNominationResult extends EditRecord
{
    protected static string $resource = NominationResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
