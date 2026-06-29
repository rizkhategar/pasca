<?php

namespace App\Filament\Resources\ManageAccounts\Pages;

use App\Filament\Resources\ManageAccounts\ManageAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListManageAccounts extends ListRecords
{
    protected static string $resource = ManageAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add Account'),
        ];
    }
}
