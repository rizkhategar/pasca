<?php

namespace App\Filament\Resources\ManageAccounts\Pages;

use App\Filament\Resources\ManageAccounts\ManageAccountResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditManageAccount extends EditRecord
{
    protected static string $resource = ManageAccountResource::class;

    protected function afterSave(): void
    {
        Role::findOrCreate($this->record->role);
        $this->record->syncRoles([$this->record->role]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (User $record): bool => auth()->id() !== $record->id),
        ];
    }
}
