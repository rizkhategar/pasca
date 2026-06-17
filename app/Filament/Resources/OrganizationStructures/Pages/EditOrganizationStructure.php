<?php

namespace App\Filament\Resources\OrganizationStructures\Pages;

use App\Filament\Resources\OrganizationStructures\OrganizationStructureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationStructure extends EditRecord
{
    protected static string $resource = OrganizationStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
