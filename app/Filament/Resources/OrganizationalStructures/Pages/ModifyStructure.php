<?php

namespace App\Filament\Resources\OrganizationalStructures\Pages;

use App\Filament\Resources\OrganizationalStructures\OrganizationalStructureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class ModifyStructure extends EditRecord
{
    protected static string $resource = OrganizationalStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
