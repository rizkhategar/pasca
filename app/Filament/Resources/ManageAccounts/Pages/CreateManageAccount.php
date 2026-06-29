<?php

namespace App\Filament\Resources\ManageAccounts\Pages;

use App\Filament\Resources\ManageAccounts\ManageAccountResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateManageAccount extends CreateRecord
{
    protected static string $resource = ManageAccountResource::class;

    protected function afterCreate(): void
    {
        Role::findOrCreate($this->record->role);
        $this->record->syncRoles([$this->record->role]);
    }
}
