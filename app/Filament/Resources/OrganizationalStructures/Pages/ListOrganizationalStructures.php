<?php

namespace App\Filament\Resources\OrganizationalStructures\Pages;

use App\Filament\Resources\OrganizationalStructures\OrganizationalStructureResource;
use App\Models\OrganizationalStructure;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationalStructures extends ListRecords
{
    protected static string $resource = OrganizationalStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Organizational Structure')
                ->visible(fn (): bool => (auth()->user()?->canManageContent() ?? false) && OrganizationalStructure::query()->count() === 0),
        ];
    }
}
